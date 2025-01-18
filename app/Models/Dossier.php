<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dossier extends Model
{
    use HasFactory;
    use SoftDeletes;

    // Étapes du workflow
    public const STEP_ANALYSIS = 1;
    public const STEP_ADMISSION = 2;
    public const STEP_PAYMENT = 3;
    public const STEP_VISA = 4;

    // Statuts pour l'étape 1 (Analysis) - repris de Prospect
    public const STATUS_WAITING_DOCS = 'attente_documents';
    public const STATUS_ANALYZING = 'analyse_en_cours';
    public const STATUS_ANALYZED = 'analyse_terminee';

    // Statuts pour l'étape 2 (Admission)
    public const STATUS_DOCS_RECEIVED = 'reception_documents_physiques';
    public const STATUS_ADMISSION_PAID = 'paiement_frais_admission';
    public const STATUS_SUBMITTED = 'dossier_soumis';
    public const STATUS_SUBMISSION_ACCEPTED = 'soumission_acceptee';
    public const STATUS_SUBMISSION_REJECTED = 'soumission_rejetee';

    // Statuts pour l'étape 3 (Payment)
    public const STATUS_AGENCY_PAID = 'paiement_frais_agence';
    public const STATUS_PARTIAL_TUITION = 'paiement_scolarite_partiel';
    public const STATUS_FULL_TUITION = 'paiement_scolarite_total';
    public const STATUS_ABANDONED = 'abandonne';

    // Statuts pour l'étape 4 (Visa)
    public const STATUS_VISA_DOCS_READY = 'dossier_visa_pret';
    public const STATUS_VISA_FEES_PAID = 'frais_visa_payes';
    public const STATUS_VISA_SUBMITTED = 'visa_soumis';
    public const STATUS_VISA_ACCEPTED = 'visa_obtenu';
    public const STATUS_VISA_REJECTED = 'visa_refuse';
    public const STATUS_FINAL_FEES_PAID = 'frais_finaux_payes';

    protected $fillable = [
        'reference_number',
        'prospect_id',
        'current_step',
        'current_status',
        'notes',
        'last_action_at',
        'completed_at',
    ];

    protected $casts = [
        'last_action_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the prospect associated with the dossier.
     */
    public function prospect(): BelongsTo
    {
        return $this->belongsTo(Prospect::class);
    }

    /**
     * Get the steps history for this dossier.
     */
    public function steps(): HasMany
    {
        return $this->hasMany(DossierStep::class);
    }

    /**
     * Get the documents for this dossier.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(DossierDocument::class);
    }

    /**
     * Get the current step instance.
     */
    public function currentStepHistory(): HasMany
    {
        return $this->steps()->where('step_number', $this->current_step);
    }

    /**
     * Get documents for the current step.
     */
    public function currentStepDocuments(): HasMany
    {
        return $this->documents()->where('step_number', $this->current_step);
    }

    /**
     * Check if the dossier can proceed to the next step.
     */
    public function canProceedToNextStep(): bool
    {
        return match ($this->current_step) {
            self::STEP_ANALYSIS => $this->current_status === self::STATUS_ANALYZED,
            self::STEP_ADMISSION => $this->current_status === self::STATUS_SUBMISSION_ACCEPTED,
            self::STEP_PAYMENT => $this->current_status === self::STATUS_FULL_TUITION,
            self::STEP_VISA => $this->current_status === self::STATUS_FINAL_FEES_PAID,
            default => false,
        };
    }

    /**
     * Get the valid statuses for the current step.
     */
    public function getValidStatusesForCurrentStep(): array
    {
        return match ($this->current_step) {
            self::STEP_ANALYSIS => [
                self::STATUS_WAITING_DOCS,
                self::STATUS_ANALYZING,
                self::STATUS_ANALYZED,
            ],
            self::STEP_ADMISSION => [
                self::STATUS_DOCS_RECEIVED,
                self::STATUS_ADMISSION_PAID,
                self::STATUS_SUBMITTED,
                self::STATUS_SUBMISSION_ACCEPTED,
                self::STATUS_SUBMISSION_REJECTED,
            ],
            self::STEP_PAYMENT => [
                self::STATUS_AGENCY_PAID,
                self::STATUS_PARTIAL_TUITION,
                self::STATUS_FULL_TUITION,
                self::STATUS_ABANDONED,
            ],
            self::STEP_VISA => [
                self::STATUS_VISA_DOCS_READY,
                self::STATUS_VISA_FEES_PAID,
                self::STATUS_VISA_SUBMITTED,
                self::STATUS_VISA_ACCEPTED,
                self::STATUS_VISA_REJECTED,
                self::STATUS_FINAL_FEES_PAID,
            ],
            default => [],
        };
    }
}
