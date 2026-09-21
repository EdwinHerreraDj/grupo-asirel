<?php

namespace App\Console\Commands;

use App\Mail\AlertasRrhhMail;
use App\Models\User;
use App\Services\Rrhh\AlertasRrhh;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Muestra las alertas de Recursos humanos y, con --enviar, las manda por
 * email a los administradores (solo si hay urgentes o avisos).
 *
 * No está programado: para recibirlo cada mañana hay que añadir al cron del
 * servidor, por ejemplo:
 *   30 7 * * 1-5  cd /ruta/app && php artisan rrhh:alertas --enviar
 */
class RrhhAlertas extends Command
{
    protected $signature = 'rrhh:alertas {--enviar : Enviar el resumen por email a los administradores}';

    protected $description = 'Alertas de Recursos humanos (contratos, documentación, bajas, nóminas…)';

    public function handle(AlertasRrhh $servicio): int
    {
        $resultado = $servicio->calcular();
        $t = $resultado['totales'];

        $this->info("Alertas: {$t['critico']} urgentes, {$t['aviso']} avisos, {$t['info']} informativas.");
        $this->table(
            ['Nivel', 'Categoría', 'Empleado', 'Alerta'],
            collect($resultado['alertas'])->map(fn ($a) => [
                $a['nivel'],
                AlertasRrhh::CATEGORIAS[$a['categoria']] ?? $a['categoria'],
                $a['empleado']['nombre_completo'] ?? '—',
                $a['titulo'].': '.$a['detalle'],
            ])->all(),
        );

        if (! $this->option('enviar')) {
            return self::SUCCESS;
        }

        if ($t['critico'] + $t['aviso'] === 0) {
            $this->info('Nada urgente: no se envía email.');

            return self::SUCCESS;
        }

        $destinatarios = User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])
            ->whereNotNull('email')
            ->pluck('email')
            ->all();

        if (! $destinatarios) {
            $this->warn('No hay administradores con email.');

            return self::SUCCESS;
        }

        try {
            Mail::to($destinatarios)->send(new AlertasRrhhMail($resultado));
        } catch (Throwable $e) {
            $this->error('No se pudo enviar el email: '.$e->getMessage());
            report($e);

            return self::FAILURE;
        }

        $this->info('Email enviado a: '.implode(', ', $destinatarios));

        return self::SUCCESS;
    }
}
