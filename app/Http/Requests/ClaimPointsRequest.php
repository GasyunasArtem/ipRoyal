<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClaimPointsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxTransactions = config('business.points.max_claim_transactions');
        
        return [
            'transaction_ids' => "nullable|array|max:{$maxTransactions}", // Prevent mass operations
            'transaction_ids.*' => 'integer|min:1|exists:points_transactions,id',
        ];
    }

    public function messages(): array
    {
        $maxTransactions = config('business.points.max_claim_transactions');
        
        return [
            'transaction_ids.max' => "Cannot claim more than {$maxTransactions} transactions at once.",
            'transaction_ids.*.min' => 'Transaction ID must be a positive integer.',
            'transaction_ids.*.exists' => 'One or more transaction IDs are invalid.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->transaction_ids) {
                // Verify transactions belong to the authenticated user
                $userTransactionIds = $this->user()
                    ->pointsTransactions()
                    ->whereIn('id', $this->transaction_ids)
                    ->where('is_claimed', false)
                    ->pluck('id')
                    ->toArray();

                $invalidIds = array_diff($this->transaction_ids, $userTransactionIds);
                
                if (!empty($invalidIds)) {
                    $validator->errors()->add('transaction_ids', 'Some transactions do not belong to you or are already claimed.');
                }
            }
        });
    }
}
