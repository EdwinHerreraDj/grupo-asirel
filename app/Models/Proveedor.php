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
        'telefono',      // Teléfono principal
        'telefonos',     // Array de objetos {numero, etiqueta}
        'email',         // Email principal
        'emails',        // Array de emails adicionales
        'direccion',
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
}