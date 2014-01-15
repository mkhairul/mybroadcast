<?php
class HomeTest extends TestCase {

    public function testSomethingIsTrue()
    {
        $response = $this->call('GET', 'home');
        $this->assertResponseOk();
    }

}