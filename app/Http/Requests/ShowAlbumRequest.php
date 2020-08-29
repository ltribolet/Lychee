<?php declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShowAlbumRequest extends FormRequest
{
    public function authorize(): bool
    {
        $album = $this->route('album');

        return $album->public || $album->owner_id === $this->user()->id || $this->user()->isAdmin();
    }

    /**
     * @return array<string>
     */
    public function rules(): array
    {
        return [];
    }
}
