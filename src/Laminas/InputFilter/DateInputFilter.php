<?php

namespace AuctioCore\Laminas\InputFilter;

use Laminas\InputFilter\InputFilter;

class DateInputFilter
{

    /**
     * Get InputFilter for a Date-type field
     *
     * @param string $name
     * @param bool $required
     * @return void|InputFilter
     */
    public static function getFilter(string $name, $required = false)
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