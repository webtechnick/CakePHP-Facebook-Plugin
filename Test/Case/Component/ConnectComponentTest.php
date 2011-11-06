<?php
App::uses('Facebook.ConnectComponent', 'Controller/Component');
App::uses('Controller', 'Utility');
App::uses('AuthComponent', 'Controller/Component');
App::uses('SessionComponent', 'Controller/Component');
App::uses('Facebook.FB', 'Lib');

class TestUser extends CakeTestModel {
  var $name = 'TestUser';
  var $data = null;
  var $useDbConfig = 'test_suite';
  var $useTable = false;
  
  function save($data){
    $this->data = $data;
    return true;
  }
  
  function hasField(){
    return true;
  }
  
  function saveField($field,$facebook_id){
    $this->facebookId = $facebook_id;
    return true;
  }
  
  function findByFacebookId($id){
    $this->facebookId = $id;
    return array();
  }
}

class TestUserHasOne extends CakeTestModel {
  var $name = 'TestUser';
  var $data = null;
  var $useDbConfig = 'test_suite';
  var $useTable = false;
  
  function save($data){
    $this->data = $data;
    return true;
  }
  
  function hasField(){
    return true;
  }
  
  function field(){
    return false;
  }
  
  function saveField($field,$facebook_id){
    $this->facebookId = $facebook_id;
    return true;
  }
  
  function findByFacebookId($id){
    $this->facebookId = $id;
    return array(
      'TestUserHasOne' => array(
        'id' => 1,
        'username' => 'test',
        'password' => 'password',
        'facebook_id' => ''
      )
    );
  }
}

class TestUserError extends CakeTestModel {
  var $name = 'TestUserError';
  var $data = null;
  var $useDbConfig = 'test_suite';
  var $useTable = false;
  
  function save($data){
    $this->data = $data;
    return true;
  }
  
  function hasField(){
    return false;
  }
  
  function saveField($field,$facebook_id){
    $this->facebookId = $facebook_id;
    return true;
  }
  
  function findByFacebookId($id){
    $this->facebookId = $id;
    return array();
  }
}

class TestCallbackController extends Controller{
	function beforeFacebookSave(){
		return false;
	}
	
	function beforeFacebookLogin(){
		return false;
	}
	
	function afterFacebookLogin(){
		return false;
	}
}

class ConnectTest extends CakeTestCase {
  var $Connect = null;
  
  function startTest(){
    Mock::generate('AuthComponent');
    Mock::generate('Controller');
    Mock::generate('SessionComponent');
    $this->Connect = new ConnectComponent();
    $this->Connect->Controller = $this->mockController();
    $this->User = new TestUser();
    Configure::write('Facebook.appId', '12345');
    Configure::write('Facebook.secret', '1234567890');
    
    Mock::generate('FB');
    $this->Connect->FB = new MockFB();
  }
  
  function mockController($callback = false){
  	if($callback){
  		Mock::generate('TestCallbackController');
  		$Controller = new MockTestCallbackController();
  	}
  	else {		
  		$Controller = new MockController();
    }
    $Controller->Auth = new MockAuthComponent();
    $Controller->Session = new MockSessionComponent();
    
    return $Controller;
  }
  
  function testInitialize(){
    $this->Connect->initialize($this->Connect->Controller);
    $this->assertFalse($this->Connect->me);
    $this->assertFalse($this->Connect->uid);
  }
  
  function testBeforeLoginCallback(){
  	$this->Connect->Controller = $this->mockController(true);
  	$this->Connect->Controller->Auth->userModel = 'TestUser';
    $this->Connect->session['uid'] = 12;
    $this->Connect->Controller->setReturnValue('beforeFacebookSave', true);
    $this->Connect->Controller->expectOnce('beforeFacebookLogin', array(array(
    	'TestUser' => array(
    		'facebook_id' => 12,
    		'password' => 'password'
    	)
    )));
    $this->Connect->Controller->Auth->setReturnValue('user', false);
    $this->Connect->Controller->Auth->setReturnValue('password', 'password');
    $this->Connect->Controller->Auth->expectOnce('login', array(array(
    	'TestUser' => array(
    		'facebook_id' => 12,
    		'password' => 'password'
    	)
    )));
    $this->assertTrue($this->Connect->__syncFacebookUser());
    $this->assertTrue($this->Connect->hasAccount);
  }
  
