<?php

namespace App\Filament\Resources\Casos\RelationManagers;

use App\Models\CasoDocumento;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class DocumentosRelationManager extends RelationManager
{
    protected static string $relationship = 'documentos';

    protected static ?string $title = 'Documentos e imágenes';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('titulo')->label('Nombre del documento')->required()->maxLength(255),
            Select::make('categoria')
                ->options([
                    'contrato' => 'Contrato',
                    'escrito' => 'Escrito',
                    'resolucion' => 'Resolución',
                    'prueba' => 'Prueba o evidencia',
                    'identificacion' => 'Identificación',
                    'imagen' => 'Imagen',
                    'otro' => 'Otro',
                ])
                ->default('otro')
                ->required(),
            Textarea::make('descripcion')->label('Descripción')->rows(3)->columnSpanFull(),
            FileUpload::make('ruta')
                ->label('Archivo')
                ->disk('case_files')
                ->directory(fn (): string => 'casos/'.$this->getOwnerRecord()->getKey())
                ->visibility('private')
                ->acceptedFileTypes([
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.oasis.opendocument.text',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'text/plain',
                    'text/csv',
                    'image/jpeg',
                    'image/png',
                    'image/webp',
                ])
                ->maxSize(25600)
                ->storeFileNamesIn('nombre_original')
                ->required()
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('titulo')->label('Documento')->searchable()->wrap(),
                TextColumn::make('categoria')->badge(),
                TextColumn::make('nombre_original')->label('Archivo')->limit(40),
                TextColumn::make('tamano')->label('Tamaño')->formatStateUsing(fn (?int $state): string => $state
                    ? number_format($state / 1024, 0).' KB'
                    : '—'),
                TextColumn::make('subidor.name')->label('Subido por'),
                TextColumn::make('created_at')->label('Fecha')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('categoria')->options([
                    'contrato' => 'Contrato',
                    'escrito' => 'Escrito',
                    'resolucion' => 'Resolución',
                    'prueba' => 'Prueba o evidencia',
                    'identificacion' => 'Identificación',
                    'imagen' => 'Imagen',
                    'otro' => 'Otro',
                ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->authorize(fn (): bool => auth()->user()->can('Update:Caso'))
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['mime_type'] = Storage::disk('case_files')->mimeType($data['ruta']) ?: 'application/octet-stream';
                        $data['tamano'] = Storage::disk('case_files')->size($data['ruta']);

                        return $data;
                    }),
            ])
            ->recordActions([
                Action::make('descargar')
                    ->label('Descargar')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->authorize(fn (): bool => auth()->user()->can('View:Caso'))
                    ->url(fn (CasoDocumento $record): string => route('casos.documentos.download', [
                        'caso' => $this->getOwnerRecord()->getKey(),
                        'documento' => $record->getKey(),
                    ]))
                    ->openUrlInNewTab(),
                EditAction::make()
                    ->authorize(fn (): bool => auth()->user()->can('Update:Caso'))
                    ->mutateFormDataUsing(function (array $data, CasoDocumento $record): array {
                        if ($data['ruta'] !== $record->ruta) {
                            $data['mime_type'] = Storage::disk('case_files')->mimeType($data['ruta']) ?: 'application/octet-stream';
                            $data['tamano'] = Storage::disk('case_files')->size($data['ruta']);
                        }

                        return $data;
                    }),
                DeleteAction::make()->authorize(fn (): bool => auth()->user()->can('Update:Caso')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->authorize(fn (): bool => auth()->user()->can('Update:Caso')),
                ]),
            ]);
    }
}
