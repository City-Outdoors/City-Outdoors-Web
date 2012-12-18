<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

class UserTests  extends AbstractTest {
	
	function testPassword() {
        $this->setupDB();
		
        $user = User::createByEmail("test@example.com","pass","pass");		
		
		$this->assertEquals(true, $user->hasPassword());
		$this->assertEquals(false, $user->checkPassword('1234'));
		$this->assertEquals(true, $user->checkPassword('pass'));
		
		$user->setNewPassword('1234','1234');
		
		$this->assertEquals(true, $user->checkPassword('1234'));
		$this->assertEquals(false, $user->checkPassword('pass'));
		
	}
	
	function testPasswordToShortOnCreate() {
		$this->setupDB();
		try {
			$user = User::createByEmail("test@example.com","p","p");	
			$this->assertEquals(true,false); // this line should not be reached, prev line should have thrown exception!	
		} catch (UserExceptionPasswordsToShort $e) {
			$this->assertEquals("Must be longer than 3 characters",$e->getMessage());
		} catch (Exception $e) {
			$this->assertEquals(true,false); // this line should not be reached, we should not get generic exception!	
		}					
	}
	
	
	function testPasswordDifferentOnCreate() {
		$this->setupDB();
		try {
		  $user = User::createByEmail("test@example.com","tns","p");		
			$this->assertEquals(true,false); // this line should not be reached, prev line should have thrown exception!		
		} catch (UserExceptionPasswordsDontMatch $e) {
			$this->assertEquals("Passwords don't match",$e->getMessage());
		} catch (Exception $e) {
			$this->assertEquals(true,false); // this line should not be reached, we should not get generic exception!	
		}				
	}
	
	
	function testPasswordToShortOnChange() {
		$this->setupDB();
		$user = User::createByEmail("test@example.com","pass","pass");
		try {
			$user->setNewPassword('1','1');		
			$this->assertEquals(true,false); // this line should not be reached, prev line should have thrown exception!	
		} catch (UserExceptionPasswordsToShort $e) {
			$this->assertEquals("Must be longer than 3 characters",$e->getMessage());
		} catch (Exception $e) {
			$this->assertEquals(true,false); // this line should not be reached, we should not get generic exception!	
		}	
				
	}
	
	
	function testPasswordDifferentOnChange() {
		$this->setupDB();
        $user = User::createByEmail("test@example.com","pass","pass");
		try {
			$user->setNewPassword('dht','ao');	
			$this->assertEquals(true,false); // this line should not be reached, prev line should have thrown exception!		
		} catch (UserExceptionPasswordsDontMatch $e) {
			$this->assertEquals("Passwords don't match",$e->getMessage());
		} catch (Exception $e) {
			$this->assertEquals(true,false); // this line should not be reached, we should not get generic exception!	
		}		
	}
	
	
	function testDuplicateEmail() {
        $this->setupDB();
		
        $user = User::createByEmail("test@example.com","pass","pass");		
		try {
			$user = User::createByEmail("test@example.com","pass","pass");		
			$this->assertEquals(true,false); // this line should not be reached, prev line should have thrown exception!
		} catch (UserExceptionEmailAlreadyKnown $e) {
			$this->assertEquals('That email address is already in use!',$e->getMessage());
		} catch (Exception $e) {
			$this->assertEquals(true,false); // this line should not be reached, we should not get generic exception!	
		}
		
	}
	
	function testEmailNotValid() {
		$this->setupDB();
        
		try {
			$user = User::createByEmail("testexamplecom","pass","pass");
			$this->assertEquals(true,false); // this line should not be reached, prev line should have thrown exception!		
		} catch (UserExceptionEmailNotValid $e) {
			$this->assertEquals("Email not Valid!",$e->getMessage());
		} catch (Exception $e) {
			$this->assertEquals(true,false); // this line should not be reached, we should not get generic exception!	
		}		
	}
	
	
}
