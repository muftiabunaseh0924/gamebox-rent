<?php

use App\Http\Middleware\EnsureRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['role' => EnsureRole::class]);

        // Tamu yang membuka halaman terlindungi diarahkan ke login yang sesuai.
        $middleware->redirectGuestsTo(fn (Request $request) =>
            $request->is('admin', 'admin/*') ? route('admin.login') : route('login'));

        // Yang sudah login lalu membuka halaman login diarahkan ke dasbornya.
        $middleware->redirectUsersTo(fn (Request $request) =>
            $request->user()?->role === 'admin' ? route('admin.dashboard') : route('dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();