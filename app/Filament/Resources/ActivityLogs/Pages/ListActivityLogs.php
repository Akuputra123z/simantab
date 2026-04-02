<?php

namespace App\Filament\Resources\ActivityLogs\Pages;

use App\Filament\Resources\ActivityLogs\ActivityLogResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\ListRecords;
use Spatie\Activitylog\Models\Activity;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class ListActivityLogs extends ListRecords
{
    protected static string $resource = ActivityLogResource::class;

    protected function getHeaderActions(): array
    {
        return [

            ActionGroup::make([
                Action::make('log_hari_ini')
                    ->label('Hapus Log Hari Ini')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Log Hari Ini')
                    ->modalDescription('Semua log yang dibuat hari ini akan dihapus.')
                    ->action(function () {
                        $deleted = Activity::whereDate('created_at', now()->toDateString())->delete();

                        Notification::make()
                            ->title("{$deleted} log hari ini berhasil dihapus.")
                            ->success()
                            ->send();
                    }),

                Action::make('log_lebih_90_hari')
                    ->label('Hapus Log > 90 Hari')
                    ->icon('heroicon-o-trash')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Log Lama')
                    ->modalDescription('Semua log yang lebih dari 90 hari akan dihapus.')
                    ->action(function () {
                        $deleted = Activity::where('created_at', '<', now()->subDays(90))->delete();

                        Notification::make()
                            ->title("{$deleted} log lama berhasil dihapus.")
                            ->success()
                            ->send();
                    }),

                // Optional: Hapus Semua Log
                Action::make('hapus_semua_log')
                    ->label('Hapus Semua Log')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Semua Log')
                    ->modalDescription('Semua log aktivitas akan dihapus secara permanen.')
                    ->action(function () {
                        $deleted = Activity::query()->delete();

                        Notification::make()
                            ->title("Semua log ({$deleted}) berhasil dihapus.")
                            ->success()
                            ->send();
                    }),
            ])
            ->label('Bersihkan Log')
            ->icon('heroicon-o-trash')
            ->button()
            
        ];
    }
}