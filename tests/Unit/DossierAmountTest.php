<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Dossier;

class DossierAmountTest extends TestCase
{
    /** @test */
    public function test_amount_formatting()
    {
        // 1. Test de la saisie et du nettoyage des données
        $dossier = new Dossier();
        
        // Test avec différents formats de saisie
        $dossier->tuition_total_amount = "1000000.00"; // avec décimales
        $this->assertEquals(1000000, $dossier->tuition_total_amount);
        
        $dossier->tuition_total_amount = "1,000,000"; // avec séparateurs
        $this->assertEquals(1000000, $dossier->tuition_total_amount);
        
        $dossier->tuition_total_amount = "1 000 000"; // avec espaces
        $this->assertEquals(1000000, $dossier->tuition_total_amount);

        // Test de l'affichage dans le formulaire
        // Simuler le comportement du formulaire Filament
        $formattedValue = (int)$dossier->tuition_total_amount;
        $this->assertEquals(1000000, $formattedValue);
        $this->assertStringNotContainsString('.', (string)$formattedValue);
        $this->assertStringNotContainsString(',', (string)$formattedValue);
    }

    /** @test */
    public function test_payment_progress_calculation()
    {
        $dossier = new Dossier();
        
        // Test avec des montants entiers
        $dossier->down_payment_amount = 500000;
        $dossier->tuition_paid_amount = 100000;
        
        // Le progrès devrait être de 20%
        $this->assertEquals(20, $dossier->tuition_progress);
        
        // Test avec des montants décimaux qui devraient être convertis
        $dossier->down_payment_amount = "500000.50";
        $dossier->tuition_paid_amount = "100000.75";
        
        // Vérifie que les montants sont convertis en entiers
        $this->assertEquals(500000, $dossier->down_payment_amount);
        $this->assertEquals(100000, $dossier->tuition_paid_amount);
        
        // Le progrès devrait toujours être de 20%
        $this->assertEquals(20, $dossier->tuition_progress);
    }
}
