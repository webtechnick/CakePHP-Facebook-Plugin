<?php
/**
  * Facebook.Connect
  * Uses the Facebook Connect API to log in a user through the Auth Component.
  *
  * The user MUST created a new field in their userModel 'facebook_id'
  *
  * @author Nick Baker <nick [at] webtechnick [dot] come>
  * @link http://www.webtechnick.com
  * @version 1.0
  * @license MIT
  */

App::import('Vendor', 'Facebook.facebook/php/facebook');
Configure::load('facebook');
class ConnectComponent extends Object {
  
  /**
    * FacebookUser is the Facebook ID of the logged in user, or null if not logged in
    */
  var $facebookUser = null;
  
  /**
    * UserModel is the placeholder for the actual userModel defined by Auth
    */
  var $UserModel = null;
  
  /**
    * Initialize, load the api, decide if we're logged in.
    * handle the logged in user if facebook returns we're logged in.
    * @return void
    * @access public
    */
  function initialize($controller){
    $this->Controller = $controller;
    $this->Facebook = new Facebook(Configure::read('Facebook.api_key'), Configure::read('Facebook.secret'));
    
    $this->facebookUser = $this->Facebook->get_loggedin_user();
    
    if($this->facebookUser){
      $this->_handleFacebookUser();
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
        $this->UserModel = ClassRegistry::init($Auth->userModel);
        $this->defaultFields = $Auth->fields;
        $this->UserModel->recursive = -1;
        
        if(!$this->UserModel->hasField('facebook_id')){
          $this->__error("Facebook.Connect::handleFacebookUser Error.  facebook_id not found in {$Auth::userModel} table.");
          return false;
        }
        
        $user = $this->UserModel->findByFacebookId($this->facebookUser);
        if(empty($user)){
          $user['facebook_id'] = $this->facebookUser;
          $user[$this->defaultFields['username']] = $this->facebookUser;
          $user[$this->defaultFields['password']] = $Auth->password($this->__generatePassword());
          $this->UserModel->save($user);
        }
        
        $Auth->fields = array('username' => 'facebook_id', 'password' => $this->defaultFields['password']);
        $Auth->login($user);
      }
    }
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