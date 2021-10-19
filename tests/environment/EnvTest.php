<?php 
declare(strict_types=1);

require_once(dirname(__FILE__)."/../../include/init.php");
es_include("localobject.php");

use PHPUnit\Framework\TestCase;

final class EnvTest extends TestCase
{
    public function testConstructor()
    {
        $this->assertEquals(1, true);
    }
}
?>