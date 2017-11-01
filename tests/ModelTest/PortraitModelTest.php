<?php

require "../../Model/PortraitModel.php";

class PortraitModelTest extends \PHPUnit_Framework_TestCase {
    protected $portrait;

    protected function setUp() {
        $this->portrait = new PortraitModel();
    }

    public function testThatWeCanGetPortraitId() {
        $this->portrait->setId(00001);

        $this->assertEquals($this->portrait->getId(), 00001);
    }

    public function testThatWeCanGetPortraitUrl() {
        $this->portrait->setImageUrl("http:lh5.ggpht.com");

        $this->assertEquals($this->portrait->getImageUrl(), "http:lh5.ggpht.com");
    }

}