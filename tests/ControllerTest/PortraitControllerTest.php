<?php

require_once __DIR__ . "/../../src/Controllers/PortraitController.php";
require_once __DIR__ . "/../../src/Model/PortraitModel.php";
require_once __DIR__ . "/../Helpers/PortraitHelper.php";

class PortraitControllerTest extends PHPUnit_Framework_TestCase {

    /** @var PortraitController */
    private $controller;
    /** @var PortraitHelper */
    private $helper;

    protected function setUp() {
        $this->controller = new PortraitController();
        $this->helper = new PortraitHelper();
    }

    public function testThatWeCanGetRandomPortrait() {
        $actualResult = $this->controller->getRandomPortrait();

        self::assertArrayHasKey('id', json_decode($actualResult, true));
        self::assertArrayHasKey('portraitURL', json_decode($actualResult, true));
    }

    public function testThatWeCanAddPortrait() {
        $model = new PortraitModel();
        $model->setId('testID');
        $model->setImageUrl('http://test.com');

        $expectedResult = $this->helper->addPortraitHelper($model);
        $actualResult = $this->controller->addPortrait($model);

        self::assertEquals($expectedResult, $actualResult);
    }

    public function testThatWeCanGetPortraitInfo() {
        $model = new PortraitModel();
        $model->setId('-AEaOcNiNdy-Bg');

        $expectedResult = $this->helper->getPortraitInfoHelper();
        $actualResult = $this->controller->getPortraitInfo($model);

        self::assertEquals($expectedResult, $actualResult);
    }

    public function testThatWeCanUpdatePortrait() {
        $expectedResult = $this->helper->updatePortraitHelper("testID");
        $actualResult = $this->controller->updatePortrait($this->helper->generateNewValuesDummyData(), "testID", "female");

        self::assertEquals($expectedResult, $actualResult);
    }

    public function testThatWeCanConvertLandmarkValue() {
        $array = array("deep-set", "monolid/almond", "downturned", "hooked");

        $expectedResult = $this->helper->convertLandmarkValueHelper();
        $actualResult = $this->controller->convertLandmarkValue($array);

        self::assertEquals($expectedResult, $actualResult);
    }

    public function testThatWeCanGenerateNewLandmarksValues() {
        $newVal = $this->helper->generateNewValuesDummyData();
        $oldVal = $this->helper->generateOldValuesDummyData();

        $expectedResult = $this->helper->generateUpdatedLandmarkValuesHelper();
        $actualResult = $this->controller->generateUpdatedLandmarkValues($newVal, null, $oldVal);

        self::assertEquals($expectedResult, $actualResult);
    }

    public function testThatWeCanDeletePortrait() {
        $model = new PortraitModel();
        $model->setId('testId');

        $expectedResult = $this->helper->deletePortraitHelper($model);
        $actualResult = $this->controller->deletePortrait($model);

        self::assertEquals($expectedResult, $actualResult);
    }

    public function testThatWeCanHandleNotApplicablePortraits() {
        $model = new PortraitModel();
        $model->setId('testId');

        $expectedResult = json_encode(array( 'response' => 'updated '));
        $actualResult = $this->controller->handleNotApplicationPortrait($model);

        self::assertEquals($expectedResult, $actualResult);
    }

    public function testThatWeCanGetStatistics() {
        $expectedResult = 'registeredUsersCount';
        $actualResult = $this->controller->getStatistics();

        self::assertContains($expectedResult, $actualResult);
    }

    public function testThatBasicSearchReturnsPortraits() {
        $data = $this->helper->basicMatchHelper();

        $contain = 'id';
        $result = $this->controller->generatePossibleDoppelgangerWithBasicSearch($data, 'male', true, true);

        self::assertContains($contain, $result);
    }

    public function testThatAdvancedSearchReturnsPortrait() {
        $data = $this->helper->basicMatchHelper();

        $contain = 'portraitId';
        $result = $this->controller->generatePossibleDoppelgangerWithAdvancedSearch($data, 'male', true,
            true, 1, null);

        self::assertContains($contain, $result);
    }

    public function testThatWeCannotAddPortrait() {
        $model = new PortraitModel();
        $model->setId(null);
        $model->setImageUrl('http://test.com');

        $expectedResult = $this->expectException(PDOException::class);
        $actualResult = $this->controller->addPortrait($model);

        self::assertEquals($expectedResult, $actualResult);
    }

}