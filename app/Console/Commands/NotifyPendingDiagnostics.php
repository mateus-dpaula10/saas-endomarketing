<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Diagnostic;
use App\Models\User;
use App\Models\Answer;
use App\Notifications\DiagnosticPendingNotification;
use Carbon\Carbon;

class NotifyPendingDiagnostics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:pending-diagnostics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia notificações para usuários que ainda não responderam diagnósticos pendentes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        $diagnostics = Diagnostic::whereHas('periods', function ($q) use ($now) {
            $q->whereDate('start', '<=', $now)
            ->whereDate('end', '>=', $now);
        })
        ->with(['periods' => function($q) use ($now) {
            $q->whereDate('start', '<=', $now)
            ->whereDate('end', '>=', $now);
        }])
        ->get();

        foreach ($diagnostics as $diagnostic) {
            foreach ($diagnostic->periods as $period) {                
                $users = User::where('tenant_id', $period->tenant_id)->get();

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

        $this->info('Notificações enviadas com sucesso.');
    }
}
