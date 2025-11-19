<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevenir clickjacking
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Prevenir MIME-type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Prevenir XSS
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Content Security Policy (permite apenas origens confiáveis utilizadas pelo projeto)
        $scriptSrc = implode(' ', [
            "'self'",
            "'unsafe-inline'",
            'https://cdn.jsdelivr.net',
            'https://unpkg.com',
            'https://cdnjs.cloudflare.com',
        ]);

        $styleSrc = implode(' ', [
            "'self'",
            "'unsafe-inline'",
            'https://fonts.googleapis.com',
            'https://cdn.jsdelivr.net',
            'https://unpkg.com',
        ]);

        $fontSrc = implode(' ', [
            "'self'",
            'https://fonts.gstatic.com',
            'data:',
        ]);

        $imgSrc = implode(' ', [
            "'self'",
            'data:',
        ]);

        $csp = implode(' ', [
            "default-src 'self';",
            "script-src {$scriptSrc};",
            "style-src {$styleSrc};",
            "font-src {$fontSrc};",
            "img-src {$imgSrc};",
            "connect-src 'self';",
        ]);

        $response->headers->set('Content-Security-Policy', $csp);

        // Forçar HTTPS em produção
        if (app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Referrer Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions Policy
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        return $response;
    }
}

