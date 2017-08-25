<?php
use Slim\Http\UploadedFile;

    function testConnection() {
        $dbh = getConnection();
    }

    function getRandomPortrait(){
        $dbh = getConnection();
        $sql = $dbh->prepare("SELECT (image) FROM portrait ORDER BY RAND()");
        $sql->execute();
        $sql->bindColumn(1, $image, PDO::PARAM_LOB);
        $sql->fetch(PDO::FETCH_BOUND);
        header("Content-Type: image");

        echo  '<img src="data:image/jpeg;base64,'.$image.'"/>';
        return $image;

    }

    function uploadImage(UploadedFile $file){
        $dbh = getConnection();
        $image = base64_encode($file->getStream());
        $sql = $dbh->prepare("INSERT INTO portrait ( image ) VALUES ( '$image')");
        $sql->execute();
        echo "Success!";
    }

    function moveUploadedFile($directory, UploadedFile $uploadedFile) {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
        $filename = sprintf('%s.%0.8s', $basename, $extension);

        uploadImage($uploadedFile);

        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

        return $filename;
    }