<?php declare(strict_types=1);

namespace Andrew72ru\Web2print;

use Andrew72ru\Web2print\Exception\DriverException;
use Symfony\Component\Process\Process;

interface DriverRunnerInterface
{
    /**
     * @throws DriverException
     */
    public function run(): Process;

    public function stop(): ?int;
}
