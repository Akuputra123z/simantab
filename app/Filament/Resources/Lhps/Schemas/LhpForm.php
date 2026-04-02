<?php

namespace App\Filament\Resources\Lhps\Schemas;

use App\Models\AuditAssignment;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
 use Illuminate\Support\Facades\Auth;

class LhpForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Tabs::make('lhp_tabs')
                    ->tabs([
                    
                        Tab::make('Informasi LHP')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Select::make('audit_assignment_id')
                                    ->label('Pilih Penugasan Audit')
                                    ->required()
                                    ->searchable()
                                    ->columnSpanFull()

                                    // ✅ Initial options saat form dibuka (sebelum user mengetik)
                                    ->options(function () {
                                        $user = Auth::user();

                                        return AuditAssignment::query()
                                            ->with('unitDiperiksa')
                                            ->when(!$user->hasRole('super_admin'), function ($q) use ($user) {
                                                $q->where(function ($q2) use ($user) {
                                                    $q2->where('ketua_tim_id', $user->id)
                                                    ->orWhereHas('members', fn ($q3) => $q3->where(
                                                        'audit_assignment_members.user_id', $user->id
                                                    ));
                                                });
                                            })
                                            ->orderByDesc('tanggal_mulai')
                                            ->limit(50)
                                            ->get()
                                            ->mapWithKeys(function ($record) {
                                                $unit = $record->unitDiperiksa?->nama_unit ?? 'N/A';
                                                return [$record->id => "{$unit} — {$record->nama_tim} | {$record->nomor_surat}"];
                                            })
                                            ->toArray();
                                    })

                                    // ✅ Label saat edit mode — format SAMA dengan options/search
                                    ->getOptionLabelUsing(function ($value): ?string {
                                        $record = AuditAssignment::with('unitDiperiksa')->find($value);
                                        if (!$record) return null;
                                        $unit = $record->unitDiperiksa?->nama_unit ?? 'N/A';
                                        return "{$unit} — {$record->nama_tim} | {$record->nomor_surat}";
                                    })

                                    // ✅ Hasil pencarian saat user mengetik
                                    ->getSearchResultsUsing(function (string $search): array {
                                        $user = Auth::user();

                                        return AuditAssignment::query()
                                            ->with('unitDiperiksa')
                                            ->where(function ($query) use ($search) {
                                                $query->where('nama_tim', 'like', "%{$search}%")
                                                    ->orWhere('nomor_surat', 'like', "%{$search}%")
                                                    ->orWhereHas('unitDiperiksa', function ($q2) use ($search) {
                                                        $q2->where('nama_unit', 'like', "%{$search}%");
                                                    });
                                            })
                                            ->when(!$user->hasRole('super_admin'), function ($q) use ($user) {
                                                $q->where(function ($q2) use ($user) {
                                                    $q2->where('ketua_tim_id', $user->id)
                                                    ->orWhereHas('members', fn ($q3) => $q3->where(
                                                        'audit_assignment_members.user_id', $user->id
                                                    ));
                                                });
                                            })
                                            ->limit(50)
                                            ->get()
                                            ->mapWithKeys(function ($record) {
                                                $unit = $record->unitDiperiksa?->nama_unit ?? 'N/A';
                                                return [$record->id => "{$unit} — {$record->nama_tim} | {$record->nomor_surat}"];
                                            })
                                            ->toArray();
                                    }),
                                Section::make('Identitas LHP')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('nomor_lhp')
                                            ->label('Nomor LHP')
                                            ->required()
                                            ->placeholder('700/008/001P/PKPT.2024')
                                            ->columnSpanFull(),

                                        DatePicker::make('tanggal_lhp')
                                            ->label('Tanggal LHP')
                                            ->native(false)
                                            ->required(),

                                        Select::make('semester')
                                            ->label('Semester')
                                            ->options([1 => 'Semester I', 2 => 'Semester II'])
                                            ->required()
                                            ->native(false),

                                        Select::make('jenis_pemeriksaan')
                                            ->label('Jenis Pemeriksaan')
                                            ->options([
                                                'Reguler'     => 'Reguler',
                                                'Khusus'      => 'Khusus',
                                                'Investigasi' => 'Investigasi',
                                                'ADTT'        => 'ADTT',
                                            ])
                                            ->required()
                                            ->native(false),

                                        Select::make('irban')
                                            ->label('Irban')
                                            ->options([
                                                'Irban I'   => 'Irban I',
                                                'Irban II'  => 'Irban II',
                                                'Irban III' => 'Irban III',
                                            ])
                                            ->native(false),

                                        Select::make('status')
                                            ->label('Status')
                                            ->options([
                                                'draft'          => 'Draft',
                                                'final'          => 'Final',
                                                'ditandatangani' => 'Ditandatangani',
                                            ])
                                            ->default('draft')
                                            ->required()
                                            ->native(false),
                                    ]),

                                Section::make('Ringkasan')
                                    ->schema([
                                        Textarea::make('catatan_umum')
                                            ->label('Simpulan Hasil Audit')
                                            ->rows(4)
                                            ->placeholder('Ringkasan temuan atau simpulan...')
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // --- TAB 3: FILE ---
                        Tab::make('Lampiran')
                            ->icon('heroicon-o-paper-clip')
                            ->schema([
                                Repeater::make('attachments')
                                    ->relationship()
                                    ->schema([
                                        FileUpload::make('file_path')
                                            ->label('File LHP (PDF/Img)')
                                            ->directory('lhp/attachments')
                                            ->disk('public')
                                            ->required(),

                                        TextInput::make('file_name')
                                            ->label('Keterangan File')
                                            ->required(),

                                        Hidden::make('jenis_bukti')->default('lhp'),
                                        Hidden::make('uploaded_by')->default(fn () => auth()->id()),
                                    ])
                                    ->columns(2)
                                    ->collapsible()
                                    ->addActionLabel('Tambah Dokumen'),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}