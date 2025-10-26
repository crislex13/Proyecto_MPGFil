<?php

namespace App\Listeners\Auth;

use Illuminate\Auth\Events\Logout;
use App\Models\ActivityLog;

class LogSuccessfulLogout
{
    public function handle(Logout $event): void
    {
        ActivityLog::create([
            'log_name'    => 'auth',
            'event'       => 'logout',
            'description' => 'Cierre de sesión',
            'causer_type' => $event->user ? get_class($event->user) : null,
            'causer_id'   => $event->user->id ?? null,
            'ip_address'  => request()->ip(),
            'user_agent'  => substr((string) request()->userAgent(), 0, 255),
        ]);
    }
}
