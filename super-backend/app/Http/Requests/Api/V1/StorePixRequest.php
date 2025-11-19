<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StorePixRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'payer_name' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-ZÀ-ÿ\s]+$/'],
            'payer_document' => ['nullable', 'string', 'max:18', new \App\Rules\CpfCnpj()],
            'description' => ['nullable', 'string', 'max:255'],
            'subadquirente' => ['nullable', 'string', 'in:subadq_a,subadq_b,subadquirente_a,subadquirente_b'],
            'simulate_failure' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Documentação adicional para o Scribe (exemplos no /docs).
     *
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        return [
            'amount' => [
                'description' => 'Valor do PIX (em reais).',
                'example' => 150.75,
            ],
            'payer_name' => [
                'description' => 'Nome do pagador. Opcional, mas útil para testes.',
                'example' => 'João da Silva',
            ],
            'payer_document' => [
                'description' => 'CPF ou CNPJ do pagador. Exemplo válido para testes.',
                'example' => '11144477735',
            ],
            'description' => [
                'description' => 'Descrição ou referência do pagamento.',
                'example' => 'Pedido #12345',
            ],
            'subadquirente' => [
                'description' => 'Força o uso de uma subadquirente específica.',
                'example' => 'subadq_a',
            ],
            'simulate_failure' => [
                'description' => 'Define se o fluxo deve simular uma falha.',
                'example' => false,
            ],
        ];
    }
}
