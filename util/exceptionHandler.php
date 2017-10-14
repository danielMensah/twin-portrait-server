<?php

    function exceptionHandler( PDOException $exception) {
        return json_encode(array(
            'status' => $exception->getCode(),
            'exception' => $exception->getMessage(),
            'stackTrace' => $exception->getTraceAsString()
        ));
    }