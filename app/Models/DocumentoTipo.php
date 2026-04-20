<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentoTipo extends Model
{
    use HasFactory;

    protected $table = 'documento_tipos';

    protected $fillable = ['nombre'];

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class);
    }

    public function esEliminable(): bool
    {
        return ! $this->documentos()->exists();
    }
}
