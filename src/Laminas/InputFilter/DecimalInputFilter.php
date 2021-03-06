<?php

namespace AuctioCore\Laminas\InputFilter;

use Laminas\InputFilter\InputFilter;

class DecimalInputFilter
{

    /**
     * Get InputFilter for a Decimal-type field
     *
     * @param string $name
     * @param int $precision total length of value
     * @param int $scale length of decimals
     * @param bool $required
     * @return void|InputFilter
     */
    public static function getFilter(string $name, int $precision, int $scale, $required = false)
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
                        'options' => [
                            'pattern' => '/^\d{1,' . ($precision - $scale) . '}\.\d{1,' . $scale . '}$/',
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