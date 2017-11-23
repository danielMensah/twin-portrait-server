<?php

require_once __DIR__ . "/../../Controllers/PortraitController.php";
require_once __DIR__ . "/../../Model/PortraitModel.php";
require_once __DIR__ . "/../Helpers/PortraitHelper.php";

class PortraitControllerTest extends PHPUnit_Framework_TestCase {
    private $controller;
    private $helper;

    protected function setUp() {
        $model = new PortraitModel();
        $model->setId('-AEaOcNiNdy-Bg');

        $this->controller = new PortraitController($model);
        $this->helper = new PortraitHelper();
    }

    public function testThatWeCanGetRandomPortrait() {
        $actualResult = $this->controller->getRandomPortrait();

        $this->assertArrayHasKey('id', json_decode($actualResult, true));
        $this->assertArrayHasKey('portraitURL', json_decode($actualResult, true));
    }

//    public function testThatWeCanAddPortrait() {
//
//    }

    public function testThatWeCanGetPortraitInfo() {
        $expectedResult = $this->helper->infoHelper();
        $actualResult = $this->controller->getPortraitInfo();

        $this->assertEquals($expectedResult, $actualResult);
    }
}