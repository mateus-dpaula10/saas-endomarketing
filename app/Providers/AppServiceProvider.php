<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Diagnostic;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Route::aliasMiddleware('role', \App\Http\Middleware\CheckRole::class);

        View::composer('layouts.dashboard', function ($view) {
            $user = Auth::user();

            if (!$user) {
                $view->with('notifications', collect());
                return;
            }

            $notifications = collect();

            $diagnostics = Diagnostic::whereHas('periods', function ($query) use ($user) {
                $query->where('tenant_id', $user->tenant_id)
                    ->whereDate('start', '<=', Carbon::now())
                    ->whereDate('end', '>=', Carbon::now());
            })->with([
                'periods' => function ($query) use ($user) {
                    $query->where('tenant_id', $user->tenant_id)
                        ->whereDate('start', '<=', Carbon::now())
                        ->whereDate('end', '>=', Carbon::now());
                },
                'answers'
            ])->get();

            $notifications = $diagnostics->filter(function ($diagnostic) use ($user) {
                $period = $diagnostic->periods->first();
                if (!$period) return false;

                return !$diagnostic->answers()
                    ->where('user_id', $user->id)
                    ->where('diagnostic_period_id', $period->id)
                    ->exists();
            })->map(function($diagnostic) {
                $period = $diagnostic->periods->first();

                return [
                    'id'            => $diagnostic->id,
                    'title'         => $diagnostic->title,
                    'deadline'      => Carbon::parse($period->end)->format('d/m/Y'),
                    'days_left'     => Carbon::now()->startOfDay()->diffInDays(Carbon::parse($period->end)->startOfDay(), false)
                ];
            })->values();

            $view->with('notifications', $notifications);
        });
    }
}