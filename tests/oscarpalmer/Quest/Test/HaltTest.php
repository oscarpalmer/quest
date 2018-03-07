<?php

namespace oscarpalmer\Quest\Test;

use oscarpalmer\Quest\Exception\Halt;

class HaltTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructor()
    {
        $halt = new Halt;

        $this->assertNotNull($halt);
        $this->assertInstanceOf('oscarpalmer\Quest\Exception\Halt', $halt);
    }
}
