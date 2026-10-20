<?php

namespace App\Filament\Resources\Clientes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ClienteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('tipo')
                    ->label('Tipo de cliente')
                    ->options([
                        'natural'  => 'Persona natural',
                        'juridica' => 'Persona jurídica (empresa)',
                    ])
                    ->default('natural')
                    ->required(),

                TextInput::make('nombre')
                    ->label('Nombre completo o razón social')
                    ->required()
                    ->maxLength(255),

                TextInput::make('documento')
                    ->label('Identidad o RTN')
                    ->unique(ignoreRecord: true)
                    ->maxLength(30),

                TextInput::make('telefono')
                    ->label('Teléfono')
                    ->tel()
                    ->maxLength(40),

                TextInput::make('correo')
                    ->label('Correo electrónico')
                    ->email()
                    ->maxLength(150),

                TextInput::make('direccion')
                    ->label('Dirección')
                    ->maxLength(255),

                Textarea::make('notas')
                    ->label('Notas generales')
                    ->rows(3)
                    ->columnSpanFull(),

                Textarea::make('notas_privadas')
                    ->label('Notas privadas (confidencial)')
                    ->rows(3)
                    ->columnSpanFull()
                    ->visible(fn (): bool => auth()->user()->puedeVerConfidencial()),
            ]);
    }
}