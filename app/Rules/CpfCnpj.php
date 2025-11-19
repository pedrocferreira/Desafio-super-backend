<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CpfCnpj implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $document = preg_replace('/[^0-9]/', '', (string) $value);

        if (strlen($document) === 11) {
            if (! $this->validateCpf($document)) {
                $fail('O CPF informado é inválido.');
            }
        } elseif (strlen($document) === 14) {
            if (! $this->validateCnpj($document)) {
                $fail('O CNPJ informado é inválido.');
            }
        } else {
            $fail('O documento deve ser um CPF (11 dígitos) ou CNPJ (14 dígitos) válido.');
        }
    }

    /**
     * Validar CPF
     */
    private function validateCpf(string $cpf): bool
    {
        // Verifica se todos os dígitos são iguais
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // Validar primeiro dígito verificador
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += (int) $cpf[$i] * (10 - $i);
        }
        $digit1 = ($sum * 10) % 11;
        if ($digit1 === 10) {
            $digit1 = 0;
        }

        if ($digit1 !== (int) $cpf[9]) {
            return false;
        }

        // Validar segundo dígito verificador
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += (int) $cpf[$i] * (11 - $i);
        }
        $digit2 = ($sum * 10) % 11;
        if ($digit2 === 10) {
            $digit2 = 0;
        }

        return $digit2 === (int) $cpf[10];
    }

    /**
     * Validar CNPJ
     */
    private function validateCnpj(string $cnpj): bool
    {
        // Verifica se todos os dígitos são iguais
        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        // Validar primeiro dígito verificador
        $sum = 0;
        $multipliers = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        for ($i = 0; $i < 12; $i++) {
            $sum += (int) $cnpj[$i] * $multipliers[$i];
        }
        $digit1 = $sum % 11 < 2 ? 0 : 11 - ($sum % 11);

        if ($digit1 !== (int) $cnpj[12]) {
            return false;
        }

        // Validar segundo dígito verificador
        $sum = 0;
        $multipliers = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        for ($i = 0; $i < 13; $i++) {
            $sum += (int) $cnpj[$i] * $multipliers[$i];
        }
        $digit2 = $sum % 11 < 2 ? 0 : 11 - ($sum % 11);

        return $digit2 === (int) $cnpj[13];
    }
}

