<?php

/**
 * Created by IntelliJ IDEA.
 * User: MrDan
 * Date: 24/10/2017
 * Time: 14:15
 */

require_once __DIR__ . "/../config/DbConnection.php";
require_once __DIR__ . "/../managers/StatementManager.php";
require_once __DIR__ . "/../Model/PortraitModel.php";

class PortraitController {

    const INFO_URL = "https://artsexperiments.withgoogle.com/tags/api/og/search/";

    private $model;
    protected $dbh;
    protected $sqlManager;
    protected $portraitManger;

    /**
     * PortraitController constructor.
     * @param PortraitModel $model
     */
    public function __construct(PortraitModel $model = null) {
        $this->dbh = new DbConnection();
        $this->sqlManager = new StatementManager();
        $this->model = $model;
    }

    public function getRandomPortrait(){

        $sql = $this->dbh->getConnection()->prepare("SELECT p.id, p.image_url FROM portrait p 
          INNER JOIN portrait_landmarks ps 
            ON p.id = ps.portrait_id 
          WHERE ps.features_completed = FALSE ORDER BY RAND() LIMIT 1");

        $this->sqlManager->handleStatementException($sql, "Error while fetching portraits!");

        $sql->bindColumn(1, $id, PDO::PARAM_STR);
        $sql->bindColumn(2, $image_url, PDO::PARAM_STR);
        $sql->fetch(PDO::FETCH_BOUND);

        return json_encode(array(
            'id' => $id,
            'portraitURL' => $image_url,
        ));

    }

    public function addPortrait() {
        $id = $this->model->getId();
        $portrait_url = $this->model->getImageUrl();

        $sql = $this->dbh->getConnection()->prepare("INSERT INTO portrait ( id, image_url ) VALUES ( :id, :image_url )");
        $sql->bindParam('id', $id, PDO::PARAM_STR);
        $sql->bindParam('image_url', $portrait_url, PDO::PARAM_STR);
        $this->sqlManager->handleStatementException($sql, "Error when adding image, might already exist");

        return $this->addPortraitInfo($this->model->getId(), $this->model->getImageUrl());
    }

    public function addPortraitInfo($id, DbConnection $dbh) {
        $url = self::INFO_URL . $id;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);            // No header in the result
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return, do not echo result

// Fetch and return content, save it.
        $raw_data = curl_exec($ch);
        curl_close($ch);

// If the API is JSON, use json_decode.
        $data = json_decode($raw_data, true);

        $title = !empty($data['title']) ? $data['title'] : null;
        $creator = !empty($data['creators'][0]['title']) ? $data['creators'][0]['title'] : null;
        $dateCreated = !empty($data['datesCreated'][0]['text']) ? $data['datesCreated'][0]['text'] : null;
        $physicalDimensions = !empty($data['physicalDimensions'][0]) ? $data['physicalDimensions'][0] : null;
        $externalLinkUrl = !empty($data['externalLinks'][0]['url']) ? $data['externalLinks'][0]['url'] : null;
        $externalLinkText = !empty($data['externalLinks'][0]['text']) ? $data['externalLinks'][0]['text'] : null;

        $sql = $dbh->getConnection()->prepare("INSERT INTO portrait_info ( portrait_id, title, creator, date_created, physical_dimensions, external_link, external_link_text ) VALUES ( 
        :id, :title, :creator, :dateCreated, :physicalDimensions, :externalLinkUrl, :externalLinkText)");
        $sql->bindParam('id', $id, PDO::PARAM_STR);
        $sql->bindParam('title', $title, PDO::PARAM_STR);
        $sql->bindParam('creator', $creator, PDO::PARAM_STR);
        $sql->bindParam('dateCreated', $dateCreated, PDO::PARAM_STR);
        $sql->bindParam('physicalDimensions', $physicalDimensions, PDO::PARAM_STR);
        $sql->bindParam('externalLinkUrl', $externalLinkUrl, PDO::PARAM_STR);
        $sql->bindParam('externalLinkText', $externalLinkText, PDO::PARAM_STR);

        $this->sqlManager->handleStatementException($sql, "Error while inserting portrait info!");
        $this->initialiseLandmarks($dbh, $id);
        return 'updated';
    }

    protected function initialiseLandmarks(DbConnection $dbh, $id) {
        $sql = $dbh->getConnection()->prepare("INSERT INTO portrait_landmarks ( portrait_id ) VALUES (:id)");
        $sql->bindParam('id', $id, PDO::PARAM_STR);

        $this->sqlManager->handleStatementException($sql, "Error while initialising landmarks!");
    }

