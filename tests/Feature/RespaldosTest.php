<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Gasto;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use PDO;
use Tests\TestCase;

final class RespaldosTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->travelTo(Carbon::create(2026, 10, 18, 3));
        config([
            'services.telegram.token' => 'TOKEN-PRUEBA',
            'services.telegram.chat_id' => '555',
        ]);
    }

    public function test_creates_a_compressed_copy_that_can_be_restored(): void
    {
        Gasto::factory()->create(['servicio' => 'Internet']);

        $this->artisan('respaldos:crear')
            ->expectsOutputToContain('gastos-2026-10-18-030000.sqlite.gz')
            ->assertSuccessful();

        $this->assertSame(['respaldos/gastos-2026-10-18-030000.sqlite.gz'], Storage::disk('local')->files('respaldos'));
        $this->assertSame(['Internet'], $this->serviciosEn('respaldos/gastos-2026-10-18-030000.sqlite.gz'));
    }

    public function test_keeps_only_the_most_recent_backups(): void
    {
        foreach (range(1, 15) as $dia) {
            Storage::disk('local')->put(sprintf('respaldos/gastos-2026-10-%02d-030000.sqlite.gz', $dia), 'viejo');
        }
        Storage::disk('local')->put('respaldos/notas.txt', 'no es un respaldo');

        $this->artisan('respaldos:crear', ['--conservar' => 3])->assertSuccessful();

        $this->assertSame([
            'respaldos/gastos-2026-10-14-030000.sqlite.gz',
            'respaldos/gastos-2026-10-15-030000.sqlite.gz',
            'respaldos/gastos-2026-10-18-030000.sqlite.gz',
            'respaldos/notas.txt',
        ], Storage::disk('local')->files('respaldos'));
    }

    public function test_backups_are_scheduled(): void
    {
        $this->artisan('schedule:list')
            ->expectsOutputToContain('respaldos:crear')
            ->expectsOutputToContain('respaldos:enviar')
            ->assertSuccessful();
    }

    public function test_sends_the_latest_backup_to_telegram(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 1]])]);
        Storage::disk('local')->put('respaldos/gastos-2026-10-11-030000.sqlite.gz', 'anterior');
        Storage::disk('local')->put('respaldos/gastos-2026-10-18-030000.sqlite.gz', 'reciente');

        $this->artisan('respaldos:enviar')
            ->expectsOutputToContain('gastos-2026-10-18-030000.sqlite.gz')
            ->assertSuccessful();

        Http::assertSentCount(1);
        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://api.telegram.org/botTOKEN-PRUEBA/sendDocument'
            && $request->isMultipart()
            && $this->campo($request, 'chat_id') === '555'
            && str_contains($this->campo($request, 'caption'), 'Mis Gastos')
            && $request->hasFile('document', 'reciente', 'gastos-2026-10-18-030000.sqlite.gz'));
    }

    public function test_does_not_send_when_telegram_is_not_configured(): void
    {
        Http::fake();
        config(['services.telegram.token' => null]);
        Storage::disk('local')->put('respaldos/gastos-2026-10-18-030000.sqlite.gz', 'reciente');

        $this->artisan('respaldos:enviar')
            ->expectsOutputToContain('Telegram no está configurado')
            ->assertSuccessful();

        Http::assertNothingSent();
    }

    public function test_fails_to_send_when_there_are_no_backups(): void
    {
        Http::fake();

        $this->artisan('respaldos:enviar')
            ->expectsOutputToContain('No hay respaldos')
            ->assertFailed();

        Http::assertNothingSent();
    }

    private function campo(Request $request, string $nombre): ?string
    {
        return collect($request->data())->firstWhere('name', $nombre)['contents'] ?? null;
    }

    /**
     * @return list<string>
     */
    private function serviciosEn(string $respaldo): array
    {
        $copia = storage_path('framework/testing/restaurado-'.uniqid().'.sqlite');
        File::put($copia, gzdecode(Storage::disk('local')->get($respaldo)));

        try {
            $pdo = new PDO('sqlite:'.$copia);

            return $pdo->query('select servicio from gastos')->fetchAll(PDO::FETCH_COLUMN);
        } finally {
            unset($pdo);
            File::delete($copia);
        }
    }
}
