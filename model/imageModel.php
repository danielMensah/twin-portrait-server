<?php
use Slim\Http\UploadedFile;
require '../util/urlGeneratorUtil.php';

    function testConnection() {
        $dbh = getConnection();
    }

    function getRandomPortrait(){

        $dbh = getConnection();
        $sql = $dbh->prepare("SELECT id, image_url FROM portrait ORDER BY RAND()");
        $sql->execute();
        $sql->bindColumn(1, $id, PDO::PARAM_STR);
        $sql->bindColumn(2, $image_url, PDO::PARAM_STR);
        $sql->fetch(PDO::FETCH_BOUND);

        return json_encode(array(
            'id' => $id,
            'portraitURL' => $image_url,
        ));

    }

    function addImage($id, $image_url) {
        $dbh = getConnection();

        $sql = $dbh->prepare("INSERT INTO portrait ( id, image_url ) VALUES ( :id, :image_url )");
        $sql->bindParam('id', $id, PDO::PARAM_STR);
        $sql->bindParam('image_url', $image_url, PDO::PARAM_STR);

        return $sql->execute() ? prepareImageInfo($id) : 'error2';
    }

    function prepareImageInfo($id) {
        $url = "https://artsexperiments.withgoogle.com/tags/api/og/search/$id";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);            // No header in the result
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return, do not echo result

// Fetch and return content, save it.
        $raw_data = curl_exec($ch);
        curl_close($ch);

