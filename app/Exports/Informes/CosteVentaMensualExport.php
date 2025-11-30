<?php

namespace App\Exports\Informes;

use App\Models\Obra;
use App\Models\Material;
use App\Models\Alquiler;
use App\Models\Subcontrata;
use App\Models\GastosVarios;
use App\Models\Venta;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CosteVentaMensualExport implements FromView, WithTitle, ShouldAutoSize
{
    public function __construct(
        public ?int $obraId = null,
        public ?string $estado = null,
        public ?string $fechaInicio = null,
        public ?string $fechaFin = null,
        public float $porcentaje = 10 // porcentaje adicional por defecto
    ) {}

    public function view(): View
    {
        $fechaInicio = $this->fechaInicio ? Carbon::parse($this->fechaInicio) : Carbon::now()->startOfYear();
        $fechaFin = $this->fechaFin ? Carbon::parse($this->fechaFin) : Carbon::now()->endOfYear();

        // Obtenemos todas las obras filtradas
        $obras = Obra::query()
            ->when($this->obraId, fn($q) => $q->where('id', $this->obraId))
            ->when($this->estado && $this->estado !== 'todas', fn($q) => $q->where('estado', $this->estado))
            ->get();

        $resultado = collect();

        foreach ($obras as $obra) {
            $mesInicio = $fechaInicio->copy()->startOfMonth();
            $mesFin = $fechaFin->copy()->endOfMonth();

            while ($mesInicio <= $mesFin) {
                $inicioMes = $mesInicio->copy()->startOfMonth();
                $finMes = $mesInicio->copy()->endOfMonth();

                $costeMateriales = $obra->materiales()->whereBetween('fecha', [$inicioMes, $finMes])->sum('importe');
                $costeAlquileres = $obra->alquileres()->whereBetween('fecha', [$inicioMes, $finMes])->sum('importe');
                $costeSubcontratas = $obra->subcontratas()->whereBetween('fecha', [$inicioMes, $finMes])->sum('importe');
                $costeGastos = $obra->gastosVarios()->whereBetween('fecha', [$inicioMes, $finMes])->sum('importe');

                $costeTotal = $costeMateriales + $costeAlquileres + $costeSubcontratas + $costeGastos;

                // Aplicamos el porcentaje adicional
                $costeAjustado = $costeTotal + ($costeTotal * ($this->porcentaje / 100));

                // Ventas del mes
                $ventasTotal = $obra->ventas()->whereBetween('fecha', [$inicioMes, $finMes])->sum('importe');

                // Beneficio
                $beneficio = $ventasTotal - $costeAjustado;

                $resultado->push([
                    'obra' => $obra->nombre,
                    'mes' => $inicioMes->format('F Y'),
                    'coste_real' => $costeTotal,
                    'porcentaje' => $this->porcentaje,
                    'coste_ajustado' => $costeAjustado,
                    'facturacion' => $ventasTotal,
                    'beneficio' => $beneficio,
                ]);

                $mesInicio->addMonth();
            }
        }

        return view('exports.informes.coste-venta-mensual', [
            'resultado' => $resultado,
            'filtros' => [
                'estado' => $this->estado,
                'fechaInicio' => $fechaInicio,
                'fechaFin' => $fechaFin,
                'porcentaje' => $this->porcentaje,
            ]
        ]);
    }

    public function title(): string
    {
        return 'Coste-Venta Mensual';
    }
}
