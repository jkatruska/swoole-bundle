<?php

declare(strict_types=1);

namespace K911\Swoole\Tests\Feature;

use K911\Swoole\Client\HttpClient;
use K911\Swoole\Tests\Fixtures\Symfony\TestBundle\Test\ServerTestCase;

final class SwooleServerStartStopCommandTest extends ServerTestCase
{
    protected function setUp(): void
    {
        $this->markTestSkippedIfXdebugEnabled();
        $this->deleteVarDirectory();
    }

    public function testStartCallStop(): void
    {
        $serverStart = $this->createConsoleProcess([
            'swoole:server:start',
            '--host=localhost',
            '--port=9999',
        ]);

        $serverStart->setTimeout(3);
        $serverStart->disableOutput();
        $serverStart->run();

        $this->assertProcessSucceeded($serverStart);

        $this->runAsCoroutineAndWait(function (): void {
            $this->deferServerStop();

            $client = HttpClient::fromDomain('localhost', 9999, false);
            $this->assertTrue($client->connect());
            $this->assertHelloWorldRequestSucceeded($client);
        });
    }

    public function testStartCallStopOnReactorRunningMode(): void
    {
        $envs = ['APP_ENV' => 'reactor'];
        $serverStart = $this->createConsoleProcess([
            'swoole:server:start',
            '--host=localhost',
            '--port=9999',
        ], $envs);

        $serverStart->setTimeout(3);
        $serverStart->disableOutput();
        $serverStart->run();

        $this->assertProcessSucceeded($serverStart);

        $this->runAsCoroutineAndWait(function () use ($envs): void {
            $this->deferServerStop([], $envs);

            $client = HttpClient::fromDomain('localhost', 9999, false);
            $this->assertTrue($client->connect());
            $this->assertHelloWorldRequestSucceeded($client);
        });
    }
}
