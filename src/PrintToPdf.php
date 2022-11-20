<?php declare(strict_types=1);

namespace Andrew72ru\Web2print;

use Andrew72ru\Web2print\Exception\PrintException;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\{DesiredCapabilities, RemoteWebDriver};
use Psr\Log\LoggerInterface;

final class PrintToPdf implements PrinterInterface
{
    private static array $defaultArguments = ['--headless', '--run-all-compositor-stages-before-draw'];
    private static array $defaultParams = [
        'displayHeaderFooter' => false,
        'printBackground' => true,
        'marginTop' => 0,
        'marginBottom' => 0,
        'marginLeft' => 0,
        'marginRight' => 0,
    ];

    public function __construct(
        private DriverRunnerInterface $runner,
        private LoggerInterface $logger,
        private int $driverPort = 4444,
        private array $arguments = [],
        private array $params = [],
    ) {
    }

    public function __invoke(string $url, bool $asBas64 = true): string
    {
        try {
            $process = $this->runner->run();
        } catch (\Throwable $e) {
            throw new PrintException('Unable to run driver', previous: $e);
        }
        $caps = DesiredCapabilities::chrome();
        $options = new ChromeOptions();
        $arguments = \array_merge(self::$defaultArguments, $this->arguments);
        $this->logger->info('Run Driver with arguments', $arguments);

        $options->addArguments($arguments);
        $caps->setCapability(ChromeOptions::CAPABILITY, $options);

        $driver = RemoteWebDriver::create(\sprintf('http://localhost:%s', (string) $this->driverPort), $caps);
        $driver->get($url);

        $printParams = \array_merge(self::$defaultParams, $this->params);
        $this->logger->info('Execute Driver command with parameters', $printParams);

        $result = $driver->executeCustomCommand('/session/:sessionId/goog/cdp/execute', 'POST', [
            'cmd' => 'Page.printToPDF',
            'params' => $printParams,
        ]);

        if ($process->isRunning()) {
            $this->runner->stop();
        }
        if (!\is_array($result)) {
            throw new PrintException('Command execution return null');
        }

        $data = $result['data'] ?? null;
        if (!\is_string($data)) {
            $this->logger->error('Command returns unexpected result', $result);

            throw new PrintException('Unable to load data');
        }

        if ($asBas64) {
            return $data;
        }

        return \base64_decode($data);
    }
}
