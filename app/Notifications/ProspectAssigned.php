<?php

namespace App\Notifications;

use App\Models\Prospect;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProspectAssigned extends Notification
{
    use Queueable;

    protected Prospect $prospect;

    public function __construct(Prospect $prospect)
    {
        $this->prospect = $prospect;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'prospect_id' => $this->prospect->id,
            'message' => "Nouveau prospect assigné : {$this->prospect->first_name} {$this->prospect->last_name}",
            'reference' => $this->prospect->reference_number,
        ];
    }
}
