<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

final class EmailSummaryRemovedTest extends TestCase
{
    public function test_daily_email_summary_command_no_longer_exists(): void
    {
        $this->assertArrayNotHasKey('gastos:resumen-diario', Artisan::all());
    }

    public function test_daily_email_summary_is_not_scheduled(): void
    {
        $comandos = collect($this->app->make(Schedule::class)->events())
            ->map(fn (Event $tarea): string => (string) $tarea->command);

        $this->assertFalse($comandos->contains(fn (string $comando): bool => str_contains($comando, 'gastos:resumen-diario')));
        $this->assertTrue($comandos->contains(fn (string $comando): bool => str_contains($comando, 'telegram:avisos')));
    }
}
