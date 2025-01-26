<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;

class Login extends BaseLogin
{
    protected static string $view = 'filament.pages.auth.login';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ])
            ->statePath('data');
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label(__('filament-panels::pages/auth/login.form.email.label'))
            ->email()
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraAttributes(['class' => 'bg-white/5 border-white/10 text-white placeholder-white/60']);
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label(__('filament-panels::pages/auth/login.form.password.label'))
            ->password()
            ->required()
            ->extraAttributes(['class' => 'bg-white/5 border-white/10 text-white placeholder-white/60']);
    }

    public function getViewData(): array
    {
        return [
            'images' => [
                [
                    'src' => asset('images/students/graduate-1.jpg'),
                    'message' => 'Votre parcours vers l\'excellence commence ici',
                ],
                [
                    'src' => asset('images/students/graduate-2.jpg'),
                    'message' => 'Accompagner vos étudiants vers la réussite',
                ],
                [
                    'src' => asset('images/students/graduate-3.jpg'),
                    'message' => 'Construisez leur avenir académique',
                ],
                [
                    'src' => asset('images/students/graduate-4.jpg'),
                    'message' => 'Transformez leurs rêves en réalité',
                ],
            ],
        ];
    }
}
