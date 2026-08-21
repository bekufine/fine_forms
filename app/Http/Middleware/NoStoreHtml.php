<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NoStoreHtml
{
    /**
     * Prevent caching of the SPA entry HTML.
     *
     * The HTML emitted by @vite() references content-hashed JS/CSS bundles.
     * Those bundles are safe to cache forever, but the HTML that points at
     * them must always be re-fetched — otherwise a browser (notably Android
     * in-app WebViews) can keep serving a stale HTML that loads an old bundle
     * with outdated wording, so different devices show different text.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }
}
