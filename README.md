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
\file_put_contents('google.pdf', \base64_decode($result));
```

As a result, you will have a 'google.pdf' file in your working directory.

## Default options

The `PrintToPdf` class has a set of default options to run the Chrome and the print command.

### Chrome options

```php
$defaultArguments = ['--headless', '--run-all-compositor-stages-before-draw'];
```

### Print options

```php
$defaultParams = [
    'displayHeaderFooter' => false,
    'printBackground' => true,
    'marginTop' => 0,
    'marginBottom' => 0,
    'marginLeft' => 0,
    'marginRight' => 0,
];
```

See references in [Chrome manual](https://chromedevtools.github.io/devtools-protocol/tot/Page/#method-printToPDF).

## Testing

Before tests, you must put the `chromedriver` executable into the project root.

Run tests:

```shell
vendor/bin/phpunit
```
