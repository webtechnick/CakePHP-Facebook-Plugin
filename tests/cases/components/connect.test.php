<?php
App::import('Component', 'Facebook.Connect');
App::import('Core', 'Controller');
App::import('Component', 'Auth');
App::import('Lib', 'Facebook.FacebookApi');

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

class TestFacebookApiClient{
  var $facebookId;
  var $params;
  function users_getInfo($facebookId, $params){
    $this->facebookId = $facebookId;
    $this->params = $params;
    return array(0 => array('email' => 'test', 'last_name' => 'baker', 'first_name' => 'nick'));
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
    
    Mock::generate('FacebookApi');
    $this->Connect->FacebookApi = new MockFacebookApi();
    $this->Connect->FacebookApi->api_client = new TestFacebookApiClient();
  }
  
  function mockController(){
    $Controller = new MockController();
    $Controller->Auth = new MockAuthComponent();
    
    return $Controller;
  }
  
  function testInitialize(){
    $this->Connect->initialize($this->mockController());
    $this->assertFalse($this->Connect->facebookUserId);
  }
  
  function testHandleFacebookUserWithValidFacebookDatabase(){
    $Controller = $this->mockController();
    $Controller->Auth->userModel = 'TestUser';
    $this->Connect->Controller = $Controller;
    $this->Connect->facebookUserId = 12;
    $this->Connect->Controller->Auth->expectOnce('password');
    $this->Connect->Controller->Auth->expectOnce('login');
    
    $this->Connect->_handleFacebookUser();
    $this->assertEqual(array('username' => 'facebook_id', 'password' => 'password'), $this->Connect->Controller->Auth->fields);
    $this->assertEqual('TestUser', $this->Connect->Controller->Auth->userModel);
    $this->assertEqual(12, $this->Connect->__UserModel->data['facebook_id']);
    $this->assertEqual(12, $this->Connect->__UserModel->data['username']);
  }
  
  function testHandleFacebookUserWithOutDatabase(){
    $Controller = $this->mockController();
    $Controller->Auth->userModel = 'TestUserError';
    $this->Connect->Controller = $Controller;
    $this->Connect->facebookUserId = 12;
    $this->expectError("Facebook.Connect handleFacebookUser Error.  facebook_id not found in TestUserError table.");
    
    $this->Connect->_handleFacebookUser();
  }
  
  function testGetUserInfoShouldBeEmptyIfNotLoggedIn(){
    $results = $this->Connect->getUserInfo();
    $this->assertTrue(empty($results));
  }
  
  function testGetUserInfoIfLoggedIn(){
    $this->Connect->facebookUserId = 12;
    $results = $this->Connect->getUserInfo();
    $expected = array('email' => 'test', 'last_name' => 'baker', 'first_name' => 'nick');
    $this->assertTrue(!empty($results));
    $this->assertEqual(12, $this->Connect->FacebookApi->api_client->facebookId);
    $this->assertEqual($expected, $results);
  }
  
  function testGetUserInfoIfUserFieldsAreSet(){
    $this->Connect->facebookUserId = 12;
    $results = $this->Connect->getUserInfo(array('email'));
    $this->assertTrue(!empty($results));
    $this->assertEqual(12, $this->Connect->FacebookApi->api_client->facebookId);
    $this->assertEqual(array('email'), $this->Connect->FacebookApi->api_client->params);
    
    
    $this->Connect->facebookUserId = 12;
    $this->Connect->userFields = array('email');
    $results = $this->Connect->getUserInfo();
    $this->assertTrue(!empty($results));
    $this->assertEqual(12, $this->Connect->FacebookApi->api_client->facebookId);
    $this->assertEqual(array('email'), $this->Connect->FacebookApi->api_client->params);
  }
  
  function testGetUserModel(){
    $Controller = $this->mockController();
    $Controller->Auth->userModel = 'TestUserError';
    $this->Connect->Controller = $Controller;
    $result = $this->Connect->__getUserModel();
    $this->assertTrue(is_a($result, 'TestUserError'));
    
    $this->Connect->userModel = 'TestUser';
    $result = $this->Connect->__getUserModel();
    $this->assertTrue(is_a($result, 'TestUser'));
  }
  
  function endTest(){
    unset($this->Connect);
  }


}
?>