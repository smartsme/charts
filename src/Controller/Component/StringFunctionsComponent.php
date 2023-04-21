<?php
declare(strict_types=1);

namespace App\Controller\Component;

use Cake\Controller\Component;

class StringFunctionsComponent extends Component
{
    /**
     * Formats snake_case to camelCase
     *
     * @param string $s String to format
     * @return string
     */
    public function snakeCaseToCamelCase($s)
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $s))));
    }

    /**
     * Formats camelCase to snake_case
     *
     * @param string $s String to format
     * @return string
     */
    public function camelCaseToSnakeCase($s)
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $s));
    }
}
