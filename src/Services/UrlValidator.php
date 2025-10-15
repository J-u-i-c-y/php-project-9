<?php

namespace Hexlet\Code\Services;

use Valitron\Validator;

class UrlValidator
{
    public static function validate(array $urlData): array
    {
        $v = new Validator($urlData);
        $v->rule('required', 'name')->message('URL не должен быть пустым');
        $v->rule('url', 'name')->message('Некорректный URL');
        $v->rule('lengthMax', 'name', 255)->message('URL не должен превышать 255 символов');

        $errors = [];

        if (!$v->validate()) {
            $rawErrors = $v->errors();

            if (is_array($rawErrors) && !empty($rawErrors)) {
                $errors = array_merge(...array_values($rawErrors));
            }
        }

        return $errors;
    }
}
