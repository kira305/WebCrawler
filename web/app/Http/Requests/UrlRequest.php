<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UrlRequest extends FormRequest
{
    public function rules()
    {
        return [
            'url' => 'required|url',
        ];
    }

    public function messages()
    {
        return [
            'url.required' => 'このフィールドは必須です。',
            'url.url' => 'バリデーションエラーが発生しました。正しいURLを入力してください。',
        ];
    }
}
