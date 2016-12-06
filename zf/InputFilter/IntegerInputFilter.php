<?php

namespace Auctio\Zf\InputFilter;

use Zend\InputFilter\InputFilter;

class IntegerInputFilter
{

    /**
     * Get InputFilter for a Integer-type field
     *
     * @param $name
     * @param bool $required
     * @return void|InputFilter
     */
    public function getFilter($name, $required = false)
    {
        if ($name == null) {
            return;
        } else {
            $filter = [
                'name' => $name,
                'required' => $required,
                'validators' => [
                    [
                        'name' => 'Digits',
                    ],
                ],
            ];

            $inputFilter = new InputFilter();
            $inputFilter->add($filter, $name);
            return $inputFilter;
        }
    }

}