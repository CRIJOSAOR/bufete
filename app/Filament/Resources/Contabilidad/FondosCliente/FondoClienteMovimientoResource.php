<?php

namespace App\Filament\Resources\Contabilidad\FondosCliente;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\Contabilidad\FondosCliente\Pages\CreateFondoClienteMovimiento;
use App\Filament\Resources\Contabilidad\FondosCliente\Pages\ListFondosCliente;
use App\Models\Caso;
use App\Models\FondoClienteMovimiento;
use App\Models\Honorario;
use App\Support\ContabilidadFormOptions;
use App\Support\ContabilidadPdf;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use BackedEnum;
use UnitEnum;

class FondoClienteMovimientoResource extends BaseResource
{
    protected static ?string $model = FondoClienteMovimiento::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static string|UnitEnum|null $navigationGroup = 'Contabilidad';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'movimiento de fondos de cliente';

    protected static ?string $pluralModelLabel = 'fondos de clientes';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Movimiento de fondos del cliente')
                ->description('Este registro es separado de los ingresos y gastos propios del bufete. Los movimientos no se editan ni eliminan.')
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    Select::make('cliente_id')
                        ->label('Cliente')
                        ->relationship('cliente', 'nombre', fn (Builder $query) => ContabilidadFormOptions::clientes($query))
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live(),
                    Select::make('caso_id')
                        ->label('Caso relacionado')
                        ->relationship(
                            'caso',
                            'numero',
                            fn (Builder $query, Get $get) => ContabilidadFormOptions::casos($query)
                                ->when($get('cliente_id'), fn (Builder $query, int|string $clientId) => $query->where('cliente_id', $clientId)),
                        )
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function (Set $set, $state): void {
                            if ($state) {
                                $set('cliente_id', Caso::query()->whereKey($state)->value('cliente_id'));
                            }
                        }),
                    Select::make('tipo')
                        ->label('Tipo de movimiento')
                        ->options([
                            FondoClienteMovimiento::INGRESO => 'Ingreso a fondos del cliente',
                            FondoClienteMovimiento::EGRESO => 'Egreso o entrega al cliente',
                        ])
                        ->required(),
                    TextInput::make('monto')
                        ->label('Monto')
                        ->numeric()
                        ->minValue(0.01)
                        ->required()
                        ->prefix(fn (Get $get): string => $get('moneda') ?? 'HNL'),
                    Select::make('moneda')
                        ->label('Moneda')
                        ->options(Honorario::MONEDAS)
                        ->default('HNL')
                        ->required(),
                    DateTimePicker::make('fecha')
                        ->label('Fecha')
                        ->default(now())
                        ->native(false)
                        ->required(),
                    TextInput::make('concepto')->label('Concepto')->required()->maxLength(255),
                    TextInput::make('referencia')->label('Referencia')->maxLength(255),
                    FileUpload::make('comprobante_ruta')
                        ->label('Foto o archivo del comprobante')
                        ->disk('accounting_files')
                        ->directory('fondos-clientes')
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
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['cliente', 'caso']))
            ->defaultSort('fecha', 'desc')
            ->columns([
                TextColumn::make('fecha')->label('Fecha')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('cliente.nombre')->label('Cliente')->searchable(),
                TextColumn::make('caso.numero')->label('Caso')->placeholder('General'),
                TextColumn::make('tipo')->label('Movimiento')->badge()
                    ->formatStateUsing(fn (string $state): string => $state === FondoClienteMovimiento::INGRESO ? 'Ingreso' : 'Egreso')
                    ->color(fn (string $state): string => $state === FondoClienteMovimiento::INGRESO ? 'success' : 'warning'),
                TextColumn::make('monto')->label('Monto')->money(fn (FondoClienteMovimiento $record) => $record->moneda),
                TextColumn::make('concepto')->label('Concepto')->searchable()->wrap(),
                TextColumn::make('referencia')->label('Referencia')->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('moneda')->options(Honorario::MONEDAS),
                SelectFilter::make('tipo')->options([
                    FondoClienteMovimiento::INGRESO => 'Ingreso',
                    FondoClienteMovimiento::EGRESO => 'Egreso',
                ]),
            ])
            ->recordActions([
                Action::make('estado_cuenta')
                    ->label('Estado de cuenta PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->authorize(fn (FondoClienteMovimiento $record): bool => auth()->user()->can('view', $record))
                    ->action(fn (FondoClienteMovimiento $record, ContabilidadPdf $pdf) => $pdf->fondos($record)),
                Action::make('comprobante')
                    ->label('Ver comprobante')
                    ->icon('heroicon-o-paper-clip')
                    ->visible(fn (FondoClienteMovimiento $record): bool => filled($record->comprobante_ruta))
                    ->authorize(fn (FondoClienteMovimiento $record): bool => auth()->user()->can('view', $record))
                    ->url(fn (FondoClienteMovimiento $record): string => route('contabilidad.comprobantes.download', ['type' => 'fondos-cliente', 'record' => $record->getKey()]))
                    ->openUrlInNewTab(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFondosCliente::route('/'),
            'create' => CreateFondoClienteMovimiento::route('/create'),
        ];
    }
}
