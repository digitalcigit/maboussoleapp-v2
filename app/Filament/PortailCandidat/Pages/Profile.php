<?php

namespace App\Filament\PortailCandidat\Pages;

use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Concerns\InteractsWithForms;

class Profile extends Page
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static string $view = 'filament.portail-candidat.pages.profile';
    protected static ?string $title = 'Mon profil';
    protected static ?string $navigationGroup = 'Compte';
    protected static ?int $navigationSort = 1;
    protected static ?string $slug = 'mon-profil';
    protected static ?string $modelLabel = 'profil';
    
    public static function getNavigationGroup(): ?string
    {
        return __('Compte');
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
    
    public static function getSlug(): string
    {
        return static::$slug ?? 'mon-profil';
    }
    
    public static function getUrl(array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?\Illuminate\Database\Eloquent\Model $tenant = null): string
    {
        $panel ??= 'portail-candidat';
        return route("filament.{$panel}.pages.".static::getSlug(), $parameters, $isAbsolute);
    }
    
    public ?array $data = [];
    
    public function mount(): void
    {
        $this->form->fill([
            'name' => auth()->user()->name,
            'email' => auth()->user()->email,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nom complet')
                    ->required()
                    ->maxLength(255),
                    
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(table: User::class, ignoreRecord: true),
                    
                FileUpload::make('avatar')
                    ->image()
                    ->disk('avatars')
                    ->directory('profiles/candidats')
                    ->visibility('public')
                    ->imageEditor()
                    ->circleCropper()
                    ->maxSize(5120)
                    ->label('Photo de profil')
                    ->helperText('Formats acceptés : JPG, PNG, GIF. Taille max : 5MB'),
                    
                TextInput::make('current_password')
                    ->password()
                    ->label('Mot de passe actuel')
                    ->dehydrated(false),
                    
                TextInput::make('new_password')
                    ->password()
                    ->label('Nouveau mot de passe')
                    ->dehydrated(false)
                    ->confirmed()
                    ->minLength(8),
                    
                TextInput::make('new_password_confirmation')
                    ->password()
                    ->label('Confirmer le nouveau mot de passe')
                    ->dehydrated(false)
                    ->requiredWith('new_password'),
            ]);
    }

    public function save()
    {
        $data = $this->form->getState();
        
        $user = auth()->user();
        
        $user->name = $data['name'];
        
        if ($data['email'] !== $user->email) {
            $user->email = $data['email'];
        }
        
        if (!empty($data['avatar'])) {
            if ($user->avatar && !str_starts_with($user->avatar, 'http')) {
                Storage::disk('avatars')->delete($user->avatar);
            }
            $user->avatar = $data['avatar'];
        }
        
        if (
            !empty($data['current_password']) &&
            !empty($data['new_password']) &&
            Hash::check($data['current_password'], $user->password)
        ) {
            $user->password = Hash::make($data['new_password']);
        } elseif (
            !empty($data['new_password']) &&
            !Hash::check($data['current_password'], $user->password)
        ) {
            Notification::make()
                ->danger()
                ->title('Mot de passe incorrect')
                ->body('Le mot de passe actuel fourni est incorrect.')
                ->send();
                
            return;
        }
        
        $user->save();
        
        Notification::make()
            ->success()
            ->title('Profil mis à jour')
            ->body('Vos informations ont été mises à jour avec succès.')
            ->send();
            
        $this->form->fill([
            'current_password' => '',
            'new_password' => '',
            'new_password_confirmation' => '',
        ]);
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Sauvegarder les modifications')
                ->action('save'),
        ];
    }
}
