<?php

namespace App\Filament\Resources\Lhps\Pages;

use App\Filament\Resources\Lhps\LhpResource;
use App\Models\AuditAssignment;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateLhp extends CreateRecord
{
    protected static string $resource = LhpResource::class;

    protected function beforeCreate(): void
    {
        $user = auth()->user();

        if (!$user->hasAnyRole(['super_admin', 'ketua_tim'])) {
            abort(403, 'Tidak punya izin membuat LHP');
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        // 🔥 MODE 1: Pakai assignment yang sudah dipilih
        if (!empty($data['audit_assignment_id'])) {
            return $data;
        }

        // 🔥 MODE 2: Buat assignment baru dari field assignment_*
        $assignmentData = $this->extractAssignmentData($data);

        // ❗ Kalau memang tidak ada data assignment → stop (biar tidak error)
        if (empty($assignmentData)) {
            throw new \Exception('Pilih penugasan audit atau isi data penugasan baru');
        }

        // ✅ Validasi hanya jika create baru
        foreach (['audit_program_id', 'unit_diperiksa_id', 'tanggal_mulai'] as $field) {
            if (empty($assignmentData[$field])) {
                throw new \Exception("Field {$field} wajib diisi");
            }
        }

        // 🔒 Paksa ketua_tim = user login (kecuali super_admin)
        if (!$user->hasRole('super_admin')) {
            $assignmentData['ketua_tim_id'] = $user->id;
        }

        // 🧠 Bersihkan null
        $assignmentData = array_filter($assignmentData, fn ($v) => !is_null($v));

        DB::beginTransaction();

        try {
            $assignment = AuditAssignment::create([
                'audit_program_id'   => $assignmentData['audit_program_id'],
                'unit_diperiksa_id'  => $assignmentData['unit_diperiksa_id'],
                'ketua_tim_id'       => $assignmentData['ketua_tim_id'] ?? $user->id,
                'nama_tim'           => $assignmentData['nama_tim'] ?? null,
                'tanggal_mulai'      => $assignmentData['tanggal_mulai'],
                'tanggal_selesai'    => $assignmentData['tanggal_selesai'] ?? null,
                'status'             => 'berjalan',
                'created_by'         => $user->id,
                'updated_by'         => $user->id,
            ]);

            $data['audit_assignment_id'] = $assignment->id;

            DB::commit();

            return $data;

        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function afterCreate(): void
    {
        $lhp = $this->record;

        if (!$lhp->auditAssignment) {
            return;
        }

        if (in_array($lhp->status, ['final', 'ditandatangani'])) {
            $lhp->auditAssignment->updateQuietly([
                'status' => 'selesai',
            ]);
        }
    }

    private function extractAssignmentData(array &$data): array
    {
        $assignmentData = [];
        $keysToRemove   = [];

        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'assignment_')) {
                $assignmentKey = substr($key, strlen('assignment_'));
                $assignmentData[$assignmentKey] = $value;
                $keysToRemove[] = $key;
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