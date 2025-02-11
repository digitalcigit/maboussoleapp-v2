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

    // Types de documents autorisés avec leurs types MIME
    public const ALLOWED_MIME_TYPES = [
        'application/pdf' => ['pdf'],
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png' => ['png'],
        'application/msword' => ['doc'],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
    ];

    // Taille maximale de fichier (2MB)
    public const MAX_FILE_SIZE = 2 * 1024 * 1024;

    // Types de documents prévisualisables
    public const PREVIEWABLE_TYPES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
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
        if (!Storage::disk('public')->exists($this->file_path)) {
            return '#';
        }
        return Storage::disk('public')->url($this->file_path);
    }

    /**
     * Check if the file exists.
     */
    public function fileExists(): bool
    {
        return Storage::disk('public')->exists($this->file_path);
    }

    /**
     * Get the file contents.
     */
    public function getFileContents(): ?string
    {
        if (!$this->fileExists()) {
            return null;
        }
        return Storage::disk('public')->get($this->file_path);
    }

    /**
     * Get the file mime type.
     */
    public function getMimeType(): string
    {
        return $this->mime_type ?? Storage::disk('public')->mimeType($this->file_path);
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

    /**
     * Valider un fichier avant l'upload
     */
    public static function validateFile($file): bool
    {
        // Vérifier la taille
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            return false;
        }

        // Vérifier le type MIME
        $mime = $file->getMimeType();
        return isset(self::ALLOWED_MIME_TYPES[$mime]);
    }

    /**
     * Obtenir l'extension autorisée pour un type MIME
     */
    public static function getAllowedExtensionForMime($mime): ?string
    {
        return self::ALLOWED_MIME_TYPES[$mime][0] ?? null;
    }

    /**
     * Vérifier si le document peut être prévisualisé
     */
    public function isPreviewable(): bool
    {
        return in_array($this->mime_type, self::PREVIEWABLE_TYPES);
    }

    /**
     * Obtenir l'URL de prévisualisation
     */
    public function getPreviewUrl(): string
    {
        if (!$this->isPreviewable()) {
            return '#';
        }

        if (str_starts_with($this->mime_type, 'image/')) {
            return $this->getDownloadUrl();
        }

        return route('dossier-documents.preview', $this);
    }

    /**
     * Get the file mime type with caching
     */
    public function getCachedMimeType(): string
    {
        $cacheKey = "document_mime_{$this->id}";
        
        return cache()->remember(
            $cacheKey,
            now()->addDay(),
            fn() => $this->getMimeType()
        );
    }

    /**
     * Get the file contents with caching for small files
     */
    public function getCachedContents(): ?string
    {
        if ($this->size > 1024 * 1024) { // Ne pas mettre en cache les fichiers > 1MB
            return $this->getFileContents();
        }

        $cacheKey = "document_contents_{$this->id}";
        
        return cache()->remember(
            $cacheKey,
            now()->addHour(),
            fn() => $this->getFileContents()
        );
    }

    /**
     * Générer une vignette pour le document
     */
    public function generateThumbnail(): ?string
    {
        if (!$this->isPreviewable()) {
            return null;
        }

        $cacheKey = "document_thumbnail_{$this->id}";
        
        return cache()->remember($cacheKey, now()->addWeek(), function () {
            if (str_starts_with($this->mime_type, 'image/')) {
                // Pour les images, créer une version redimensionnée
                $image = \Intervention\Image\Facades\Image::make($this->getFullPath());
                $image->fit(200, 200);
                
                $thumbnailPath = 'thumbnails/' . basename($this->file_path);
                Storage::disk('public')->put($thumbnailPath, $image->encode());
                
                return Storage::disk('public')->url($thumbnailPath);
            }
            
            if ($this->mime_type === 'application/pdf') {
                // Pour les PDFs, générer une image de la première page
                return null; // À implémenter avec une bibliothèque PDF
            }
            
            return null;
        });
    }
}
