<?php

require_once __DIR__ . "/../../config/DbConnection.php";
require_once __DIR__ . "/../../src/Managers/UtilManager.php";

class UtilManagerTest extends PHPUnit_Framework_TestCase {
    /** @var DbConnection */
    private $dbh;

    /** @var UtilManager */
    private $manager;

    protected function setUp() {
        $this->dbh = new DbConnection();
        $this->manager = new UtilManager();
    }


    public function testThatHandleStatementExceptionThrowException() {
        $sql = $this->dbh->getConnection()->prepare("SELECT * from unknow_table");

        $this->expectException(PDOException::class);
        $this->manager->handleStatementException($sql, "Test Error");
    }

    public function testThatHandleStatementExceptionDoesntThrowException() {
        $sql = $this->dbh->getConnection()->prepare("SELECT id from portrait");

        $this->manager->handleStatementException($sql, "Test Error");

        $this->addToAssertionCount(1); //This gets executed if no exception is thrown
    }

    public function testThatWeCanGetMaxScore() {
        $len = 3;

        $expectedResult = 6;
        $actualResult = $this->manager->getMaxScore($len);

        self::assertEquals($expectedResult, $actualResult);
    }

    public function testThatWeCanGetArrayPosition() {
        $len = 3;

        $expectedResult = 2;
        $actualResult = $this->manager->convertArrayPosition($len, 1);

        self::assertEquals($expectedResult, $actualResult);
    }

    public function testThatCanGetUploadForm() {
        $expected  = '
            <form method="POST" action="/uploadPortrait" enctype="multipart/form-data">
                <input type="number" name="json" value="0" />
                <button name="api" value="1">API 1</button>
                <button name="api" value="2">API 2</button>
            </form>
        ';

        $actual = $this->manager->uploadPortraitForm();

        self::assertEquals($expected, $actual);
    }
}