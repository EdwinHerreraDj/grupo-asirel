<?php

namespace Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Support\Facades\Schema;
use ReflectionClass;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Las relaciones Eloquent sin clave explícita deducen la clave foránea del
 * nombre del método o del modelo. Si la columna real se llama distinto, la
 * relación devuelve null en silencio (p. ej. FacturaVentaPago::factura()
 * buscaba `factura_id` en vez de `factura_venta_id`).
 *
 * Este test recorre todas las relaciones de app/Models y comprueba que sus
 * columnas existen en la base de datos.
 */
class RelacionesModelosTest extends TestCase
{
    public function test_las_columnas_de_todas_las_relaciones_existen(): void
    {
        $errores = [];
        $revisadas = 0;

        foreach (glob(app_path('Models/*.php')) as $fichero) {
            $clase = 'App\\Models\\'.basename($fichero, '.php');

            if (! class_exists($clase)) {
                continue;
            }

            $reflexion = new ReflectionClass($clase);
            if ($reflexion->isAbstract() || ! $reflexion->isSubclassOf(Model::class)) {
                continue;
            }

            $modelo = new $clase;
            $lineas = file($fichero);

            foreach ($reflexion->getMethods(ReflectionMethod::IS_PUBLIC) as $metodo) {
                if (
                    $metodo->isStatic()
                    || $metodo->getNumberOfParameters() > 0
                    || $metodo->getFileName() !== $reflexion->getFileName()
                ) {
                    continue;
                }

                $codigo = implode('', array_slice($lineas, $metodo->getStartLine() - 1, $metodo->getEndLine() - $metodo->getStartLine() + 1));
                if (! preg_match('/->(belongsTo|hasMany|hasOne|belongsToMany)\(/', $codigo)) {
                    continue;
                }

                $nombre = class_basename($clase).'::'.$metodo->getName().'()';

                try {
                    $relacion = $metodo->invoke($modelo);
                } catch (\Throwable $e) {
                    $errores[] = "{$nombre}: no se pudo construir ({$e->getMessage()})";
                    continue;
                }

                $revisadas++;

                if ($relacion instanceof BelongsToMany) {
                    $pivote = $relacion->getTable();
                    foreach ([$relacion->getForeignPivotKeyName(), $relacion->getRelatedPivotKeyName()] as $columna) {
                        if (! Schema::hasColumn($pivote, $columna)) {
                            $errores[] = "{$nombre}: falta la columna {$pivote}.{$columna}";
                        }
                    }
                } elseif ($relacion instanceof BelongsTo) {
                    $tabla = $modelo->getTable();
                    if (! Schema::hasColumn($tabla, $relacion->getForeignKeyName())) {
                        $errores[] = "{$nombre}: falta la columna {$tabla}.{$relacion->getForeignKeyName()}";
                    }
                    $tablaRelacionada = $relacion->getRelated()->getTable();
                    if (! Schema::hasColumn($tablaRelacionada, $relacion->getOwnerKeyName())) {
                        $errores[] = "{$nombre}: falta la columna {$tablaRelacionada}.{$relacion->getOwnerKeyName()}";
                    }
                } elseif ($relacion instanceof HasOneOrMany) {
                    $tablaRelacionada = $relacion->getRelated()->getTable();
                    if (! Schema::hasColumn($tablaRelacionada, $relacion->getForeignKeyName())) {
                        $errores[] = "{$nombre}: falta la columna {$tablaRelacionada}.{$relacion->getForeignKeyName()}";
                    }
                }
            }
        }

        $this->assertGreaterThan(20, $revisadas, 'Se esperaban más relaciones revisadas');
        $this->assertSame([], $errores, "Relaciones con columnas inexistentes:\n".implode("\n", $errores));
    }
}
