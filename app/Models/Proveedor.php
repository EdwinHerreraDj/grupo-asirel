<?php
// app/Models/Proveedor.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Proveedor extends Model
{
    use HasFactory;
    
    protected $table = 'proveedores';
    
    protected $fillable = [
        'nombre',
        'cif',
        'telefono',
        'telefonos',
        'email',
        'emails',
        'direccion',
        'codigo_postal',
        'poblacion',
        'provincia',
        'pais',
        'tipo',
        'activo',
    ];
    
    protected $casts = [
        'activo' => 'boolean',
        'emails' => 'array',
        'telefonos' => 'array',  // [{numero: "123", etiqueta: "Facturación"}, ...]
    ];

    public function facturas()
    {
        return $this->hasMany(FacturaRecibida::class);
    }

    // Accessor para obtener todos los emails
    public function getTodosLosEmailsAttribute()
    {
        $emails = [];
        
        if ($this->email) {
            $emails[] = $this->email;
        }
        
        if ($this->emails) {
            $emails = array_merge($emails, $this->emails);
        }
        
        return array_filter($emails);
    }

    // Accessor para obtener todos los teléfonos con etiquetas
    public function getTodosLosTelefonosAttribute()
    {
        $telefonos = [];
        
        if ($this->telefono) {
            $telefonos[] = [
                'numero' => $this->telefono,
                'etiqueta' => 'Principal'
            ];
        }
        
        if ($this->telefonos && is_array($this->telefonos)) {
            foreach ($this->telefonos as $tel) {
                if (is_array($tel)) {
                    $telefonos[] = $tel;
                } else {
                    // Compatibilidad con formato antiguo (solo strings)
                    $telefonos[] = ['numero' => $tel, 'etiqueta' => ''];
                }
            }
        }

        return $telefonos;
    }

    // Dirección completa formateada en una línea
    public function getDireccionCompletaAttribute(): ?string
    {
        $linea1 = trim((string) $this->direccion);
        $cpPob  = trim(implode(' ', array_filter([$this->codigo_postal, $this->poblacion])));
        $provPais = trim(implode(', ', array_filter([$this->provincia, $this->pais])));

        $partes = array_filter([$linea1, $cpPob, $provPais]);

        return $partes ? implode(', ', $partes) : null;
    }
}