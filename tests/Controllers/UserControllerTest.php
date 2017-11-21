<?php
require_once __DIR__ . "/../../Controllers/UserController.php";

class UserControllerTest extends \PHPUnit_Framework_TestCase {
    private $controller;

    protected function setUp() {
        $this->controller = new UserController();
    }

    public function testThatCanGeneratePromoCode() {
        $promoCode = $this->controller->generatePromoCode();

        $this->assertEquals(20, strlen($promoCode));
    }
}