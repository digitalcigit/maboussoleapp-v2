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
    public const STATUS_WAITING_PHYSICAL_DOCS = 'attente_documents_physiques';
    public const STATUS_DOCS_RECEIVED = 'reception_documents_physiques';
    public const STATUS_ADMISSION_PAID = 'paiement_frais_admission';
    public const STATUS_SUBMITTED = 'dossier_soumis';
    public const STATUS_SUBMISSION_ACCEPTED = 'soumission_acceptee';
    public const STATUS_SUBMISSION_REJECTED = 'soumission_rejetee';

    // Statuts pour l'étape 3 (Payment)
    public const STATUS_WAITING_AGENCY_PAYMENT = 'attente_paiement_frais_agence';
    public const STATUS_AGENCY_PAID = 'paiement_frais_agence';
    public const STATUS_TUITION_PAYMENT = 'paiement_scolarite';
    public const STATUS_ABANDONED = 'abandonne';

    // Remplacer les anciennes constantes
    public const STATUS_PARTIAL_TUITION = self::STATUS_TUITION_PAYMENT; // Pour compatibilité
    public const STATUS_FULL_TUITION = self::STATUS_TUITION_PAYMENT; // Pour compatibilité

    // Statuts pour l'étape 4 (Visa)
    public const STATUS_VISA_CONSTITUTION = 'constitution_dossier_visa';
    public const STATUS_VISA_DOCS_READY = 'dossier_visa_pret';
    public const STATUS_VISA_FEES_PAID = 'frais_visa_payes';
    public const STATUS_VISA_SUBMITTED = 'visa_soumis';
    public const STATUS_VISA_ACCEPTED = 'visa_obtenu';
    public const STATUS_VISA_REJECTED = 'visa_refuse';
    public const STATUS_FINAL_FEES_PAID = 'frais_finaux_payes';

    protected $fillable = [
        'reference_number',
        'prospect_id',
        'status',
        'first_name',
        'last_name',
        'email',
        'phone',
        'birth_date',
        'profession',
        'education_level',
        'desired_field',
        'desired_destination',
        'emergency_contact',
        'documents',
        'commercial_code',
        'notes',
        'prospect_info',
        'current_step',
        'current_status',
        'school_name',
        'school_program',
        'school_country',
        'school_notes',
        'admission_fees',
        'assigned_to',
        'agency_payment_amount',
        'tuition_total_amount',
        'tuition_paid_amount',
        'last_action_at',
    ];

    protected $casts = [
        'emergency_contact' => 'json',
        'documents' => 'json',
        'birth_date' => 'date',
        'prospect_info' => 'json',
        'agency_payment_amount' => 'decimal:2',
        'tuition_total_amount' => 'decimal:2',
        'tuition_paid_amount' => 'decimal:2',
        'current_step' => 'integer',
        'last_action_at' => 'datetime',
    ];

    protected $attributes = [
        'current_step' => 1,
        'current_status' => 'en_attente'
    ];

    protected $appends = [
        'tuition_progress'
    ];

    /**
     * Get the prospect that owns the dossier
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
            self::STEP_PAYMENT => $this->current_status === self::STATUS_TUITION_PAYMENT && $this->isTuitionFullyPaid(),
            self::STEP_VISA => $this->current_status === self::STATUS_FINAL_FEES_PAID,
            default => false,
        };
    }

    /**
     * Get valid statuses for the current step
     */
    public static function getValidStatusesForStep(int $step): array
    {
        return match ($step) {
            self::STEP_ANALYSIS => [
                self::STATUS_WAITING_DOCS,
                self::STATUS_ANALYZING,
                self::STATUS_ANALYZED,
            ],
            self::STEP_ADMISSION => [
                self::STATUS_WAITING_PHYSICAL_DOCS,
                self::STATUS_DOCS_RECEIVED,
                self::STATUS_ADMISSION_PAID,
                self::STATUS_SUBMITTED,
                self::STATUS_SUBMISSION_ACCEPTED,
                self::STATUS_SUBMISSION_REJECTED,
            ],
            self::STEP_PAYMENT => [
                self::STATUS_WAITING_AGENCY_PAYMENT,
                self::STATUS_AGENCY_PAID,
                self::STATUS_TUITION_PAYMENT,
                self::STATUS_ABANDONED,
            ],
            self::STEP_VISA => [
                self::STATUS_VISA_CONSTITUTION,
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

    /**
     * Get valid statuses for the current step
     */
    public function getValidStatusesForCurrentStep(): array
    {
        return self::getValidStatusesForStep($this->current_step);
    }

    /**
     * Get the status label in French
     */
    public static function getStatusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_WAITING_DOCS => 'En attente de documents',
            self::STATUS_ANALYZING => 'Analyse en cours',
            self::STATUS_ANALYZED => 'Analyse terminée',
            self::STATUS_WAITING_PHYSICAL_DOCS => 'En attente de documents physiques',
            self::STATUS_DOCS_RECEIVED => 'Documents physiques reçus',
            self::STATUS_ADMISSION_PAID => 'Frais d\'admission payés',
            self::STATUS_SUBMITTED => 'Dossier soumis',
            self::STATUS_SUBMISSION_ACCEPTED => 'Soumission acceptée',
            self::STATUS_SUBMISSION_REJECTED => 'Soumission rejetée',
            self::STATUS_WAITING_AGENCY_PAYMENT => 'En attente de paiement des frais d\'agence',
            self::STATUS_AGENCY_PAID => 'Frais d\'agence payés',
            self::STATUS_TUITION_PAYMENT => 'Paiement de la scolarité',
            self::STATUS_ABANDONED => 'Dossier abandonné',
            self::STATUS_VISA_CONSTITUTION => 'Constitution du dossier visa',
            self::STATUS_VISA_DOCS_READY => 'Dossier visa prêt',
            self::STATUS_VISA_FEES_PAID => 'Frais visa payés',
            self::STATUS_VISA_SUBMITTED => 'Visa soumis',
            self::STATUS_VISA_ACCEPTED => 'Visa obtenu',
            self::STATUS_VISA_REJECTED => 'Visa refusé',
            self::STATUS_FINAL_FEES_PAID => 'Frais finaux payés',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    /**
     * Get valid statuses for a step with their French labels
     */
    public static function getValidStatusesForStepWithLabels(int $step): array
    {
        return collect(self::getValidStatusesForStep($step))
            ->mapWithKeys(fn ($status) => [$status => self::getStatusLabel($status)])
            ->toArray();
    }

    /**
     * Obtenir le libellé d'une étape
     */
    public static function getStepLabel(int $step): string
    {
        return match ($step) {
            self::STEP_ANALYSIS => 'Analyse',
            self::STEP_ADMISSION => 'Admission',
            self::STEP_PAYMENT => 'Paiement',
            self::STEP_VISA => 'Visa',
            default => 'Inconnu',
        };
    }

    /**
     * Obtenir la liste des étapes avec leurs libellés
     */
    public static function getStepOptions(): array
    {
        return [
            self::STEP_ANALYSIS => 'Analyse',
            self::STEP_ADMISSION => 'Admission',
            self::STEP_PAYMENT => 'Paiement',
            self::STEP_VISA => 'Visa',
        ];
    }

    /**
     * Passer à l'étape suivante du workflow
     */
    public function progressToNextStep(): bool
    {
        $nextStep = match ($this->current_step) {
            self::STEP_ANALYSIS => self::STEP_ADMISSION,
            self::STEP_ADMISSION => self::STEP_PAYMENT,
            self::STEP_PAYMENT => self::STEP_VISA,
            default => null,
        };

        if ($nextStep === null) {
            return false;
        }

        // Définir le statut initial de la nouvelle étape
        $initialStatus = match ($nextStep) {
            self::STEP_ADMISSION => self::STATUS_WAITING_PHYSICAL_DOCS,
            self::STEP_PAYMENT => self::STATUS_WAITING_AGENCY_PAYMENT,
            self::STEP_VISA => self::STATUS_VISA_CONSTITUTION,
            default => null,
        };

        if ($initialStatus === null) {
            return false;
        }

        $this->update([
            'current_step' => $nextStep,
            'current_status' => $initialStatus,
            'last_action_at' => now(),
        ]);

        return true;
    }

    /**
     * Vérifier si le dossier peut passer à l'étape suivante
     */
    public function canProgressToNextStep(): bool
    {
        return match ($this->current_step) {
            self::STEP_ANALYSIS => $this->current_status === self::STATUS_ANALYZED,
            self::STEP_ADMISSION => $this->current_status === self::STATUS_SUBMISSION_ACCEPTED,
            self::STEP_PAYMENT => $this->current_status === self::STATUS_TUITION_PAYMENT && $this->isTuitionFullyPaid(),
            default => false,
        };
    }

    /**
     * Copie les données d'un prospect vers ce dossier
     */
    public function copyFromProspect(Prospect $prospect): void
    {
        $this->update([
            'first_name' => $prospect->first_name,
            'last_name' => $prospect->last_name,
            'email' => $prospect->email,
            'phone' => $prospect->phone,
            'birth_date' => $prospect->birth_date,
            'profession' => $prospect->profession,
            'education_level' => $prospect->education_level,
            'desired_field' => $prospect->desired_field,
            'desired_destination' => $prospect->desired_destination,
            'emergency_contact' => $prospect->emergency_contact,
            'documents' => $prospect->documents,
            'commercial_code' => $prospect->commercial_code,
            'notes' => $prospect->notes
        ]);
    }

    /**
     * Obtient le nom complet
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the user that the dossier is assigned to
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Calculate tuition payment progress as percentage
     */
    public function getTuitionProgressAttribute(): float
    {
        if (!$this->tuition_total_amount || $this->tuition_total_amount <= 0) {
            return 0;
        }
        return min(100, ($this->tuition_paid_amount / $this->tuition_total_amount) * 100);
    }

    /**
     * Check if tuition is fully paid
     */
    public function isTuitionFullyPaid(): bool
    {
        if (!$this->tuition_total_amount || $this->tuition_total_amount <= 0) {
            return false;
        }
        return $this->tuition_paid_amount >= $this->tuition_total_amount;
    }
}
