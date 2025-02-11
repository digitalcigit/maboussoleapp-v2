<?php

namespace App\Mail;

use App\Models\Dossier;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DossierRetardMail extends Mailable
{
    use Queueable, SerializesModels;

    public $dossier;

    public function __construct(Dossier $dossier)
    {
        $this->dossier = $dossier;
    }

    public function build()
    {
        return $this->markdown('emails.dossiers.retard')
            ->subject('Rappel : Documents en attente pour votre dossier');
    }
}
