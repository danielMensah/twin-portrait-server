<?php

/**
 * Created by IntelliJ IDEA.
 * User: MrDan
 * Date: 24/10/2017
 * Time: 15:18
 */
class UtilManager {

    public function handleStatementException(PDOStatement $sql, $msg) {
        try {
            if (!$sql->execute()) {
                throw new PDOException($msg, 500);
            }
        } catch (PDOException $exception) {
            throw $exception;
        }

        return true;
    }

    public function curlCall($url, $assoc = false) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);            // No header in the result
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return, do not echo result

// Fetch and return content, save it.
        $raw_data = curl_exec($ch);
        curl_close($ch);

// If the API is JSON, use json_decode.
        return json_decode($raw_data, $assoc);
    }

}