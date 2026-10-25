<?php

namespace App\Support;

use App\Models\FondoClienteMovimiento;
use App\Models\Gasto;
use App\Models\Honorario;
use App\Models\Pago;
use App\Models\User;
use App\Support\AccountingMoney;
use App\Support\RecordAccessScope;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ContabilidadPdf
{
    public function honorario(Honorario $honorario)
    {
        Gate::authorize('view', $honorario);
        $honorario->load(['cliente', 'caso', 'pagos']);

        return $this->download('honorario', ['honorario' => $honorario], 'honorario-'.$honorario->getKey());
    }

    public function pago(Pago $pago)
    {
        Gate::authorize('view', $pago);
        $pago->load(['honorario.cliente', 'honorario.caso']);

        return $this->download('pago', [
            'pago' => $pago,
            'comprobanteImagen' => $this->embedImage($pago->comprobante_ruta, $pago->comprobante_mime),
        ], 'recibo-pago-'.$pago->getKey());
    }

    public function gasto(Gasto $gasto)
    {
        Gate::authorize('view', $gasto);
        $gasto->load(['cliente', 'caso']);

        return $this->download('gasto', [
            'gasto' => $gasto,
            'comprobanteImagen' => $this->embedImage($gasto->comprobante_ruta, $gasto->comprobante_mime),
        ], 'gasto-'.$gasto->getKey());
    }

    public function fondos(FondoClienteMovimiento $movimiento)
    {
        Gate::authorize('view', $movimiento);
        $movimiento->load(['cliente', 'caso']);

        $movimientos = RecordAccessScope::apply(FondoClienteMovimiento::query(), auth()->user() instanceof User ? auth()->user() : null)
            ->where('cliente_id', $movimiento->cliente_id)
            ->where('moneda', $movimiento->moneda)
            ->with('caso')
            ->orderBy('fecha')
            ->orderBy('id')
            ->get();
        $balance = 0;
        $detalleMovimientos = $movimientos->map(function (FondoClienteMovimiento $item) use (&$balance): array {
            $amount = AccountingMoney::toMinorUnits($item->monto);
            $balance += $item->tipo === FondoClienteMovimiento::INGRESO ? $amount : -$amount;

            return [
                'movimiento' => $item,
                'balance' => AccountingMoney::formatMinorUnits($balance),
            ];
        });

        return $this->download('fondos-cliente', [
            'movimiento' => $movimiento,
            'movimientos' => $detalleMovimientos,
            'saldoActual' => AccountingMoney::formatMinorUnits($balance),
            'comprobanteImagen' => $this->embedImage($movimiento->comprobante_ruta, $movimiento->comprobante_mime),
        ], 'estado-fondos-cliente-'.$movimiento->cliente_id.'-'.$movimiento->moneda);
    }

    private function embedImage(?string $path, ?string $mimeType): ?string
    {
        if (! $path || ! str_starts_with((string) $mimeType, 'image/')) {
            return null;
        }

        $contents = Storage::disk('accounting_files')->get($path);
        $imageInfo = getimagesizefromstring($contents);

        if ($imageInfo === false || ! in_array($imageInfo['mime'], ['image/jpeg', 'image/png', 'image/webp'], true)) {
            throw new RuntimeException('El comprobante de imagen no se puede procesar.');
        }

        $mimeType = $imageInfo['mime'];

        if ($mimeType === 'image/webp') {
            $image = imagecreatefromstring($contents);

            if ($image === false) {
                throw new RuntimeException('El comprobante WEBP no se puede convertir para el PDF.');
            }

            ob_start();

            try {
                if (! imagepng($image)) {
                    throw new RuntimeException('El comprobante WEBP no se puede convertir para el PDF.');
                }

                $contents = ob_get_contents();

                if ($contents === false) {
                    throw new RuntimeException('El comprobante WEBP no se puede convertir para el PDF.');
                }
            } finally {
                ob_end_clean();
                imagedestroy($image);
            }

            $mimeType = 'image/png';
        }

        return 'data:'.$mimeType.';base64,'.base64_encode($contents);
    }

    private function download(string $view, array $data, string $fileName)
    {
        $pdf = Pdf::loadView('pdf.contabilidad.'.$view, $data)->setPaper('a4');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            Str::slug($fileName).'.pdf',
            ['Content-Type' => 'application/pdf'],
        );
    }
}
