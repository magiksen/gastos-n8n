<?php

declare(strict_types=1);

namespace App\Services\Respaldos;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\DatabaseManager;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use RuntimeException;

final readonly class RespaldosBaseDatos
{
    private const CARPETA = 'respaldos';

    private const PATRON = '/^gastos-\d{4}-\d{2}-\d{2}-\d{6}\.sqlite\.gz$/';

    public function __construct(
        private DatabaseManager $baseDeDatos,
        private FilesystemManager $discos,
    ) {}

    public function crear(): string
    {
        $conexion = $this->baseDeDatos->connection();

        if ($conexion->getDriverName() !== 'sqlite') {
            throw new RuntimeException('Los respaldos solo funcionan con bases SQLite.');
        }

        $respaldo = self::CARPETA.'/gastos-'.now()->format('Y-m-d-His').'.sqlite.gz';
        $this->disco()->makeDirectory(self::CARPETA);
        $copia = $this->disco()->path($respaldo.'.tmp');

        try {
            $conexion->statement('VACUUM INTO ?', [$copia]);
            $this->disco()->put($respaldo, gzencode(File::get($copia), 9));
        } finally {
            File::delete($copia);
        }

        return $respaldo;
    }

    public function conservarUltimos(int $cantidad): int
    {
        $sobrantes = $this->recientes()->slice($cantidad)->values();

        $this->disco()->delete($sobrantes->all());

        return $sobrantes->count();
    }

    public function masReciente(): ?string
    {
        return $this->recientes()->first();
    }

    public function rutaCompleta(string $respaldo): string
    {
        return $this->disco()->path($respaldo);
    }

    public function tamanoLegible(string $respaldo): string
    {
        return number_format($this->disco()->size($respaldo) / 1024, 0, ',', '.').' KB';
    }

    /**
     * @return Collection<int, string>
     */
    private function recientes(): Collection
    {
        return collect($this->disco()->files(self::CARPETA))
            ->filter(fn (string $archivo): bool => preg_match(self::PATRON, basename($archivo)) === 1)
            ->sortDesc()
            ->values();
    }

    private function disco(): Filesystem
    {
        return $this->discos->disk('local');
    }
}
