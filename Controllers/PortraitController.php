<?php

/**
 * Created by IntelliJ IDEA.
 * User: MrDan
 * Date: 24/10/2017
 * Time: 14:15
 */

require_once __DIR__ . "/../config/DbConnection.php";
require_once __DIR__ . "/../Managers/UtilManager.php";
require_once __DIR__ . "/../Model/PortraitModel.php";

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

        return "Portrait: $id added from the database!";
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
        $updatedLandmarks = $this->generateUpdatedLandmarkValues($arrayOfLandmarks, $portraitId);

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
        $oldValues = $oldValues ? $oldValues : $this->getCurrentLandmarkValues($portraitId);

        $finalNewValues = array(
            "mustache" => $newValues['mustache'] ? 1 : 0,
            "beard" => $newValues['beard'] ? 1 : 0,
            "eyebrows" => $this->convertLandmarkValue($newValues['eyebrows']),
            "eye" => $this->convertLandmarkValue($newValues['eye']),
            "nose" => $this->convertLandmarkValue($newValues['nose'])
        );

        if (!$oldValues['completed']) {
            return $finalNewValues;
        }

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
     * @return string
     */
    public function generatePossibleDoppelganger($arrayOfLandmarks, $gender, $beard, $mustache) {
        $criteria = $this->generateCriteria($arrayOfLandmarks, $beard, $mustache);

        $sql = $this->dbh->getConnection()->prepare("SELECT DISTINCT p.id, p.image_url FROM portrait p
          INNER JOIN portrait_landmarks pl
            ON p.id = pl.portrait_id WHERE pl.gender = :gender ORDER BY $criteria LIMIT 5");
        $sql->bindParam(':gender', $gender, PDO::PARAM_STR);

        $this->utilManager->handleStatementException($sql, "Error while fetching match!");

        return json_encode($sql->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * @param $arrayOfLandmarks
     * @param $beard
     * @param $mustache
     * @return string
     */
    public function generateCriteria($arrayOfLandmarks, $beard, $mustache) {
        $key = $arrayOfLandmarks['primary']['key'];
        $criteria = $arrayOfLandmarks['primary'][$key][0] . " DESC, ";

        foreach ($arrayOfLandmarks['secondary'] as $landmark) {
            $keyL = key($landmark);
            $criteria = $criteria . $landmark[$keyL][0] . " DESC, ";
        }

        if ($beard)
            $criteria = $criteria . "beard DESC, ";

        if ($mustache)
            $criteria = $criteria . "mustache DESC, ";

        return rtrim($criteria, ", ");
    }

    /**
     * @param $arrayOfLandmarks
     * @param $relevance
     * @param $gender
     * @param $beard
     * @param $mustache
     * @return string
     */
    public function generatePossibleDoppelgangerv2($arrayOfLandmarks, $relevance, $gender, $beard, $mustache) {
        $criteria = $this->generateCriteriav2($arrayOfLandmarks, $relevance, $beard, $mustache);

        $sql = $this->dbh->getConnection()->prepare("SELECT DISTINCT p.id, p.image_url FROM portrait p
          INNER JOIN portrait_landmarks pl
            ON p.id = pl.portrait_id WHERE pl.gender = :gender ORDER BY $criteria LIMIT 5");
        $sql->bindParam(':gender', $gender, PDO::PARAM_STR);

        $this->utilManager->handleStatementException($sql, "Error while fetching match!");

        return json_encode($sql->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * @param $arrayOfLandmarks
     * @param $relevance
     * @param $beard
     * @param $mustache
     * @return string
     */
    public function generateCriteriav2($arrayOfLandmarks, $relevance, $beard, $mustache) {
        $criteria = "";

        foreach ($relevance as $key) {
            $criteria = $criteria . $arrayOfLandmarks[$key][0] . " DESC, ";
        }

        if ($beard)
            $criteria = $criteria . "beard DESC, ";

        if ($mustache)
            $criteria = $criteria . "mustache DESC, ";

        return rtrim($criteria, ", ");
    }

    /**
     * @param $arrayOfLandmarks
     * @param $gender
     * @param $beard
     * @param $mustache
     * @return string
     */
    public function generatePossibleDoppelgangerWithSimilarTest($arrayOfLandmarks, $gender, $beard, $mustache) {

        $sql = $this->dbh->getConnection()->prepare("SELECT * FROM portrait_landmarks WHERE gender = :gender");
        $sql->bindParam(':gender', $gender, PDO::PARAM_STR);

        $this->utilManager->handleStatementException($sql, "Error while selecting portraits for landmark calculation function!");
        $data = $sql->fetchAll(PDO::FETCH_ASSOC);

        $items = array();
        foreach ($data as $item) {
            array_push($items, self::getData($item, $arrayOfLandmarks));
        }

        usort($items, function($a, $b) {
            return $b['similarity'] > $a['similarity'];
        });

        return json_encode($items);
    }

    public function getData($item, $arrayOfLandmarks) {
        $data = array("eyebrows" => array(
            "flat_shaped" => $item['EB_FLAT_SHAPED'],
            "rounded" => $item['EB_ROUNDED'],
            "angled" => $item['EB_ANGLED']),
            "eye" => array(
                "deep_set" => $item['EYE_DEEP_SET'],
                "monolid_almond" => $item['EYE_MONOLID_ALMOND'],
                "downturned" => $item['EYE_DOWNTURNED'],
                "hooded" => $item['EYE_HOODED']),
            "nose" => array(
                "aquiline" => $item['NOSE_AQUILINE'],
                "flat" => $item['NOSE_FLAT'],
                "roman_hooked" => $item['NOSE_ROMAN_HOOKED'],
                "snub" => $item['NOSE_SNUB']));

        $eyebrows = $this->utilManager->groupLandmarks($data['eyebrows']);
        $eyes = $this->utilManager->groupLandmarks($data['eye']);
        $nose = $this->utilManager->groupLandmarks($data['nose']);

        $percentageEB = self::similarityGenerator($arrayOfLandmarks['eyebrows'], $eyebrows);
        $percentageEYE = self::similarityGenerator($arrayOfLandmarks['eye'], $eyes);
        $percentageNOSE = self::similarityGenerator($arrayOfLandmarks['nose'], $nose);

        $similarityPercentage = number_format(($percentageEB + $percentageEYE + $percentageNOSE) / 3, 2);

        return array(
            "portrait_url" => self::getPortraitWithId($item['portrait_id']),
            "similarity" => $similarityPercentage
        );
    }


    /**
     * @param $userDataArray
     * @param $dbDataArray
     * @param bool $priority
     * @return int
     */
    public function similarityGenerator($userDataArray, $dbDataArray, $priority = false) {
        $length = sizeof($userDataArray);
        $multiArray = [ $userDataArray, $dbDataArray ];

        $similarity = 0;
        $max = $length;

        for ($i = 0; $i < $length; $i++) {
            if ($multiArray[0][$i] == $multiArray[1][$i]) {
                $similarity = $similarity + $max;
            } else {
                $userPos = $this->utilManager->convertArrayPosition($length, $i); // e.g. if $i = 0 && length = 4, return 4
                $dbPos = $this->utilManager->convertArrayPosition($length, array_search($multiArray[1][$i], $multiArray[0]));

                //performing matrix
                if ($priority && $userPos == 4 || $priority && $userPos == 3) {
                    $matrixResult = $userPos - (abs(($userPos - $dbPos) * 2)); // using abs because it might return a negative number which will cause userPos - (-n) = positive
                    $similarity = $similarity + $matrixResult;
                } else {
                    $matrixResult = $userPos - (abs($dbPos - $userPos));
                    $similarity = $similarity + $matrixResult;
                }
            }

            $max--;
        }

        $maxScore = $this->utilManager->getMaxScore($length);
        $similarity = abs(($similarity /  $maxScore) * 100);
        return (int) number_format($similarity);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getPortraitWithId($id) {
        $sql = $this->dbh->getConnection()->prepare("SELECT image_url FROM portrait WHERE id = :id");
        $sql->bindParam(':id', $id, PDO::PARAM_STR);

        $this->utilManager->handleStatementException($sql, "Error while selecting portraits for landmark calculation function!");

        $sql->bindColumn('image_url', $portrait_url, PDO::PARAM_STR);
        $sql->fetch(PDO::FETCH_BOUND);

        return $portrait_url;
    }

}