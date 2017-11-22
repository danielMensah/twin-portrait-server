<?php

require_once __DIR__ . "/../../Model/UserModel.php";

class UserModelTest extends \PHPUnit_Framework_TestCase {
    protected $user;

    protected function setUp() {
        $this->user = new UserModel();

    }

    public function testThatWeCanGetTheUserEmail() {
        $this->user->setEmail("test@gmail.com");

        $this->assertEquals($this->user->getEmail(), "test@gmail.com");

    }

    public function testThatWeCanGetTheUserType() {
        $this->user->setUserType("consumer");

        $this->assertEquals($this->user->getUserType(), "consumer");

    }
}