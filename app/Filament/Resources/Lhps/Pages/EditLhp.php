<?php

namespace App\Filament\Resources\Lhps\Pages;

use App\Filament\Resources\Lhps\LhpResource;
use App\Models\AuditAssignment;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditLhp extends EditRecord
{
    protected static string $resource = LhpResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    /**
     * Saat form di-hydrate untuk edit, field assignment_ sudah
     * diisi via afterStateHydrated() di LhpForm masing-masing field.
     * Tidak perlu override mutateFormDataBeforeFill().
     */

    /**
     * Sebelum update LHP, update AuditAssignment dengan data terbaru.
     */
    // protected function mutateFormDataBeforeSave(array $data): array
    // {
    //     $assignmentData = $this->extractAssignmentData($data);

    //     // Update AuditAssignment yang sudah ada
    //     $this->record->auditAssignment?->update([
    //         'audit_program_id'  => $assignmentData['audit_program_id']  ?? null,
    //         'unit_diperiksa_id' => $assignmentData['unit_diperiksa_id'] ?? null,
    //         'ketua_tim_id'      => $assignmentData['ketua_tim_id']      ?? null,
    //         'nama_tim'          => $assignmentData['nama_tim']           ?? null,
    //         'tanggal_mulai'     => $assignmentData['tanggal_mulai']      ?? null,
    //         'tanggal_selesai'   => $assignmentData['tanggal_selesai']    ?? null,
    //         'updated_by'        => auth()->id(),
    //     ]);

    //     return $data;
    // }

    protected function mutateFormDataBeforeSave(array $data): array
{
    $user = Auth::user();

    $assignmentData = $this->extractAssignmentData($data);
    $assignment     = $this->record->auditAssignment;

    // ✅ VALIDASI AKSES (WAJIB)
    if ($assignment && !$user->hasRole('super_admin')) {
        $allowed = AuditAssignment::where(function ($q) use ($user) {
            $q->where('ketua_tim_id', $user->id)
              ->orWhereHas('members', function ($q2) use ($user) {
                  $q2->where('user_id', $user->id);
              });
        })->pluck('id')->toArray();

        if (!in_array($assignment->id, $allowed)) {
            abort(403, 'Tidak punya akses ke penugasan ini');
        }
    }

    // ✅ FILTER DATA (hindari null overwrite)
    if ($assignment && !empty($assignmentData)) {

        $filtered = array_filter(
            $assignmentData,
            fn ($value) => !is_null($value)
        );

        if (!empty($filtered)) {
            $assignment->update(array_merge($filtered, [
                'updated_by' => $user->id,
            ]));
        }
    }

    return $data;
}

    protected function afterSave(): void
{
    $lhp = $this->record->fresh();

    if (!$lhp->auditAssignment) {
        return;
    }

    $assignmentStatus = match ($lhp->status) {
        'ditandatangani', 'final' => 'selesai',
        default                   => 'berjalan',
    };

    $lhp->auditAssignment->updateQuietly([
        'status' => $assignmentStatus,
    ]);
}

    /**
     * Pisahkan field dengan prefix 'assignment_' dari data form.
     */
    private function extractAssignmentData(array &$data): array
    {
        $assignmentData = [];
        $keysToRemove   = [];

        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'assignment_')) {
                $assignmentKey                  = substr($key, strlen('assignment_'));
                $assignmentData[$assignmentKey] = $value;
                $keysToRemove[]                 = $key;
            }
        }

        foreach ($keysToRemove as $key) {
            unset($data[$key]);
        }

        return $assignmentData;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}