<?php

namespace AuctioCore;

class Input
{

    /**
     * Convert JSON-string (or object/array) into array/object
     *
     * @param $dataString
     * @param string $output
     * @return mixed|void
     */
    public function convertJson($dataString, $output = "object")
    {
        if (!is_object($dataString) && !is_array($dataString)) {
            $dataString = str_replace("'", '"', $dataString);
            if ($this->isJson($dataString)) {
                $data = $dataString;
            } else {
                return;
            }
        } else {
            $data = json_encode($dataString);
        }

        if ($output == "object") {
            return json_decode($data);
        } else {
            return json_decode($data, true);
        }
    }

    /**
     * Decode x-form-encoded data
     *
     * @param string $dataString, example test=1&test2=3&data=1+2
     * @param boolean $urlDecode, url-decode value
     * @return array
     */
    public function formDecode($dataString, $urlDecode = false)
    {
        if (empty($dataString)) return array();
        if (self::isJson($dataString)) return json_decode($dataString, true);

        $output = array();
        $dataElements = explode("&", $dataString);
        foreach ($dataElements AS $dataElement) {
            $data = explode("=", $dataElement);
            $output[$data[0]] = ($urlDecode === true) ? urldecode($data[1]) : $data[1];
        }

        return $output;
    }

    /**
     * Check if string is JSON-string
     *
     * @param string $dataString
     * @return boolean
     */
    public function isJson($dataString)
    {
        return is_string($dataString) && is_array(json_decode($dataString, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
    }


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
        $output['customRequestId'] = (isset($params->customRequestId) && !empty($params->customRequestId)) ? $params->customRequestId : null;

        return $output;
    }

}