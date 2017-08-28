<?php
use Slim\Http\UploadedFile;
require '../util/urlGeneratorUtil.php';

    function testConnection() {
        $dbh = getConnection();
    }

    function getRandomPortrait(){

        $dbh = getConnection();
        $sql = $dbh->prepare("SELECT (image_url) FROM portrait ORDER BY RAND()");
        $sql->execute();
        $sql->bindColumn(1, $image_url, PDO::PARAM_LOB);
        $sql->fetch(PDO::FETCH_BOUND);
        header("Content-Type: image");

        return $image_url;

    }

    function uploadImage(UploadedFile $uploadedFile){

        $dbh = getConnection();
        $image_url = portraitURL($uploadedFile->getClientFilename());

        $sql = $dbh->prepare("INSERT INTO portrait ( image_url ) VALUES ( '$image_url')");
        $sql->execute();

        return $image_url;
    }

    function updatePortrait($arrayOfLandmarks, $portraitUrl) {
        $dbh = getConnection();

        $face = $arrayOfLandmarks['face']['landmarkKey'];
        $eye = $arrayOfLandmarks['eye']['landmarkKey'];
        $eyebrows = $arrayOfLandmarks['eyebrows']['landmarkKey'];
        $nose = $arrayOfLandmarks['nose']['landmarkKey'];
        $lips = $arrayOfLandmarks['lips']['landmarkKey'];

        $updatedLandmarks = getPortraitForUpdate($arrayOfLandmarks, $portraitUrl);

        $sql = $dbh->prepare("UPDATE portrait SET $face = :face_value, $eye=:eye_value, $eyebrows=:eyebrows_value, $nose=:nose_value, $lips=:lips_value WHERE image_url=:portrait_url");
        $sql->bindParam(':face_value', $updatedLandmarks['face'], PDO::PARAM_INT);
        $sql->bindParam(':eye_value', $updatedLandmarks['eye'], PDO::PARAM_INT);
        $sql->bindParam(':eyebrows_value', $updatedLandmarks['eyebrows'], PDO::PARAM_INT);
        $sql->bindParam(':nose_value', $updatedLandmarks['nose'], PDO::PARAM_INT);
        $sql->bindParam(':lips_value', $updatedLandmarks['lips'], PDO::PARAM_INT);
        $sql->bindParam(':portrait_url', $portraitUrl, PDO::PARAM_STR);

        $sql->execute();

        $response = [];

        if ($sql->execute()) {
            $response = array(
                'response' => 'updated'
            );
        } else {
            $response = array(
                'response' => 'error'
            );
        }

        return json_encode($response);
    }

    function getPortraitForUpdate($arrayOfLandmarks, $portraitUrl) {
        $dbh = getConnection();

        $sql = $dbh->prepare("SELECT * FROM portrait WHERE image_url = :image_url");
        $sql->bindParam(':image_url', $portraitUrl, PDO::PARAM_STR);
        $sql->execute();
        $sql->bindColumn($arrayOfLandmarks['face']['landmarkKey'], $face, PDO::PARAM_INT);
        $sql->bindColumn($arrayOfLandmarks['eye']['landmarkKey'], $eye, PDO::PARAM_INT);
        $sql->bindColumn($arrayOfLandmarks['eyebrows']['landmarkKey'], $eyebrows, PDO::PARAM_INT);
        $sql->bindColumn($arrayOfLandmarks['nose']['landmarkKey'], $nose, PDO::PARAM_INT);
        $sql->bindColumn($arrayOfLandmarks['lips']['landmarkKey'], $lips, PDO::PARAM_INT);
        $sql->fetch(PDO::FETCH_BOUND);

        $fetchedLandmarksValues = array(
            'face' => $face+1,
            'eye' => $eye+1,
            'eyebrows' => $eyebrows+1,
            'nose' => $nose+1,
            'lips' => $lips+1
        );

        return $fetchedLandmarksValues;

    }

//    function moveUploadedFile($directory, UploadedFile $uploadedFile) {
//        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
//        $basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
//        $filename = sprintf('%s.%0.8s', $basename, $extension);
//
//        uploadImage($uploadedFile);
//
//        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);
//
//        return $filename;
//    }