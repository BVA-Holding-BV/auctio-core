<?php

namespace AuctioCore\Zf\InputFilter;

use Zend\InputFilter\InputFilter;

class IntegerInputFilter
{

    /**
     * Get InputFilter for a Integer-type field
     *
     * @param $name
     * @param bool $allow_negative_values
     * @param bool $required
     * @return void|InputFilter
     */
    public function getFilter($name, $allow_negative_values = false, $required = false)
    {
        if ($name == null) {
            return;
        } else {
            if ($allow_negative_values === true) {
                $filter = [
                    'name' => $name,
                    'required' => $required,
                    'validators' => [
                        [
                            'name' => 'Regex',
                            'options' => [
                                'pattern' => '/^[-]?\d*$/',
                            ],
                        ],
                    ],
                ];
            } else {
                $filter = [
                    'name' => $name,
                    'required' => $required,
                    'validators' => [
                        ['name' => 'Digits'],
                    ],
                ];
            }

            $inputFilter = new InputFilter();
            $inputFilter->add($filter, $name);
            return $inputFilter;
        }
    }

}