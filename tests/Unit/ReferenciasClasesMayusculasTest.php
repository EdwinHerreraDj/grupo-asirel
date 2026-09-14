<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * En Windows el sistema de archivos no distingue mayúsculas, pero en el
 * servidor (Linux) sí: `App\Services\Facturas\X` y `App\Services\facturas\X`
 * son rutas distintas y el autoload puede cargar un fichero equivocado o
 * no encontrar ninguno. Este test falla si alguna referencia a una clase
 * `App\...` existe en disco con otra combinación de mayúsculas.
 */
class ReferenciasClasesMayusculasTest extends TestCase
{
    private const CARPETAS = ['app', 'routes', 'database', 'tests', 'config', 'resources/views'];

    /** @var array<string, string[]> */
    private array $listados = [];

    public function test_las_referencias_a_clases_app_respetan_mayusculas(): void
    {
        $root = dirname(__DIR__, 2);
        $errores = [];

        foreach (self::CARPETAS as $carpeta) {
            $ruta = $root.DIRECTORY_SEPARATOR.$carpeta;
            if (! is_dir($ruta)) {
                continue;
            }

            $ficheros = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($ruta, RecursiveDirectoryIterator::SKIP_DOTS));

            foreach ($ficheros as $fichero) {
                if (! str_ends_with($fichero->getFilename(), '.php')) {
                    continue;
                }

                $contenido = file_get_contents($fichero->getPathname());
                preg_match_all('/\bApp\\\\((?:[A-Za-z0-9_]+\\\\)*[A-Za-z0-9_]+)/', $contenido, $coincidencias);

                foreach (array_unique($coincidencias[1]) as $clase) {
                    $problema = $this->comprobar($root, $clase);
                    if ($problema) {
                        $relativo = substr($fichero->getPathname(), strlen($root) + 1);
                        $errores[] = "{$relativo}: App\\{$clase} → en disco es {$problema}";
                    }
                }
            }
        }

        $this->assertSame([], $errores, "Referencias con mayúsculas que no coinciden con el disco:\n".implode("\n", $errores));
    }

    /**
     * Devuelve la ruta real si existe con otras mayúsculas, o null si
     * coincide exactamente o no corresponde a ningún fichero (p. ej. namespaces).
     */
    private function comprobar(string $root, string $clase): ?string
    {
        $segmentos = explode('\\', $clase);
        $ultimo = array_pop($segmentos).'.php';
        $segmentos[] = $ultimo;

        $actual = $root.DIRECTORY_SEPARATOR.'app';
        $real = ['app'];
        $difiere = false;

        foreach ($segmentos as $segmento) {
            $entradas = $this->listar($actual);

            if (in_array($segmento, $entradas, true)) {
                $encontrado = $segmento;
            } else {
                $alternativas = array_values(array_filter($entradas, fn ($e) => strcasecmp($e, $segmento) === 0));
                if (! $alternativas) {
                    return null;
                }
                $encontrado = $alternativas[0];
                $difiere = true;
            }

            $real[] = $encontrado;
            $actual .= DIRECTORY_SEPARATOR.$encontrado;
        }

        return $difiere ? implode('/', $real) : null;
    }

    /** @return string[] */
    private function listar(string $directorio): array
    {
        if (! isset($this->listados[$directorio])) {
            $this->listados[$directorio] = is_dir($directorio) ? (scandir($directorio) ?: []) : [];
        }

        return $this->listados[$directorio];
    }
}
