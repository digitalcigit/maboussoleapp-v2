<?php

namespace App\Http\Controllers;

use App\Models\DossierDocument;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class DossierDocumentPreviewController extends Controller
{
    public function __invoke(DossierDocument $document)
    {
        // Vérifier les permissions
        $this->authorize('view', $document);

        // Vérifier si le document peut être prévisualisé
        if (!$document->isPreviewable()) {
            abort(404);
        }

        // Pour les images, rediriger vers l'URL de téléchargement
        if (str_starts_with($document->mime_type, 'image/')) {
            return redirect($document->getDownloadUrl());
        }

        // Pour les PDFs, afficher directement dans le navigateur
        if ($document->mime_type === 'application/pdf') {
            $path = $document->getFullPath();
            
            if (!file_exists($path)) {
                abort(404);
            }

            return response()->file($path, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $document->original_name . '"'
            ]);
        }

        abort(404);
    }
}
