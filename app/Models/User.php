<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use App\Traits\HasAuditoria;

class User extends Authenticatable
{
    use HasAuditoria;
    use HasFactory, Notifiable, SoftDeletes, HasRoles, HasPanelShield;

    protected $fillable = [
        'name',
        'ci',
        'username',
        'password',
        'telefono',
        'foto',
        'estado',
        'registrado_por',
        'modificado_por',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getFotoUrlAttribute(): string
    {
        return $this->foto
            ? asset('storage/' . $this->foto)
            : asset('/images/default-user.png');
    }

    public function getEnLineaAttribute(): bool
    {
        return $this->last_login_at && $this->last_login_at->gt(now()->subMinutes(5));
    }

    public function canAccessPanel(string $panel): bool
    {
        return match ($panel) {
            'admin' => $this->hasAnyRole(['admin', 'supervisor', 'recepcionista']),
            'cliente-dashboard' => $this->hasRole('cliente'),
            'instructor-dashboard' => $this->hasRole('instructor'),
            'dashboard-multiples' => $this->hasAnyRole(['cliente', 'instructor']),
            default => false,
        };
    }
}