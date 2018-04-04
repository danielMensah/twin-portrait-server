<?php
/**
 * Created by PhpStorm.
 * User: MrDan
 * Date: 13/02/2018
 * Time: 11:27
 */

require_once __DIR__ . "/../../src/Controllers/SimilarityController.php";

class SimilarityControllerTest extends PHPUnit_Framework_TestCase {

    /** @var SimilarityController */
    private $controller;

    protected function setUp() {
        $this->controller = new SimilarityController();
    }

    public function testThatWeCanCalculateSimilarityWith3Landmarks() {
        $arr1 = ["A", "C", "B"];
        $arr2 = ["A", "B", "C"];

        $expectedResult = 67;
        $actualResult = $this->controller->similarityCalculator($arr1, $arr2);

        self::assertEquals($expectedResult, $actualResult);
    }

    public function testThatWeCanCalculateSimilarityWith4Landmarks() {
        $arr1 = ["A", "C", "D", "B"];
        $arr2 = ["D", "C", "B", "A"];

        $expectedResult = 40;
        $actualResult = $this->controller->similarityCalculator($arr1, $arr2);

        self::assertEquals($expectedResult, $actualResult);
    }

    public function testThatWeCanCalculateSimilarityWith4LandmarksPriority() {
        $arr1 = ["S", "A", "RH"];
        $arr2 = ["S", "A", "RH"];

        $expectedResult = 100;
        $actualResult = $this->controller->similarityCalculator($arr1, $arr2, true);

        self::assertEquals($expectedResult, $actualResult);
    }

    public function testThatWeCanCalculateSimilarityWith4LandmarksPriority2() {
        $arr1 = ["F", "A", "RH", "S"];
        $arr2 = ["A", "F", "RH", "S"];

        $expectedResult = 60;
        $actualResult = $this->controller->similarityCalculator($arr1, $arr2, true);

        self::assertEquals($expectedResult, $actualResult);
    }

    public function testThatCanCalculateFacialHairWithBeard() {
        $userData = array("beard"=>true, "mustache"=>false);
        $dbData = array("beard"=>false, "mustache"=>true);
        $similarity = 80;

        $expectedResult = 70;
        $actualResult = $this->controller->facialHairCalculator($userData, $dbData, $similarity);

        self::assertEquals($expectedResult, $actualResult);
    }

    public function testThatCanCalculateFacialHairWithBeardAndMustache() {
        $userData = array("beard"=>true, "mustache"=>true);
        $dbData = array("beard"=>false, "mustache"=>false);
        $similarity = 80;

        $expectedResult = 70;
        $actualResult = $this->controller->facialHairCalculator($userData, $dbData, $similarity);

        self::assertEquals($expectedResult, $actualResult);
    }

    public function testThatCanGenerateBasicSearchCriteria() {
        $userData = array(
            "eye" => ["EYE_DEEP_SET", "EYE_DOWNTURNED", "EYE_MONOLID_ALMOND", "EYE_HOODED"],
            "eyebrows" => ["EB_ANGLED", "EB_ROUNDED", "EB_FLAT_SHAPED"],
            "nose" => ["NOSE_SNUB", "NOSE_AQUILINE", "NOSE_FLAT", "NOSE_ROMAN_HOOKED"]
        );

        $expectedResult = " AND pl.EB_ANGLED = 3 AND pl.EYE_DEEP_SET = 4 AND pl.NOSE_SNUB = 4 AND pl.EB_ROUNDED = 2 AND pl.EYE_DOWNTURNED = 3 AND pl.NOSE_AQUILINE = 3 AND pl.EB_FLAT_SHAPED = 1 AND pl.EYE_MONOLID_ALMOND = 2 AND pl.NOSE_FLAT = 2 AND pl.EYE_HOODED = 1 AND pl.NOSE_ROMAN_HOOKED = 1";
        $actualResult = $this->controller->generateSimilarityCriteria($userData);

        self::assertEquals($expectedResult, $actualResult);

    }

}