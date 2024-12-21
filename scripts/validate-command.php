<?php

class CommandValidator
{
    private array $criticalCommands = [
        'migrate' => [
            'required_flags' => ['--env'],
            'test_values' => ['testing'],
            'error_message' => 'La commande migrate doit inclure --env=testing pendant les tests'
        ],
        'db:seed' => [
            'required_flags' => ['--env'],
            'test_values' => ['testing'],
            'error_message' => 'La commande db:seed doit inclure --env=testing pendant les tests'
        ],
        'config:cache' => [
            'required_flags' => ['--env'],
            'test_values' => ['testing', 'local'],
            'error_message' => 'La commande config:cache doit spécifier l\'environnement'
        ]
    ];

    public function validate(array $args): array
    {
        $command = $args[0] ?? '';
        if (!$command) {
            return ['valid' => false, 'message' => 'Aucune commande fournie'];
        }

        // Si ce n'est pas une commande critique, on la considère valide
        if (!$this->isCriticalCommand($command)) {
            return ['valid' => true, 'message' => 'Commande non critique'];
        }

        $rules = $this->criticalCommands[$command];
        $flags = $this->parseFlags($args);

        // Vérification des flags requis
        foreach ($rules['required_flags'] as $flag) {
            if (!isset($flags[$flag])) {
                return [
                    'valid' => false,
                    'message' => $rules['error_message'],
                    'suggestion' => $this->getSuggestion($command, $args)
                ];
            }

            // Pour --env, vérifier la valeur
            if ($flag === '--env' && !in_array($flags[$flag], $rules['test_values'])) {
                return [
                    'valid' => false,
                    'message' => "La valeur '{$flags[$flag]}' n'est pas autorisée pour --env",
                    'suggestion' => $this->getSuggestion($command, $args)
                ];
            }
        }

        return ['valid' => true, 'message' => 'Commande valide'];
    }

    private function isCriticalCommand(string $command): bool
    {
        return isset($this->criticalCommands[$command]);
    }

    private function parseFlags(array $args): array
    {
        $flags = [];
        foreach ($args as $arg) {
            if (strpos($arg, '--env=') === 0) {
                $flags['--env'] = substr($arg, 6);
            }
        }
        return $flags;
    }

    private function getSuggestion(string $command, array $args): string
    {
        $suggestion = "php artisan $command";
        if ($command === 'migrate' || $command === 'db:seed') {
            $suggestion .= " --env=testing";
        }
        return $suggestion;
    }
}

// Utilisation du validateur
if (php_sapi_name() === 'cli') {
    $validator = new CommandValidator();
    $result = $validator->validate(array_slice($argv, 1));
    
    if (!$result['valid']) {
        echo "⚠️  ERREUR : {$result['message']}\n";
        if (isset($result['suggestion'])) {
            echo "✅ Suggestion : {$result['suggestion']}\n";
        }
        exit(1);
    }
    
    echo "✅ {$result['message']}\n";
    exit(0);
}
