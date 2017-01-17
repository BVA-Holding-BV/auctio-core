<?php

namespace AuctioCore;

class RequestParams
{

    /**
     * Set parameters by request-object
     *
     * @param $params
     * @return array
     */
    public function setParams($params)
    {
        $output = array();

        $output['filters'] = (isset($params->filters)) ? json_decode($params->filters, true) : null;
        $output['orderBy'] = (isset($params->orderBy)) ? json_decode($params->orderBy, true) : null;
        $output['limit'] = (isset($params->limit)) ? $params->limit : null;
        $output['offset'] = (isset($params->page)) ? ($params->page - 1) * $output['limit'] : null;
        $output['debug'] = (isset($params->debug) && ($params->debug == 'true' || $params->debug == 1)) ? true : false;

        return $output;
    }

}