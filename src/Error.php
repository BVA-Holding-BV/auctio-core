<?php

namespace AuctioCore;

class Error
{

    /**
     * Convert (default) error-code into error-message
     *
     * @param string $type
     * @param string $errorCode
     * @return mixed|void
     */
    public function convertErrorCode($type, $errorCode)
    {
        $message = "";

        if (strtolower($type) == "upload") {
            switch ($errorCode) {
                case UPLOAD_ERR_OK:
                    $message = "";
                    break;
                case UPLOAD_ERR_INI_SIZE:
                    $message = "File size is too large (upload_max_filesize: " . ini_get('upload_max_filesize') . ")";
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $message = "File size is too large (max_file_size: " . ini_get('max_file_size') . ")";
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $message = "File was only partially uploaded";
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $message = "No file was uploaded";
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $message = "Missing temporary folder";
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $message = "Failed to write file to disk";
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $message = "A PHP extension stopped the file upload";
                    break;
            }
        }

        // Return
        return $message;
    }

}