<?php
/**
  * Facebook.Connect
  * Uses the Facebook Connect API to log in a user through the Auth Component.
  *
  * The user MUST created a new field in their userModel 'facebook_id'
  *
  * @author Nick Baker <nick [at] webtechnick [dot] come>
  * @link http://www.webtechnick.com
  * @version 1.5.4
  * @license MIT
  */
App::import('Lib', 'Facebook.FacebookApi');
class ConnectComponent extends Object {
  
  /**
    * FacebookUserId is the Facebook ID of the logged in user, or null if not logged in
    */
  var $facebookUserId = null;
  
  /**
    * FacebookUser is the Facebook User array of a logged in user.
    */
  var $facebookUser = array();
  
  /**
    * If set to true, autoUserDetails will retrieve the user details and save them into facebookUser for later access
    */
  var $autoUserDetails = true;
  
  /**
    * Default user fields to retrieve when autoUserDetails is set to true
    */
  var $userFields = array('last_name','first_name','email');
  
  /**
    * The class name of the user model to use.
    */
  var $userModel = null;
  
  /**
    * __UserModel is the placeholder for the actual userModel defined by Auth
    */
  var $__UserModel = null;
  
  /**
    * Initialize, load the api, decide if we're logged in.
    * handle the logged in user if facebook returns we're logged in.
    * @return void
    * @access public
    */
  function initialize($controller, $settings = array()){
    $this->Controller = $controller;
    $this->_set($settings);
    $this->FacebookApi = new FacebookApi();
    $this->facebookUserId = $this->FacebookApi->get_loggedin_user();
    if($this->facebookUserId){
      $this->_handleFacebookUser();
    }
  }
  
  /**
    * Get the User information of the logged in user.
    * @param array of options
    * @link http://wiki.developers.facebook.com/index.php/Users.getInfo
    */
  function getUserInfo($params = null){
    if($this->facebookUserId){
      if(!$params){
        $params = $this->userFields;
      }
      try{
        $userinfo = $this->FacebookApi->api_client->users_getInfo($this->facebookUserId, $params);
        if(isset($userinfo[0])){
          $this->facebookUser = $userinfo[0];
        }
        else {
          $this->facebookUser = $userinfo;
        }
      } catch (Exception $ex){
        $this->FacebookApi->clear_cookie_state();
      }
      
      return $this->facebookUser;
    }
    else {
      return array();
    }
  }
  
  /**
    * Handle the loggedin facebook user. 
    * If user is not created
    * @return void
    * @access protected
    */
  function _handleFacebookUser(){
    if(isset($this->Controller->Auth)){
      $Auth = $this->Controller->Auth;
      if(!$Auth->user()){
        $this->__UserModel = $this->__getUserModel();
        $this->defaultFields = $Auth->fields;
        $this->__UserModel->recursive = -1;
        
        if(!$this->__UserModel->hasField('facebook_id')){
          $this->__error("Facebook.Connect handleFacebookUser Error.  facebook_id not found in {$Auth->userModel} table.");
          return false;
        }
        
        $user = $this->__UserModel->findByFacebookId($this->facebookUserId);
        if(empty($user)){
          $user['facebook_id'] = $this->facebookUserId;
          $user[$this->defaultFields['username']] = $this->facebookUserId;
          $user[$this->defaultFields['password']] = $Auth->password($this->__generatePassword());
          $this->__UserModel->save($user);
        }
        elseif (empty($user[$this->__UserModel->alias]['facebook_id'])){
          $user[$this->__UserModel->alias]['facebook_id'] = $this->facebookUserId;
          $this->__UserModel->save($user);
        }
        
        $Auth->fields = array('username' => 'facebook_id', 'password' => $this->defaultFields['password']);
        $Auth->login($user);
      }
    }
    
    if($this->autoUserDetails){
      $this->getUserInfo($this->userFields);
    }
  }
  
  /**
    * User returns the logged in facebook user details.
    * @param string key to retrieve
    * @return mixed array of logged in user, or string of specific key
    * @access public
    */
  function user($key = null){
    if($key == 'id'){
      $key = 'uid'; //facebook id key.
    }
    if($this->facebookUser && $key){
      return $this->facebookUser[$key];
    }
    return $this->facebookUser;
  }
  
  /**
    * Return the actual User Model object defined by Auth or User.
    * @return mixed object User Model or false if unable to determine.
    * @access private
    */
  function __getUserModel(){
    if($this->userModel){
      return ClassRegistry::init($this->userModel);
    }
    
    if(isset($this->Controller->Auth)){
      return ClassRegistry::init($this->Controller->Auth->userModel);
    }
    
    return false;
  }
  
  /**
    * Handle errors.
    * @param string of error message
    * @return void
    * @access private
    */
  function __error($msg){
    trigger_error($msg, E_USER_WARNING);
  }
  
  /**
    * Generate a password for the facebook user
    * @access private
    * @return string of generated password
    * @param int length of password (default 9)
    * @param int strengh of password (default 4)
    */
  function __generatePassword($length=9, $strength=4) {
    $vowels = 'aeuy';
    $consonants = 'bdghjmnpqrstvz';
    if ($strength & 1) {
        $consonants .= 'BDGHJLMNPQRSTVWXZ';
    }
    if ($strength & 2) {
        $vowels .= "AEUY";
    }
    if ($strength & 4) {
        $consonants .= '23456789';
    }
    if ($strength & 8) {
        $consonants .= '@#$%';
    }

    $password = '';
    $alt = time() % 2;
    for ($i = 0; $i < $length; $i++) {
        if ($alt == 1) {
            $password .= $consonants[(rand() % strlen($consonants))];
            $alt = 0;
        } else {
            $password .= $vowels[(rand() % strlen($vowels))];
            $alt = 1;
        }
    }
    return $password;
  }
  
  
}
?>