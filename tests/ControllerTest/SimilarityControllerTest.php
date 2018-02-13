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

    public function testThatWeCanGetSimilarityWithAdvancedSearch() {
        $arr1 = ["A", "C", "B"];
        $arr2 = ["C", "A", "B"];

        $expectedResult = 67;
        $actualResult = $this->controller->similarityCalculator($arr1, $arr2);

        self::assertEquals($expectedResult, $actualResult);
    }

    public function testThatWeCanCalculateSimilarity() {
        $arr1 = ["A", "C", "B"];
        $arr2 = ["C", "A", "B"];

        $expectedResult = 67;
        $actualResult = $this->controller->similarityCalculator($arr1, $arr2);

        self::assertEquals($expectedResult, $actualResult);
    }

}