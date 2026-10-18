<?php

declare(strict_types=1);

namespace Tests\Feature\Telegram;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class ConfigurarTelegramTest extends TestCase
{
    private const PREGUNTA_TOKEN = 'Pega el token que te dio @BotFather';

    private string $carpetaEnv;

    protected function setUp(): void
    {
        parent::setUp();

        $this->carpetaEnv = storage_path('framework/testing/env-'.uniqid());
        File::ensureDirectoryExists($this->carpetaEnv);
        File::put($this->carpetaEnv.'/.env', "APP_NAME=\"Mis Gastos\"\nTELEGRAM_CHAT_ID=viejo\n");
        $this->app->useEnvironmentPath($this->carpetaEnv);
        config(['app.url' => 'https://samupruebas.site']);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->carpetaEnv);

        parent::tearDown();
    }

    public function test_configures_bot_chat_and_webhook(): void
    {
        Http::fake([
            '*/getMe' => Http::response(['ok' => true, 'result' => ['username' => 'MisGastosBot']]),
            '*/deleteWebhook' => Http::response(['ok' => true, 'result' => true]),
            '*/getUpdates*' => Http::response(['ok' => true, 'result' => [
                ['update_id' => 10, 'message' => ['chat' => ['id' => 123456], 'text' => '/start']],
            ]]),
            '*/setWebhook' => Http::response(['ok' => true, 'result' => true]),
            '*/sendMessage' => Http::response(['ok' => true, 'result' => ['message_id' => 1]]),
        ]);

        $this->artisan('telegram:configurar')
            ->expectsQuestion(self::PREGUNTA_TOKEN, '123:ABC')
            ->expectsOutputToContain('@MisGastosBot')
            ->assertSuccessful();

        $env = File::get($this->carpetaEnv.'/.env');
        $this->assertStringContainsString('APP_NAME="Mis Gastos"', $env);
        $this->assertStringContainsString("TELEGRAM_BOT_TOKEN=123:ABC\n", $env);
        $this->assertStringContainsString("TELEGRAM_CHAT_ID=123456\n", $env);
        $this->assertStringNotContainsString('viejo', $env);
        $this->assertMatchesRegularExpression('/^TELEGRAM_WEBHOOK_SECRET=[A-Za-z0-9]{48}$/m', $env);
        preg_match('/^TELEGRAM_WEBHOOK_SECRET=(.+)$/m', $env, $secreto);

        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://api.telegram.org/bot123:ABC/setWebhook'
            && $request['url'] === 'https://samupruebas.site/telegram/webhook'
            && $request['secret_token'] === $secreto[1]
            && $request['allowed_updates'] === ['callback_query']);
        Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/sendMessage')
            && $request['chat_id'] === 123456);
    }

    public function test_fails_with_invalid_token_without_touching_env(): void
    {
        Http::fake(['*/getMe' => Http::response(['ok' => false, 'description' => 'Unauthorized'], 401)]);

        $this->artisan('telegram:configurar')
            ->expectsQuestion(self::PREGUNTA_TOKEN, 'malo')
            ->expectsOutputToContain('token no es válido')
            ->assertFailed();

        $this->assertStringNotContainsString('TELEGRAM_BOT_TOKEN', File::get($this->carpetaEnv.'/.env'));
    }

    public function test_fails_when_nobody_writes_to_the_bot(): void
    {
        Http::fake([
            '*/getMe' => Http::response(['ok' => true, 'result' => ['username' => 'MisGastosBot']]),
            '*/deleteWebhook' => Http::response(['ok' => true, 'result' => true]),
            '*/getUpdates*' => Http::response(['ok' => true, 'result' => []]),
        ]);

        $this->artisan('telegram:configurar', ['--intentos' => 2])
            ->expectsQuestion(self::PREGUNTA_TOKEN, '123:ABC')
            ->expectsOutputToContain('No recibí ningún mensaje')
            ->assertFailed();

        $this->assertStringNotContainsString('TELEGRAM_BOT_TOKEN', File::get($this->carpetaEnv.'/.env'));
    }
}
