<?php declare(strict_types=1);

namespace Andrew72ru\Web2print;

use Andrew72ru\Web2print\Exception\DriverException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Process\Process;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

final class DriverRunner implements DriverRunnerInterface
{
    public const DEFAULT_TIMEOUT = 30;

    private Process | null $process = null;

    public function __construct(private LoggerInterface $logger, private string $driverPath, private int $driverPort = 4444, private array $driverOptions = [])
    {
    }

    /**
     * @throws DriverException
     */
    public function run(): Process
    {
        $this->logger->info(\sprintf('Starting Chrome-driver at %s', \date_create()->format(\DateTimeInterface::ATOM)));

        $options = \array_merge([\sprintf('--port=%s', $this->driverPort)], $this->driverOptions);
        $this->process = new Process([$this->driverPath, ...$options]);

        $this->process->start(function (string $type, string $buffer) {
            if ($type === Process::ERR) {
                $this->logger->error($buffer);
            }

            if ($type === Process::OUT) {
                $this->logger->info($buffer);
            }
        });

        /** @var mixed $optTimeout get timeout from options */
        $optTimeout = $this->driverOptions['timeout'] ?? null;
        $timeout = \is_numeric($optTimeout) ? (int) $optTimeout : self::DEFAULT_TIMEOUT;
        try {
            $this->waitUntilReady($this->process, $timeout);
        } catch (\Throwable $e) {
            throw new DriverException('Unable to start Chromedriver', previous: $e);
        }

        $this->logger->info(\sprintf('Chrome-driver started at %s', \date_create()->format(\DateTimeInterface::ATOM)));

        return $this->process;
    }

    public function stop(): ?int
    {
        if ($this->process instanceof Process && $this->process->isRunning()) {
            return $this->process->stop();
        }

        return null;
    }

    /**
     * Inspired by @see https://github.com/symfony/panther/blob/main/src/ProcessManager/ChromeManager.php.
     *
     * @throws TransportExceptionInterface
     */
    private function waitUntilReady(Process $process, int $timeout = 30): void
    {
        $client = HttpClient::create(['timeout' => $timeout]);
        $start = \microtime(true);

        while (true) {
            $status = $process->getStatus();
            if (Process::STATUS_TERMINATED === $status) {
                throw new \RuntimeException(\sprintf('Could not start Webdriver. Exit code: %d (%s). Error output: %s', (string) $process->getExitCode(), (string) $process->getExitCodeText(), $process->getErrorOutput()));
            }

            if (Process::STATUS_STARTED !== $status) {
                if (\microtime(true) - $start >= $timeout) {
                    throw new \RuntimeException(\sprintf('Could not start Webdriver (or it crashed) after %s seconds.', $timeout));
                }

                \usleep(1000);

                continue;
            }

            $response = $client->request('GET', \sprintf('http://localhost:%s/status', $this->driverPort));
            $e = $statusCode = null;
            try {
                $statusCode = $response->getStatusCode();
                if ($statusCode === 200) {
                    return;
                }
            } catch (\Throwable $e) {
            }

            if (\microtime(true) - $start >= $timeout) {
                if ($e instanceof \Throwable) {
                    $message = $e->getMessage();
                } else {
                    $message = \sprintf('Status code: %s', (int) $statusCode);
                }
                throw new \RuntimeException(\sprintf('Could not connect to Webdriver after %s seconds (%s).', $timeout, $message));
            }

            \usleep(1000);
        }
    }
}
