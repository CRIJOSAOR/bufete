<?php

namespace App\Filament\Resources\Contabilidad\Pagos;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\Contabilidad\Pagos\Pages\CreatePago;
use App\Filament\Resources\Contabilidad\Pagos\Pages\ListPagos;
use App\Models\Honorario;
use App\Models\Pago;
use App\Support\ContabilidadFormOptions;
use App\Support\ContabilidadPdf;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use BackedEnum;
use UnitEnum;

class PagoResource extends BaseResource
{
    protected static ?string $model = Pago::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    protected static string|UnitEnum|null $navigationGroup = 'Contabilidad';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'pago';

    protected static ?string $pluralModelLabel = 'pagos';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Registrar pago')
                ->description('Los pagos registrados no se editan ni eliminan; si hay un error, registre el movimiento correctivo correspondiente.')
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    Select::make('honorario_id')
                        ->label('Honorario')
                        ->relationship('honorario', 'concepto', fn (Builder $query) => ContabilidadFormOptions::honorarios($query)->withTotalPagado()->with('cliente'))
                        ->getOptionLabelFromRecordUsing(fn (Honorario $record): string => $record->cliente->nombre.' — '.$record->concepto.' ('.$record->moneda.' '.$record->saldo.')')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live(),
                    TextInput::make('monto')
                        ->label('Monto recibido')
                        ->numeric()
                        ->minValue(0.01)
                        ->required()
                        ->prefix(fn (Get $get): string => $get('honorario_id')
                            ? (ContabilidadFormOptions::honorarios(Honorario::query())->whereKey($get('honorario_id'))->value('moneda') ?? 'HNL')
                            : 'HNL'),
                    DateTimePicker::make('fecha_pago')
                        ->label('Fecha y hora del pago')
                        ->default(now())
                        ->native(false)
                        ->required(),
                    Select::make('metodo')
                        ->label('Método de pago')
                        ->options([
                            'efectivo' => 'Efectivo',
                            'transferencia' => 'Transferencia',
                            'tarjeta' => 'Tarjeta',
                            'cheque' => 'Cheque',
                            'otro' => 'Otro',
                        ])
                        ->required(),
                    TextInput::make('referencia')
                        ->label('Referencia o número de operación')
                        ->maxLength(255)
                        ->columnSpanFull(),
                    FileUpload::make('comprobante_ruta')
                        ->label('Comprobante del pago')
                        ->disk('accounting_files')
                        ->directory('pagos')
                        ->visibility('private')
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'application/pdf'])
                        ->maxSize(10240)
                        ->storeFileNamesIn('comprobante_original')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['honorario.cliente', 'honorario.caso']))
            ->defaultSort('fecha_pago', 'desc')
            ->columns([
                TextColumn::make('fecha_pago')->label('Fecha')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('honorario.cliente.nombre')->label('Cliente')->searchable(),
                TextColumn::make('honorario.caso.numero')->label('Expediente')->placeholder('General'),
                TextColumn::make('honorario.concepto')->label('Honorario')->wrap(),
                TextColumn::make('monto')->label('Monto')->money(fn (Pago $record) => $record->honorario->moneda),
                TextColumn::make('metodo')->label('Método')->badge(),
                TextColumn::make('referencia')->label('Referencia')->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('metodo')->options([
                    'efectivo' => 'Efectivo',
                    'transferencia' => 'Transferencia',
                    'tarjeta' => 'Tarjeta',
                    'cheque' => 'Cheque',
                    'otro' => 'Otro',
                ]),
            ])
            ->recordActions([
                Action::make('recibo')
                    ->label('Recibo PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->authorize(fn (Pago $record): bool => auth()->user()->can('view', $record))
                    ->action(fn (Pago $record, ContabilidadPdf $pdf) => $pdf->pago($record)),
                Action::make('comprobante')
                    ->label('Ver comprobante')
                    ->icon('heroicon-o-paper-clip')
                    ->visible(fn (Pago $record): bool => filled($record->comprobante_ruta))
                    ->authorize(fn (Pago $record): bool => auth()->user()->can('view', $record))
                    ->url(fn (Pago $record): string => route('contabilidad.comprobantes.download', ['type' => 'pago', 'record' => $record->getKey()]))
                    ->openUrlInNewTab(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPagos::route('/'),
            'create' => CreatePago::route('/create'),
        ];
    }
}
