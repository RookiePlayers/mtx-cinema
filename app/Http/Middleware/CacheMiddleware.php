<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;


//This caching middleware is applied to all GET requests in routes/web.php,
//and it uses Redis to store cached responses. It generates a unique cache key based on the request's method, URL, headers, and user information.
class CacheMiddleware
{
    public function handle(Request $request, Closure $next, ?int $ttl = null): Response
    {
        if (! $request->isMethod('GET')) {
            return $next($request);
        }

        $seconds = max(0, $ttl ?? (int) env('HTTP_CACHE_TTL', 60));

        if ($seconds === 0) {
            return $next($request);
        }

        $cacheKey = $this->buildCacheKey($request);
        $cachedResponse = Cache::store('redis')->get($cacheKey);

        if (is_array($cachedResponse)) {
            $response = new Response(
                $cachedResponse['content'],
                $cachedResponse['status'],
                $cachedResponse['headers'],
            );

            $response->headers->set('X-Response-Cache', 'HIT');
            $response->headers->set('Cache-Control', sprintf('private, max-age=%d', $seconds));

            return $response;
        }

        /** @var Response $response */
        $response = $next($request);

        if (
            ! $response->isSuccessful()
            || $response->headers->has('Set-Cookie')
            || $response instanceof StreamedResponse
            || $response instanceof BinaryFileResponse
        ) {
            return $response;
        }

        $headers = $response->headers->all();
        unset($headers['set-cookie']);

        Cache::store('redis')->put($cacheKey, [
            'status' => $response->getStatusCode(),
            'content' => $response->getContent(),
            'headers' => $headers,
        ], $seconds);

        $response->headers->set('Cache-Control', sprintf('private, max-age=%d', $seconds));
        $response->headers->set('X-Response-Cache', 'MISS');

        return $response;
    }

    private function buildCacheKey(Request $request): string
    {
        return 'http_response_cache:'.sha1(json_encode([
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'accept' => $request->header('Accept'),
            'inertia' => $request->header('X-Inertia'),
            'inertia_version' => $request->header('X-Inertia-Version'),
            'user_id' => $request->user()?->id,
            'guest_token' => $request->cookie('guest_token'),
        ]));
    }
}
