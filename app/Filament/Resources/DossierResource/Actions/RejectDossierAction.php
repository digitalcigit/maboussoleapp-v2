<?php

namespace App\Filament\Resources\DossierResource\Actions;

use App\Models\Dossier;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Enums\ActionSize;
use Illuminate\Support\HtmlString;

class RejectDossierAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'reject-dossier';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Rejeter le dossier')
            ->modalHeading('Rejeter le dossier')
            ->modalDescription('Veuillez fournir un rapport détaillé expliquant les raisons du rejet.')
            ->modalSubmitActionLabel('Confirmer le rejet')
            ->modalCancelActionLabel('Annuler')
            ->color('danger')
            ->size(ActionSize::Large)
            ->form([
                MarkdownEditor::make('rejection_report')
                    ->label('Rapport de rejet')
                    ->required()
                    ->toolbarButtons([
                        'bold',
                        'bulletList',
                        'italic',
                        'link',
                        'orderedList',
                        'redo',
                        'strike',
                        'undo',
                    ])
                    ->columnSpanFull()
                    ->live(debounce: 500)
                    ->dehydrated()
                    ->afterStateUpdated(function ($state, $component) {
                        if ($state === null) {
                            $component->helperText('');
                            return;
                        }

                        $preview = view('filament.forms.components.markdown-preview', [
                            'content' => $state
                        ])->render();
                        
                        $component->helperText(new HtmlString($preview));
                    }),
            ])
            ->action(function (array $data) {
                $dossier = $this->getRecord();

                // Créer le rapport de rejet
                $dossier->rejectionReports()->create([
                    'content' => $data['rejection_report'],
                    'created_by' => auth()->id(),
                ]);

                // Mettre à jour le statut du dossier
                $dossier->update([
                    'current_status' => Dossier::STATUS_SUBMISSION_REJECTED,
                    'last_action_at' => now(),
                ]);

                // Notification de succès
                Notification::make()
                    ->success()
                    ->title('Dossier rejeté')
                    ->body('Le dossier a été rejeté et le rapport a été enregistré.')
                    ->send();
            });
    }
}
