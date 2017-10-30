<?php

require_once __DIR__ . "/../../Model/ConsumerModel.php";

class ConsumerModelTest extends \PHPUnit_Framework_TestCase {
    protected $consumer;

    protected function setUp() {
        $this->consumer = new ConsumerModel();
    }

    public function testThatWeCanGetConsumerFeedback() {
        $this->consumer->setFeedback("This is just some feedback");

        $this->assertEquals($this->consumer->getFeedback(), "This is just some feedback");
    }

    public function testThatWeCanGetConsumerRegisteredAt() {
        $this->consumer->setRegisteredAt(new DateTime("2001-03-10 17:16:18"));

        $this->assertEquals($this->consumer->getRegisteredAt(), new DateTime("2001-03-10 17:16:18"));
    }

}