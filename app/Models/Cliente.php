<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;
    
    protected $table = 'clientes';
    
    protected $fillable = [
        'nombre',
        'cif',
        'email',        
        'emails',       
        'telefono',     
        'telefonos',    
        'direccion',
        'descripcion',
        'activo',
    ];
    
    protected $casts = [
        'activo' => 'boolean',
        'emails' => 'array',    
        'telefonos' => 'array', 
    ];

    // Accessor para obtener todos los emails (principal + adicionales)
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

    // Accessor para obtener todos los teléfonos (principal + adicionales)
    public function getTodosLosTelefonosAttribute()
    {
        $telefonos = [];
        
        if ($this->telefono) {
            $telefonos[] = $this->telefono;
        }
        
        if ($this->telefonos) {
            $telefonos = array_merge($telefonos, $this->telefonos);
        }
        
        return array_filter($telefonos);
    }
}