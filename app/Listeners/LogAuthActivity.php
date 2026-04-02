<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Spatie\Activitylog\Facades\Activity;

class LogAuthActivity
{
    public function handleLogin(Login $event): void
    {
        activity('auth')
            ->causedBy($event->user)
            ->withProperties([
                'ip'         => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])
            ->log('Login ke sistem');
    }

    public function handleLogout(Logout $event): void
    {
        if (! $event->user) return;

        activity('auth')
            ->causedBy($event->user)
            ->withProperties([
                'ip' => request()->ip(),
            ])
            ->log('Logout dari sistem');
    }
}