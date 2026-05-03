<?php

declare(strict_types=1);

namespace App\Services\AuthBridge;

/**
 * Single source of truth for «which consumer hosts the auth bridge knows
 * about». Both controllers ask this service, so the whitelist + URL math
 * is not duplicated and not leaking into HTTP layer.
 */
class ConsumerDomainRegistry
{
    /**
     * @param  list<string>  $hosts
     */
    public function __construct(private array $hosts)
    {
        //
    }

    public static function fromConfig(): self
    {
        /** @var list<string> $hosts */
        $hosts = array_values((array) config('auth_bridge.consumer_domains'));

        return new self($hosts);
    }

    public function isAllowedHost(string $host): bool
    {
        return $host !== '' && in_array($host, $this->hosts, true);
    }

    /**
     * True only for absolute http(s) URLs whose host is whitelisted.
     */
    public function isAllowedUrl(string $url): bool
    {
        $parts = $this->parse($url);

        if ($parts === null) {
            return false;
        }

        if (! in_array($parts['scheme'], ['https', 'http'], true)) {
            return false;
        }

        return $this->isAllowedHost($parts['host']);
    }

    /**
     * True if the URL is whitelisted AND its host matches the given expected
     * host. Used by the consume endpoint to enforce same-origin `next` and
     * prevent the bridge from becoming an open-redirect vector.
     */
    public function isSameHostAllowedUrl(string $url, string $expectedHost): bool
    {
        $parts = $this->parse($url);

        if ($parts === null) {
            return false;
        }

        return $parts['host'] === $expectedHost && $this->isAllowedHost($expectedHost);
    }

    /**
     * Build the consumer-side bridge endpoint URL from a whitelisted return URL.
     * Caller MUST have validated the URL via isAllowedUrl() first.
     */
    public function consumeUrlFor(string $returnUrl): string
    {
        $parts = $this->parse($returnUrl) ?? ['scheme' => 'https', 'host' => $this->fallbackHost(), 'port' => null];

        $port = $parts['port'] !== null ? ':'.$parts['port'] : '';

        return $parts['scheme'].'://'.$parts['host'].$port.'/auth/consume';
    }

    public function fallbackHomeUrl(): string
    {
        return 'https://'.$this->fallbackHost();
    }

    private function fallbackHost(): string
    {
        return $this->hosts[0] ?? 'growskins.com';
    }

    /**
     * @return array{scheme: string, host: string, port: int|null}|null
     */
    private function parse(string $url): ?array
    {
        if ($url === '') {
            return null;
        }

        $parts = parse_url($url);

        if (! is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) {
            return null;
        }

        return [
            'scheme' => (string) $parts['scheme'],
            'host' => (string) $parts['host'],
            'port' => isset($parts['port']) ? (int) $parts['port'] : null,
        ];
    }
}
