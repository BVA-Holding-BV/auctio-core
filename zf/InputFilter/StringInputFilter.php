<?php

namespace Auctio\Zf\InputFilter;

use Zend\InputFilter\InputFilter;

class StringInputFilter
{

    /**
     * Get InputFilter for a String-type field
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