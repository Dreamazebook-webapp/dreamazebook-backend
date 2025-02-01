<?php

namespace App\Http\Requests\Admin\PicbookPage;

use Illuminate\Validation\Rule;
use App\Models\PicbookPage;

class UpdatePicbookPageRequest extends BasePicbookPageRequest
{
    public function rules(): array
    {
        return array_merge([
            'status' => ['integer', Rule::in([
                PicbookPage::STATUS_DRAFT,
                PicbookPage::STATUS_PUBLISHED,
                PicbookPage::STATUS_HIDDEN
            ])],
        ], $this->baseRules());
    }
} 