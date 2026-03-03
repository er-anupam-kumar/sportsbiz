<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlaceBidRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'team_id' => ['required', 'integer', 'exists:teams,id'],
            'player_id' => ['required', 'integer', 'exists:players,id'],
            'is_auto_bid' => ['sometimes', 'boolean'],
        ];
    }
}
