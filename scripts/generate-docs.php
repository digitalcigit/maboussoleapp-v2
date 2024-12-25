<?php

namespace Scripts;

class DocumentationGenerator
{
    private $basePath;

    private $docsPath;

    public function __construct()
    {
        $this->basePath = dirname(__DIR__);
        $this->docsPath = $this->basePath.'/docs';
    }

    /**
     * Génère la documentation des modèles
     */
    public function generateModelsDocs()
    {
        $modelsPath = $this->basePath.'/app/Models';
        $modelsDocs = $this->docsPath.'/technical/models';

        if (! is_dir($modelsDocs)) {
            mkdir($modelsDocs, 0755, true);
        }

        foreach (glob($modelsPath.'/*.php') as $modelFile) {
            $this->documentModel($modelFile, $modelsDocs);
        }
    }

    /**
     * Génère la documentation d'un modèle
     */
    private function documentModel($modelFile, $outputPath)
    {
        $className = basename($modelFile, '.php');
        $reflection = new \ReflectionClass("App\\Models\\$className");

        $documentation = "# $className\n\n";
        $documentation .= "## Propriétés\n";

        // Documenter les propriétés
        foreach ($reflection->getProperties() as $property) {
            $documentation .= '- '.$property->getName()."\n";
        }

        // Documenter les relations
        $documentation .= "\n## Relations\n";
        foreach ($reflection->getMethods() as $method) {
            if ($this->isRelationMethod($method)) {
                $documentation .= '- '.$method->getName()."\n";
            }
        }

        file_put_contents("$outputPath/$className.md", $documentation);
    }

    /**
     * Vérifie si une méthode est une relation Eloquent
     */
    private function isRelationMethod(\ReflectionMethod $method)
    {
        $returnType = $method->getReturnType();
        if (! $returnType) {
            return false;
        }

        $relationTypes = [
            'HasOne', 'HasMany', 'BelongsTo', 'BelongsToMany',
            'MorphTo', 'MorphOne', 'MorphMany',
        ];

        foreach ($relationTypes as $type) {
            if (str_contains($returnType->getName(), $type)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Génère la documentation des contrôleurs
     */
    public function generateControllersDocs()
    {
        $controllersPath = $this->basePath.'/app/Http/Controllers';
        $controllersDocs = $this->docsPath.'/technical/controllers';

        if (! is_dir($controllersDocs)) {
            mkdir($controllersDocs, 0755, true);
        }

        foreach (glob($controllersPath.'/*.php') as $controllerFile) {
            $this->documentController($controllerFile, $controllersDocs);
        }
    }

    /**
     * Génère la documentation d'un contrôleur
     */
    private function documentController($controllerFile, $outputPath)
    {
        $className = basename($controllerFile, '.php');
        $reflection = new \ReflectionClass("App\\Http\\Controllers\\$className");

        $documentation = "# $className\n\n";
        $documentation .= "## Actions\n";

        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->class === $reflection->getName()) {
                $documentation .= '### '.$method->getName()."\n";
                $documentation .= $this->getMethodDocumentation($method);
                $documentation .= "\n";
            }
        }

        file_put_contents("$outputPath/$className.md", $documentation);
    }

    /**
     * Extrait la documentation d'une méthode
     */
    private function getMethodDocumentation(\ReflectionMethod $method)
    {
        $doc = $method->getDocComment();
        if (! $doc) {
            return "Pas de documentation disponible.\n";
        }

        return $this->formatDocComment($doc);
    }

    /**
     * Formate un commentaire de documentation
     */
    private function formatDocComment($docComment)
    {
        $lines = explode("\n", $docComment);
        $formatted = '';

        foreach ($lines as $line) {
            $line = trim($line, "/* \t");
            if ($line && ! str_starts_with($line, '@')) {
                $formatted .= "$line\n";
            }
        }

        return $formatted;
    }

    /**
     * Génère un rapport de couverture de la documentation
     */
    public function generateCoverageReport()
    {
        $report = "# Rapport de Couverture de la Documentation\n\n";
        $report .= 'Généré le : '.date('Y-m-d H:i:s')."\n\n";

        // Vérification des modèles
        $report .= "## Modèles\n";
        $modelsPath = $this->basePath.'/app/Models';
        $totalModels = count(glob($modelsPath.'/*.php'));
        $documentedModels = count(glob($this->docsPath.'/technical/models/*.md'));
        $report .= "- Total : $totalModels\n";
        $report .= "- Documentés : $documentedModels\n";
        $report .= '- Couverture : '.($totalModels ? round(($documentedModels / $totalModels) * 100) : 0)."%\n\n";

        // Vérification des contrôleurs
        $report .= "## Contrôleurs\n";
        $controllersPath = $this->basePath.'/app/Http/Controllers';
        $totalControllers = count(glob($controllersPath.'/*.php'));
        $documentedControllers = count(glob($this->docsPath.'/technical/controllers/*.md'));
        $report .= "- Total : $totalControllers\n";
        $report .= "- Documentés : $documentedControllers\n";
        $report .= '- Couverture : '.($totalControllers ? round(($documentedControllers / $totalControllers) * 100) : 0)."%\n";

        file_put_contents($this->docsPath.'/coverage-report.md', $report);
    }
}

// Exécution
$generator = new DocumentationGenerator;
$generator->generateModelsDocs();
$generator->generateControllersDocs();
$generator->generateCoverageReport();
