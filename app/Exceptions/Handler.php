<?php

namespace App\Exceptions;

use Throwable;
use RuntimeException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session
     * on validation exceptions.
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
        /**
         * ------------------------------------------------------------
         * 🔐 WALLET / VTU SAFE ERROR MAPPING (PRODUCTION CRITICAL)
         * ------------------------------------------------------------
         */
        $this->renderable(function (RuntimeException $e, Request $request) {

            $msg = strtolower($e->getMessage());

            // 🔒 Wallet integrity / frozen / fraud protection
            if (Str::contains($msg, [
                'wallet integrity',
                'integrity check failed',
                'wallet is frozen',
                'debit blocked',
                'credit blocked',
                'account frozen',
            ])) {

                return back()
                    ->withInput()
                    ->with('error', 'Your wallet is temporarily restricted. Please contact support.');
            }

            // 💸 Insufficient wallet balance
            if (Str::contains($msg, [
                'insufficient wallet balance',
                'insufficient funds',
            ])) {

                return back()
                    ->withInput()
                    ->with('error', 'Insufficient wallet balance.');
            }

            // ⚠️ General transaction failure
            if (Str::contains($msg, [
                'wallet',
                'transaction',
                'payment',
            ])) {

                return back()
                    ->withInput()
                    ->with('error', 'Transaction could not be completed. Please try again.');
            }

            return null; // Let Laravel handle everything else
        });

        /**
         * ------------------------------------------------------------
         * 🧾 VALIDATION ERRORS (STANDARD LARAVEL)
         * ------------------------------------------------------------
         */
        $this->renderable(function (ValidationException $e, Request $request) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
        });
    }

    /**
     * Convert exception to HTTP response.
     * Keeps API / web behavior correct.
     */
    public function render($request, Throwable $e)
    {
        // API JSON responses
        if ($request->expectsJson()) {

            if ($e instanceof ValidationException) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors'  => $e->errors(),
                ], 422);
            }

            if ($e instanceof HttpExceptionInterface) {
                return response()->json([
                    'message' => $e->getMessage() ?: 'Request failed',
                ], $e->getStatusCode());
            }

            return response()->json([
                'message' => 'An unexpected error occurred.',
            ], 500);
        }

        return parent::render($request, $e);
    }
}
