<?php

namespace App\Filament\Resources\Contabilidad\Gastos;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\Contabilidad\Gastos\Pages\CreateGasto;
use App\Filament\Resources\Contabilidad\Gastos\Pages\EditGasto;
use App\Filament\Resources\Contabilidad\Gastos\Pages\ListGastos;
use App\Models\Caso;
use App\Models\Gasto;
use App\Models\Honorario;
use App\Support\ContabilidadFormOptions;
use App\Support\ContabilidadPdf;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use BackedEnum;
use UnitEnum;

class GastoResource extends BaseResource
{
    protected static ?string $model = Gasto::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptRefund;

    protected static string|UnitEnum|null $navigationGroup = 'Contabilidad';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'gasto';

    protected static ?string $pluralModelLabel = 'gastos';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Detalle del gasto')
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('concepto')->label('Concepto')->required()->maxLength(255),
                    Select::make('categoria')
                        ->label('Categoría')
                        ->options([
                            'tasas' => 'Tasas y derechos',
                            'copias' => 'Copias y documentos',
                            'viajes' => 'Viajes y transporte',
                            'peritajes' => 'Peritajes y especialistas',
                            'oficina' => 'Gastos de oficina',
                            'servicios' => 'Servicios y suscripciones',
                            'personal' => 'Personal',
                            'renta' => 'Renta',
                            'otro' => 'Otro',
                        ])
                        ->required(),
                    Select::make('caso_id')
                        ->label('Caso relacionado')
                        ->relationship('caso', 'numero', fn (Builder $query) => ContabilidadFormOptions::casos($query))
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function (Set $set, $state): void {
                            if ($state) {
                                $set('cliente_id', Caso::query()->whereKey($state)->value('cliente_id'));
                            }
                        }),
                    Select::make('cliente_id')
                        ->label('Cliente')
                        ->relationship('cliente', 'nombre', fn (Builder $query) => ContabilidadFormOptions::clientes($query))
                        ->searchable()
                        ->preload()
                        ->live()
                        ->helperText('Puede dejarse vacío para gastos generales del bufete.'),
                    TextInput::make('monto')->label('Monto')->numeric()->minValue(0.01)->required()
                        ->prefix(fn (Get $get): string => $get('moneda') ?? 'HNL'),
                    Select::make('moneda')->label('Moneda')->options(Honorario::MONEDAS)->default('HNL')->required(),
                    DatePicker::make('fecha')->label('Fecha del gasto')->native(false)->default(now())->required(),
                    TextInput::make('proveedor')->label('Proveedor')->maxLength(255),
                    TextInput::make('pagado_por')->label('Pagado por')->maxLength(255),
                    Toggle::make('reembolsable')->label('Reembolsable por el cliente')->default(false)->live(),
                    Toggle::make('reembolsado')->label('Ya fue reembolsado')->default(false)
                        ->visible(fn (Get $get): bool => (bool) $get('reembolsable')),
                    FileUpload::make('comprobante_ruta')
                        ->label('Foto o archivo del recibo')
                        ->disk('accounting_files')
                        ->directory('gastos')
                        ->visibility('private')
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'application/pdf'])
                        ->maxSize(10240)
                        ->storeFileNamesIn('comprobante_original')
                        ->columnSpanFull(),
                    Textarea::make('notas')->label('Notas')->rows(3)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['cliente', 'caso']))
            ->defaultSort('fecha', 'desc')
            ->columns([
                TextColumn::make('fecha')->label('Fecha')->date('d/m/Y')->sortable(),
                TextColumn::make('concepto')->label('Concepto')->searchable()->wrap(),
                TextColumn::make('categoria')->label('Categoría')->badge(),
                TextColumn::make('caso.numero')->label('Caso')->placeholder('General'),
                TextColumn::make('cliente.nombre')->label('Cliente')->placeholder('Bufete')->searchable(),
                TextColumn::make('monto')->label('Monto')->money(fn (Gasto $record) => $record->moneda),
                IconColumn::make('reembolsable')->label('Reembolsable')->boolean(),
                TextColumn::make('reembolsado')->label('Reembolso')->badge()
                    ->formatStateUsing(fn (bool $state, Gasto $record): string => ! $record->reembolsable ? 'No aplica' : ($state ? 'Reembolsado' : 'Pendiente'))
                    ->color(fn (bool $state, Gasto $record): string => ! $record->reembolsable ? 'gray' : ($state ? 'success' : 'warning')),
            ])
            ->filters([
                SelectFilter::make('moneda')->options(Honorario::MONEDAS),
                SelectFilter::make('categoria')->options([
                    'tasas' => 'Tasas y derechos',
                    'copias' => 'Copias y documentos',
                    'viajes' => 'Viajes y transporte',
                    'peritajes' => 'Peritajes y especialistas',
                    'oficina' => 'Gastos de oficina',
                    'servicios' => 'Servicios y suscripciones',
                    'personal' => 'Personal',
                    'renta' => 'Renta',
                    'otro' => 'Otro',
                ]),
            ])
            ->recordActions([
                Action::make('reporte_pdf')
                    ->label('Reporte PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->authorize(fn (Gasto $record): bool => auth()->user()->can('view', $record))
                    ->action(fn (Gasto $record, ContabilidadPdf $pdf) => $pdf->gasto($record)),
                Action::make('comprobante')
                    ->label('Ver comprobante')
                    ->icon('heroicon-o-paper-clip')
                    ->visible(fn (Gasto $record): bool => filled($record->comprobante_ruta))
                    ->authorize(fn (Gasto $record): bool => auth()->user()->can('view', $record))
                    ->url(fn (Gasto $record): string => route('contabilidad.comprobantes.download', ['type' => 'gasto', 'record' => $record->getKey()]))
                    ->openUrlInNewTab(),
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGastos::route('/'),
            'create' => CreateGasto::route('/create'),
            'edit' => EditGasto::route('/{record}/edit'),
        ];
    }
}
