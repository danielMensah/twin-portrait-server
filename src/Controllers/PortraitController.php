<?php

/**
 * Created by IntelliJ IDEA.
 * User: MrDan
 * Date: 24/10/2017
 * Time: 14:15
 */

require_once __DIR__ . "/../../config/DbConnection.php";
require_once __DIR__ . "/../Managers/UtilManager.php";
require_once __DIR__ . "/../Model/PortraitModel.php";
require_once __DIR__ . "/SimilarityController.php";

class PortraitController {

    const INFO_URL = "https://artsexperiments.withgoogle.com/tags/api/og/search/";

    /** @var PortraitModel */
    private $model;
    /** @var DbConnection */
    protected $dbh;
    /** @var UtilManager */
    protected $utilManager;

    /**
     * PortraitController constructor.
     */
    public function __construct() {
        $this->dbh = new DbConnection();
        $this->utilManager = new UtilManager();
    }

    /**
     * @return string
     */
    public function getRandomPortrait(){

        $sql = $this->dbh->getConnection()->prepare("SELECT p.id, p.image_url FROM portrait p 
          INNER JOIN portrait_landmarks ps 
            ON p.id = ps.portrait_id 
          WHERE ps.features_completed = FALSE ORDER BY RAND() LIMIT 1");

        $this->utilManager->handleStatementException($sql, "Error while fetching portraits!");

        $sql->bindColumn(1, $id, PDO::PARAM_STR);
        $sql->bindColumn(2, $image_url, PDO::PARAM_STR);
        $sql->fetch(PDO::FETCH_BOUND);

        return json_encode(array(
            'id' => $id,
            'portraitURL' => $image_url,
        ));

    }

    /**
     * @param $model
     * @return string
     */
    public function addPortrait($model) {
        $this->model = $model;

        $id = $this->model->getId();
        $portrait_url = $this->model->getImageUrl();

        $sql = $this->dbh->getConnection()->prepare("INSERT INTO portrait ( id, image_url ) VALUES ( :id, :image_url )");
        $sql->bindParam('id', $id, PDO::PARAM_STR);
        $sql->bindParam('image_url', $portrait_url, PDO::PARAM_STR);
        $this->utilManager->handleStatementException($sql, "Couldn't added $id. Might be a supplicate");

        $this->addPortraitInfo($id);

        return "Portrait: $id added to the database!";
    }

    /**
     * @param $id
     * @return string
     */
    public function addPortraitInfo($id) {
        $url = self::INFO_URL . $id;

        $data = $this->utilManager->curlCall($url, true);

        $title = !empty($data['title']) ? $data['title'] : null;
        $creator = !empty($data['creators'][0]['title']) ? $data['creators'][0]['title'] : null;
        $dateCreated = !empty($data['datesCreated'][0]['text']) ? $data['datesCreated'][0]['text'] : null;
        $physicalDimensions = !empty($data['physicalDimensions'][0]) ? $data['physicalDimensions'][0] : null;
        $externalLinkUrl = !empty($data['externalLinks'][0]['url']) ? $data['externalLinks'][0]['url'] : null;
        $externalLinkText = !empty($data['externalLinks'][0]['text']) ? $data['externalLinks'][0]['text'] : null;

        $sql = $this->dbh->getConnection()->prepare("INSERT INTO portrait_info ( portrait_id, title, creator, date_created, physical_dimensions, external_link, external_link_text ) VALUES ( 
        :id, :title, :creator, :dateCreated, :physicalDimensions, :externalLinkUrl, :externalLinkText)");
        $sql->bindParam('id', $id, PDO::PARAM_STR);
        $sql->bindParam('title', $title, PDO::PARAM_STR);
        $sql->bindParam('creator', $creator, PDO::PARAM_STR);
        $sql->bindParam('dateCreated', $dateCreated, PDO::PARAM_STR);
        $sql->bindParam('physicalDimensions', $physicalDimensions, PDO::PARAM_STR);
        $sql->bindParam('externalLinkUrl', $externalLinkUrl, PDO::PARAM_STR);
        $sql->bindParam('externalLinkText', $externalLinkText, PDO::PARAM_STR);

        $this->utilManager->handleStatementException($sql, "Error while inserting portrait info!");
        $this->initialiseLandmarks($id);

        return json_encode(array(
            'response' => 'Added portrait'
        ));
    }

    /**
     * @param $id
     */
    protected function initialiseLandmarks($id) {
        $sql = $this->dbh->getConnection()->prepare("INSERT INTO portrait_landmarks ( portrait_id ) VALUES (:id)");
        $sql->bindParam('id', $id, PDO::PARAM_STR);

        $this->utilManager->handleStatementException($sql, "Error while initialising landmarks!");
    }

    /**
     * @param $arrayOfLandmarks
     * @param $portraitId
     * @param $gender
     * @return string
     */
    public function updatePortrait($arrayOfLandmarks, $portraitId, $gender) {
        $updatedLandmarks = self::generateUpdatedLandmarkValues($arrayOfLandmarks, $portraitId);

        $sql = $this->dbh->getConnection()->prepare("UPDATE portrait_landmarks SET EB_FLAT_SHAPED=:EB_FLAT_SHAPED, EB_ANGLED=:EB_ANGLED, 
        EB_ROUNDED=:EB_ROUNDED, EYE_MONOLID_ALMOND=:EYE_MONOLID_ALMOND, EYE_DEEP_SET=:EYE_DEEP_SET, EYE_DOWNTURNED=:EYE_DOWNTURNED,
        EYE_HOODED=:EYE_HOODED, NOSE_AQUILINE=:NOSE_AQUILINE, NOSE_FLAT=:NOSE_FLAT, NOSE_ROMAN_HOOKED=:NOSE_ROMAN_HOOKED,
        NOSE_SNUB=:NOSE_SNUB, mustache=:mustache, beard=:beard, gender=:gender, features_completed = TRUE WHERE portrait_id=:portrait_id");
        $sql->bindParam('EB_FLAT_SHAPED', $updatedLandmarks['eyebrows']['flat_shaped'], PDO::PARAM_STR);
        $sql->bindParam('EB_ANGLED', $updatedLandmarks['eyebrows']['angled'], PDO::PARAM_STR);
        $sql->bindParam('EB_ROUNDED', $updatedLandmarks['eyebrows']['rounded'], PDO::PARAM_STR);
        $sql->bindParam('EYE_MONOLID_ALMOND', $updatedLandmarks['eye']['monolid_almond'], PDO::PARAM_STR);
        $sql->bindParam('EYE_DEEP_SET', $updatedLandmarks['eye']['deep_set'], PDO::PARAM_STR);
        $sql->bindParam('EYE_DOWNTURNED', $updatedLandmarks['eye']['downturned'], PDO::PARAM_STR);
        $sql->bindParam('EYE_HOODED', $updatedLandmarks['eye']['hooded'], PDO::PARAM_STR);
        $sql->bindParam('NOSE_AQUILINE', $updatedLandmarks['nose']['aquiline'], PDO::PARAM_STR);
        $sql->bindParam('NOSE_FLAT', $updatedLandmarks['nose']['flat'], PDO::PARAM_STR);
        $sql->bindParam('NOSE_ROMAN_HOOKED', $updatedLandmarks['nose']['roman_hooked'], PDO::PARAM_STR);
        $sql->bindParam('NOSE_SNUB', $updatedLandmarks['nose']['snub'], PDO::PARAM_STR);
        $sql->bindParam(':mustache', $updatedLandmarks['mustache'], PDO::PARAM_STR);
        $sql->bindParam(':beard', $updatedLandmarks['beard'], PDO::PARAM_STR);
        $sql->bindParam(':gender', $gender, PDO::PARAM_STR);
        $sql->bindParam(':portrait_id', $portraitId, PDO::PARAM_STR);

        $this->utilManager->handleStatementException($sql, "Error while update landmarks!");

        return json_encode(array(
            "response" => "Portrait : $portraitId was updated"
        ));
    }

    /**
     * @param $newValues
     * @param $portraitId
     * @param null $oldValues
     * @return array
     */
    public function generateUpdatedLandmarkValues($newValues, $portraitId, $oldValues = null) {
        $updateValues = array();
        $oldValues = $oldValues ? $oldValues : self::getCurrentLandmarkValues($portraitId);

        $finalNewValues = array(
            "mustache" => $newValues['mustache'] ? 1 : 0,
            "beard" => $newValues['beard'] ? 1 : 0,
            "eyebrows" => $this->convertLandmarkValue($newValues['eyebrows']),
            "eye" => $this->convertLandmarkValue($newValues['eye']),
            "nose" => $this->convertLandmarkValue($newValues['nose'])
        );

        if (!$oldValues['completed']) return $finalNewValues;

        foreach ($finalNewValues as $key => $value) {
            if ($key == 'mustache' || $key == 'beard') {
                $updateValues[$key] = $this->average($oldValues[$key], $value);
            } else {
                $calculatedLandmarks = array();
                foreach ($value as $lKey => $lValue) {
                    $calculatedLandmarks[$lKey] = $this->average($oldValues[$key][$lKey], $lValue);
                }
                $updateValues[$key] = $calculatedLandmarks;
            }
        }

        return $updateValues;
    }

    /**
     * @param $portraitId
     * @return array
     */
    private function getCurrentLandmarkValues($portraitId) {
        $sql = $this->dbh->getConnection()->prepare("SELECT * FROM portrait_landmarks WHERE portrait_id = :portrait_id");
        $sql->bindParam(':portrait_id', $portraitId, PDO::PARAM_STR);

        $this->utilManager->handleStatementException($sql, "Error while selecting portraits for landmark calculation function!");

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
        $sql->bindColumn('features_completed', $completed, PDO::PARAM_INT);
        $sql->fetch(PDO::FETCH_BOUND);

        return array(
            "mustache" => $fetchedMustache,
            "beard" => $fetchedBeard,
            "eyebrows" => array(
                "flat_shaped" => $eb_flat_shaped,
                "rounded" => $eb_rounded,
                "angled" => $eb_angled),
            "eye" => array(
                "deep_set" => $eye_deep_set,
                "monolid_almond" => $eye_monolid_almond,
                "downturned" => $eye_downturned,
                "hooded" => $eye_hooded),
            "nose" => array(
                "aquiline" => $nose_aquiline,
                "flat" => $nose_flat,
                "roman_hooked" => $nose_roman_hooked,
                "snub" => $nose_snub),
            "completed" => $completed
        );
    }

    /**
     * @param $a
     * @param $b
     * @return string
     */
    public function average($a, $b) {
        return number_format(($a+$b) / 2, 1);
    }

    /**
     * @param $arrayLandmarks
     * @return array
     */
    public function convertLandmarkValue($arrayLandmarks) {
        $value = count($arrayLandmarks);
        $convertedLandmarks = array();

        foreach ($arrayLandmarks as $landmark) {
            $convertedLandmarks[$landmark] = $value;
            $value--;
        }

        return $convertedLandmarks;
    }

    /**
     * @param $model
     * @return string
     */
    public function handleNotApplicationPortrait($model) {
        $this->model = $model;

        $id = $this->model->getId();

        $sql = $this->dbh->getConnection()->prepare("UPDATE portrait_landmarks SET not_applicable = TRUE, features_completed = TRUE WHERE portrait_id = :portrait_id");
        $sql->bindParam(':portrait_id', $id, PDO::PARAM_STR);

        $this->utilManager->handleStatementException($sql, "Error while setting portrait as not applicable!");
        return json_encode(array( 'response' => 'updated '));
    }

    /**
     * @param $model
     * @return string
     */
    public function getPortraitInfo($model) {
        $this->model = $model;

        $unknown = 'Unknown';
        $id = $this->model->getId();

        $sql = $this->dbh->getConnection()->prepare("SELECT * FROM portrait_info WHERE portrait_id = :id");
        $sql->bindParam(':id', $id, PDO::PARAM_STR);
        $this->utilManager->handleStatementException($sql, "Error while getting portrait information");
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

    /**
     * @return string
     */
    public function getStatistics() {

        $registeredUsersStm = $this->dbh->getConnection()->prepare("SELECT u.email, c.user_id, c.feedback, c.registered_at FROM users u 
          INNER JOIN consumer c 
            ON u.id = c.user_id WHERE u.user_type = 'consumer'");
        $this->utilManager->handleStatementException($registeredUsersStm, "Error while fetching users statistics!");

        $completedLandmarksStm = $this->dbh->getConnection()->prepare("SELECT * FROM portrait_landmarks WHERE features_completed = TRUE");
        $this->utilManager->handleStatementException($completedLandmarksStm, "Error while fetching landmarks statistics!");

        return json_encode(array(
            "registeredUsersCount" => $registeredUsersStm->rowCount(),
            "registeredUsers" => $registeredUsersStm->fetchAll(PDO::FETCH_ASSOC),
            "completedLandmarksCount" => $completedLandmarksStm->rowCount(),
            "completedLandmarks" => $completedLandmarksStm->fetchAll(PDO::FETCH_ASSOC)
        ));

    }

    /**
     * @param PortraitModel $model
     * @return string
     */
    public function deletePortrait(PortraitModel $model) {
        $portraitId = $model->getId();

        $sql = $this->dbh->getConnection()->prepare("DELETE FROM portrait WHERE id = :id");
        $sql->bindParam(':id', $portraitId, PDO::PARAM_STR);

        $this->utilManager->handleStatementException($sql, "Error while deleting portrait!");

        return "Portrait: $portraitId deleted from the database!";
    }

    /**
     * @param $arrayOfLandmarks
     * @param $gender
     * @param $beard
     * @param $mustache
     * @return string
     */
    public function generatePossibleDoppelgangerWithBasicSearch($arrayOfLandmarks, $gender, $beard, $mustache) {
        $time_start = microtime(true);
        $similarityController = new SimilarityController();
        $criteria = $similarityController->generateSimilarityCriteria($arrayOfLandmarks, $beard, $mustache);

        $sql = $this->dbh->getConnection()->prepare("SELECT DISTINCT p.id, p.image_url FROM portrait p
          INNER JOIN portrait_landmarks pl
            ON p.id = pl.portrait_id WHERE pl.gender = :gender ORDER BY $criteria LIMIT 1");
        $sql->bindParam(':gender', $gender, PDO::PARAM_STR);

        $this->utilManager->handleStatementException($sql, "Error while fetching match!");

        $data = $sql->fetchAll(PDO::FETCH_ASSOC);

        $time_end = microtime(true);
        $execution_time = ($time_end - $time_start)/60;

        echo "\n Execution : $execution_time \n";

        return json_encode($data);
    }

    /**
     * @param $arrayOfLandmarks
     * @param $gender
     * @param $beard
     * @param $mustache
     * @param $facialHairImportance
     * @param $priority
     * @return string
     */
    public function generatePossibleDoppelgangerWithAdvancedSearch($arrayOfLandmarks, $gender, $beard, $mustache, $facialHairImportance, $priority) {
        $time_start = microtime(true);
        $similarityController = new SimilarityController();

        $stm = "SELECT * FROM portrait_landmarks WHERE gender = :gender AND features_completed = TRUE";

        if ($facialHairImportance) $stm = "SELECT * FROM portrait_landmarks WHERE gender = :gender AND features_completed = TRUE 
            AND beard = :beard AND mustache = :mustache";

        $sql = $this->dbh->getConnection()->prepare($stm);
        $sql->bindParam(':gender', $gender, PDO::PARAM_STR);

        if ($facialHairImportance) {
            $sql->bindParam(':beard', $beard, PDO::PARAM_STR);
            $sql->bindParam(':mustache', $mustache, PDO::PARAM_STR);
        }

        $this->utilManager->handleStatementException($sql, "Error while selecting portraits for landmark calculation function!");
        $data = $sql->fetchAll(PDO::FETCH_ASSOC);

        $time_end = microtime(true);
        $execution_time = ($time_end - $time_start)/60;

        echo "\n Execution : $execution_time \n";

        $items = array();
        $counter = 0;
        foreach ($data as $item) {
            if ($counter < 60) {
                array_push($items, $similarityController->advancedSimilaritySearch($item, $arrayOfLandmarks, $beard, $mustache, $priority));
            }

            $counter++;
        }

        usort($items, function($a, $b) {
            return $b['similarity'] > $a['similarity'];
        });

        return json_encode($items);
    }

}