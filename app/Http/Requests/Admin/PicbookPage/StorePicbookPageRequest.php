<?php

namespace App\Http\Requests\Admin\PicbookPage;

use Illuminate\Validation\Rule;

class StorePicbookPageRequest extends BasePicbookPageRequest
{
    public function rules(): array
    {
        $baseRules = $this->baseRules();
        
        return array_merge([
            'page_number' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('picbook_pages')
                    ->where('picbook_id', $this->getPicbook()->id)
                    ->where('gender', $this->input('gender'))
                    ->where('skincolor', $this->input('skincolor'))
            ],
            'gender' => ['required', 'integer', 'in:1,2'],
            'skincolor' => ['required', 'integer', 'min:1'],
            'image' => array_merge(['required'], $baseRules['image']),
        ], $baseRules);
    }

    public function messages(): array
    {
        return array_merge([
            'page_number.unique' => '该页码在当前性别和肤色组合下已存在',
        ], parent::messages());
    }
} 