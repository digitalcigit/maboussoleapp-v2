<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Filament\Notifications\Auth\VerifyEmail;

class ProspectPortalAccess extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $password;
    public $verificationUrl;

    public function __construct(User $user, string $password)
    {
        $this->user = $user;
        $this->password = $password;
        $this->verificationUrl = config('app.url') . '/portail/email-verification/' . $user->id . '/' . sha1($user->email);
    }

    public function build()
    {
        return $this->markdown('emails.prospect-portal-access')
                    ->subject('Accès à votre espace candidat - Ma Boussole');
    }
}
