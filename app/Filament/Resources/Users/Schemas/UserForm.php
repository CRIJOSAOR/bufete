<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Acceso al sistema')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre completo')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Correo electrónico')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('password')
                            ->label('Contraseña')
                            ->password()
                            ->revealable()
                            ->minLength(8)
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn ($state): bool => filled($state))
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->helperText('Al editar, déjela vacía para no cambiarla.'),

                        Select::make('roles')
                            ->label('Rol')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->maxItems(1)
                            ->preload()
                            ->required(fn (): bool => auth()->user()->isPermissionAdministrator())
                            ->disabled(fn (): bool => ! auth()->user()->isPermissionAdministrator())
                            ->visible(fn (): bool => auth()->user()->isPermissionAdministrator())
                            ->disabled(fn (?User $record): bool => $record?->is(auth()->user()) ?? false)
                            ->helperText('Cada persona tiene un solo rol.'),

                        Toggle::make('activo')
                            ->label('Activo')
                            ->helperText('Si se desactiva, la persona ya no puede entrar al sistema.')
                            ->default(true)
                            ->disabled(fn (?User $record): bool => $record?->is(auth()->user()) ?? false),
                    ]),

                Section::make('Datos laborales')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('cargo')
                            ->label('Cargo')
                            ->placeholder('Socio fundador, Asociado, Secretaria...')
                            ->maxLength(120),

                        TextInput::make('especialidad')
                            ->label('Especialidad')
                            ->maxLength(120),

                        TextInput::make('numero_colegiado')
                            ->label('Número de colegiado')
                            ->maxLength(30),

                        DatePicker::make('fecha_ingreso')
                            ->label('Fecha de ingreso'),
                    ]),

                Section::make('Datos personales')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('identidad')
                            ->label('Identidad')
                            ->unique(ignoreRecord: true)
                            ->maxLength(30),

                        TextInput::make('telefono')
                            ->label('Teléfono')
                            ->tel()
                            ->maxLength(40),

                        TextInput::make('direccion')
                            ->label('Dirección')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('contacto_emergencia_nombre')
                            ->label('Contacto de emergencia')
                            ->maxLength(120),

                        TextInput::make('contacto_emergencia_telefono')
                            ->label('Teléfono de emergencia')
                            ->tel()
                            ->maxLength(40),
                    ]),
            ]);
    }
}