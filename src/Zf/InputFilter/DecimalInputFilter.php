<?php

namespace AuctioCore\Zf\InputFilter;

use Zend\InputFilter\InputFilter;

class DecimalInputFilter
{

    /**
     * Get InputFilter for a Decimal-type field
     *
     * @param $name
     * @param int $precision, total length of value
     * @param int $scale, length of decimals
     * @param bool $required
     * @return void|InputFilter
     */
    public function getFilter($name, $precision, $scale, $required = false)
    {
        if ($name == null) {
            return;
        } else {
            $filter = [
                'name'      => $name,
                'required'  => $required,
                'validators'   => [
                    [
                        'name' => 'Regex',
                        'pattern' => '\b\d{1,' . ($precision - $scale) . '}\.\d{1,' . $scale . '}\b',
                    ],
                ],
            ];

            $inputFilter = new InputFilter();
            $inputFilter->add($filter, $name);
            return $inputFilter;
        }
    }

}