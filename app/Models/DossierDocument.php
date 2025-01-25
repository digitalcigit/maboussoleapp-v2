<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class DossierDocument extends Model
{
    use HasFactory;
    use SoftDeletes;

    // Types de documents par étape
    public const TYPES = [
        // Étape 1 : Analyse
        'passeport' => 'Passeport',
        'cv' => 'CV',
        'diplome' => 'Diplôme',
        'releve_notes' => 'Relevé de notes',
        
        // Étape 2 : Admission
        'attestation_naissance' => 'Attestation de naissance',
        'photo_identite' => 'Photo d\'identité',
        'lettre_motivation' => 'Lettre de motivation',
        'preuve_paiement_admission' => 'Preuve de paiement admission',
        
        // Étape 3 : Paiement
        'preuve_paiement_agence' => 'Preuve paiement agence',
        'preuve_paiement_scolarite' => 'Preuve paiement scolarité',
        'attestation_admission' => 'Attestation d\'admission',
        
        // Étape 4 : Visa
        'garant_financier' => 'Garant financier',
        'attestation_travail' => 'Attestation de travail',
        'attestation_bancaire' => 'Attestation bancaire',
        'preuve_paiement_visa' => 'Preuve paiement visa',
        'formulaire_visa' => 'Formulaire de visa',
    ];

    protected $fillable = [
        'dossier_id',
        'step_number',
        'document_type',
        'file_path',
        'original_name',
        'mime_type',
        'size',
        'description',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    /**
     * Get the dossier that owns this document.
     */
    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Dossier::class);
    }

    /**
     * Get the document type label.
     */
    public function getDocumentTypeLabel(): string
    {
        return self::TYPES[$this->document_type] ?? $this->document_type;
    }

    /**
     * Get the full storage path of the file.
     */
    public function getFullPath(): string
    {
        return Storage::disk('public')->path($this->file_path);
    }

    /**
     * Get the URL to download the file.
     */
    public function getDownloadUrl(): string
    {
        return Storage::disk('public')->url($this->file_path);
    }

    /**
     * Get the human readable file size.
     */
    public function getHumanFileSize(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get the required document types for a specific step.
     */
    public static function getRequiredTypesForStep(int $step): array
    {
        return match ($step) {
            Dossier::STEP_ANALYSIS => [
                'passeport',
                'cv',
                'diplome',
                'releve_notes',
            ],
            Dossier::STEP_ADMISSION => [
                'attestation_naissance',
                'photo_identite',
                'lettre_motivation',
                'preuve_paiement_admission',
            ],
            Dossier::STEP_PAYMENT => [
                'preuve_paiement_agence',
                'preuve_paiement_scolarite',
                'attestation_admission',
            ],
            Dossier::STEP_VISA => [
                'garant_financier',
                'attestation_travail',
                'attestation_bancaire',
                'preuve_paiement_visa',
                'formulaire_visa',
            ],
            default => [],
        };
    }
}
