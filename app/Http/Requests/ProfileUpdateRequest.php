<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
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
        $maxAnswers = config('business.validation.max_profile_answers');
        $maxLength = config('business.validation.max_answer_length');
        
        return [
            'answers' => "required|array|min:1|max:{$maxAnswers}", // Prevent abuse
            'answers.*' => "required", // Will be validated based on question type
        ];
    }

    public function messages(): array
    {
        $maxAnswers = config('business.validation.max_profile_answers');
        $maxLength = config('business.validation.max_answer_length');
        
        return [
            'answers.min' => 'At least one answer is required.',
            'answers.max' => "Cannot submit more than {$maxAnswers} answers at once.",
            'answers.*.max' => "Each answer cannot exceed {$maxLength} characters.",
        ];
    }

    /**
     * Get the validated data from the request.
     */
    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated($key, $default);
        
        // Sanitize answers based on type
        if (isset($validated['answers'])) {
            $validated['answers'] = array_map(function ($answer) {
                // For multiselect, answer could be an array
                if (is_array($answer)) {
                    return array_map(function ($item) {
                        return trim(strip_tags($item));
                    }, $answer);
                }
                // For single/date, answer is a string
                return trim(strip_tags($answer));
            }, $validated['answers']);
        }
        
        return $validated;
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->answers) {
                // Get questions with their types and options
                $questionIds = array_keys($this->answers);
                $questions = \App\Models\ProfilingQuestion::where('is_active', true)
                    ->whereIn('id', $questionIds)
                    ->get()
                    ->keyBy('id');

                $validQuestionIds = $questions->pluck('id')->toArray();
                $invalidIds = array_diff($questionIds, $validQuestionIds);
                
                if (!empty($invalidIds)) {
                    $validator->errors()->add('answers', 'Some question IDs are invalid or inactive.');
                    return;
                }

                // Validate each answer based on question type
                foreach ($this->answers as $questionId => $answer) {
                    $question = $questions->get($questionId);
                    if (!$question) continue;

                    $this->validateAnswerByType($validator, $question, $answer, $questionId);
                }
            }
        });
    }

    private function validateAnswerByType($validator, $question, $answer, $questionId): void
    {
        $maxLength = config('business.validation.max_answer_length');

        switch ($question->type) {
            case 'single':
                // Single choice - should be a string and in options
                if (!is_string($answer)) {
                    $validator->errors()->add("answers.{$questionId}", 'Single choice answer must be a string.');
                    return;
                }
                
                if (strlen($answer) > $maxLength) {
                    $validator->errors()->add("answers.{$questionId}", "Answer cannot exceed {$maxLength} characters.");
                    return;
                }

                if ($question->options && !in_array($answer, $question->options)) {
                    $validator->errors()->add("answers.{$questionId}", 'Selected option is not valid for this question.');
                }
                break;

            case 'multiple':
                // Multiple choice - should be an array and all items in options
                if (!is_array($answer)) {
                    $validator->errors()->add("answers.{$questionId}", 'Multiple choice answer must be an array.');
                    return;
                }

                if (empty($answer)) {
                    $validator->errors()->add("answers.{$questionId}", 'At least one option must be selected.');
                    return;
                }

                foreach ($answer as $item) {
                    if (!is_string($item)) {
                        $validator->errors()->add("answers.{$questionId}", 'All selected options must be strings.');
                        return;
                    }
                    
                    if (strlen($item) > $maxLength) {
                        $validator->errors()->add("answers.{$questionId}", "Each option cannot exceed {$maxLength} characters.");
                        return;
                    }

                    if ($question->options && !in_array($item, $question->options)) {
                        $validator->errors()->add("answers.{$questionId}", "Selected option '{$item}' is not valid for this question.");
                        return;
                    }
                }
                break;

            case 'date':
                // Date - should be a valid date string
                if (!is_string($answer)) {
                    $validator->errors()->add("answers.{$questionId}", 'Date answer must be a string.');
                    return;
                }

                try {
                    \Carbon\Carbon::parse($answer);
                } catch (\Exception $e) {
                    $validator->errors()->add("answers.{$questionId}", 'Invalid date format.');
                }
                break;
        }
    }
}
