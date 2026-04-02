<?php

namespace App\Filament\Resources\Attachments\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AttachmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('model_type')
                    ->required(),
                TextInput::make('model_id')
                    ->required()
                    ->numeric(),
                TextInput::make('file_name')
                    ->required(),
                TextInput::make('file_path')
                    ->required(),
                TextInput::make('file_type')
                    ->default(null),
                TextInput::make('file_size')
                    ->numeric()
                    ->default(null),
                TextInput::make('hash')
                    ->default(null),
                Toggle::make('is_verified')
                    ->required(),
                TextInput::make('uploaded_by')
                    ->required()
                    ->numeric(),
                TextInput::make('verified_by')
                    ->numeric()
                    ->default(null),
                DateTimePicker::make('verified_at'),
            ]);
    }
}
