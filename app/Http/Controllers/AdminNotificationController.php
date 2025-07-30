<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Diagnostic;
use App\Models\User;
use App\Models\Answer;
use App\Models\Tenant;
use App\Notifications\DiagnosticPendingNotification;

class AdminNotificationController extends Controller
{
    public function notifyPendingUsers() {
        $now = Carbon::now();

        $diagnostics = Diagnostic::whereHas('periods', function ($q) use ($now) {
            $q->whereDate('start', '<=', $now)
                ->whereDate('end', '>=', $now);
        })->with(['periods' => function ($q) use ($now) {
            $q->whereDate('start', '<=', $now)
                ->whereDate('end', '>=', $now);
        }])->get();

        foreach ($diagnostics as $diagnostic) {
            foreach ($diagnostic->periods as $period) {
                $tenant = Tenant::find($period->tenant_id);

                if (!$tenant || !$tenant->active_tenant) {
                    continue;
                }

                $users = User::where('tenant_id', $tenant->id)->get();

                foreach ($users as $user) {
                    $answered = Answer::where('user_id', $user->id)
                        ->where('diagnostic_id', $diagnostic->id)
                        ->where('diagnostic_period_id', $period->id)
                        ->exists();

                    if (!$answered) {
                        $user->notifications()
                            ->where('type', DiagnosticPendingNotification::class)
                            ->where('data->diagnostic_id', $diagnostic->id)
                            ->delete();

                        $user->notify(new DiagnosticPendingNotification($diagnostic, $period->end, $period));
                    }
                }
            }
        }

        return response()->json(['message' => 'Notificações enviadas com sucesso!']);
    }
}
