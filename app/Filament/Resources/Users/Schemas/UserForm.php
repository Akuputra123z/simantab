<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),

                // ✅ FIX: ambil role dari database (Shield / Spatie)
                Select::make('roles')
                    ->label('Role')
                    ->relationship('roles', 'name') // penting!
                    ->preload()
                    ->searchable()
                    ->required(),

                DateTimePicker::make('email_verified_at'),

                TextInput::make('password')
                    ->password()
                    ->required(fn ($context) => $context === 'create') // hanya wajib saat create
                    ->dehydrated(fn ($state) => filled($state)) // tidak kirim jika kosong
                    ->maxLength(255),
            ]);
    }
}