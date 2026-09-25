<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Acceso de un usuario: cuándo entró, desde dónde y cuándo cerró sesión.
 *
 * El cierre solo se guarda cuando el usuario pulsa "Cerrar sesión". Si la
 * sesión caduca sola, `logged_out_at` se queda vacío: por eso un acceso sin
 * cierre pasado el tiempo de sesión se considera caducado, no abierto.
 */
class LoginLog extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'ip_address', 'user_agent', 'logged_in_at', 'logged_out_at'];

    protected $casts = [
        'logged_in_at' => 'datetime',
        'logged_out_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Accesos sin cierre registrado que aún están dentro del tiempo de sesión. */
    public function scopeAbiertas(Builder $query): Builder
    {
        return $query->whereNull('logged_out_at')
            ->where('logged_in_at', '>=', now()->subMinutes((int) config('session.lifetime', 120)));
    }

    /** abierta | cerrada | caducada */
    public function getEstadoAttribute(): string
    {
        if ($this->logged_out_at) {
            return 'cerrada';
        }

        return $this->logged_in_at?->gte(now()->subMinutes((int) config('session.lifetime', 120)))
            ? 'abierta'
            : 'caducada';
    }

    /** Minutos que duró la sesión (si se cerró). */
    public function getDuracionMinutosAttribute(): ?int
    {
        return $this->logged_out_at && $this->logged_in_at
            ? (int) $this->logged_in_at->diffInMinutes($this->logged_out_at)
            : null;
    }

    /** "2 h 15 min" */
    public function getDuracionAttribute(): ?string
    {
        $minutos = $this->duracion_minutos;

        if ($minutos === null) {
            return null;
        }
        if ($minutos < 60) {
            return "{$minutos} min";
        }

        $horas = intdiv($minutos, 60);
        $resto = $minutos % 60;

        return $resto ? "{$horas} h {$resto} min" : "{$horas} h";
    }

    /** Navegador aproximado a partir del user agent. */
    public function getNavegadorAttribute(): string
    {
        $ua = (string) $this->user_agent;

        return match (true) {
            str_contains($ua, 'Edg/') => 'Edge',
            str_contains($ua, 'OPR/') || str_contains($ua, 'Opera') => 'Opera',
            str_contains($ua, 'Chrome/') && ! str_contains($ua, 'Chromium') => 'Chrome',
            str_contains($ua, 'Firefox/') => 'Firefox',
            str_contains($ua, 'Safari/') => 'Safari',
            $ua === '' => 'Desconocido',
            default => 'Otro',
        };
    }

    /** Ordenador, móvil o tableta. */
    public function getDispositivoAttribute(): string
    {
        $ua = (string) $this->user_agent;

        return match (true) {
            str_contains($ua, 'iPad') || str_contains($ua, 'Tablet') => 'Tableta',
            str_contains($ua, 'Android') || str_contains($ua, 'iPhone') || str_contains($ua, 'Mobile') => 'Móvil',
            $ua === '' => 'Desconocido',
            default => 'Ordenador',
        };
    }

    /** Sistema operativo aproximado. */
    public function getSistemaAttribute(): string
    {
        $ua = (string) $this->user_agent;

        return match (true) {
            str_contains($ua, 'Windows') => 'Windows',
            str_contains($ua, 'iPhone') || str_contains($ua, 'iPad') => 'iOS',
            str_contains($ua, 'Mac OS') => 'macOS',
            str_contains($ua, 'Android') => 'Android',
            str_contains($ua, 'Linux') => 'Linux',
            default => '—',
        };
    }
}
