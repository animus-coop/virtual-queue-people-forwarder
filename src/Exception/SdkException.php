<?php

namespace VirtualQueue\PeopleForwarder\Exception;

/**
 * Excepción base para todas las excepciones del SDK.
 */
class SdkException extends \Exception {
    public function __construct($message = "", $code = 0) {
        parent::__construct($message, $code);
    }
}
