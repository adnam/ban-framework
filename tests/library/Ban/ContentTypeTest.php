<?php

class Ban_ContentypeTest extends GenericTestCase
{
    public function testFromString_Simple()
    {
        $asString = 'application/vnd.ban+json;version=1.0;view=default;type=recordset';
        $ct = Ban_ContentType::fromString($asString);

        $this->assertTrue($ct instanceof Ban_ContentType);
        $this->assertEquals('application/vnd.ban+json', $ct->getMime());
        $this->assertEquals('1.0', $ct->version);
        $this->assertEquals('default', $ct->view);
        $this->assertEquals('recordset', $ct->type);
        $this->assertEquals(null, $ct->nonExistentParam);
    }
    
    public function testFromString_NoParams()
    {
        $asString = 'application/vnd.ban+json';
        $ct = Ban_ContentType::fromString($asString);

        $this->assertTrue($ct instanceof Ban_ContentType);
        $this->assertEquals('application/vnd.ban+json', $ct->getMime());
        $this->assertEquals(null, $ct->version);
        $this->assertEquals(null, $ct->view);
        $this->assertEquals(null, $ct->type);
        $this->assertEquals(null, $ct->nonExistentParam);
    }
    
    public function testFromString_QuotedParams()
    {
        $asString = 'application/vnd.ban+json;version="1.0";view="default";type="recordset"';
        $ct = Ban_ContentType::fromString($asString);
        
        $this->assertTrue($ct instanceof Ban_ContentType);
        $this->assertEquals('application/vnd.ban+json', $ct->getMime());
        $this->assertEquals('1.0', $ct->version);
        $this->assertEquals('default', $ct->view);
        $this->assertEquals('recordset', $ct->type);
        $this->assertEquals(null, $ct->nonExistentParam);
    }
    
    public function testFromString_WithWhitespace()
    {
        $asString = "application/vnd.ban+json; version = 1.0 ;\tview=default;  type=recordset";
        $ct = Ban_ContentType::fromString($asString);
        
        $this->assertTrue($ct instanceof Ban_ContentType);
        $this->assertEquals('application/vnd.ban+json', $ct->getMime());
        $this->assertEquals('1.0', $ct->version);
        $this->assertEquals('default', $ct->view);
        $this->assertEquals('recordset', $ct->type);
        $this->assertEquals(null, $ct->nonExistentParam);
    }
    
    public function testFromString_WithFlags()
    {
        $asString = "application/vnd.ban+json; version = 1.0 ; flag1;  flag2";
        $ct = Ban_ContentType::fromString($asString);
        
        $this->assertTrue($ct instanceof Ban_ContentType);
        $this->assertEquals('application/vnd.ban+json', $ct->getMime());
        $this->assertEquals('1.0', $ct->version);
        $this->assertTrue($ct->flag1);
        $this->assertTrue($ct->flag2);
        $this->assertNull($ct->flag3);
    }

    public function testGetSet()
    {
        $ct = Ban_ContentType::fromString('application/vnd.ban+json');
        $this->assertEquals(null, $ct->type);
        $ct->type = 'setType';
        $this->assertEquals('setType', $ct->type);
    }
    
    public function testIsJson()
    {
        $ct = Ban_ContentType::fromString('application/vnd.ban+json');
        $this->assertTrue($ct->isJson());
        $this->assertFalse($ct->isXml());

        $ct = Ban_ContentType::fromString('application/vnd.ban+xml');
        $this->assertFalse($ct->isJson());
        $this->assertTrue($ct->isXml());

        $ct = Ban_ContentType::fromString('text/plain');
        $this->assertFalse($ct->isJson());
        $this->assertFalse($ct->isXml());

        $ct = Ban_ContentType::fromString('ab/cd');
        $this->assertFalse($ct->isJson());
        $this->assertFalse($ct->isXml());

    }

}
