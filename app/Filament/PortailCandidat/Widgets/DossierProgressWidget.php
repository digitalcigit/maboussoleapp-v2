<?php

namespace App\Filament\PortailCandidat\Widgets;

use App\Models\Dossier;
use App\Models\DossierDocument;
use Filament\Widgets\Widget;

class DossierProgressWidget extends Widget
{
    protected static string $view = 'filament.portail-candidat.widgets.dossier-progress';
    
    public function getProgress()
    {
        $user = auth()->user();
        if (!$user || !$user->prospect || !$user->prospect->dossier) {
            return null;
        }
        
        $dossier = $user->prospect->dossier;
        
        $etapes = [
            Dossier::STEP_ANALYSIS => [
                'label' => Dossier::getStepLabel(Dossier::STEP_ANALYSIS),
                'icon' => 'heroicon-o-document-magnifying-glass',
                'description' => 'Analyse de votre dossier',
            ],
            Dossier::STEP_ADMISSION => [
                'label' => Dossier::getStepLabel(Dossier::STEP_ADMISSION),
                'icon' => 'heroicon-o-academic-cap',
                'description' => 'Processus d\'admission',
            ],
            Dossier::STEP_PAYMENT => [
                'label' => Dossier::getStepLabel(Dossier::STEP_PAYMENT),
                'icon' => 'heroicon-o-currency-dollar',
                'description' => 'Paiement des frais',
            ],
            Dossier::STEP_VISA => [
                'label' => Dossier::getStepLabel(Dossier::STEP_VISA),
                'icon' => 'heroicon-o-identification',
                'description' => 'Procédure de visa',
            ],
        ];
        
        // Ajouter les informations spécifiques à l'étape actuelle et aux étapes précédentes
        foreach ($etapes as $step => $info) {
            if ($step <= $dossier->current_step) {
                $etapes[$step]['status'] = $step === $dossier->current_step ? $dossier->current_status : 'completed';
                $etapes[$step]['status_label'] = $step === $dossier->current_step ? $dossier->getStatusLabel($dossier->current_status) : 'Terminé';
                
                // Récupérer les documents requis pour l'étape actuelle
                if ($step === $dossier->current_step) {
                    $documentsRequis = [];
                    foreach (DossierDocument::getRequiredTypesForStep($step) as $type) {
                        $documentsRequis[$type] = [
                            'label' => DossierDocument::TYPES[$type],
                            'uploaded' => $dossier->documents()->where('document_type', $type)->exists(),
                        ];
                    }
                    $etapes[$step]['documents_requis'] = $documentsRequis;
                }
            }
        }
        
        return [
            'current_step' => $dossier->current_step,
            'etapes' => $etapes,
            'can_progress' => $dossier->canProgressToNextStep(),
            'progress_percent' => (($dossier->current_step - 1) / (count($etapes) - 1)) * 100,
        ];
    }
}
