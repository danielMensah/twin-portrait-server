<?php

require_once __DIR__ . "/../../src/Controllers/UserController.php";
require_once __DIR__ . "/../../src/Model/UserModel.php";
require_once __DIR__ . "/../Helpers/UserHelper.php";

class UserControllerTest extends PHPUnit_Framework_TestCase {

    /** @var UserController */
    private $controller;

    protected function setUp() {
        $this->controller = new UserController();
    }

    public function testThatWeCanRegisterConsumer() {
        $model = new ConsumerModel();
        $model->setEmail('test1@gmail.com');
        $model->setFeedback('someFeedback');
        $model->setUserType("consumer");

        $actualResult = json_decode($this->controller->registerUser($model, 0), true);

        $this->assertArrayHasKey('response', $actualResult);
        $this->assertArrayHasKey('promoCode', $actualResult);

    }

    public function testThatWeCanUpdateSatisfactionIfUsersExists() {
        $model = new ConsumerModel();
        $model->setEmail('test1@gmail.com');
        $model->setFeedback('someFeedback');
        $model->setUserType("consumer");
        $model->setSatisfaction(1);

        $actualResult = json_decode($this->controller->registerUser($model, 1), true);

        $this->assertArrayHasKey('response', $actualResult);

    }

    public function testThatWeCantRegisterExistingConsumer() {
        $model = new ConsumerModel();
        $model->setEmail('test@gmail.com');
        $model->setFeedback('someFeedback');
        $model->setUserType("consumer");

        $actualResult = json_decode($this->controller->registerUser($model, 0), true);

        $this->assertArrayNotHasKey('promoCode', $actualResult);
    }

    public function testThatWeCanRemoveUser() {
        $helper = new UserHelper();

        $expectedResult = $helper->removeUser('test1@gmail.com');
        $actualResult = $this->controller->removeUser('test1@gmail.com');

        $this->assertEquals($expectedResult, $actualResult);
    }

}