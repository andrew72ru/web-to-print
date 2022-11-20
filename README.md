# Web to Print

The package for generating PDF pages from HTML with headless Chrome and Chrome driver.

## Requirements

This package requires PHP >= 8.0 with `ctype` and `iconv` extensions. You must also have installed Chrome (chromium) and `cromedriver` executables in your working environment.

## Usage

First, create an instance of the Chrome Driver runner:

```php
$logger = new \Psr\Log\NullLogger(); // Choose your Psr\Log\LoggerInterface implementation
$runner = new \Andrew72ru\Web2print\DriverRunner($logger, '/path/to/chromedriver');
```

After that, you can call the `PrintToPdf` class:

```php
$printer = new \Andrew72ru\Web2print\PrintToPdf($runner, $logger);
$result = $printer('https://google.com', asBas64: true); // You can get Base64 or binary string
\file_put_contents('google.pdf', $result);
```

As a result, you will have a 'google.pdf' file in your working directory.

## Testing

Before tests, you must put the `chromedriver` executable into the project root.

Run tests:

```shell
vendor/bin/phpunit
```
