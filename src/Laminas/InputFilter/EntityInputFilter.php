<?php

namespace AuctioCore\Laminas\InputFilter;

use Laminas\InputFilter\InputFilter;

class EntityInputFilter
{

    /**
     * Get InputFilter for a Entity-type field
     *
     * @param $name
     * @param boolean $required
     * @return void|InputFilter
     */
    public static function getFilter($name, $required = false)
    {
        if ($name == null) {
            return;
        } else {
            if ($required === false) {
                $filter = [
                    'name' => $name,
                    'allow_empty' => true,
                ];
            } else {
                $filter = [
                    'name' => $name,
                    'validators' => [
                        ['name' => 'NotEmpty'],
                    ],
                ];
            }

            $inputFilter = new InputFilter();
            $inputFilter->add($filter, $name);
            return $inputFilter;
        }
    }

}
