<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once(dirname(__FILE__)."/../../include/init.php");
require_once(dirname(__FILE__)."/../../include/user.php");
es_include("localobject.php");

final class UserTest extends TestCase
{
	public function testConstructor()
    {
    	$user = new User(array("Test" => "value"));
    	$this->assertInstanceOf(User::class, $user);
    	$this->assertEquals($user->GetProperty("Test"), "value");
    }
    
    public function testSave()
    {
    	$request = new LocalObject(array(
    		"email" => "auto_user_test@test.com",
    		"password1" => "12345",
    		"password2" => "12345",
    		"first_name" => "Tester",
    		"last_name" => "Tester",
    		"phone" => "0000000000",
    		"PermissionIDs" => array(1),
    	));
    	$user = new User();
    	$user->AppendFromObject($request);
    	$this->assertTrue($user->Save(-1), print_r($user));
    }
}
?>