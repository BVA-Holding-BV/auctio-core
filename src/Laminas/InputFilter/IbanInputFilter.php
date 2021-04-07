<?php

namespace AuctioCore\Laminas\InputFilter;

use Laminas\InputFilter\InputFilter;

class IbanInputFilter
{

    /**
     * Get InputFilter for a IBAN field
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
                    ['name' => 'Iban'],
                ],
            ];

            $inputFilter = new InputFilter();
            $inputFilter->add($filter, $name);
            return $inputFilter;
        }
    }

}