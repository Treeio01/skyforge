<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Captures UTM params and Referer header into session on landing.
 * Stored once and never overwritten — the FIRST source the user came from.
 */
class CaptureUtm
{
    private const UTM_KEYS = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'];

    private const SESSION_KEY = 'attribution';

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has(self::SESSION_KEY)) {
            $captured = [];

            foreach (self::UTM_KEYS as $key) {
                $value = $request->query($key);

                if (is_string($value) && $value !== '') {
                    $captured[$key] = mb_substr($value, 0, 128);
                }
            }

            $referrer = $request->headers->get('referer');

            if ($referrer && ! str_starts_with($referrer, $request->getSchemeAndHttpHost())) {
                $captured['referrer'] = mb_substr($referrer, 0, 512);
            }

            if (! empty($captured)) {
                $captured['ip'] = $request->ip();
                $request->session()->put(self::SESSION_KEY, $captured);
            }
        }

        return $next($request);
    }
}
