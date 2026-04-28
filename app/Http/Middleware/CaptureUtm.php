<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\UtmMark;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Captures UTM params, ?ref slug, and Referer header on landing.
 * Stored once and never overwritten — the FIRST source the user came from.
 *
 * Resolves to a UtmMark (admin-created via slug, or auto-created from UTM combo).
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

            $refSlug = $request->query('ref');

            if (is_string($refSlug) && $refSlug !== '') {
                $captured['ref'] = mb_substr($refSlug, 0, 64);
            }

            $referrer = $request->headers->get('referer');

            if ($referrer && ! str_starts_with($referrer, $request->getSchemeAndHttpHost())) {
                $captured['referrer'] = mb_substr($referrer, 0, 512);
            }

            $hasAnySource = isset($captured['ref']) || ! empty(array_intersect_key($captured, array_flip(self::UTM_KEYS)));

            if ($hasAnySource) {
                $captured['ip'] = $request->ip();

                $mark = UtmMark::resolve(
                    $captured['ref'] ?? null,
                    array_intersect_key($captured, array_flip(self::UTM_KEYS)),
                );

                if ($mark !== null) {
                    $captured['mark_id'] = $mark->id;
                }

                $request->session()->put(self::SESSION_KEY, $captured);
            }
        }

        return $next($request);
    }
}
