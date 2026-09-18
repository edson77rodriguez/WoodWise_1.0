<?php

namespace App\Services;

use App\Models\Formula;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class FormulaEngineService
{
    private const VARIABLE_ALIASES = [
        'dap' => ['diametro_pecho', 'diametro', 'dbh', 'd'],
        'altura' => ['altura_total', 'h'],
        'h' => ['altura_total'],
        'l' => ['longitud'],
        'd' => ['diametro_pecho', 'diametro'],
        'd_m' => ['diametro_medio', 'dm'],
        'dm' => ['diametro_medio'],
        'd_0' => ['diametro', 'diametro_otro_extremo'],
        'd1' => ['diametro_otro_extremo'],
        'd_1' => ['diametro_otro_extremo'],
        'factor' => ['densidad'],
    ];

    private const RESERVED_IDENTIFIERS = [
        'abs', 'acos', 'asin', 'atan', 'ceil', 'cos', 'exp', 'floor', 'log',
        'max', 'min', 'pow', 'round', 'sin', 'sqrt', 'tan', 'pi', 'true', 'false',
    ];

    public function extractVariables(string $expression): array
    {
        $normalized = $this->normalizeExpression($expression);
        preg_match_all('/\b[a-zA-Z_][a-zA-Z0-9_]*\b/', $normalized, $matches);

        $variables = [];

        foreach ($matches[0] as $identifier) {
            $lower = strtolower($identifier);

            if (in_array($lower, self::RESERVED_IDENTIFIERS, true)) {
                continue;
            }

            $variables[$identifier] = true;
        }

        return array_keys($variables);
    }

    public function normalizeExpression(string $expression): string
    {
        $expression = str_replace(['×', '·'], '*', $expression);
        $expression = str_replace('π', 'pi', $expression);
        $expression = preg_replace('/^[A-Za-z_][A-Za-z0-9_]*\s*=\s*/', '', $expression);
        $expression = preg_replace('/\b[A-Za-z_][A-Za-z0-9_]*\s*=\s*/', '', $expression);
        $expression = preg_replace('/\b([A-Za-z_][A-Za-z0-9_]*)\^2\b/', '$1**2', $expression);
        $expression = preg_replace('/\b([A-Za-z_][A-Za-z0-9_]*)²\b/u', '$1**2', $expression);

        return trim($expression);
    }

    public function buildVariablesFromSchema(?array $schema, Model $entity, array $overrides = []): array
    {
        $schema = $schema ?? [];
        $resolved = [];

        foreach ($schema as $item) {
            $name = $item['name'] ?? null;

            if (!$name) {
                continue;
            }

            $canonicalName = $this->canonicalVariableName($name);

            if (array_key_exists($name, $overrides)) {
                $resolved[$canonicalName] = $this->toNumeric($overrides[$name]);
                continue;
            }

            if (array_key_exists($canonicalName, $overrides)) {
                $resolved[$canonicalName] = $this->toNumeric($overrides[$canonicalName]);
                continue;
            }

            foreach ($this->aliasCandidates($canonicalName) as $alias) {
                $aliasKey = $this->canonicalVariableName($alias);

                if (array_key_exists($aliasKey, $overrides)) {
                    $resolved[$canonicalName] = $this->toNumeric($overrides[$aliasKey]);
                    continue 2;
                }

                $aliasValue = data_get($entity, $alias);
                if ($aliasValue !== null && $aliasValue !== '') {
                    $resolved[$canonicalName] = $this->toNumeric($aliasValue);
                    continue 2;
                }
            }

            if (array_key_exists('value', $item) && $item['value'] !== null && $item['value'] !== '') {
                $resolved[$canonicalName] = $this->toNumeric($item['value']);
                continue;
            }

            $source = $item['source'] ?? null;
            if (is_string($source) && $source !== '') {
                $value = data_get($entity, $source);

                if ($value !== null && $value !== '') {
                    $resolved[$canonicalName] = $this->toNumeric($value);
                    continue;
                }
            }

            if (array_key_exists('default', $item) && $item['default'] !== null && $item['default'] !== '') {
                $resolved[$canonicalName] = $this->toNumeric($item['default']);
                continue;
            }

            if (!empty($item['required'])) {
                throw new InvalidArgumentException("Falta el valor para la variable {$name}.");
            }
        }

        return $resolved;
    }

    public function evaluate(string $expression, array $variables = []): float
    {
        $expression = $this->normalizeExpression($expression);
        $expression = preg_replace('/\bpi\b/i', '(pi())', $expression);
        $expression = str_replace('^', '**', $expression);

        $normalizedVariables = [];
        foreach ($variables as $name => $value) {
            $normalizedVariables[$this->canonicalVariableName((string) $name)] = $this->toNumeric($value);
        }
        $allowedIdentifiers = array_fill_keys(array_keys($normalizedVariables), true);

        foreach ($normalizedVariables as $name => $value) {
            $expression = preg_replace(
                '/\b' . preg_quote($name, '/') . '\b/i',
                '(' . $value . ')',
                $expression
            );
        }

        $remainingIdentifiers = $this->extractIdentifiers($expression);
        foreach ($remainingIdentifiers as $identifier) {
            $canonicalIdentifier = $this->canonicalVariableName($identifier);

            if (isset($allowedIdentifiers[$canonicalIdentifier])) {
                continue;
            }

            if (!in_array($canonicalIdentifier, self::RESERVED_IDENTIFIERS, true)) {
                throw new InvalidArgumentException("La expresión contiene un identificador no soportado: {$identifier}.");
            }
        }

if (!preg_match('/^[0-9+\-*\/().,\sA-Za-z_]*$/', $expression)) {
                    throw new InvalidArgumentException('La expresión contiene caracteres no permitidos.');
        }

        try {
            /** @noinspection PhpUsageOfSilenceOperatorInspection */
            $result = @eval('return ' . $expression . ';');
        } catch (\Throwable $throwable) {
            throw new InvalidArgumentException('No fue posible evaluar la expresión: ' . $throwable->getMessage());
        }

        if (!is_numeric($result)) {
            throw new InvalidArgumentException('La expresión no devolvió un valor numérico.');
        }

        return (float) $result;
    }

    public function resolveOutputs(Formula $formula, float $result): array
    {
        $carbonoFactor = $formula->carbono_factor !== null ? (float) $formula->carbono_factor : 0.5;
        $biomasaFactor = $formula->biomasa_factor !== null ? (float) $formula->biomasa_factor : null;
        $resultadoTipo = strtolower((string) ($formula->resultado_tipo ?? 'calculo'));

        $calculo = round($result, 10);
        $biomasa = null;
        $carbono = null;

        if ($resultadoTipo === 'biomasa') {
            $biomasa = $biomasaFactor !== null ? round($calculo * $biomasaFactor, 10) : $calculo;
            $carbono = round($biomasa * $carbonoFactor, 10);
            return compact('calculo', 'biomasa', 'carbono');
        }

        if ($resultadoTipo === 'carbono') {
            $carbono = $calculo;
            $biomasa = $carbonoFactor > 0 ? round($carbono / $carbonoFactor, 10) : $calculo;
            return compact('calculo', 'biomasa', 'carbono');
        }

        $biomasa = $biomasaFactor !== null ? round($calculo * $biomasaFactor, 10) : $calculo;
        $carbono = round($biomasa * $carbonoFactor, 10);

        return compact('calculo', 'biomasa', 'carbono');
    }

    public function calculateForModel(Formula $formula, Model $entity, array $overrides = []): array
    {
        $expression = $this->normalizeExpression($formula->expresion);
        $variables = $this->buildVariablesFromSchema($formula->variables_schema, $entity, $overrides);

        if (empty($variables)) {
            $variables = $this->buildVariablesFromExpression($expression, $entity, $overrides);
        }

        $result = $this->evaluate($expression, $variables);

        return $this->resolveOutputs($formula, $result);
    }

    private function buildVariablesFromExpression(string $expression, Model $entity, array $overrides = []): array
    {
        $resolved = [];
        $variables = $this->extractVariables($expression);

        foreach ($variables as $variable) {
            $canonical = $this->canonicalVariableName($variable);

            if (array_key_exists($canonical, $resolved)) {
                continue;
            }

            if (array_key_exists($canonical, $overrides)) {
                $resolved[$canonical] = $this->toNumeric($overrides[$canonical]);
                continue;
            }

            $aliasCandidates = $this->aliasCandidates($canonical);
            $aliasCandidates[] = $canonical;

            foreach ($aliasCandidates as $alias) {
                $aliasKey = $this->canonicalVariableName($alias);

                if (array_key_exists($aliasKey, $overrides)) {
                    $resolved[$canonical] = $this->toNumeric($overrides[$aliasKey]);
                    continue 2;
                }

                $value = data_get($entity, $alias);
                if ($value !== null && $value !== '') {
                    $resolved[$canonical] = $this->toNumeric($value);
                    continue 2;
                }
            }
        }

        return $resolved;
    }

    private function aliasCandidates(string $name): array
    {
        $name = $this->canonicalVariableName($name);

        return self::VARIABLE_ALIASES[$name] ?? [];
    }

    private function extractIdentifiers(string $expression): array
    {
        preg_match_all('/\b[a-zA-Z_][a-zA-Z0-9_]*\b/', $expression, $matches);

        return array_values(array_unique($matches[0] ?? []));
    }

    private function canonicalVariableName(string $name): string
    {
        return strtolower(trim($name));
    }

    private function toNumeric(mixed $value): float
    {
        if (!is_numeric($value)) {
            throw new InvalidArgumentException('Se esperaba un valor numérico para la fórmula.');
        }

        return (float) $value;
    }
}