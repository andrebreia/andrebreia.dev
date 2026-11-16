<?php

use Sentry\Event;
use Sentry\State\HubInterface;
use Symfony\Component\Process\Process;

it('reports application exceptions to Sentry', function () {
    $events = [];
    app(HubInterface::class)->getClient()->getOptions()->setBeforeSendCallback(function (Event $event) use (&$events): ?Event {
        $events[] = $event;

        return null;
    });

    report(new RuntimeException('Sentry reporting regression'));

    expect($events)->toHaveCount(1);
    expect($events[0]->getExceptions()[0]->getValue())->toBe('Sentry reporting regression');
});

it('sets environment-specific trace sampling and honors an explicit override', function (string $environment, ?string $override, float $expected) {
    $process = new Process([PHP_BINARY, '-r', <<<'PHP'
        require 'vendor/autoload.php';
        $app = require 'bootstrap/app.php';
        $app->loadEnvironmentFrom('.env.example');
        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        $options = $app->make(Sentry\State\HubInterface::class)->getClient()->getOptions();
        echo json_encode($options->getTracesSampleRate());
        PHP,
    ], base_path(), [
        'APP_ENV' => $environment,
        'SENTRY_TRACES_SAMPLE_RATE' => $override ?? false,
        'SENTRY_LARAVEL_DSN' => 'null',
        'SENTRY_DSN' => 'null',
    ]);

    $process->mustRun();

    expect((float) json_decode($process->getOutput(), true))->toBe($expected);
})->with([
    'local' => ['local', null, 1.0],
    'production' => ['production', null, 0.1],
    'disabled explicitly' => ['production', '0', 0.0],
]);
