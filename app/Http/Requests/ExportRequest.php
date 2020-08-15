<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\AlbumExists;
use Illuminate\Foundation\Http\FormRequest;

class ExportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->replace(['albumIDs' => \explode(',', $this->albumIDs)]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'albumIDs' => 'required|array',
            'albumIDs.*' => new AlbumExists(),
        ];
    }
}
