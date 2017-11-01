<?php

require_once "../../Model/AdminModel.php";

class AdminModelTest extends \PHPUnit_Framework_TestCase {
    protected $admin;

    protected function setUp() {
        $this->admin = new AdminModel();
    }

    public function testThatWeCanGetAdminUsername() {
        $this->admin->setUsername("Daniel");

        $this->assertEquals($this->admin->getUsername(), "Daniel");
    }

    public function testThatWeCanGetAdminPassword() {
        $this->admin->setPassword("password123");

        $this->assertEquals($this->admin->getPassword(), "password123");
    }

}