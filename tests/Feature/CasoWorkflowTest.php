<?php

namespace Tests\Feature;

use App\Models\Caso;
use App\Models\CasoDocumento;
use App\Models\RoleDataScope;
use App\Models\User;
use App\Support\CasoPdfImageEmbedder;
use App\Support\RecordAccessScope;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CasoWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_case_can_track_tasks_hearings_timeline_and_documents(): void
    {
        $case = Caso::create([
            'numero' => 'EXP-WORKFLOW',
            'titulo' => 'Expediente de prueba',
            'cliente_id' => \App\Models\Cliente::create([
                'tipo' => 'natural',
                'nombre' => 'Cliente de prueba',
            ])->id,
            'tipo' => 'civil',
        ]);

        $task = $case->tareas()->create([
            'titulo' => 'Preparar escrito',
            'prioridad' => 'alta',
            'estado' => 'completada',
        ]);
        $hearing = $case->audiencias()->create([
            'titulo' => 'Audiencia inicial',
            'fecha_hora' => now()->addDay(),
        ]);
        $event = $case->eventos()->create([
            'tipo' => 'actuacion',
            'titulo' => 'Se presentó demanda',
            'ocurrido_en' => now(),
        ]);
        $document = $case->documentos()->create([
            'titulo' => 'Demanda',
            'categoria' => 'escrito',
            'ruta' => 'casos/1/demanda.pdf',
            'nombre_original' => 'demanda.pdf',
            'mime_type' => 'application/pdf',
            'tamano' => 1024,
        ]);

        $this->assertModelExists($task);
        $this->assertModelExists($hearing);
        $this->assertModelExists($event);
        $this->assertModelExists($document);
    }

    public function test_completing_and_reopening_a_task_updates_completion_timestamp(): void
    {
        $case = Caso::create([
            'numero' => 'EXP-TASK',
            'titulo' => 'Tarea',
            'cliente_id' => \App\Models\Cliente::create([
                'tipo' => 'natural',
                'nombre' => 'Cliente',
            ])->id,
            'tipo' => 'civil',
        ]);
        $task = $case->tareas()->create([
            'titulo' => 'Revisar expediente',
            'estado' => 'pendiente',
        ]);

        $task->update(['estado' => 'completada']);
        $completedAt = $task->fresh()->completada_en;
        $task->update(['estado' => 'pendiente']);

        $this->assertNotNull($completedAt);
        $this->assertNull($task->fresh()->completada_en);
    }

    public function test_case_document_download_requires_case_view_permission(): void
    {
        Storage::fake('case_files');
        $user = $this->userWithCasePermission('View:Caso');
        $case = $this->createCase();
        $path = 'casos/'.$case->getKey().'/prueba.pdf';
        Storage::disk('case_files')->put($path, '%PDF-1.4 expediente');
        $document = $this->createDocument($case, $path);

        $response = $this->actingAs($user)->get(route('casos.documentos.download', [
            'caso' => $case->getKey(),
            'documento' => $document->getKey(),
        ]));

        $response->assertDownload('prueba.pdf');
    }

    public function test_case_document_download_is_forbidden_without_case_view_permission(): void
    {
        Storage::fake('case_files');
        $user = User::factory()->create();
        $case = $this->createCase();
        $path = 'casos/'.$case->getKey().'/privado.pdf';
        Storage::disk('case_files')->put($path, '%PDF-1.4 privado');
        $document = $this->createDocument($case, $path);

        $this->actingAs($user)
            ->get(route('casos.documentos.download', [
                'caso' => $case->getKey(),
                'documento' => $document->getKey(),
            ]))
            ->assertNotFound();
    }

    public function test_case_document_download_does_not_allow_a_document_from_another_case(): void
    {
        Storage::fake('case_files');
        $user = $this->userWithCasePermission('View:Caso');
        $case = $this->createCase('EXP-OWNER');
        $otherCase = $this->createCase('EXP-OTHER');
        $path = 'casos/'.$otherCase->getKey().'/otro.pdf';
        Storage::disk('case_files')->put($path, '%PDF-1.4 otro');
        $document = $this->createDocument($otherCase, $path);

        $this->actingAs($user)
            ->get(route('casos.documentos.download', [
                'caso' => $case->getKey(),
                'documento' => $document->getKey(),
            ]))
            ->assertNotFound();
    }

    public function test_force_deleting_a_case_removes_its_private_documents(): void
    {
        Storage::fake('case_files');
        $case = $this->createCase('EXP-DELETE');
        $path = 'casos/'.$case->getKey().'/eliminar.pdf';
        Storage::disk('case_files')->put($path, '%PDF-1.4 eliminar');
        $document = $this->createDocument($case, $path);

        $case->forceDelete();

        $this->assertModelMissing($document);
        Storage::disk('case_files')->assertMissing($path);
    }

    public function test_case_document_download_does_not_expose_records_outside_the_users_scope(): void
    {
        Storage::fake('case_files');
        $role = Role::create(['name' => 'assigned-cases-only', 'guard_name' => 'web']);
        $role->givePermissionTo(Permission::create([
            'name' => 'View:Caso',
            'guard_name' => 'web',
        ]));
        RoleDataScope::create([
            'role_id' => $role->getKey(),
            'model_type' => Caso::class,
            'scope' => RecordAccessScope::ASSIGNED_TO_ME,
        ]);
        $user = User::factory()->create();
        $user->assignRole($role);
        $case = $this->createCase('EXP-OUT-OF-SCOPE');
        $path = 'casos/'.$case->getKey().'/fuera.pdf';
        Storage::disk('case_files')->put($path, '%PDF-1.4 fuera');
        $document = $this->createDocument($case, $path);

        $this->actingAs($user)
            ->get(route('casos.documentos.download', [
                'caso' => $case->getKey(),
                'documento' => $document->getKey(),
            ]))
            ->assertNotFound();
    }

    public function test_case_report_pdf_is_generated_and_confidential_fields_are_opt_in(): void
    {
        $case = $this->createCase('EXP-PDF');
        $case->update([
            'notas_internas' => 'NOTA CONFIDENCIAL DE PRUEBA',
            'estrategia' => 'ESTRATEGIA CONFIDENCIAL DE PRUEBA',
        ]);
        $case->tareas()->create(['titulo' => 'Tarea que aparece en el expediente']);

        $publicHtml = view('pdf.casos.expediente', [
            'caso' => $case->load(['cliente', 'abogados', 'tareas.asignado', 'audiencias.responsable', 'eventos.creador', 'documentos.subidor']),
            'imagenes' => [],
            'incluirConfidencial' => false,
        ])->render();
        $confidentialHtml = view('pdf.casos.expediente', [
            'caso' => $case,
            'imagenes' => [],
            'incluirConfidencial' => true,
        ])->render();
        $pdfContents = Pdf::loadView('pdf.casos.expediente', [
            'caso' => $case,
            'imagenes' => [],
            'incluirConfidencial' => false,
        ])->output();

        $this->assertStringNotContainsString('NOTA CONFIDENCIAL DE PRUEBA', $publicHtml);
        $this->assertStringContainsString('NOTA CONFIDENCIAL DE PRUEBA', $confidentialHtml);
        $this->assertStringStartsWith('%PDF-', $pdfContents);
        $this->assertStringContainsString('Tarea que aparece en el expediente', $publicHtml);
    }

    public function test_case_pdf_generation_requires_its_own_permission(): void
    {
        $case = $this->createCase('EXP-PDF-PERMISSION');
        $caseViewer = $this->userWithCasePermission('View:Caso', 'case-viewer');
        $pdfGenerator = $this->userWithCasePermission('GenerarPdf:Caso', 'pdf-generator');

        $this->assertFalse(Gate::forUser($caseViewer)->allows('generarPdf', $case));
        $this->assertTrue(Gate::forUser($pdfGenerator)->allows('generarPdf', $case));
    }

    public function test_case_report_pdf_embeds_uploaded_images(): void
    {
        Storage::fake('case_files');
        $case = $this->createCase('EXP-PDF-IMAGE');
        $path = 'casos/'.$case->getKey().'/evidencia.png';
        Storage::disk('case_files')->put($path, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+j8ioAAAAASUVORK5CYII=',
            true,
        ));
        $document = $this->createDocument($case, $path);
        $document->update(['mime_type' => 'image/png']);

        $imagenes = app(CasoPdfImageEmbedder::class)->embed($case->documentos);
        $html = view('pdf.casos.expediente', [
            'caso' => $case->load(['cliente', 'abogados', 'tareas.asignado', 'audiencias.responsable', 'eventos.creador', 'documentos.subidor']),
            'imagenes' => $imagenes,
            'incluirConfidencial' => false,
        ])->render();
        $pdfContents = Pdf::loadView('pdf.casos.expediente', [
            'caso' => $case->load(['cliente', 'abogados', 'tareas.asignado', 'audiencias.responsable', 'eventos.creador', 'documentos.subidor']),
            'imagenes' => $imagenes,
            'incluirConfidencial' => false,
        ])->output();

        $this->assertStringContainsString('<img class="attachment-image"', $html);
        $this->assertStringContainsString('src="data:image/png;base64,', $html);
        $this->assertStringStartsWith('%PDF-', $pdfContents);
    }

    private function userWithCasePermission(string $permissionName, string $roleName = 'case-viewer'): User
    {
        $role = Role::create(['name' => $roleName, 'guard_name' => 'web']);
        $role->givePermissionTo(Permission::create([
            'name' => $permissionName,
            'guard_name' => 'web',
        ]));
        RoleDataScope::create([
            'role_id' => $role->getKey(),
            'model_type' => Caso::class,
            'scope' => RecordAccessScope::ALL,
        ]);

        return User::factory()->create()->assignRole($role);
    }

    private function createCase(string $number = 'EXP-DOWNLOAD'): Caso
    {
        return Caso::create([
            'numero' => $number,
            'titulo' => 'Caso para prueba',
            'cliente_id' => \App\Models\Cliente::create([
                'tipo' => 'natural',
                'nombre' => 'Cliente '.$number,
            ])->id,
            'tipo' => 'civil',
        ]);
    }

    private function createDocument(Caso $case, string $path): CasoDocumento
    {
        return $case->documentos()->create([
            'titulo' => 'Documento',
            'categoria' => 'prueba',
            'ruta' => $path,
            'nombre_original' => basename($path),
            'mime_type' => 'application/pdf',
            'tamano' => 20,
        ]);
    }
}
