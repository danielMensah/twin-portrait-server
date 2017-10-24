<?php

function makeCall($url, $assoc = false) {
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