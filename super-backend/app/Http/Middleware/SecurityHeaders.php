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
        $scriptSrc = [
            "'self'",
            "'unsafe-inline'",
            "'unsafe-eval'", // Necessário para Vue.js em desenvolvimento
            'https://cdn.jsdelivr.net',
            'https://unpkg.com',
            'https://cdnjs.cloudflare.com',
        ];

        $styleSrc = [
            "'self'",
            "'unsafe-inline'",
            'https://fonts.googleapis.com',
            'https://cdn.jsdelivr.net',
            'https://unpkg.com',
        ];

        $connectSrc = [
            "'self'",
        ];

        $fontSrc = [
            "'self'",
            'https://fonts.gstatic.com',
            'data:',
        ];

        $imgSrc = [
            "'self'",
            'data:',
        ];

        // Em ambiente local, desabilita CSP para evitar problemas com Vite/HMR
        // (IPv6 [::1] não é suportado pelo CSP, causando bloqueios)
        // Em produção/staging, o CSP será aplicado normalmente
        if (!app()->environment('local')) {
            // Em produção/staging, aplica CSP normalmente
            // (em local, CSP é desabilitado para permitir Vite/HMR funcionar)

            // Construir CSP com script-src-elem e style-src-elem explicitamente
            // (necessário para que o navegador use essas diretivas ao invés de script-src/style-src)
            $cspParts = [
                "default-src 'self';",
                "script-src " . implode(' ', $scriptSrc) . ";",
                "script-src-elem " . implode(' ', $scriptSrc) . ";", // Para elementos <script>
                "style-src " . implode(' ', $styleSrc) . ";",
                "style-src-elem " . implode(' ', $styleSrc) . ";", // Para elementos <style> e <link rel="stylesheet">
                "font-src " . implode(' ', $fontSrc) . ";",
                "img-src " . implode(' ', $imgSrc) . ";",
                "connect-src " . implode(' ', $connectSrc) . ";",
            ];

            $csp = implode(' ', $cspParts);
            $response->headers->set('Content-Security-Policy', $csp);
        }
        // Em ambiente local, não aplica CSP para evitar conflitos com Vite/HMR

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
