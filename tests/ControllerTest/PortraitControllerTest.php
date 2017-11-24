<?php

require_once __DIR__ . "/../../Controllers/PortraitController.php";
require_once __DIR__ . "/../../Model/PortraitModel.php";
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

        $this->assertArrayHasKey('id', json_decode($actualResult, true));
        $this->assertArrayHasKey('portraitURL', json_decode($actualResult, true));
    }

    public function testThatWeCanAddPortrait() {
        $model = new PortraitModel();
        $model->setId('testID');
        $model->setImageUrl('http://test.com');

        $expectedResult = $this->helper->addPortraitHelper($model);
        $actualResult = $this->controller->addPortrait($model);

        $this->assertEquals($expectedResult, $actualResult);
    }

    public function testThatWeCanGetPortraitInfo() {
        $model = new PortraitModel();
        $model->setId('-AEaOcNiNdy-Bg');

        $expectedResult = $this->helper->getPortraitInfoHelper();
        $actualResult = $this->controller->getPortraitInfo($model);

        $this->assertEquals($expectedResult, $actualResult);
    }

    public function testThatWeCanDeletePortrait() {
        $model = new PortraitModel();
        $model->setId('testId');

        $expectedResult = $this->helper->deletePortraitHelper($model);
        $actualResult = $this->controller->deletePortrait($model);

        $this->assertEquals($expectedResult, $actualResult);
    }
}