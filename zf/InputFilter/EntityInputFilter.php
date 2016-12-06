<?php

namespace Auctio\Zf\InputFilter;

use Zend\InputFilter\InputFilter;

class EntityInputFilter
{

    /**
     * Get InputFilter for a Entity-type field
     *
     * @param $name
     * @param boolean $required
     * @return void|InputFilter
     */
    public function getFilter($name)
    {
        if ($name == null) {
            return;
        } else {
            $filter = [
                'name' => $name,
                'validators' => [
                    [
                        'name' => 'NotEmpty',
                    ],
                ],
            ];

            $inputFilter = new InputFilter();
            $inputFilter->add($filter, $name);
            return $inputFilter;
        }
    }

}
