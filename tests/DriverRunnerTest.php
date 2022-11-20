<?php declare(strict_types=1);

namespace Andrew72ru\Web2print\Tests;

use Andrew72ru\Web2print\DriverRunner;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;

class DriverRunnerTest extends TestCase
{
    public function testRunMethod(): void
    {
        $logger = $this->createMock(AbstractLogger::class);
        $logger->expects(self::atLeastOnce())->method('info');

        $runner = new DriverRunner($logger, \dirname(__DIR__) . '/chromedriver', 8888, ['--verbose']);
        $result = $runner->run();
        self::assertTrue($result->isRunning());
        self::assertStringContainsString('8888', $result->getCommandLine());
        self::assertStringContainsString('--verbose', $result->getCommandLine());

        $runner->stop();
    }

    public function testWrongDriverPath(): void
    {
        $logger = $this->createMock(AbstractLogger::class);
        $logger->expects(self::atLeastOnce())->method('error');
        $runner = new DriverRunner($logger, 'no-such-file');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Unable to start Chromedriver/');
        $runner->run();
    }

    public function testStopNotRunning(): void
    {
        $logger = $this->createMock(AbstractLogger::class);
        $runner = new DriverRunner($logger, \dirname(__DIR__) . '/chromedriver');

        self::assertNull($runner->stop());
    }
}
