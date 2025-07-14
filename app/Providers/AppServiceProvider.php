<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Diagnostic;
use App\Models\User;
use App\Models\Answer;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Notifications\DatabaseNotification;

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

        View::composer('dashboard', function ($view) {
            $user = Auth::user();

            if (!$user) {
                $view->with('notifications', collect())
                    ->with('pendingUsersNotifications', collect())
                    ->with('dbNotifications', collect())
                    ->with('notificationCount', 0);
                return;
            }

            $now = Carbon::now();

            $diagnostics = Diagnostic::whereHas('periods', function ($query) use ($user, $now) {
                $query->where('tenant_id', $user->tenant_id)
                    ->whereDate('start', '<=', $now)
                    ->whereDate('end', '>=', $now);
            })->with([
                'periods' => function ($query) use ($user, $now) {
                    $query->where('tenant_id', $user->tenant_id)
                        ->whereDate('start', '<=', $now)
                        ->whereDate('end', '>=', $now);
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
            })->map(function ($diagnostic) {
                $period = $diagnostic->periods->first();

                return [
                    'id'            => $diagnostic->id,
                    'diagnostic_id' => $diagnostic->id,
                    'title'         => $diagnostic->title,
                    'deadline'      => $period->end->format('d-m-Y'),
                    'days_left'     => Carbon::now()->startOfDay()->diffInDays(Carbon::parse($period->end)->startOfDay(), false)
                ];
            })->values();

            $pendingUsersNotifications = collect();

            if ($user->role === 'admin') {
                $collaborators = User::where('tenant_id', $user->tenant_id)->get();   

                foreach ($diagnostics as $diagnostic) {
                    $period = $diagnostic->periods->first();
                    if (!$period) continue;

                    $respondedUserIds = Answer::where('diagnostic_id', $diagnostic->id)
                        ->where('diagnostic_period_id', $period->id)
                        ->pluck('user_id')
                        ->toArray();
                        
                    $pendingUsers = $collaborators->filter(fn($collab) => !in_array($collab->id, $respondedUserIds));

                    if ($pendingUsers->count() > 0) {
                        $pendingUsersNotifications->push([
                            'diagnostic_id' => $diagnostic->id,
                            'title'         => $diagnostic->title,
                            'deadline'      => $period->end->toDateString(),
                            'pending_count' => $pendingUsers->count(),
                            'pending_users' => $pendingUsers->pluck('name')->values(),
                        ]);
                    }
                }
            }

            $dbNotifications = DatabaseNotification::where('notifiable_id', $user->id)
                ->where('notifiable_type', User::class)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($notif) {
                    return [
                        'id' => $notif->id,
                        'title' => $notif->data['title'] ?? 'Sem título',
                        'message' => $notif->data['message'] ?? 'Sem mensagem',
                        'created_at' => $notif->created_at->toDateTimeString(),
                    ];
                });

            $notificationCount = $notifications->count() + $pendingUsersNotifications->count() + $dbNotifications->count();

            $view->with('notifications', $notifications)
                ->with('pendingUsersNotifications', $pendingUsersNotifications)
                ->with('dbNotifications', $dbNotifications)
                ->with('notificationCount', $notificationCount);
        });
    }
}