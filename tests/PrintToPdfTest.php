<?php declare(strict_types=1);

namespace Andrew72ru\Web2print\Tests;

use Andrew72ru\Web2print\Exception\{DriverException, PrintException};
use Andrew72ru\Web2print\{DriverRunner, DriverRunnerInterface, PrintToPdf};
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class PrintToPdfTest extends TestCase
{
    public function testUnableToRunDriverException(): void
    {
        $driver = $this->createMock(DriverRunnerInterface::class);
        $driver->expects(self::once())->method('run')->willThrowException(new DriverException());
        $logger = $this->createMock(LoggerInterface::class);

        $printer = new PrintToPdf($driver, $logger);
        $this->expectException(PrintException::class);
        $this->expectExceptionMessage('Unable to run driver');

        $printer('https://example.com');
    }

    public function testExecuteWithoutErrors(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $file = \realpath(__DIR__ . '/_data/example.html');
        $url = \sprintf('file://%s', $file);

        $runner = new DriverRunner($logger, \dirname(__DIR__) . '/chromedriver');
        $printer = new PrintToPdf($runner, $logger);

        $logger->expects(self::atLeast(2))->method('info');
        $logger->expects(self::never())->method('error');

        $base64 = $printer($url);
        $bin = $printer($url, false);
        self::assertEquals($bin, \base64_decode($base64));
    }
}
