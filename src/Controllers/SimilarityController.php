<?php
/**
 * Created by PhpStorm.
 * User: MrDan
 * Date: 13/02/2018
 * Time: 11:17
 */
require_once __DIR__ . "/../Managers/UtilManager.php";
require_once __DIR__ . "/../../config/DbConnection.php";

class SimilarityController {

    /** @var UtilManager */
    private $utilManager;
    /** @var DbConnection */
    private $dbh;

    /**
     * SimilarityController constructor.
     */
    public function __construct() {
        $this->utilManager = new UtilManager();
        $this->dbh = new DbConnection();
    }

    public function advancedSimilaritySearch($dbLandmarks, $userLandmarks, $beard, $mustache, $priority) {
        $userFH = array("beard"=>$beard, "mustache"=>$mustache);
        $portraitFH = array("beard"=>$dbLandmarks['beard'], "mustache"=>$dbLandmarks['mustache']);

        $data = array(
            "eyebrows" => array(
                "flat_shaped" => $dbLandmarks['EB_FLAT_SHAPED'],
                "rounded" => $dbLandmarks['EB_ROUNDED'],
                "angled" => $dbLandmarks['EB_ANGLED']),
            "eye" => array(
                "deep_set" => $dbLandmarks['EYE_DEEP_SET'],
                "monolid_almond" => $dbLandmarks['EYE_MONOLID_ALMOND'],
                "downturned" => $dbLandmarks['EYE_DOWNTURNED'],
                "hooded" => $dbLandmarks['EYE_HOODED']),
            "nose" => array(
                "aquiline" => $dbLandmarks['NOSE_AQUILINE'],
                "flat" => $dbLandmarks['NOSE_FLAT'],
                "roman_hooked" => $dbLandmarks['NOSE_ROMAN_HOOKED'],
                "snub" => $dbLandmarks['NOSE_SNUB']));

        $eyebrows = $this->utilManager->groupLandmarks($data['eyebrows']);
        $eyes = $this->utilManager->groupLandmarks($data['eye']);
        $nose = $this->utilManager->groupLandmarks($data['nose']);

        $percentageEB = self::similarityCalculator($userLandmarks['eyebrows'], $eyebrows, $priority == 'eyebrows');
        $percentageEYE = self::similarityCalculator($userLandmarks['eye'], $eyes, $priority == 'eye');
        $percentageNOSE = self::similarityCalculator($userLandmarks['nose'], $nose, $priority == 'nose');

        $similarityPercentage = number_format(($percentageEB + $percentageEYE + $percentageNOSE) / 3, 2);
        self::facialHairCalculator($userFH, $portraitFH, $similarityPercentage);

        return array(
            "portrait_url" => self::getPortraitWithId($dbLandmarks['portrait_id']),
            "similarity" => $similarityPercentage,
            "portraitId" => $dbLandmarks['portrait_id']
        );
    }

    /**
     * @param $userDataArray
     * @param $dbDataArray
     * @param bool $priority
     * @return int
     */
    public function similarityCalculator($userDataArray, $dbDataArray, $priority = false) {
        $length = sizeof($userDataArray);
        $multiArray = [ $userDataArray, $dbDataArray ];
        $priority = (int) $priority;

        $similarity = 0;
        $max = $length;

        for ($i = 0; $i < $length; $i++) {
            if ($multiArray[0][$i] == $multiArray[1][$i]) {
                $similarity += $max;
            } else {
                $userPos = $this->utilManager->convertArrayPosition($length, $i); // e.g. if $i = 0 && length = 4, return 4
                $dbPos = $this->utilManager->convertArrayPosition($length, array_search($multiArray[1][$i], $multiArray[0]));

                //performing matrix
                if ($priority && $userPos == 4 || $priority && $userPos == 3) {
                    $matrixResult = $userPos - (abs(($userPos - $dbPos) * 2)); // using abs because it might return a negative number which will cause userPos - (-n) = positive
                    $similarity += $matrixResult;
                } else {
                    $matrixResult = $userPos - (abs($dbPos - $userPos));
                    $similarity += $matrixResult;
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

    /**
     * @param $arrayOfLandmarks
     * @param $beard
     * @param $mustache
     * @return string
     */
    public function generateSimilarityCriteria($arrayOfLandmarks, $beard, $mustache) {
        $criteria = "";

        foreach ($arrayOfLandmarks as $landmark) {
            foreach ($landmark as $item) {
                $criteria .= $item . " DESC, ";
            }
        }

        if ($beard) $criteria .= "beard DESC, ";
        if ($mustache) $criteria .= "mustache DESC, ";

        return rtrim($criteria, ", ");
    }

    /**
     * @param $userFH
     * @param $portraitFH
     * @param $similarity
     * @return int
     */
    public function facialHairCalculator($userFH, $portraitFH, &$similarity) {
        if ((int)$userFH['beard'] !== (int)$portraitFH['beard']) $similarity += -5;
        if ((int)$userFH['mustache'] !== (int)$portraitFH['mustache']) $similarity += -5;

        return $similarity;
    }

}