    public function updatePortrait($arrayOfLandmarks, $portraitId, $gender, $mustache, $beard) {
        $updatedLandmarks = $this->portraitLandmarkCalculation($arrayOfLandmarks, $portraitId, $mustache, $beard, $this->dbh);

        $sql = $this->dbh->getConnection()->prepare("UPDATE portrait_landmarks SET EB_FLAT_SHAPED=:EB_FLAT_SHAPED, EB_ANGLED=:EB_ANGLED, 
        EB_ROUNDED=:EB_ROUNDED, EYE_MONOLID_ALMOND=:EYE_MONOLID_ALMOND, EYE_DEEP_SET=:EYE_DEEP_SET, EYE_DOWNTURNED=:EYE_DOWNTURNED,
        EYE_HOODED=:EYE_HOODED, NOSE_AQUILINE=:NOSE_AQUILINE, NOSE_FLAT=:NOSE_FLAT, NOSE_ROMAN_HOOKED=:NOSE_ROMAN_HOOKED,
        NOSE_SNUB=:NOSE_SNUB, mustache=:mustache, beard=:beard, gender=:gender, features_completed = TRUE WHERE portrait_id=:portrait_id");
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
        $sql->bindParam(':portrait_id', $portraitId, PDO::PARAM_STR);

        $this->sqlManager->handleStatementException($sql, "Error while update landmarks!");

        return json_encode(array( 'response' => 'updated '));
    }

    public function portraitLandmarkCalculation($arrayOfLandmarks, $portraitId, $mustache, $beard, DbConnection $dbh) {

        $sql = $dbh->getConnection()->prepare("SELECT * FROM portrait_landmarks WHERE portrait_id = :portrait_id");
        $sql->bindParam(':portrait_id', $portraitId, PDO::PARAM_STR);
        $this->sqlManager->handleStatementException($sql, "Error while selecting portraits for landmark calculation function");
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
                $nose_flat = $nose_flat + 0.4;
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

    public function handleNotApplicationPortrait() {
        $id = $this->model->getId();

        $sql = $this->dbh->getConnection()->prepare("UPDATE portrait_landmarks SET not_applicable = TRUE, features_completed = TRUE WHERE portrait_id = :portrait_id");
        $sql->bindParam(':portrait_id', $id, PDO::PARAM_STR);

        $this->sqlManager->handleStatementException($sql, "Error while setting portrait as not applicable!");
        return json_encode(array( 'response' => 'updated '));
    }

    public function getPortraitInfo() {
        $unknown = 'Unknown';
        $id = $this->model->getId();

        $sql = $this->dbh->getConnection()->prepare("SELECT * FROM portrait_info WHERE portrait_id = :id");
        $sql->bindParam(':id', $id, PDO::PARAM_STR);
        $this->sqlManager->handleStatementException($sql, "Error while getting portrait information");
        $sql->bindColumn('title', $title, PDO::PARAM_STR);
        $sql->bindColumn('creator', $creator, PDO::PARAM_STR);
        $sql->bindColumn('date_created', $dateCreated, PDO::PARAM_STR);
        $sql->bindColumn('physical_dimensions', $physicalDimensions, PDO::PARAM_STR);
        $sql->bindColumn('external_link', $externalLinkUrl, PDO::PARAM_STR);
        $sql->bindColumn('external_link_text', $externalLinkText, PDO::PARAM_STR);
        $sql->fetch(PDO::FETCH_BOUND);

        return json_encode(array(
            'title' => !empty($title) ? $title : $unknown,
            'creator' => !empty($creator) ? $creator : $unknown,
            'date_created' => !empty($dateCreated) ? $dateCreated : $unknown,
            'physical_dimensions' => !empty($physicalDimensions) ? $physicalDimensions : $unknown,
            'external_link' => !empty($externalLinkUrl) ? $externalLinkUrl : $unknown,
            'external_link_text' => !empty($externalLinkText) ? $externalLinkText : $unknown,
        ));
    }

    public function getStatistics($table) {

        switch ($table) {
            case 'users':
                $sql = $this->dbh->getConnection()->prepare("SELECT COUNT(*) FROM users");
                break;
            case 'portrait':
                $sql = $this->dbh->getConnection()->prepare("SELECT COUNT(*) FROM portrait_landmarks WHERE features_completed = TRUE");
                break;
            default:
                throw new PDOException("Couldn't find $table", 404);
        }

        $this->sqlManager->handleStatementException($sql, "Error while getting statistics!");

        return json_encode(array( $table => $sql->fetchColumn()));
    }

}