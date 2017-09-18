<?php

    function portraitURL($name) {
        return "http://s3.eu-west-2.amazonaws.com/twinportraint/Portraits/$name=s512.jpg";
    }

    function preparePortraitUrl($str) {
        $str = trim(substr($str, strrpos($str, '/') + 1));
        return portraitURL($str);
    }