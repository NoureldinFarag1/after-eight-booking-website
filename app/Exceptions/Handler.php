<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
        if ($e instanceof AuthorizationException) {
            // If the user is authenticated, flash a friendly message & redirect back or home.
            if ($request->user()) {
                // Only set flash if none already present
                if (!session()->has('error')) {
                    session()->flash('error', 'You are not authorized to perform this action.');
                }
                $referer = url()->previous();
                // Avoid redirect loops: if previous URL is same as current, fallback home
                if ($referer === $request->fullUrl()) {
                    return redirect()->route('home');
                }
                return redirect()->to($referer);
            }
            // Unauthenticated: maintain original 403 so auth middleware can handle redirects
            return response()->view('errors.403', [
                'message' => 'You are not authorized to access this resource.'
            ], 403);
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
}
