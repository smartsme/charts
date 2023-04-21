<?php

namespace App\Controller\Component;

use Cake\Controller\Component;

class StringFunctionsComponent extends Component
{
    public function snakeCaseToCamelCase($s)
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $s))));
    }

    public function camelCaseToSnakeCase($s)
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $s));
    }
}