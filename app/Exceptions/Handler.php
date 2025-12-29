<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        // Authorization/Access denied handling (403)
        if ($e instanceof AuthorizationException || $e instanceof AccessDeniedHttpException) {
            return $this->handle403($request, $e);
        } elseif ($e instanceof HttpExceptionInterface && $e->getStatusCode() === 403) {
            return $this->handle403($request, $e);
        }

        // 405 Method Not Allowed handling (user-friendly)
        if ($e instanceof MethodNotAllowedHttpException) {
            // JSON/API requests: return a structured error
            if ($request->expectsJson() || $request->wantsJson() || $request->is('api/*')) {
                return new JsonResponse([
                    'message' => 'Method Not Allowed',
                    'allowed' => $e->getHeaders()['Allow'] ?? null,
                ], 405, $e->getHeaders());
            }

            // Special case: user hit GET /logout (common when pasting URL or clicking a GET link)
            if (strtolower($request->method()) === 'get' && trim($request->path(), '/') === 'logout') {
                return response()->view('errors.405', [
                    'message' => 'To log out, please confirm below.',
                    'showLogoutHelper' => true,
                ], 405)->withHeaders($e->getHeaders());
            }

            // Generic friendly 405 page
            return response()->view('errors.405', [
                'message' => 'The action you tried is not allowed for this page. Please use the proper button or go back.',
                'showLogoutHelper' => false,
            ], 405)->withHeaders($e->getHeaders());
        }

        // 404 Not Found handling
        if ($e instanceof NotFoundHttpException) {
            // Prefer a dedicated 404 view. Could log path for analytics if desired.
            return response()->view('errors.404', [
                'path' => $request->path(),
            ], 404);
        }

        // 419 Page Expired / CSRF token mismatch handling
        if ($e instanceof TokenMismatchException) {
            // For non-GET (state changing) requests, redirect back with flash instead of showing static page
            if ($request->method() !== 'GET') {
                session()->flash('error', 'Your session expired. Please try again.');
                // Attempt to redirect back; fallback home or login for guests
                $target = url()->previous();
                if (!$target || $target === $request->fullUrl()) {
                    $target = $request->user() ? route('home') : route('login');
                }
                return redirect()->to($target);
            }
            return response()->view('errors.419', [], 419);
        }

        return parent::render($request, $e);
    }

    /**
     * Centralized 403 handling with loop-safe redirects and JSON support.
     */
    protected function handle403(Request $request, Throwable $e)
    {
        // JSON/API: return a proper 403 payload
        if ($request->expectsJson() || $request->wantsJson() || $request->is('api/*')) {
            return new JsonResponse([
                'message' => 'Forbidden',
                'error' => $e->getMessage() ?: 'You are not authorized to perform this action.'
            ], 403);
        }

        // Authenticated web requests
        if ($request->user()) {
            if (!session()->has('error')) {
                session()->flash('error', 'You are not authorized to perform this action.');
            }

            $current = $request->fullUrl();
            $referer = url()->previous();
            $homeUrl = route('home');

            // Guard 1: if referer is missing or same as current, prefer home
            $target = ($referer && $referer !== $current) ? $referer : $homeUrl;

            // Guard 2: if current is already home, don't redirect (avoid loop)
            if ($this->urlsEqual($current, $homeUrl)) {
                return response()->view('errors.403', [
                    'message' => 'You are not authorized to access this page.'
                ], 403);
            }

            // Guard 3: if target resolves to current (unlikely after Guard 1), show 403 view
            if ($this->urlsEqual($target, $current)) {
                return response()->view('errors.403', [
                    'message' => 'You are not authorized to access this page.'
                ], 403);
            }

            // Optional: If you later add access rules that could make home protected for some roles,
            // insert a guard here to detect that case and render the 403 view instead of redirecting.

            return redirect()->to($target);
        }

        // Guests: show 403 view so auth middleware elsewhere can handle login redirects
        return response()->view('errors.403', [
            'message' => 'You are not authorized to access this resource.'
        ], 403);
    }

    /**
     * Compare two URLs ignoring trailing slashes.
     */
    private function urlsEqual(?string $a, ?string $b): bool
    {
        if ($a === null || $b === null) return false;
        $na = rtrim($a, '/');
        $nb = rtrim($b, '/');
        return $na === $nb;
    }

    /**
     * Helper to check current route name.
     */
    private function routeNameIs(Request $request, string $name): bool
    {
        return optional($request->route())->getName() === $name;
    }
}
