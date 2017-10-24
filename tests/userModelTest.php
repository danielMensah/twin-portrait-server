<?php

require __DIR__ . "/../Model/UserModel.php";

class UserModelTest extends \PHPUnit_Framework_TestCase {
    protected $user;

    protected function setUp() {
        $this->user = new UserModel();

    }

    public function testThatWeCanGetTheUserEmail() {
        $this->user->setEmail("test@gmail.com");

        $this->assertEquals($this->user->getEmail(), "test@gmail.com");

    }

    public function testThatWeCanGetTheUserFeedback() {
        $this->user->setFeedback("this is just a feedback");

        $this->assertEquals($this->user->getFeedback(), "this is just a feedback");

    }
}