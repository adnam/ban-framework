<?php

class Ban_ResponseTest extends GenericTestCase
{

    public function testGetSet_String()
    {
        $response = new Ban_Response();
        $string = 'bar';
        $response->foo = $string;
        $this->assertEquals($string, $response->foo);
        $this->assertEquals(array('foo' => $string), $response->getBody());
        
        $this->assertTrue(isset($response->foo));
        $this->assertFalse(isset($response->nunu));

    }

    public function testGetSet_Array()
    {
        $response = new Ban_Response();
        $array = array('first' => 1, 'second' => 2);
        $response->foo = $array;
        $this->assertEquals($array, $response->foo);
        $this->assertEquals(array('foo' => $array), $response->getBody());
    }

    public function testDefaultResponseCode()
    {
        $response = new Ban_Response();
        $this->assertEquals(200, $response->getHttpResponseCode());
    }

}
