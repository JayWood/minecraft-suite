<?php

class MS_Registered_Users_Test extends WP_UnitTestCase {

	function test_sample() {
		// replace this with some actual testing code
		$this->assertTrue( true );
	}

	function test_class_exists() {
		$this->assertTrue( class_exists( 'MS_Registered_Users') );
	}

	function test_class_access() {
		$this->assertTrue( minecraft_suite()->registered-users instanceof MS_Registered_Users );
	}
}
