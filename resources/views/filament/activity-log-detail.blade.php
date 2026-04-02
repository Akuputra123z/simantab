{{-- Load Tailwind CDN khusus untuk blade ini --}}
<script src="https://cdn.tailwindcss.com"></script>

<div class="space-y-4 text-sm">

    {{-- HEADER INFO --}}
    <div class="grid grid-cols-2 gap-4 p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
        <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Waktu</p>
            <p class="font-semibold text-gray-900 dark:text-white">
                {{ $record->created_at->format('d M Y, H:i:s') }}
            </p>
            <p class="text-xs text-gray-400">{{ $record->created_at->diffForHumans() }}</p>
        </div>

        <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Pengguna</p>
            <p class="font-semibold text-gray-900 dark:text-white">
                {{ $record->causer?->name ?? '— Sistem —' }}
            </p>
            <p class="text-xs text-gray-400">{{ $record->causer?->email ?? '' }}</p>
        </div>

        <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Modul</p>
            <p class="font-semibold capitalize text-gray-900 dark:text-white">
                {{ match($record->log_name) {
                    'lhp'            => '📋 LHP',
                    'temuan'         => '🔍 Temuan',
                    'recommendation' => '📌 Rekomendasi',
                    'tindaklanjut'   => '✅ Tindak Lanjut',
                    'auth'           => '🔐 Auth',
                    default          => ucfirst($record->log_name ?? '-'),
                } }}
            </p>
        </div>

        <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Aksi</p>
            @php
                [$eventLabel, $eventClass] = match($record->event) {
                    'created'  => ['✅ Dibuat',     'text-green-600'],
                    'updated'  => ['✏️ Diubah',     'text-blue-600'],
                    'deleted'  => ['🗑️ Dihapus',   'text-red-600'],
                    'restored' => ['♻️ Dipulihkan', 'text-yellow-600'],
                    default    => [ucfirst($record->event ?? '-'), 'text-gray-600'],
                };
            @endphp
            <p class="font-bold {{ $eventClass }}">{{ $eventLabel }}</p>
        </div>

        <div class="col-span-2">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Keterangan</p>
            <p class="font-semibold text-gray-900 dark:text-white">{{ $record->description }}</p>
        </div>

        <div class="col-span-2">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Data Terkait</p>
            <p class="font-semibold text-gray-900 dark:text-white">
                {{ class_basename($record->subject_type ?? '') }} #{{ $record->subject_id ?? '-' }}
                @if($record->subject)
                    —
                    {{ $record->subject?->nomor_lhp
                        ?? $record->subject?->kondisi
                        ?? $record->subject?->uraian_rekom
                        ?? $record->subject?->jenis_penyelesaian
                        ?? '' }}
                @endif
            </p>
        </div>
    </div>

    {{-- IP & USER AGENT --}}
    @php
        $props     = is_array($record->properties) ? $record->properties : $record->properties->toArray();
        $ip        = $props['ip'] ?? null;
        $userAgent = $props['user_agent'] ?? null;
    @endphp

    @if($ip || $userAgent)
        <div class="p-4 rounded-lg bg-blue-50 border border-blue-100">
            <p class="mb-2 text-xs font-semibold text-blue-600 uppercase tracking-wide">🖥️ Info Perangkat</p>
            @if($ip)
                <p class="text-xs text-gray-700">
                    <span class="font-semibold">IP Address:</span> {{ $ip }}
                </p>
            @endif
            @if($userAgent)
                <p class="text-xs text-gray-700 break-all mt-1">
                    <span class="font-semibold">Browser / Device:</span> {{ $userAgent }}
                </p>
            @endif
        </div>
    @endif

    {{-- PERUBAHAN DATA --}}
    @php
        $old     = $props['old'] ?? [];
        $new     = $props['attributes'] ?? [];
        $changes = [];

        if (!empty($old)) {
            foreach ($new as $key => $val) {
                $oldVal = $old[$key] ?? null;
                if ($oldVal !== $val) {
                    $changes[$key] = ['old' => $oldVal, 'new' => $val];
                }
            }
        }

        if (empty($old) && !empty($new) && $record->event === 'created') {
            foreach ($new as $key => $val) {
                $changes[$key] = ['old' => null, 'new' => $val];
            }
        }
    @endphp

    @if(!empty($changes))
        <div>
            <p class="mb-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                📝 Detail Perubahan ({{ count($changes) }} field)
            </p>
            <div class="overflow-hidden border border-gray-200 rounded-lg">
                <table class="w-full text-xs">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold text-gray-600 w-1/4">Field</th>
                            <th class="px-3 py-2 text-left font-semibold text-red-500 w-[37.5%]">✗ Sebelum</th>
                            <th class="px-3 py-2 text-left font-semibold text-green-600 w-[37.5%]">✓ Sesudah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($changes as $field => $diff)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-3 py-2 font-mono font-semibold text-gray-700">{{ $field }}</td>
                                <td class="px-3 py-2 text-red-500 break-all">
                                    @if(is_null($diff['old']))
                                        <em class="text-gray-400">(kosong)</em>
                                    @elseif(is_array($diff['old']))
                                        <code>{{ json_encode($diff['old']) }}</code>
                                    @else
                                        <span class="line-through">{{ $diff['old'] }}</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-green-600 font-semibold break-all">
                                    @if(is_null($diff['new']))
                                        <em class="text-gray-400">(kosong)</em>
                                    @elseif(is_array($diff['new']))
                                        <code>{{ json_encode($diff['new']) }}</code>
                                    @else
                                        {{ $diff['new'] }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- LOG AUTH --}}
    @if($record->log_name === 'auth' && empty($changes))
        <div class="p-4 rounded-lg bg-green-50 border border-green-100">
            <p class="text-sm font-medium text-green-700">
                {{ $record->description }} pada
                <strong>{{ $record->created_at->format('d M Y') }}</strong>
                pukul
                <strong>{{ $record->created_at->format('H:i:s') }}</strong>
            </p>
        </div>
    @endif

    {{-- TIDAK ADA PERUBAHAN --}}
    @if(empty($changes) && $record->log_name !== 'auth')
        <div class="p-4 rounded-lg bg-gray-50 text-center">
            <p class="text-xs text-gray-400 italic">Tidak ada detail perubahan yang tersimpan.</p>
        </div>
    @endif

</div>