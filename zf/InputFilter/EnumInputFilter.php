<?php

namespace Auctio\Zf\InputFilter;

use Zend\InputFilter\InputFilter;

class EnumInputFilter
{

    /**
     * Get InputFilter for a Enum-type field
     *
     * @param $name
     * @param bool $required
     * @param array $enumValues
     * @return void|InputFilter
     */
    public function getFilter($name, $required = false, $enumValues = [])
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
                            'strict' => \Zend\Validator\InArray::COMPARE_STRICT
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