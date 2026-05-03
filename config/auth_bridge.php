<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Auth Bridge Domain
    |--------------------------------------------------------------------------
    |
    | The domain that hosts the Steam OpenID flow. All consumer domains
    | (main + mirrors) redirect users here to authenticate, then receive
    | a short-lived signed token back. Steam application "realm" must be
    | registered to this domain.
    |
    */
    'auth_domain' => env('AUTH_BRIDGE_DOMAIN', 'growsauth.com'),

    /*
    |--------------------------------------------------------------------------
    | Consumer Domains Whitelist
    |--------------------------------------------------------------------------
    |
    | Hosts that the auth domain is allowed to redirect tokens to. Anything
    | else is rejected to prevent token theft via fake `return` URLs.
    | Comma-separated string in env. Compared on host (no scheme/path).
    |
    */
    'consumer_domains' => array_filter(array_map(
        'trim',
        explode(',', (string) env('AUTH_BRIDGE_CONSUMERS', 'growskins.com')),
    )),

    /*
    |--------------------------------------------------------------------------
    | Shared Secret
    |--------------------------------------------------------------------------
    |
    | HMAC secret used to sign bridge tokens. Both the auth domain and
    | every consumer domain MUST share the same value. Rotate by deploying
    | the new value everywhere within ~2 minutes (longer than token TTL).
    |
    */
    'secret' => env('AUTH_BRIDGE_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Token TTL (seconds)
    |--------------------------------------------------------------------------
    |
    | Bridge tokens are one-shot: a fresh JTI + this short window. Long
    | enough for the redirect roundtrip, short enough that a leaked token
    | is useless before a human can act on it.
    |
    */
    'token_ttl' => 60,

    /*
    |--------------------------------------------------------------------------
    | Bridge Enabled
    |--------------------------------------------------------------------------
    |
    | When true (production), the consumer domain login button points at
    | the auth domain. When false (local dev), the legacy direct Steam
    | flow on the consumer domain stays in use.
    |
    */
    'enabled' => (bool) env('AUTH_BRIDGE_ENABLED', false),
];
