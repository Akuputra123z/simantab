<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EditProfile extends BaseEditProfile
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                FileUpload::make('avatar_url')
                    ->label('Foto Profil')
                    ->avatar() // Mengubah UI menjadi lingkaran (avatar mode)
                    ->image() // Validasi file harus gambar
                    ->disk('public')
                    ->directory('avatars') // Folder penyimpanan di storage/app/public
                    ->visibility('public')
                    ->circleCropper(),
                
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                $this->getCurrentPasswordFormComponent(),
            ]);
    }
}