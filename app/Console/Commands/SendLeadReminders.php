<?php

namespace App\Console\Commands;

use App\Models\Lead;
use App\Models\User;
use App\Notifications\LeadFollowUpReminder;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SendLeadReminders extends Command
{
    protected $signature = 'leads:send-reminders';
    protected $description = 'Send notifications for leads that need follow-up today';

    public function handle()
    {
        $leads = Lead::whereDate('follow_up_date', Carbon::today())
            ->where('status', 'pending')
            ->whereNotNull('assigned_seller_id')
            ->get();

        foreach ($leads as $lead) {
            $seller = User::find($lead->assigned_seller_id);
            if ($seller) {
                $seller->notify(new LeadFollowUpReminder($lead));
            }
        }

        $this->info('Reminders sent successfully.');
    }
}
