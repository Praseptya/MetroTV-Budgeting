<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class BudgetStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $budget;
    protected $status;
    protected $reason;

    public function __construct($budget, $status, $reason = null)
    {
        $this->budget = $budget;
        $this->status = $status;
        $this->reason = $reason;
    }

    public function via($notifiable)
    {
        // Kirim via email dan database
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $isApproved = strtolower($this->status) === 'approved';
        $subject = $isApproved ? 'Budget Anda Disetujui ✅' : 'Budget Anda Ditolak ❌';

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting('Halo, ' . $notifiable->name)
            ->line('Budget "' . $this->budget->budget_name . '" telah diperbarui statusnya.')
            ->line('Status: **' . ucfirst($this->status) . '**');

        if (!$isApproved && $this->reason) {
            $mail->line('Alasan Penolakan: "' . $this->reason . '"');
        }

        $mail->action('Lihat Detail', url(route('budgets.show', $this->budget->id_budget)))
             ->line('Terima kasih telah menggunakan sistem MetroTV Budgeting.');

        return $mail;
    }

    public function toArray($notifiable)
    {
        return [
            'budget_id' => $this->budget->id_budget,
            'budget_name' => $this->budget->budget_name,
            'status' => $this->status,
            'reason' => $this->reason,
        ];
    }
}
