<?php

namespace AuctioCore\Zf\InputFilter;

use Zend\InputFilter\InputFilter;

class DateInputFilter
{

    /**
     * Get InputFilter for a Date-type field
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
                    ['name' => 'Date'],
                ],
            ];

            $inputFilter = new InputFilter();
            $inputFilter->add($filter, $name);
            return $inputFilter;
        }
    }

}