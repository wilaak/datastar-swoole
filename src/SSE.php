<?php

namespace Wilaak\DatastarSwoole;

use starfederation\datastar\enums\ElementPatchMode;
use starfederation\datastar\events\EventInterface;
use starfederation\datastar\events\ExecuteScript;
use starfederation\datastar\events\Location;
use starfederation\datastar\events\PatchElements;
use starfederation\datastar\events\PatchSignals;
use starfederation\datastar\events\RemoveElements;
use starfederation\datastar\Consts;

/**
 * Server-Sent Event (SSE) generator for Datastar Swoole.
 */
class SSE
{
    /**
     * Whether the response headers have been sent.
     */
    public bool $headersSent = false;

    /**
     * Constructor for the SSE generator.
     *
     * @param \Swoole\Http\Request $request The Swoole HTTP request object.
     * @param \Swoole\Http\Response $response The Swoole HTTP response object.
     */
    public function __construct(
        private \Swoole\Http\Request $request,
        private \Swoole\Http\Response $response
    ) {}

    /**
     * Returns the signals sent in the incoming request.
     */
    public function readSignals(): array
    {
        $input = $this->request->get[Consts::DATASTAR_KEY] ?? $this->request->rawContent();
        $signals = $input ? \json_decode($input, true) : [];

        return \is_array($signals) ? $signals : [];
    }

    /**
     * Sends the response headers using the Swoole HTTP response object, if not already sent.
     */
    public function sendHeaders(): void
    {
        if ($this->headersSent) {
            return;
        }

        $headers = [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/event-stream',
            // Disable buffering for Nginx.
            // https://nginx.org/en/docs/http/ngx_http_proxy_module.html#proxy_buffering
            'X-Accel-Buffering' => 'no',
        ];

        // Connection-specific headers are only allowed in HTTP/1.1.
        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Connection
        $proto = $this->request->server['server_protocol'] ?? 'HTTP/1.1';
        if ($proto === 'HTTP/1.1') {
            $headers['Connection'] = 'keep-alive';
        }

        foreach ($headers as $name => $value) {
            $this->response->header($name, $value);
        }
        $this->headersSent = true;
    }

    /**
     * Patches HTML elements into the DOM and returns the resulting output.
     *
     * @param array{
     *     selector?: string|null,
     *     mode?: ElementPatchMode|string|null,
     *     useViewTransition?: bool|null,
     *     eventId?: string|null,
     *     retryDuration?: int|null,
     * } $options
     */
    public function patchElements(string $elements, array $options = []): string
    {
        return $this->sendEvent(new PatchElements($elements, $options));
    }

    /**
     * Patches signals and returns the resulting output.
     */
    public function patchSignals(array|string $signals, array $options = []): string
    {
        return $this->sendEvent(new PatchSignals($signals, $options));
    }

    /**
     * Removes elements from the DOM and returns the resulting output.
     *
     * @param array{
     *      eventId?: string|null,
     *      retryDuration?: int|null,
     *  } $options
     */
    public function removeElements(string $selector, array $options = []): string
    {
        return $this->sendEvent(new RemoveElements($selector, $options));
    }

    /**
     * Executes JavaScript in the browser and returns the resulting output.
     */
    public function executeScript(string $script, array $options = []): string
    {
        return $this->sendEvent(new ExecuteScript($script, $options));
    }

    /**
     * Redirects the browser by setting the location to the provided URI and returns the resulting output.
     */
    public function location(string $uri, array $options = []): string
    {
        return $this->sendEvent(new Location($uri, $options));
    }

    /**
     * Sends an event using the Swoole HTTP response object and returns the resulting output.
     */
    protected function sendEvent(EventInterface $event): string
    {
        if (!$this->headersSent) {
            $this->sendHeaders();
        }
        $output = $event->getOutput();
        $this->response->write($output);
        return $output;
    }
}
