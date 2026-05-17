<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SecurityHeaders Middleware
 * 
 * Adds security headers to all HTTP responses to protect against common web vulnerabilities:
 * - Clickjacking (X-Frame-Options)
 * - MIME sniffing (X-Content-Type-Options)
 * - XSS attacks (X-XSS-Protection, Content-Security-Policy)
 * - HTTPS enforcement (Strict-Transport-Security)
 * - Privacy protection (Referrer-Policy)
 * - Feature restrictions (Permissions-Policy)
 */
class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent clickjacking attacks
        // SAMEORIGIN: Page can only be displayed in a frame on the same origin
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Prevent MIME type sniffing
        // Browsers must respect the Content-Type header
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Enable XSS filter in older browsers
        // Modern browsers use CSP instead, but this provides backward compatibility
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Control referrer information
        // Only send origin when navigating to different origins
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // HTTPS enforcement (only in production)
        // Force HTTPS for 1 year, including subdomains
        if (app()->environment('production')) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        // Content Security Policy
        // Defines approved sources of content that browsers should load
        $csp = implode('; ', [
            "default-src 'self'",
            // FIX: Tambahkan 'unsafe-eval' dan https://cdnjs.cloudflare.com
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://unpkg.com",
            // FIX: Tambahkan cdnjs
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
            // FIX: Tambahkan blob: untuk antisipasi render Leaflet
            "img-src 'self' data: blob: https:",
            // FIX: Tambahkan data: dan cdnjs untuk font icon
            "font-src 'self' data: https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
            // FIX: Izinkan koneksi (connect-src) ke CDN
            "connect-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://unpkg.com https://*.openstreetmap.org",
            "frame-ancestors 'self'",
            "base-uri 'self'",
            "form-action 'self'",
        ]);
        $response->headers->set('Content-Security-Policy', $csp);

        // Permissions Policy (formerly Feature-Policy)
        // Disable potentially dangerous browser features
        $response->headers->set(
            'Permissions-Policy',
            'geolocation=(), microphone=(), camera=()'
        );

        return $response;
    }
}