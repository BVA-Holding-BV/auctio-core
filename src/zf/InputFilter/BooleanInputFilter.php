<?php

namespace AuctioCore\Zf\InputFilter;

use Zend\InputFilter\InputFilter;

class BooleanInputFilter
{

    /**
     * Get InputFilter for a Boolean-type field
     *
     * @param $name
     * @return void|InputFilter
     */
    public function getFilter($name)
    {
        // REQUIRED OPTION IS NOT WORKING, BECAUSE "false" IS NOT A VALID NON-EMPTY VALUE!
        // SO THIS CONSTRAINT HAS TO BE CHECKED AS "NOT NULL" IN TABLE
        if ($name == null) {
            return;
        } else {
            $filter = [
                'name' => $name,
                'allow_empty' => true,
                'required' => false,
                'filters' => [],
                'validators' => [
                    [
                        'name' => 'InArray',
                        'options' => [
                            'haystack' => [true, false],
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
