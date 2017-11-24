<?php

namespace Tests\Functional;

class ApiTest extends BaseTestCase {

    /**
     * The issue is that PHPUnit will print a header to the screen and at that point you can't add more headers.
     * That is why process isolation is needed. The isolation can be added to phpunit.xml as well but that will run
     * every single test in separate process and therefore, a slower test. The other tests do not need to run in
     * separate processes.
     */

    /**
     * @runInSeparateProcess
     */

    public function testGetRandomPortrait() {
        $response = $this->runApp('GET', '/getPortrait');

        $this->assertArrayHasKey('id', json_decode($response->getBody(), true));
        $this->assertArrayHasKey('portraitURL', json_decode($response->getBody(), true));
    }

    /**
     * @runInSeparateProcess
     */

    public function testPostRandomPortraitNotAllowed() {
        $response = $this->runApp('POST', '/getPortrait', ['test']);

        $this->assertEquals(405, $response->getStatusCode());
        $this->assertContains('Method not allowed', (string) $response->getBody());
    }
}