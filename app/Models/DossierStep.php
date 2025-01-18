<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DossierStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'dossier_id',
        'step_number',
        'status',
        'notes',
        'metadata',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the dossier that owns this step.
     */
    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Dossier::class);
    }

    /**
     * Get the step name.
     */
    public function getStepName(): string
    {
        return match ($this->step_number) {
            Dossier::STEP_ANALYSIS => 'Analyse de dossier',
            Dossier::STEP_ADMISSION => 'Ouverture & Admission',
            Dossier::STEP_PAYMENT => 'Paiement',
            Dossier::STEP_VISA => 'Accompagnement Visa',
            default => 'Étape inconnue',
        };
    }

    /**
     * Get the status label.
     */
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            // Étape 1 : Analyse
            Dossier::STATUS_WAITING_DOCS => 'En attente de documents',
            Dossier::STATUS_ANALYZING => 'Analyse en cours',
            Dossier::STATUS_ANALYZED => 'Analyse terminée',
            
            // Étape 2 : Admission
            Dossier::STATUS_DOCS_RECEIVED => 'Documents physiques reçus',
            Dossier::STATUS_ADMISSION_PAID => 'Frais d\'admission payés',
            Dossier::STATUS_SUBMITTED => 'Dossier soumis',
            Dossier::STATUS_SUBMISSION_ACCEPTED => 'Soumission acceptée',
            Dossier::STATUS_SUBMISSION_REJECTED => 'Soumission rejetée',
            
            // Étape 3 : Paiement
            Dossier::STATUS_AGENCY_PAID => 'Frais d\'agence payés',
            Dossier::STATUS_PARTIAL_TUITION => 'Paiement partiel scolarité',
            Dossier::STATUS_FULL_TUITION => 'Paiement total scolarité',
            Dossier::STATUS_ABANDONED => 'Dossier abandonné',
            
            // Étape 4 : Visa
            Dossier::STATUS_VISA_DOCS_READY => 'Dossier visa prêt',
            Dossier::STATUS_VISA_FEES_PAID => 'Frais visa payés',
            Dossier::STATUS_VISA_SUBMITTED => 'Visa soumis',
            Dossier::STATUS_VISA_ACCEPTED => 'Visa obtenu',
            Dossier::STATUS_VISA_REJECTED => 'Visa refusé',
            Dossier::STATUS_FINAL_FEES_PAID => 'Frais finaux payés',
            
            default => $this->status,
        };
    }

    /**
     * Check if this step is completed.
     */
    public function isCompleted(): bool
    {
        return !is_null($this->completed_at);
    }

    /**
     * Check if this step is the current active step.
     */
    public function isCurrentStep(): bool
    {
        return $this->dossier->current_step === $this->step_number;
    }
}
