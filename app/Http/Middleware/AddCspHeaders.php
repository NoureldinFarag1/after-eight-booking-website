<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AddCspHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Set a comprehensive Content Security Policy that allows eval for Chart.js and Alpine.js
        // Also allow TinyMCE (cdn.tiny.cloud) and Google Maps iframe preview
        $csp = implode('; ', [
            "default-src 'self'",
            // Allow core CDNs plus Google's reCAPTCHA domains
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://unpkg.com https://cdn.tiny.cloud https://www.google.com https://www.gstatic.com",
            // Allow styles from TinyMCE CDN and Google Fonts
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com https://cdn.tiny.cloud",
            // TinyMCE skins may load fonts from its CDN
            "font-src 'self' data: https://cdn.jsdelivr.net https://fonts.gstatic.com https://cdn.tiny.cloud",
            "img-src 'self' data: https: blob:",
            // Allow TinyMCE to fetch any assets and talk to its API and reCAPTCHA network activity
            "connect-src 'self' https://cdn.tiny.cloud https://api.tiny.cloud https://www.google.com https://www.gstatic.com",
            // Enable Google Maps embed preview and reCAPTCHA iframes
            "frame-src 'self' https://www.google.com https://recaptcha.google.com",
        ]);

        $response->headers->set('Content-Security-Policy', $csp);
        return $response;
    }
}
