<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class FacturasRecibidasExport implements FromView
{
    public function __construct(
        public $facturas,
        public $totales,
        public $obra,
        public $empresa
    ) {}

    public function view(): View
    {
        return view('excel.facturas-recibidas', [
            'facturas' => $this->facturas,
            'totales'  => $this->totales,
            'obra'     => $this->obra,
            'empresa'  => $this->empresa,
        ]);
    }
}
