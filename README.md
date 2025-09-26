# Datastar SDK for Swoole PHP

This package offers an SDK for integrating [Datastar](https://data-star.dev) with [Swoole](https://wiki.swoole.com/en/#/). It is a simple "wrapper" of the official [PHP SDK](https://github.com/starfederation/datastar-php).

Traditional PHP SAPI servers (like Apache, PHP-FPM, or FrankenPHP) struggle to efficiently handle large numbers of concurrent, long-lived requests.

Swoole’s asynchronous, coroutine-driven architecture enables your application to manage thousands of simultaneous long-lived connections with high efficiency.

## Installation

First you must install the Swoole PHP extension. Please refer to the [documentation](https://wiki.swoole.com/en/#/environment?id=pecl).

    composer require wilaak/datastar-swoole

## Usage Examples

In Swoole, each request is put in its own [coroutine](https://wiki.swoole.com/en/#/coroutine), allowing you to write PHP code in a standard blocking way.

> [!TIP]    
> To ensure proper behavior of built-in functions, you must enable coroutine hooks. This is achieved by calling `\Swoole\Runtime::enableCoroutine()` at the start of your program.

```PHP
// After this line of code, file operations, sleep, Mysqli, PDO, streams, etc., all become asynchronous IO.
\Swoole\Runtime::enableCoroutine();

$http = new \Swoole\Http\Server("0.0.0.0", 8082);

$http->on('request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
    $sse = new \Wilaak\DatastarSwoole\SSE($request, $response);

    $message = "Hello, World!";
    foreach (str_split($message) as $i => $char) {
        $sse->patchElements("<h3 id='message'>" . substr($message, 0, $i + 1) . "</h3>");
        sleep(1);
    }
});

$http->start();
```

> [!NOTE]   
> When in a long-running request, it's important to close the connection once the user disconnects so as to not keep running forever:

```PHP
$http->on('request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
    $sse = new \Wilaak\DatastarSwoole\SSE($request, $response);
    while (true) {
        $sse->patchElements("<h3 id='message'>" . time() . "</h3>");
        $success = $response->write('ping: hello');
        if ($success === false) {
            break;
        }
        sleep(1);
    }
});
```

This example covers most of the usage possible with this SDK:

```php
use Swoole\Http\{Request, Response};
use DatastarSwoole\{SSE, ElementPatchMode};

$http = new Swoole\Http\Server("0.0.0.0", 8082);

$http->on('request', function (Request $request, Response $response) {

    // Creates a new SSE instance.
    $sse = new SSE($request, $response);

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