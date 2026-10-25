<?php

namespace App\Filament\Resources\Contabilidad\Honorarios;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\Contabilidad\Honorarios\Pages\CreateHonorario;
use App\Filament\Resources\Contabilidad\Honorarios\Pages\EditHonorario;
use App\Filament\Resources\Contabilidad\Honorarios\Pages\ListHonorarios;
use App\Models\Caso;
use App\Models\Honorario;
use App\Models\User;
use App\Support\ContabilidadFormOptions;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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

class HonorarioResource extends BaseResource
{
    protected static ?string $model = Honorario::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|UnitEnum|null $navigationGroup = 'Contabilidad';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'honorario';

    protected static ?string $pluralModelLabel = 'honorarios';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Honorario acordado')
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
                        })
                        ->helperText('Opcional para honorarios generales del cliente.'),
                    TextInput::make('concepto')
                        ->label('Concepto')
                        ->required()
                        ->maxLength(255),
                    Select::make('modalidad')
                        ->label('Modalidad')
                        ->options([
                            'fijo' => 'Monto fijo',
                            'por_hora' => 'Por hora',
                            'anticipo' => 'Anticipo',
                            'cuotas' => 'Por cuotas',
                        ])
                        ->default('fijo')
                        ->required(),
                    TextInput::make('monto_acordado')
                        ->label('Monto acordado')
                        ->numeric()
                        ->minValue(0.01)
                        ->required()
                        ->prefix(fn (Get $get): string => $get('moneda') ?? 'HNL'),
                    Select::make('moneda')
                        ->label('Moneda')
                        ->options(Honorario::MONEDAS)
                        ->default('HNL')
                        ->required(),
                    DatePicker::make('fecha_vencimiento')
                        ->label('Fecha de vencimiento')
                        ->native(false),
                    Textarea::make('notas')
                        ->label('Notas internas')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withTotalPagado()->with(['cliente', 'caso']))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('cliente.nombre')->label('Cliente')->searchable()->sortable(),
                TextColumn::make('caso.numero')->label('Expediente')->placeholder('General')->searchable(),
                TextColumn::make('concepto')->label('Concepto')->searchable()->wrap(),
                TextColumn::make('monto_acordado')->label('Acordado')->money(fn (Honorario $record) => $record->moneda),
                TextColumn::make('saldo')
                    ->label('Saldo')
                    ->state(fn (Honorario $record): string => $record->moneda.' '.$record->saldo)
                    ->color(fn (Honorario $record): string => $record->estado_pago === 'Pagado' ? 'success' : 'warning'),
                TextColumn::make('fecha_vencimiento')->label('Vence')->date('d/m/Y')->sortable()->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('moneda')->options(Honorario::MONEDAS),
                SelectFilter::make('modalidad')->options([
                    'fijo' => 'Monto fijo',
                    'por_hora' => 'Por hora',
                    'anticipo' => 'Anticipo',
                    'cuotas' => 'Por cuotas',
                ]),
            ])
            ->recordActions([
                Action::make('estado_cuenta')
                    ->label('Estado de cuenta PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->authorize(fn (Honorario $record): bool => auth()->user()->can('view', $record))
                    ->action(fn (Honorario $record, \App\Support\ContabilidadPdf $pdf) => $pdf->honorario($record)),
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHonorarios::route('/'),
            'create' => CreateHonorario::route('/create'),
            'edit' => EditHonorario::route('/{record}/edit'),
        ];
    }
}
