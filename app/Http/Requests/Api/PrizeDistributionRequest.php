<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PrizeDistributionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'event_id' => 'required|integer|min:1',
            'event_type' => ['required', 'string', Rule::in(['nomination', 'ugc'])],
            'event_subtype' => 'nullable|string|max:255', // Required for UGC, format: {position}_W{amount}
            'event_meta' => 'required|array',
            'event_meta.title' => 'required|string|max:255',
            'event_meta.title_ar' => 'nullable|string|max:255',
            'event_meta.date' => 'required|date',
            'winners' => 'required|array|min:1',
            'winners.*.position' => 'required|integer|min:1|max:5',
            'winners.*.category' => ['required', 'string', Rule::in(['attendance_fan', 'online_fan', 'ugc_creator'])],
            'winners.*.user_id' => 'required|integer|min:1', // Midan user ID
            'winners.*.email' => 'nullable|string|email|max:255',
            'winners.*.phone' => 'nullable|string|max:20',
            'winners.*.points' => 'nullable|integer|min:0', // Points scored (for reference, not used for prize calculation)
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'event_id.required' => 'Event ID is required',
            'event_id.integer' => 'Event ID must be an integer',
            'event_type.required' => 'Event type is required',
            'event_type.in' => 'Event type must be either "nomination" or "ugc"',
            'event_meta.required' => 'Event metadata is required',
            'event_meta.array' => 'Event metadata must be an array',
            'event_meta.title.required' => 'Event title is required',
            'event_meta.date.required' => 'Event date is required',
            'event_meta.date.date' => 'Event date must be a valid date',
            'winners.required' => 'Winners array is required',
            'winners.array' => 'Winners must be an array',
            'winners.min' => 'At least one winner must be provided',
            'winners.*.position.required' => 'Winner position is required',
            'winners.*.position.integer' => 'Winner position must be an integer',
            'winners.*.position.min' => 'Winner position must be at least 1',
            'winners.*.position.max' => 'Winner position must be at most 5',
            'winners.*.category.required' => 'Winner category is required',
            'winners.*.category.in' => 'Winner category must be one of: attendance_fan, online_fan, ugc_creator',
            'winners.*.user_id.required' => 'Winner user ID is required',
            'winners.*.user_id.integer' => 'Winner user ID must be an integer',
            'winners.*.email.email' => 'Winner email must be a valid email address',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validate event_subtype is required for UGC events
            if ($this->input('event_type') === 'ugc' && empty($this->input('event_subtype'))) {
                $validator->errors()->add('event_subtype', 'Event subtype is required for UGC events');
            }

            // Validate position limits based on event type
            $eventType = $this->input('event_type');
            $winners = $this->input('winners', []);
            
            foreach ($winners as $index => $winner) {
                $position = $winner['position'] ?? 0;
                $category = $winner['category'] ?? '';
                
                if ($eventType === 'ugc' && $position > 3) {
                    $validator->errors()->add("winners.{$index}.position", 'UGC events can only have positions 1-3');
                }
                
                if ($eventType === 'nomination' && $position > 5) {
                    $validator->errors()->add("winners.{$index}.position", 'Nomination events can only have positions 1-5');
                }
                
                // Validate category matches event type
                if ($eventType === 'ugc' && $category !== 'ugc_creator') {
                    $validator->errors()->add("winners.{$index}.category", 'UGC events can only have ugc_creator category');
                }
                
                if ($eventType === 'nomination' && !in_array($category, ['attendance_fan', 'online_fan'])) {
                    $validator->errors()->add("winners.{$index}.category", 'Nomination events can only have attendance_fan or online_fan category');
                }
            }
        });
    }
}

