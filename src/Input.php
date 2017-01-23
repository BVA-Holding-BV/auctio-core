<?php

namespace AuctioCore;

class Input
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

        $output['filter'] = (isset($params->filter)) ? json_decode($params->filter, true) : null;
        $output['orderBy'] = (isset($params->orderBy)) ? json_decode($params->orderBy, true) : null;
        $output['limit'] = (isset($params->limit)) ? $params->limit : null;
        $output['offset'] = (isset($params->page)) ? ($params->page - 1) * $output['limit'] : null;
        $output['debug'] = (isset($params->debug) && ($params->debug == 'true' || $params->debug == 1)) ? true : false;

        return $output;
    }

    /**
     * Decode x-form-encoded data
     *
     * @param string $dataString, example test=1&test2=3&data=1+2
     * @param boolean $urlDecode, url-decode value
     * @return array
     */
    public function formDecode($dataString, $urlDecode = false) {
        if (empty($dataString)) return array();

        $output = array();
        $dataElements = explode("&", $dataString);
        foreach ($dataElements AS $dataElement) {
            $data = explode("=", $dataElement);
            $output[$data[0]] = ($urlDecode === true) ? urldecode($data[1]) : $data[1];
        }

        return $output;
    }

}