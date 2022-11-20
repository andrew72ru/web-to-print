<?php declare(strict_types=1);

namespace Andrew72ru\Web2print;

interface PrinterInterface
{
    public function __invoke(string $url, bool $asBas64 = true): string;
}
