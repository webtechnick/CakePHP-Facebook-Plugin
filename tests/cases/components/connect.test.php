<?php
App::import('Component', 'Facebook.Connect');
App::import('Core', 'Controller');
App::import('Component', 'Auth');

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
    $this->Connect = new ConnectComponent();
    $this->Connect->Controller = new MockController();
    $this->Connect->Controller->Auth = new MockAuthComponent();
    $this->User = new TestUser();
  }
  
  function mockController(){
    $Controller = new MockController();
    $Controller->Auth = new MockAuthComponent();
    
    return $Controller;
  }
  
  function testInitialize(){
    $this->Connect->initialize($this->mockController());
    $this->assertFalse($this->Connect->facebookUser);
  }
  
  function testHandleFacebookUserWithValidFacebookDatabase(){
    $Controller = $this->mockController();
    $Controller->Auth->userModel = 'TestUser';
    $this->Connect->Controller = $Controller;
    $this->Connect->facebookUser = 12;
    $this->Connect->Controller->Auth->expectOnce('password');
    $this->Connect->Controller->Auth->expectOnce('login');
    
    $this->Connect->_handleFacebookUser();
    $this->assertEqual(array('username' => 'facebook_id', 'password' => 'password'), $this->Connect->Controller->Auth->fields);
    $this->assertEqual('TestUser', $this->Connect->Controller->Auth->userModel);
    $this->assertEqual(12, $this->Connect->UserModel->data['facebook_id']);
    $this->assertEqual(12, $this->Connect->UserModel->data['username']);
  }
  
  function testHandleFacebookUserWithOutDatabase(){
    $Controller = $this->mockController();
    $Controller->Auth->userModel = 'TestUserError';
    $this->Connect->Controller = $Controller;
    $this->Connect->facebookUser = 12;
    $this->expectError("Facebook.Connect handleFacebookUser Error.  facebook_id not found in TestUserError table.");
    
    $this->Connect->_handleFacebookUser();
  }
  
  function endTest(){
    unset($this->Connect);
  }


}
?>