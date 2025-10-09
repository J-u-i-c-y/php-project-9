<?php

namespace Hexlet\Code;

use Valitron\Validator;

class UrlValidator
{
    public static function validate(array $urlData): array
    {
        $v = new Validator($urlData);
        $v->rule('required', 'name')->message('URL не должен быть пустым');
        $v->rule('url', 'name')->message('Некорректный URL');
        $v->rule('lengthMax', 'name', 255)->message('URL не должен превышать 255 символов');

        if (!$v->validate()) {
            $errors = $v->errors();
            return array_merge(...$errors);
        }

        return [];
    }
}
