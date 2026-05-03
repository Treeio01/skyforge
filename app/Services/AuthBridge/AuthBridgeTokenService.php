<?php

declare(strict_types=1);

namespace App\Services\AuthBridge;

use App\Exceptions\AuthBridgeTokenException;
use Illuminate\Support\Facades\Cache;

/**
 * HMAC-signed one-shot bridge token. Format: base64url(payload).base64url(sig).
 *
 * Anti-replay: every token carries a random `jti`; once verified, the jti is
 * locked in cache for slightly longer than the TTL. A second consume of the
 * same token throws.
 */
class AuthBridgeTokenService
{
    /**
     * @param  array<string, mixed>  $claims
     */
    public function issue(array $claims): string
    {
        $body = (string) json_encode([
            ...$claims,
            'iat' => time(),
            'exp' => time() + (int) config('auth_bridge.token_ttl'),
            'jti' => bin2hex(random_bytes(16)),
        ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        $sig = hash_hmac('sha256', $body, $this->secret(), true);

        return self::b64u($body).'.'.self::b64u($sig);
    }

    /**
     * @return array<string, mixed>
     */
    public function consume(string $token): array
    {
        if (! str_contains($token, '.')) {
            throw new AuthBridgeTokenException('Malformed token');
        }

        [$bodyB64, $sigB64] = explode('.', $token, 2);
        $body = self::b64uDecode($bodyB64);
        $sig = self::b64uDecode($sigB64);

        $expected = hash_hmac('sha256', $body, $this->secret(), true);

        if (! hash_equals($expected, $sig)) {
            throw new AuthBridgeTokenException('Bad signature');
        }

        try {
            $payload = (array) json_decode($body, true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new AuthBridgeTokenException('Malformed payload');
        }

        $exp = (int) ($payload['exp'] ?? 0);

        if ($exp < time()) {
            throw new AuthBridgeTokenException('Expired');
        }

        $jti = (string) ($payload['jti'] ?? '');

        if ($jti === '') {
            throw new AuthBridgeTokenException('Missing jti');
        }

        $cacheKey = "auth_bridge:jti:{$jti}";

        if (! Cache::add($cacheKey, true, (int) config('auth_bridge.token_ttl') + 60)) {
            throw new AuthBridgeTokenException('Replay');
        }

        return $payload;
    }

    private function secret(): string
    {
        $secret = (string) config('auth_bridge.secret');

        if ($secret === '') {
            throw new AuthBridgeTokenException('AUTH_BRIDGE_SECRET is not configured');
        }

        return $secret;
    }

    private static function b64u(string $bytes): string
    {
        return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
    }

    private static function b64uDecode(string $str): string
    {
        $padded = str_pad(strtr($str, '-_', '+/'), strlen($str) % 4 === 0 ? strlen($str) : strlen($str) + (4 - strlen($str) % 4), '=');
        $decoded = base64_decode($padded, true);

        if ($decoded === false) {
            throw new AuthBridgeTokenException('Bad base64');
        }

        return $decoded;
    }
}
