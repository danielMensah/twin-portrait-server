<?php

/**
 * Created by IntelliJ IDEA.
 * User: MrDan
 * Date: 24/10/2017
 * Time: 15:18
 */
class UtilManager {

    /**
     * @param PDOStatement $sql
     * @param $msg
     * @return bool
     */
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

    /**
     * @param $url
     * @param bool $assoc
     * @return mixed
     */
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

    /**
     * @param $len
     * @return int
     */
    public function getMaxScore($len) {
        $len = $len + 1;
        $total = 0;

        for ($i = 1; $i < $len; $i++) {
            $total = $total + ($len - $i);
        }

        return $total;
    }

    /**
     * @param $length
     * @param $position
     * @return mixed
     */
    public function convertArrayPosition($length, $position) {
        return $length - $position;
    }

    /**
     * @param $data
     * @return array
     */
    public function groupLandmarks($data) {
        arsort($data);

        $items = array();

        foreach ($data as $keyL => $value) {
            array_push($items, $keyL);
        }

        return $items;
    }

    /**
     * @return string
     */
    public function uploadPortraitForm() {
        $form  = '
            <form method="POST" action="/uploadPortrait" enctype="multipart/form-data">
                <input type="number" name="json" value="0" />
                <button name="api" value="1">API 1</button>
                <button name="api" value="2">API 2</button>
            </form>
        ';

        return $form;
    }
}