// If the API is JSON, use json_decode.
        $data = json_decode($raw_data, true);

        return addImageInfo(
            $id,
            !empty($data['title']) ? $data['title'] : null,
            !empty($data['creators'][0]['title']) ? $data['creators'][0]['title'] : null,
            !empty($data['datesCreated'][0]['text']) ? $data['datesCreated'][0]['text'] : null,
            !empty($data['physicalDimensions'][0]) ? $data['physicalDimensions'][0] : null,
            !empty($data['externalLinks'][0]['url']) ? $data['externalLinks'][0]['url'] : null,
            !empty($data['externalLinks'][0]['text']) ? $data['externalLinks'][0]['text'] : null
        );
    }

    function addImageInfo($id, $title, $creator, $dateCreated, $physicalDimensions, $externalLinkUrl, $externalLinkText) {
        $dbh = getConnection();

        $sql = $dbh->prepare("INSERT INTO portrait_info ( portrait_id, title, creator, date_created, physical_dimensions, external_link, external_link_text ) VALUES ( 
        :id, :title, :creator, :dateCreated, :physicalDimensions, :externalLinkUrl, :externalLinkText)");
        $sql->bindParam('id', $id, PDO::PARAM_STR);
        $sql->bindParam('title', $title, PDO::PARAM_STR);
        $sql->bindParam('creator', $creator, PDO::PARAM_STR);
        $sql->bindParam('dateCreated', $dateCreated, PDO::PARAM_STR);
        $sql->bindParam('physicalDimensions', $physicalDimensions, PDO::PARAM_STR);
        $sql->bindParam('externalLinkUrl', $externalLinkUrl, PDO::PARAM_STR);
        $sql->bindParam('externalLinkText', $externalLinkText, PDO::PARAM_STR);

        return $sql->execute() ? 'update' : "error for id: $id";
    }

    function updatePortrait($arrayOfLandmarks, $portraitUrl, $gender, $mustache, $beard) {
        $dbh = getConnection();

        $updatedLandmarks = portraitLandmarkCalculation($arrayOfLandmarks, $portraitUrl, $mustache, $beard);

        $sql = $dbh->prepare("UPDATE portrait SET EB_FLAT_SHAPED=:EB_FLAT_SHAPED, EB_ANGLED=:EB_ANGLED, 
        EB_ROUNDED=:EB_ROUNDED, EYE_MONOLID_ALMOND=:EYE_MONOLID_ALMOND, EYE_DEEP_SET=:EYE_DEEP_SET, EYE_DOWNTURNED=:EYE_DOWNTURNED,
        EYE_HOODED=:EYE_HOODED, NOSE_AQUILINE=:NOSE_AQUILINE, NOSE_FLAT=:NOSE_FLAT, NOSE_ROMAN_HOOKED=:NOSE_ROMAN_HOOKED,
        NOSE_SNUB=:NOSE_SNUB, mustache=:mustache, beard=:beard, gender=:gender WHERE image_url=:portrait_url");
        $sql->bindParam('EB_FLAT_SHAPED', $updatedLandmarks['EB_FLAT_SHAPED'], PDO::PARAM_STR);
        $sql->bindParam('EB_ANGLED', $updatedLandmarks['EB_ANGLED'], PDO::PARAM_STR);
        $sql->bindParam('EB_ROUNDED', $updatedLandmarks['EB_ROUNDED'], PDO::PARAM_STR);
        $sql->bindParam('EYE_MONOLID_ALMOND', $updatedLandmarks['EYE_MONOLID_ALMOND'], PDO::PARAM_STR);
        $sql->bindParam('EYE_DEEP_SET', $updatedLandmarks['EYE_DEEP_SET'], PDO::PARAM_STR);
        $sql->bindParam('EYE_DOWNTURNED', $updatedLandmarks['EYE_DOWNTURNED'], PDO::PARAM_STR);
        $sql->bindParam('EYE_HOODED', $updatedLandmarks['EYE_HOODED'], PDO::PARAM_STR);
        $sql->bindParam('NOSE_AQUILINE', $updatedLandmarks['NOSE_AQUILINE'], PDO::PARAM_STR);
        $sql->bindParam('NOSE_FLAT', $updatedLandmarks['NOSE_FLAT'], PDO::PARAM_STR);
        $sql->bindParam('NOSE_ROMAN_HOOKED', $updatedLandmarks['NOSE_ROMAN_HOOKED'], PDO::PARAM_STR);
        $sql->bindParam('NOSE_SNUB', $updatedLandmarks['NOSE_SNUB'], PDO::PARAM_STR);
        $sql->bindParam(':mustache', $updatedLandmarks['mustache'], PDO::PARAM_STR);
        $sql->bindParam(':beard', $updatedLandmarks['beard'], PDO::PARAM_STR);
        $sql->bindParam(':gender', $gender, PDO::PARAM_STR);
        $sql->bindParam(':portrait_url', $portraitUrl, PDO::PARAM_STR);

        return $sql->execute() ? json_encode(array(
            'response' => 'updated'
        )) : json_encode(array(
            'response' => 'error'
        ));

    }

    function portraitLandmarkCalculation($arrayOfLandmarks, $portraitUrl, $mustache, $beard) {
        $dbh = getConnection();

        $sql = $dbh->prepare("SELECT * FROM portrait WHERE image_url = :image_url");
        $sql->bindParam(':image_url', $portraitUrl, PDO::PARAM_STR);
        $sql->execute();
        $sql->bindColumn('EB_FLAT_SHAPED', $eb_flat_shaped, PDO::PARAM_STR);
        $sql->bindColumn('EB_ANGLED', $eb_angled, PDO::PARAM_STR);
        $sql->bindColumn('EB_ROUNDED', $eb_rounded, PDO::PARAM_STR);
        $sql->bindColumn('EYE_MONOLID_ALMOND', $eye_monolid_almond, PDO::PARAM_STR);
        $sql->bindColumn('EYE_DEEP_SET', $eye_deep_set, PDO::PARAM_STR);
        $sql->bindColumn('EYE_DOWNTURNED', $eye_downturned, PDO::PARAM_STR);
        $sql->bindColumn('EYE_HOODED', $eye_hooded, PDO::PARAM_STR);
        $sql->bindColumn('NOSE_AQUILINE', $nose_aquiline, PDO::PARAM_STR);
        $sql->bindColumn('NOSE_FLAT', $nose_flat, PDO::PARAM_STR);
        $sql->bindColumn('NOSE_ROMAN_HOOKED', $nose_roman_hooked, PDO::PARAM_STR);
        $sql->bindColumn('NOSE_SNUB', $nose_snub, PDO::PARAM_STR);
        $sql->bindColumn('mustache', $fetchedMustache, PDO::PARAM_STR);
        $sql->bindColumn('beard', $fetchedBeard, PDO::PARAM_STR);
        $sql->fetch(PDO::FETCH_BOUND);

         switch ($arrayOfLandmarks['eye']['landmarkKey']) {
             case 'EYE_DEEP_SET':
                 $eye_deep_set = $eye_deep_set + 1;
                 $eye_downturned = $eye_downturned + 0.5;
                 $eye_monolid_almond = $eye_monolid_almond + 0.3;
                 break;
             case 'EYE_MONOLID_ALMOND':
                 $eye_monolid_almond = $eye_monolid_almond + 1;
                 $eye_hooded = $eye_hooded + 0.5;
                 $eye_deep_set = $eye_deep_set + 0.3;
                 break;
             case 'EYE_DOWNTURNED':
                 $eye_downturned = $eye_downturned + 1;
                 $eye_deep_set = $eye_deep_set + 0.5;
                 break;
             case 'EYE_HOODED':
                 $eye_hooded = $eye_hooded + 1;
                 $eye_monolid_almond = $eye_monolid_almond + 0.5;
                 break;
         };

        switch ($arrayOfLandmarks['eyebrows']['landmarkKey']) {
            case 'EB_FLAT_SHAPED':
                $eb_flat_shaped = $eb_flat_shaped + 1;
                break;
            case 'EB_ANGLED':
                $eb_angled = $eb_angled + 1;
                $eb_rounded = $eb_rounded + 0.5;
                break;
            case 'EB_ROUNDED':
                $eb_rounded = $eb_rounded + 1;
                $eb_angled = $eb_angled + 0.5;
                break;
        };

        switch ($arrayOfLandmarks['nose']['landmarkKey']) {
            case 'NOSE_AQUILINE':
                $nose_aquiline = $nose_aquiline + 1;
                $nose_roman_hooked = $nose_roman_hooked + 0.8;
                break;
            case 'NOSE_FLAT':
                $nose_flat = $nose_flat + 1;
                $nose_snub = $nose_snub + 0.3;
                break;
            case 'NOSE_ROMAN_HOOKED':
                $nose_roman_hooked = $nose_roman_hooked + 1;
                $nose_aquiline = $nose_aquiline + 0.8;
                break;
            case 'NOSE_SNUB':
                $nose_snub = $nose_snub + 1;
                $nose_flat = $nose_flat + 0.3;
                break;
        };

        $fetchedLandmarksValues = array(
            'EB_FLAT_SHAPED' => $eb_flat_shaped,
            'EB_ANGLED' => $eb_angled,
            'EB_ROUNDED' => $eb_rounded,
            'EYE_MONOLID_ALMOND' => $eye_monolid_almond,
            'EYE_DEEP_SET' => $eye_deep_set,
            'EYE_DOWNTURNED' => $eye_downturned,
            'EYE_HOODED' => $eye_hooded,
            'NOSE_AQUILINE' => $nose_aquiline,
            'NOSE_FLAT' => $nose_flat,
            'NOSE_ROMAN_HOOKED' => $nose_roman_hooked,
            'NOSE_SNUB' => $nose_snub,
            'mustache' => ($mustache == 'true') ? $fetchedMustache + 0.5 : $fetchedMustache,
            'beard' => ($beard == 'true') ? $fetchedBeard + 0.5 : $fetchedBeard
        );

        return $fetchedLandmarksValues;

    }

    function handleNotApplicationPortrait($portraitUrl) {
        $dbh = getConnection();

        $sql = $dbh->prepare("UPDATE portrait SET not_applicable = true WHERE image_url = :image_url");
        $sql->bindParam(':image_url', $portraitUrl, PDO::PARAM_STR);

        return $sql->execute() ? json_encode(array(
            'response' => 'updated'
        )) : json_encode(array(
            'response' => 'error'
        ));
    }

    function showNotApplicationPortraits() {

    }

    function getPortraitInfo($id) {
        $dbh = getConnection();

        $sql = $dbh->prepare("SELECT * FROM portrait_info WHERE portrait_id = :id");
        $sql->bindParam(':id', $id, PDO::PARAM_STR);
        $sql->execute();
        $sql->bindColumn('title', $title, PDO::PARAM_STR);
        $sql->bindColumn('creator', $creator, PDO::PARAM_STR);
        $sql->bindColumn('date_created', $dateCreated, PDO::PARAM_STR);
        $sql->bindColumn('physical_dimensions', $physicalDimensions, PDO::PARAM_STR);
        $sql->bindColumn('external_link', $externalLinkUrl, PDO::PARAM_STR);
        $sql->bindColumn('external_link_text', $externalLinkText, PDO::PARAM_STR);
        $sql->fetch(PDO::FETCH_BOUND);

        return json_encode(array(
           'title' => $title,
           'creator' => $creator,
           'date_created' => $dateCreated,
           'physical_dimensions' => $physicalDimensions,
           'external_link' => $externalLinkUrl,
           'external_link_text' => $externalLinkText,
        ));
    }