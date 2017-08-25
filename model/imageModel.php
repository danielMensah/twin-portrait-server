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