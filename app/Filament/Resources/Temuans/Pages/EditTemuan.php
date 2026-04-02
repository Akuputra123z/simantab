<?php

namespace App\Filament\Resources\Temuans\Pages;

use App\Filament\Resources\Temuans\TemuanResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTemuan extends EditRecord
{
    protected static string $resource = TemuanResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['nilai_temuan'] = collect([
            $data['nilai_kerugian_negara']   ?? 0,
            $data['nilai_kerugian_daerah']   ?? 0,
            $data['nilai_kerugian_desa']     ?? 0,
            $data['nilai_kerugian_bos_blud'] ?? 0,
        ])->sum();

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
