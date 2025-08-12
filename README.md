# Datastar PHP Swoole SDK

This package provides an SDK for using [Datastar](https://data-star.dev) with [SwoolePHP](https://wiki.swoole.com/en/).

## Installation

    composer require wilaak/datastar-php-swoole

## Usage Examples

In the SwoolePHP HTTP Server each request is put in a [coroutine](https://wiki.swoole.com/en/#/start/coroutine), allowing you to use standard blocking PHP code without troubles.

```PHP
use Swoole\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Wilaak\Datastar\Swoole\ServerSentEventGenerator;

$http = new Server("0.0.0.0", 8082);

$http->on('request', function (Request $request, Response $response) {
    $sse = new ServerSentEventGenerator($request, $response);

    $message = "Hello, World!";
    foreach (str_split($message) as $i => $char) {
        $sse->patchElements("<h3 id='message'>" . substr($message, 0, $i + 1) . "</h3>");
        sleep(1);
    }
});

$http->start();
```

```php
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;
use Wilaak\Datastar\Swoole\ServerSentEventGenerator;
use starfederation\datastar\enums\ElementPatchMode;

$http = new Server("0.0.0.0", 8082);

$http->on('request', function (Request $request, Response $response) {
    // Creates a new `ServerSentEventGenerator` instance.
    $sse = new ServerSentEventGenerator($request, $response);

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