<?php

namespace App\Support;

use Filament\Forms\Components\FileUpload;

class FormComponents
{
    public static function attachment(string $directory): FileUpload
    {
        return FileUpload::make('file_path')
            ->directory($directory)
            ->disk('public')
            ->downloadable()
            ->openable()
            ->maxSize(10240)
            ->acceptedFileTypes([
                'application/pdf',
                'image/jpeg',
                'image/png',
            ]);
    }
}
?>