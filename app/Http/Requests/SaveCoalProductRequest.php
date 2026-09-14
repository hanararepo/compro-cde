<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveCoalProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can($this->route('coalProduct') ? 'coal-products.edit' : 'coal-products.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'name'                    => ['required', 'string', 'max:255'],
            // Top-level columns definition
            'columns'                 => ['required', 'array', 'min:1', 'max:10'],
            'columns.*'               => ['required', 'string', 'max:100'],
            // Rows: each row is an array of cell values matching columns
            'rows'                    => ['required', 'array', 'min:1', 'max:200'],
            'rows.*'                  => ['required', 'array'],
            'rows.*.*'                => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'columns.required'  => 'Define at least one column.',
            'columns.min'       => 'Define at least one column.',
            'columns.max'       => 'A product can have up to 10 columns.',
            'columns.*.required'=> 'Column name cannot be empty.',
            'rows.required'     => 'Add at least one specification row.',
            'rows.min'          => 'Add at least one specification row.',
            'rows.max'          => 'A product can contain up to 200 specification rows.',
        ];
    }
}
