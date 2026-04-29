# Getting Started with Tessitura PHP

This guide will walk you through installing the library and making your first API calls.

## Requirements

- PHP 8.0 or higher
- [Composer](https://getcomposer.org/)
- Tessitura API credentials (base URL, username, password, machine name, and usergroup)

## Installation

Install the library via Composer:

```bash
composer require clubdeuce/tessitura
```

## Basic Setup

### Direct API Usage

The simplest way to use the library is to instantiate the `Api` helper directly:

```php
use Clubdeuce\Tessitura\Helpers\Api;

$api = new Api([
    'base_route' => 'https://api.example.com/TessituraService/',
    'username'   => 'your_username',
    'password'   => 'your_password',
    'machine'    => 'your_machine',
    'usergroup'  => 'your_usergroup',
    'version'    => '16',
]);
```

### Using the Service Container

For applications that need multiple resources, the `Container` class manages dependencies for you:

```php
use Clubdeuce\Tessitura\Base\Container;

$container = new Container([
    'base_route' => 'https://api.example.com/TessituraService/',
    'username'   => 'your_username',
    'password'   => 'your_password',
    'machine'    => 'your_machine',
    'usergroup'  => 'your_usergroup',
    'version'    => '16',
]);

// The container lazily creates and caches services on first access.
$api = $container->get('api');
```

## Fetching Performances

### Get Upcoming Performances

```php
use Clubdeuce\Tessitura\Base\Container;
use Clubdeuce\Tessitura\Resources\Performances;

$container = new Container([
    'base_route' => 'https://api.example.com/TessituraService/',
    'username'   => 'your_username',
    'password'   => 'your_password',
    'machine'    => 'your_machine',
    'usergroup'  => 'your_usergroup',
]);

/** @var Performances $performances */
$performances = $container->get('performances');

// Get performances in the next 30 days (default)
$upcoming = $performances->getUpcomingPerformances();

// Or specify the number of days
$upcoming = $performances->getUpcomingPerformances(60);

foreach ($upcoming as $performance) {
    echo $performance->title() . ' — ' . $performance->date()->format('D, M j Y g:i A') . PHP_EOL;
}
```

### Search Performances Between Two Dates

```php
use DateTime;

$start = new DateTime('2025-01-01');
$end   = new DateTime('2025-03-31');

$performances = $container->get('performances');
$results = $performances->getPerformancesBetween($start, $end);

foreach ($results as $performance) {
    echo sprintf(
        "[%d] %s at %s on %s\n",
        $performance->id(),
        $performance->title(),
        $performance->facilityDescription(),
        $performance->date()->format('Y-m-d H:i')
    );
}
```

### Get Performances for a Production Season

```php
$productionSeasonId = 1234;
$performances = $container->get('performances');
$results = $performances->getPerformancesForProductionSeason($productionSeasonId);
```

### Check Zone Availability for a Performance

```php
$performanceId = 5678;
$performances  = $container->get('performances');
$zones = $performances->getPerformanceZoneAvailabilities($performanceId);

foreach ($zones as $zone) {
    echo 'Available seats in zone: ' . $zone->availableCount() . PHP_EOL;
}
```

## Adding a Logger

Pass any [PSR-3](https://www.php-fig.org/psr/psr-3/) compatible logger to the `Api` constructor to capture request activity:

```php
use Clubdeuce\Tessitura\Helpers\Api;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('tessitura');
$logger->pushHandler(new StreamHandler('php://stdout'));

$api = new Api(
    [
        'base_route' => 'https://api.example.com/TessituraService/',
        'username'   => 'your_username',
        'password'   => 'your_password',
        'machine'    => 'your_machine',
        'usergroup'  => 'your_usergroup',
    ],
    null,   // HTTP client (auto-created from base_route)
    $logger
);
```

## Using Environment Variables

Avoid hard-coding credentials by loading them from a `.env` file with
[vlucas/phpdotenv](https://github.com/vlucas/phpdotenv):

```bash
# .env
TESSITURA_BASE_ROUTE=https://api.example.com/TessituraService/
TESSITURA_USERNAME=your_username
TESSITURA_PASSWORD=your_password
TESSITURA_MACHINE=your_machine
TESSITURA_USERGROUP=your_usergroup
```

```php
use Dotenv\Dotenv;
use Clubdeuce\Tessitura\Base\Container;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$container = new Container([
    'base_route' => $_ENV['TESSITURA_BASE_ROUTE'],
    'username'   => $_ENV['TESSITURA_USERNAME'],
    'password'   => $_ENV['TESSITURA_PASSWORD'],
    'machine'    => $_ENV['TESSITURA_MACHINE'],
    'usergroup'  => $_ENV['TESSITURA_USERGROUP'],
]);
```

## Next Steps

- [Caching](caching.md) — reduce API calls with Redis or in-memory caching
- [Tasks and Roadmap](tasks.md) — planned improvements to the library
