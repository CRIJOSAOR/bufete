<?php

namespace Tests\Feature;

use App\Models\Caso;
use App\Models\Cliente;
use App\Models\FondoClienteMovimiento;
use App\Models\Gasto;
use App\Models\Honorario;
use App\Models\Pago;
use App\Models\RoleDataScope;
use App\Models\User;
use App\Support\AccountingLedger;
use App\Support\AccountingMoney;
use App\Support\RecordAccessScope;
use App\Support\ContabilidadPdf;
use App\Filament\Widgets\ContabilidadResumenWidget;
use App\Filament\Resources\Roles\RoleResource;
use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AccountingModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_partial_payments_update_the_honorarium_balance_without_exceeding_the_agreement(): void
    {
        $client = $this->createClient();
        $fee = $this->createHonorarium($client, '100.00', 'HNL');
        $user = $this->userWithAccountingPermissions(['Create:Pago', 'ViewAny:Pago']);
        $this->actingAs($user);

        $payment = app(AccountingLedger::class)->registerPayment([
            'monto' => '35.50',
            'fecha_pago' => now(),
            'metodo' => 'transferencia',
        ] + ['honorario_id' => $fee->getKey()]);

        $this->assertModelExists($payment);
        $this->assertSame('35.50', $payment->monto);
        $this->assertSame('64.50', $fee->fresh()->saldo);

        try {
            app(AccountingLedger::class)->registerPayment([
                'honorario_id' => $fee->getKey(),
                'monto' => '64.51',
                'fecha_pago' => now(),
                'metodo' => 'efectivo',
            ]);
            $this->fail('The payment exceeded the agreed fee.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('monto', $exception->errors());
        }

        $this->assertDatabaseCount('pagos', 1);
    }

    public function test_fractional_payment_totals_are_summed_without_losing_cents(): void
    {
        $client = $this->createClient();
        $fee = $this->createHonorarium($client, '1.00', 'HNL');
        $this->actingAs($this->userWithAccountingPermissions(['Create:Pago']));
        $ledger = app(AccountingLedger::class);

        foreach (['0.10', '0.20'] as $amount) {
            $ledger->registerPayment([
                'honorario_id' => $fee->getKey(),
                'monto' => $amount,
                'fecha_pago' => now(),
                'metodo' => 'efectivo',
            ]);
        }

        $fee = $fee->fresh();

        $this->assertSame('0.30', $fee->total_pagado);
        $this->assertSame('0.70', $fee->saldo);
        $this->assertSame('0.30', Honorario::query()->withTotalPagado()->findOrFail($fee->getKey())->total_pagado);
        $this->assertSame('999999999999.99', AccountingMoney::formatMinorUnits(99999999999999));
    }

    public function test_client_funds_keep_currency_balances_separate_and_reject_overdrafts(): void
    {
        $client = $this->createClient();
        $this->actingAs($this->userWithAccountingPermissions(['Create:FondoClienteMovimiento', 'ViewAny:FondoClienteMovimiento']));
        $ledger = app(AccountingLedger::class);

        $ledger->registerClientFundMovement($this->fundMovement($client, 'ingreso', '100.00', 'HNL'));
        $ledger->registerClientFundMovement($this->fundMovement($client, 'ingreso', '50.00', 'USD'));

        try {
            $ledger->registerClientFundMovement($this->fundMovement($client, 'egreso', '100.01', 'HNL'));
            $this->fail('The client-fund withdrawal exceeded the balance.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('monto', $exception->errors());
        }

        $ledger->registerClientFundMovement($this->fundMovement($client, 'egreso', '70.00', 'HNL'));

        $this->assertDatabaseCount('fondos_cliente_movimientos', 3);
        $this->assertSame('30.00', $this->clientFundsBalance($client, 'HNL'));
        $this->assertSame('50.00', $this->clientFundsBalance($client, 'USD'));
    }

    public function test_expense_case_must_belong_to_the_selected_client(): void
    {
        $client = $this->createClient('Cliente A');
        $otherClient = $this->createClient('Cliente B');
        $case = $this->createCase($client);
        $user = $this->userWithAccountingPermissions(['Create:Gasto']);
        $this->actingAs($user);

        try {
            Gasto::create([
                'cliente_id' => $otherClient->getKey(),
                'caso_id' => $case->getKey(),
                'concepto' => 'Gasto de prueba',
                'categoria' => 'tasas',
                'monto' => '10.00',
                'moneda' => 'HNL',
                'fecha' => now()->toDateString(),
            ]);
            $this->fail('A case expense was linked to a different client.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('caso_id', $exception->errors());
        }

        $this->assertDatabaseCount('gastos', 0);
    }

    public function test_accounting_receipts_are_downloaded_only_by_users_with_record_access(): void
    {
        Storage::fake('accounting_files');
        $client = $this->createClient();
        $path = 'gastos/recibo.png';
        Storage::disk('accounting_files')->put($path, 'receipt-image');
        $expense = Gasto::create([
            'cliente_id' => $client->getKey(),
            'concepto' => 'Copias',
            'categoria' => 'copias',
            'monto' => '12.00',
            'moneda' => 'HNL',
            'fecha' => now()->toDateString(),
            'comprobante_ruta' => $path,
            'comprobante_original' => 'recibo.png',
            'comprobante_mime' => 'image/png',
            'comprobante_tamano' => 13,
        ]);
        $user = $this->userWithAccountingPermissions(['View:Gasto', 'ViewAny:Gasto']);

        $this->actingAs($user)
            ->get(route('contabilidad.comprobantes.download', ['type' => 'gasto', 'record' => $expense->getKey()]))
            ->assertDownload('recibo.png');

        $otherUser = User::factory()->create();
        $this->actingAs($otherUser)
            ->get(route('contabilidad.comprobantes.download', ['type' => 'gasto', 'record' => $expense->getKey()]))
            ->assertNotFound();
    }

    public function test_fund_movements_cannot_be_edited_after_registration(): void
    {
        $client = $this->createClient();
        $movement = FondoClienteMovimiento::create(
            $this->fundMovement($client, 'ingreso', '20.00', 'USD'),
        );

        $this->expectException(\LogicException::class);

        $movement->update(['monto' => '1.00']);
    }

    public function test_financial_dashboard_is_only_visible_with_the_reporting_permission(): void
    {
        $user = $this->userWithAccountingPermissions(['consultar_reportes_contables']);

        $this->actingAs($user);
        $this->assertTrue(ContabilidadResumenWidget::canView());

        $this->actingAs(User::factory()->create());
        $this->assertFalse(ContabilidadResumenWidget::canView());
    }

    public function test_financial_dashboard_reports_amounts_by_currency(): void
    {
        $client = $this->createClient();
        $fee = $this->createHonorarium($client, '100.00', 'HNL');
        $user = $this->userWithAccountingPermissions([
            'consultar_reportes_contables',
            'Create:Pago',
            'Create:Gasto',
            'Create:FondoClienteMovimiento',
        ]);
        $this->actingAs($user);
        app(AccountingLedger::class)->registerPayment([
            'honorario_id' => $fee->getKey(),
            'monto' => '30.00',
            'fecha_pago' => now(),
            'metodo' => 'efectivo',
        ]);
        Gasto::create([
            'cliente_id' => $client->getKey(),
            'concepto' => 'Copias',
            'categoria' => 'copias',
            'monto' => '5.00',
            'moneda' => 'HNL',
            'fecha' => now()->toDateString(),
        ]);
        app(AccountingLedger::class)->registerClientFundMovement($this->fundMovement($client, 'ingreso', '40.00', 'USD'));

        Livewire::test(ContabilidadResumenWidget::class)
            ->assertSee('HNL 70.00')
            ->assertSee('HNL 30.00')
            ->assertSee('HNL 5.00')
            ->assertSee('USD 40.00');
    }

    public function test_accounting_resources_appear_in_role_permissions(): void
    {
        $honorarioResource = collect(FilamentShield::getResources())
            ->firstWhere('resourceFqcn', \App\Filament\Resources\Contabilidad\Honorarios\HonorarioResource::class);
        $options = RoleResource::getResourcePermissionOptions($honorarioResource);

        $this->assertArrayHasKey('ViewAny:Honorario', $options);
        $this->assertArrayHasKey('Create:Honorario', $options);
        $this->assertArrayHasKey('consultar_reportes_contables', FilamentShield::getCustomPermissions());

        $paymentResource = collect(FilamentShield::getResources())
            ->firstWhere('resourceFqcn', \App\Filament\Resources\Contabilidad\Pagos\PagoResource::class);
        $paymentOptions = RoleResource::getResourcePermissionOptions($paymentResource);

        $this->assertArrayHasKey('Create:Pago', $paymentOptions);
        $this->assertArrayNotHasKey('Update:Pago', $paymentOptions);
    }

    public function test_replacing_or_deleting_an_expense_receipt_cleans_up_private_files(): void
    {
        Storage::fake('accounting_files');
        $client = $this->createClient();
        $oldPath = 'gastos/anterior.pdf';
        $newPath = 'gastos/nuevo.pdf';
        Storage::disk('accounting_files')->put($oldPath, '%PDF-1.4 old');
        Storage::disk('accounting_files')->put($newPath, '%PDF-1.4 new');
        $expense = Gasto::create([
            'cliente_id' => $client->getKey(),
            'concepto' => 'Tasa judicial',
            'categoria' => 'tasas',
            'monto' => '12.00',
            'moneda' => 'HNL',
            'fecha' => now()->toDateString(),
            'comprobante_ruta' => $oldPath,
            'comprobante_original' => 'anterior.pdf',
            'comprobante_mime' => 'application/pdf',
            'comprobante_tamano' => 13,
        ]);

        $expense->update(['comprobante_ruta' => $newPath]);
        Storage::disk('accounting_files')->assertMissing($oldPath);
        Storage::disk('accounting_files')->assertExists($newPath);

        $expense->delete();
        Storage::disk('accounting_files')->assertMissing($newPath);
    }

    public function test_expense_pdf_includes_the_private_image_receipt(): void
    {
        Storage::fake('accounting_files');
        $client = $this->createClient();
        $path = 'gastos/recibo.png';
        Storage::disk('accounting_files')->put(
            $path,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+j8ioAAAAASUVORK5CYII=', true),
        );
        $expense = Gasto::create([
            'cliente_id' => $client->getKey(),
            'concepto' => 'Tasa judicial',
            'categoria' => 'tasas',
            'monto' => '12.00',
            'moneda' => 'HNL',
            'fecha' => now()->toDateString(),
            'comprobante_ruta' => $path,
            'comprobante_original' => 'recibo.png',
            'comprobante_mime' => 'image/png',
            'comprobante_tamano' => 68,
        ]);
        $this->actingAs($this->userWithAccountingPermissions(['View:Gasto']));

        $response = app(ContabilidadPdf::class)->gasto($expense);
        ob_start();
        $response->sendContent();
        $pdf = ob_get_clean();

        $this->assertStringStartsWith('%PDF-', $pdf);
    }

    private function createClient(string $name = 'Cliente de contabilidad'): Cliente
    {
        return Cliente::create([
            'tipo' => 'natural',
            'nombre' => $name,
        ]);
    }

    private function createCase(Cliente $client): Caso
    {
        return Caso::create([
            'numero' => 'EXP-ACCOUNTING',
            'titulo' => 'Caso de contabilidad',
            'cliente_id' => $client->getKey(),
            'tipo' => 'civil',
        ]);
    }

    private function createHonorarium(Cliente $client, string $amount, string $currency): Honorario
    {
        return Honorario::create([
            'cliente_id' => $client->getKey(),
            'concepto' => 'Honorario acordado',
            'modalidad' => 'fijo',
            'monto_acordado' => $amount,
            'moneda' => $currency,
        ]);
    }

    private function fundMovement(Cliente $client, string $type, string $amount, string $currency): array
    {
        return [
            'cliente_id' => $client->getKey(),
            'tipo' => $type,
            'monto' => $amount,
            'moneda' => $currency,
            'fecha' => now(),
            'concepto' => 'Movimiento de prueba',
        ];
    }

    private function clientFundsBalance(Cliente $client, string $currency): string
    {
        $balance = FondoClienteMovimiento::query()
            ->where('cliente_id', $client->getKey())
            ->where('moneda', $currency)
            ->selectRaw("COALESCE(SUM(CASE WHEN tipo = 'ingreso' THEN monto ELSE -monto END), 0) as balance")
            ->value('balance');

        return number_format((float) $balance, 2, '.', '');
    }

    private function userWithAccountingPermissions(array $permissions): User
    {
        $role = Role::create(['name' => 'accounting-role', 'guard_name' => 'web']);

        foreach ($permissions as $permission) {
            $role->givePermissionTo(Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]));
        }

        $user = User::factory()->create();
        $user->assignRole($role);

        foreach ([Cliente::class, Caso::class, Honorario::class, Pago::class, Gasto::class, FondoClienteMovimiento::class] as $model) {
            RoleDataScope::create([
                'role_id' => $role->getKey(),
                'model_type' => $model,
                'scope' => RecordAccessScope::ALL,
            ]);
        }

        return $user;
    }
}
