<?php

namespace App\Filament\Resources\Temuans\Pages;

use App\Filament\Resources\Temuans\TemuanResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTemuan extends CreateRecord
{
    protected static string $resource = TemuanResource::class;

     protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['nilai_temuan'] = collect([
            $data['nilai_kerugian_negara']   ?? 0,
            $data['nilai_kerugian_daerah']   ?? 0,
            $data['nilai_kerugian_desa']     ?? 0,
            $data['nilai_kerugian_bos_blud'] ?? 0,
        ])->sum();

        return $data;
    }
}
