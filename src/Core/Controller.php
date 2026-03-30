<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function validate(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $ruleString) {
            $fieldRules = explode('|', $ruleString);
            $value      = $data[$field] ?? null;

            foreach ($fieldRules as $rule) {
                if ($rule === 'required' && empty($value) && $value !== '0') {
                    $errors[$field][] = "O campo {$field} é obrigatório.";
                }

                if (str_starts_with($rule, 'max:')) {
                    $max = (int) substr($rule, 4);
                    if (strlen((string) $value) > $max) {
                        $errors[$field][] = "O campo {$field} deve ter no máximo {$max} caracteres.";
                    }
                }

                if ($rule === 'in') {
                    // handled per-field with specific enums
                }
            }
        }

        return $errors;
    }
}
