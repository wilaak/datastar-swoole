# Datastar SDK for Swoole PHP

This package offers an SDK for integrating [Datastar](https://data-star.dev) with [Swoole](https://wiki.swoole.com/en/#/).

Traditional PHP SAPI servers such as Apache, PHP-FPM or FrankenPHP struggle with efficiently handling large numbers of concurrent long-lived requests.

Swoole’s asynchronous, coroutine-driven architecture allows your application to efficiently manage thousands of simultaneous long-lived connections.

## Installation

First you must install the Swoole PHP extension. Please refer to the [documentation](https://wiki.swoole.com/en/#/environment?id=pecl).

    composer require wilaak/datastar-swoole

## Usage Examples

```PHP
$http = new \Swoole\Http\Server("0.0.0.0", 8082);

$http->on('request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
    $sse = new \Wilaak\DatastarSwoole\SSE($request, $response);

    $message = "Hello, World!";
    foreach (str_split($message) as $i => $char) {
        $sse->patchElements("<h3 id='message'>" . substr($message, 0, $i + 1) . "</h3>");
        co::sleep(1);
    }
});

$http->start();
```

```php
use starfederation\datastar\enums\ElementPatchMode;

$http = new \Swoole\Http\Server("0.0.0.0", 8082);

$http->on('request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {

    // Creates a new `SSE` instance.
    $sse = new \Wilaak\DatastarSwoole\SSE($request, $response);

    // Reads signals from the request.
    $signals = $sse->readSignals();

    // Patches elements into the DOM.
    $sse->patchElements('<div></div>', [
        'selector' => '#my-div',
        'mode' => ElementPatchMode::Append,
        'useViewTransition' => true,
    ]);

    // Removes elements from the DOM.
    $sse->removeElements('#my-div', [
        'useViewTransition' => true,
    ]);

    // Patches signals.
    $sse->patchSignals(['foo' => 123], [
        'onlyIfMissing' => true,
    ]);

    // Executes JavaScript in the browser.
    $sse->executeScript('console.log("Hello, world!")', [
        'autoRemove' => true,
        'attributes' => [
            'type' => 'application/javascript',
        ],
    ]);

    // Redirects the browser by setting the location to the provided URI.
    $sse->location('/guide');
});

$http->start();
```

## License

MIT