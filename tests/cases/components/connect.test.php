<?php
App::import('Component', 'Facebook.Connect');
App::import('Core', 'Controller');
App::import('Component', 'Auth');
App::import('Component', 'Session');
App::import('Lib', 'Facebook.FB');

class TestUser extends CakeTestModel {
  var $name = 'TestUser';
  var $data = null;
  var $useDbConfig = 'test_suite';
  var $useTable = false;
  
  function save($data){
    $this->data = $data;
  }
  
  function hasField(){
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
  }
  
  function hasField(){
    return false;
  }
  
  function findByFacebookId($id){
    $this->facebookId = $id;
    return array();
  }
}


class ConnectTest extends CakeTestCase {
  var $Connect = null;
  
  function startTest(){
    Mock::generate('AuthComponent');
    Mock::generate('Controller');
    Mock::generate('SessionComponent');
    $this->Connect = new ConnectComponent();
    $this->Connect->Controller = new MockController();
    $this->Connect->Controller->Auth = new MockAuthComponent();
    $this->Connect->Controller->Session = new MockSessionComponent();
    $this->User = new TestUser();
    
    Mock::generate('FB');
    $this->Connect->FB = new MockFB();
  }
  
  function mockController(){
    $Controller = new MockController();
    $Controller->Auth = new MockAuthComponent();
    $Controller->Session = new MockSessionComponent();
    
    return $Controller;
  }
  
  function testInitialize(){
    $this->Connect->Controller->Session->expectOnce('delete');
    $this->Connect->initialize($this->Connect->Controller);
    $this->assertFalse($this->Connect->hasAccount);
    $this->assertFalse($this->Connect->me);
    $this->assertFalse($this->Connect->uid);
  }
  
  function testFacebookSyncShouldDoNothingIfAuthIsNotDetected(){
    unset($this->Connect->Controller->Auth);
    $this->assertFalse($this->Connect->__syncFacebookUser()); 
  }
  
  function testFacebookSyncShouldLoginAlreadyLinkedUser(){
    $this->Connect->Controller->Auth->userModel = 'TestUserHasOne';
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
    $this->Connect->uid = 12;
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
    $this->Connect->uid = '12';
    $this->Connect->createUser = false;
    $this->Connect->Controller->Auth->setReturnValue('user', false);
    $this->Connect->Controller->Auth->setReturnValue('password', 'password');
    $this->Connect->Controller->Auth->expectOnce('login');
    $this->assertTrue($this->Connect->__syncFacebookUser());
    $this->assertEqual(null, $this->Connect->User->data); //user create wasn't called
  }
  
  function testFacebookSyncShouldCreateUser(){
    $this->Connect->Controller->Auth->userModel = 'TestUser';
    $this->Connect->uid = '12';
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
  }


}
?>