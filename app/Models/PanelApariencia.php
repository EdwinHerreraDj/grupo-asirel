<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Imágenes del panel: logo del menú, icono del menú plegado y favicon.
 * Siempre hay una sola fila. Si un campo está vacío se usa la imagen por
 * defecto del tema. No tiene nada que ver con el logo de la empresa, que
 * es el que sale en los PDFs e informes.
 */
class PanelApariencia extends Model
{
    public const CAMPOS = ['logo', 'logo_pequeno', 'favicon'];

    protected $table = 'panel_apariencia';

    protected $fillable = self::CAMPOS;

    private static ?self $vigente = null;

    private static bool $cargada = false;

    /** La fila de ajustes (se crea la primera vez). */
    public static function actual(): self
    {
        $fila = static::firstOrCreate([]);
        static::olvidar();

        return $fila;
    }

    /** Lo que ven las vistas; se consulta una vez por petición. */
    public static function vigente(): ?self
    {
        if (! static::$cargada) {
            static::$vigente = static::first();
            static::$cargada = true;
        }

        return static::$vigente;
    }

    /** Tras guardar o quitar una imagen, volver a leerla. */
    public static function olvidar(): void
    {
        static::$vigente = null;
        static::$cargada = false;
    }

    public function url(string $campo): ?string
    {
        return $this->{$campo} ? Storage::disk('public')->url($this->{$campo}) : null;
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->url('logo');
    }

    /** Menú plegado: su imagen o, si no hay, la del favicon. */
    public function getIconoUrlAttribute(): ?string
    {
        return $this->url('logo_pequeno') ?? $this->url('favicon');
    }

    /** Favicon: el suyo o, si no hay, el icono del menú plegado. */
    public function getFaviconUrlAttribute(): ?string
    {
        return $this->url('favicon') ?? $this->url('logo_pequeno');
    }
}
