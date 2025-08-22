<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StatsRequest extends FormRequest
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
        $maxDays = config('business.stats.max_period_days');
        
        return [
            'days' => "nullable|integer|min:1|max:{$maxDays}",
        ];
    }

    public function messages(): array
    {
        $maxDays = config('business.stats.max_period_days');
        
        return [
            'days.min' => 'Days must be at least 1.',
            'days.max' => "Cannot request more than {$maxDays} days of stats.",
        ];
    }

    /**
     * Get the validated data from the request.
     */
    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated($key, $default);
        
        // Set default if not provided
        if (!isset($validated['days'])) {
            $validated['days'] = config('business.stats.default_period_days');
        }
        
        return $validated;
    }
}
