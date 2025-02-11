<?php

namespace Tests\Feature;

use App\Models\Dossier;
use App\Models\DossierDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DossierDocumentTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function it_can_upload_a_pdf_document()
    {
        $user = User::factory()->create();
        $dossier = Dossier::factory()->create();
        
        $file = UploadedFile::fake()->create('document.pdf', 1024, 'application/pdf');
        
        $document = DossierDocument::create([
            'dossier_id' => $dossier->id,
            'document_type' => 'cv',
            'file_path' => $file->store('documents', 'public'),
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'uploaded_at' => now(),
        ]);

        $this->assertTrue($document->fileExists());
        $this->assertEquals('application/pdf', $document->getMimeType());
        Storage::disk('public')->assertExists($document->file_path);
    }

    /** @test */
    public function it_can_handle_large_files()
    {
        $user = User::factory()->create();
        $dossier = Dossier::factory()->create();
        
        // Créer un fichier de 10MB
        $file = UploadedFile::fake()->create('large_document.pdf', 10240);
        
        $document = DossierDocument::create([
            'dossier_id' => $dossier->id,
            'document_type' => 'cv',
            'file_path' => $file->store('documents', 'public'),
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'uploaded_at' => now(),
        ]);

        $this->assertTrue($document->fileExists());
        $this->assertEquals('10 MB', $document->getHumanFileSize());
    }

    /** @test */
    public function it_handles_missing_files_gracefully()
    {
        $document = DossierDocument::create([
            'dossier_id' => Dossier::factory()->create()->id,
            'document_type' => 'cv',
            'file_path' => 'documents/missing.pdf',
            'original_name' => 'missing.pdf',
            'mime_type' => 'application/pdf',
            'size' => 1024,
            'uploaded_at' => now(),
        ]);

        $this->assertFalse($document->fileExists());
        $this->assertEquals('#', $document->getDownloadUrl());
        $this->assertNull($document->getFileContents());
    }

    /** @test */
    public function it_validates_allowed_document_types()
    {
        $this->assertTrue(in_array('cv', DossierDocument::TYPES));
        $this->assertTrue(in_array('passeport', DossierDocument::TYPES));
        $this->assertTrue(in_array('diplome', DossierDocument::TYPES));
    }

    /** @test */
    public function it_can_handle_image_files()
    {
        $user = User::factory()->create();
        $dossier = Dossier::factory()->create();
        
        $file = UploadedFile::fake()->image('photo.jpg');
        
        $document = DossierDocument::create([
            'dossier_id' => $dossier->id,
            'document_type' => 'photo_identite',
            'file_path' => $file->store('documents', 'public'),
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'uploaded_at' => now(),
        ]);

        $this->assertTrue($document->fileExists());
        $this->assertEquals('image/jpeg', $document->getMimeType());
    }
}
