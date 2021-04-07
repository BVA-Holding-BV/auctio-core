<?php

namespace AuctioCore\Laminas\InputFilter;

use Laminas\InputFilter\InputFilter;
use Laminas\Validator\InArray;

class EnumInputFilter
{

    /**
     * Get InputFilter for a Enum-type field
     *
     * @param string $name
     * @param bool $required
     * @param array $enumValues
     * @return void|InputFilter
     */
    public static function getFilter(string $name, $required = false, $enumValues = [])
    {
        if ($name == null) {
            return;
        } else {
            $filter = [
                'name' => $name,
                'required' => $required,
                'filters' => [],
                'validators' => [
                    [
                        'name' => 'InArray',
                        'options' => [
                            'haystack' => $enumValues,
                            'strict' => InArray::COMPARE_STRICT
                        ],
                    ],
                ],
            ];

            $inputFilter = new InputFilter();
            $inputFilter->add($filter, $name);
            return $inputFilter;
        }
    }

}