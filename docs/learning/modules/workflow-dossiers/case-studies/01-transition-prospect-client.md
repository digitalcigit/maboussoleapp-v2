# Étude de cas : Transition Prospect vers Client

## Contexte

Un prospect a complété toutes les étapes initiales du workflow et est prêt à effectuer le paiement pour devenir client.

## Problématique

Comment gérer correctement la transition d'un prospect vers un client lors du paiement, en s'assurant que :
1. Toutes les validations nécessaires sont effectuées
2. Les données sont cohérentes
3. Les notifications appropriées sont envoyées
4. Le processus est traçable

## Solution

### 1. Validation du dossier

```php
class DossierPaymentValidator
{
    public function validate(Dossier $dossier): bool
    {
        // Vérifier que toutes les étapes précédentes sont complétées
        if ($dossier->current_step < Dossier::STEP_VALIDATION) {
            throw new InvalidStepException("Les étapes précédentes doivent être complétées");
        }

        // Vérifier les documents requis
        if (!$dossier->hasRequiredDocuments()) {
            throw new MissingDocumentsException("Documents requis manquants");
        }

        // Vérifier les informations personnelles
        if (!$dossier->hasCompletePersonalInfo()) {
            throw new IncompleteInfoException("Informations personnelles incomplètes");
        }

        return true;
    }
}
```

### 2. Gestion du paiement

```php
class DossierPaymentProcessor
{
    public function process(Dossier $dossier, PaymentData $paymentData): bool
    {
        DB::beginTransaction();
        
        try {
            // Créer la transaction
            $payment = $dossier->payments()->create([
                'amount' => $paymentData->amount,
                'method' => $paymentData->method,
                'reference' => $paymentData->reference,
            ]);

            // Vérifier le paiement avec le prestataire
            $paymentProvider = $this->getPaymentProvider();
            $result = $paymentProvider->verify($payment);

            if ($result->isSuccess()) {
                // Mettre à jour le statut du dossier
                $dossier->update([
                    'current_step' => Dossier::STEP_PAYMENT,
                    'current_status' => Dossier::STATUS_COMPLETED,
                    'payment_date' => now(),
                ]);

                DB::commit();
                return true;
            }

            throw new PaymentFailedException($result->error);
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}
```

### 3. Notifications

```php
class DossierPaymentNotifier
{
    public function notify(Dossier $dossier)
    {
        // Notifier le client
        $dossier->notify(new PaymentConfirmationNotification());

        // Notifier les administrateurs
        User::role('admin')->each(function ($admin) use ($dossier) {
            $admin->notify(new NewClientNotification($dossier));
        });

        // Enregistrer dans l'historique
        activity()
            ->performedOn($dossier)
            ->log('Transition prospect vers client effectuée');
    }
}
```

## Implémentation

```php
class DossierPaymentService
{
    public function __construct(
        private DossierPaymentValidator $validator,
        private DossierPaymentProcessor $processor,
        private DossierPaymentNotifier $notifier
    ) {}

    public function handlePayment(Dossier $dossier, PaymentData $paymentData)
    {
        // 1. Validation
        $this->validator->validate($dossier);

        // 2. Traitement du paiement
        $success = $this->processor->process($dossier, $paymentData);

        // 3. Notifications
        if ($success) {
            $this->notifier->notify($dossier);
        }

        return $success;
    }
}
```

## Tests

```php
class DossierPaymentTest extends TestCase
{
    public function test_successful_payment_transition()
    {
        // Arrange
        $dossier = Dossier::factory()->create([
            'current_step' => Dossier::STEP_VALIDATION,
            'current_status' => Dossier::STATUS_COMPLETED,
        ]);

        $paymentData = new PaymentData([
            'amount' => 1000,
            'method' => 'card',
            'reference' => 'TEST123',
        ]);

        // Act
        $service = app(DossierPaymentService::class);
        $result = $service->handlePayment($dossier, $paymentData);

        // Assert
        $this->assertTrue($result);
        $this->assertEquals(Dossier::STEP_PAYMENT, $dossier->fresh()->current_step);
        $this->assertTrue($dossier->fresh()->isClient());
        $this->assertNotNull($dossier->fresh()->payment_date);
    }

    public function test_prevents_invalid_transition()
    {
        // Arrange
        $dossier = Dossier::factory()->create([
            'current_step' => Dossier::STEP_INITIAL,
        ]);

        // Act & Assert
        $this->expectException(InvalidStepException::class);
        
        $service = app(DossierPaymentService::class);
        $service->handlePayment($dossier, $paymentData);
    }
}
```

## Leçons apprises

1. **Validation stricte** : La validation rigoureuse avant le paiement évite les problèmes de cohérence des données.

2. **Transactions** : L'utilisation de transactions garantit l'intégrité des données en cas d'erreur.

3. **Notifications** : Un système de notification complet améliore l'expérience utilisateur et la traçabilité.

4. **Tests** : Des tests complets sont essentiels pour une fonctionnalité aussi critique.

## Applications possibles

1. **Autres transitions d'état** : Le même pattern peut être appliqué pour d'autres transitions importantes dans le workflow.

2. **Intégration de paiement** : La structure peut être adaptée pour différents prestataires de paiement.

3. **Audit** : Le système de traçabilité peut être étendu pour d'autres opérations critiques.
