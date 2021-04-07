<?php

namespace AuctioCore\Laminas\InputFilter;

use Laminas\InputFilter\InputFilter;

class StringInputFilter
{

    /**
     * Get InputFilter for a String-type field
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
                'name'      => $name,
                'required'  => $required,
                'filters'   => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim'],
                ],
            ];

            $inputFilter = new InputFilter();
            $inputFilter->add($filter, $name);
            return $inputFilter;
        }
    }

}