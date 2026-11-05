<?php
namespace App\Mail;
use App\Models\WorkoutPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PlanApprovedMail extends Mailable
{
    use Queueable, SerializesModels;
    public $plan;

    public function __construct(WorkoutPlan $plan) { $this->plan = $plan; }

    public function build()
    {
        return $this->subject('Your Workout Plan is Approved!')
                    ->view('emails.plan-approved');
    }
}