  function testSaveHaultedByBeforeFacebookSave(){
  	$this->Connect->Controller = $this->mockController(true);
  	$this->Connect->Controller->Auth->userModel = 'TestUser';
    $this->Connect->session['uid'] = 12;
    $this->Connect->Controller->setReturnValue('beforeFacebookSave', false);
    $this->Connect->Controller->Auth->expectNever('login', false);
    $this->Connect->Controller->Auth->setReturnValue('user', false);
    $this->Connect->Controller->Auth->setReturnValue('password', 'password');
    $this->assertTrue($this->Connect->__syncFacebookUser());
    $this->assertFalse($this->Connect->hasAccount);
  }
  
  function testFacebookSyncShouldDoNothingIfAuthIsNotDetected(){
    unset($this->Connect->Controller->Auth);
    $this->assertFalse($this->Connect->__syncFacebookUser()); 
  }
  
  function testFacebookSyncShouldLoginAlreadyLinkedUser(){
    $this->Connect->Controller->Auth->userModel = 'TestUserHasOne';
    $this->Connect->session['uid'] = 12;
    $this->Connect->Controller->Auth->expectOnce('login', array(array(
      'TestUserHasOne' => array(
        'id' => 1,
        'username' => 'test',
        'password' => 'password',
        'facebook_id' => ''
      )
    )));
    $this->assertTrue($this->Connect->__syncFacebookUser());
  }
  
  function testFacebookSyncShouldUpdateTheFacebookIdIfNotFound(){
    $this->Connect->Controller->Auth->userModel = 'TestUserHasOne';
    $this->Connect->Controller->Auth->setReturnValue('user', 1);
    $this->Connect->Controller->Auth->expectNever('login');
    $this->Connect->session['uid'] = 12;
    $this->assertTrue($this->Connect->__syncFacebookUser());
    $this->assertEqual(1, $this->Connect->User->id);
    $this->assertEqual(12, $this->Connect->User->facebookId);
  }
  
  function testFacebookSyncShouldReturnFalseIfWeDontHaveFacebookIDInTable(){
    $this->Connect->Controller->Auth->userModel = 'TestUserError';
    $this->Connect->Controller->Auth->setReturnValue('user', 1);
    $this->Connect->Controller->Auth->expectNever('login');
    $this->assertFalse($this->Connect->__syncFacebookUser());
    $this->assertEqual('Facebook.Connect handleFacebookUser Error.  facebook_id not found in TestUserError table.', $this->Connect->errors[0]);
  }
  
  function testFacebookSyncShouldNotCreateUser(){
    $this->Connect->Controller->Auth->userModel = 'TestUser';
    $this->Connect->session['uid'] = 12;
    $this->Connect->createUser = false;
    $this->Connect->Controller->Auth->setReturnValue('user', false);
    $this->Connect->Controller->Auth->setReturnValue('password', 'password');
    $this->Connect->Controller->Auth->expectNever('login');
    $this->assertTrue($this->Connect->__syncFacebookUser());
    $this->assertFalse($this->Connect->hasAccount); //user create wasn't called
  }
  
  function testFacebookSyncShouldCreateUser(){
    $this->Connect->Controller->Auth->userModel = 'TestUser';
    $this->Connect->session['uid'] = 12;
    $this->Connect->Controller->Auth->setReturnValue('user', false);
    $this->Connect->Controller->Auth->setReturnValue('password', 'password');
    $this->Connect->Controller->Auth->expectOnce('login');
    $this->assertTrue($this->Connect->__syncFacebookUser());
    $this->assertEqual(array('TestUser' => array('facebook_id' => 12, 'password' => 'password')), $this->Connect->User->data);
  }
  
  function testUserIfLoggedIn(){
    $this->Connect->me = array('email' => 'test@example.com', 'id' => '12');
    
    $results = $this->Connect->user();
    $this->assertEqual(array('email' => 'test@example.com', 'id' => '12'), $results);
    
    $results = $this->Connect->user('email');
    $this->assertEqual('test@example.com', $results);
    
    $results = $this->Connect->user('id');
    $this->assertEqual('12', $results);
  }
  
  function testUserIfLoggedOut(){
    $this->Connect->me = null;
    
    $results = $this->Connect->user();
    $this->assertEqual(null, $results);
    
    $results = $this->Connect->user('email');
    $this->assertEqual(null, $results);
    
    $results = $this->Connect->user('id');
    $this->assertEqual(null, $results);
  }
  
  function endTest(){
    unset($this->Connect);
    FacebookInfo::$configs = null;
    Configure::delete('Facebook');
  }


}
?>