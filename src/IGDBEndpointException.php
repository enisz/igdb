<?php

    class IGDBEndpointException extends Exception {
        public function __construct($message, $code, $previous = null) {
            parent::__construct($message, $code, $previous);
        }
    }

?>