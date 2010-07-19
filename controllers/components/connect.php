<?php
/**
  * Facebook.Connect
  * Uses the Facebook Connect API to log in a user through the Auth Component.
  *
  * The user MUST create a new field in their user model called 'facebook_id'
  *
  * @author Nick Baker <nick [at] webtechnick [dot] come>
  * @link http://www.webtechnick.com
  * @version 1.7
  * @license MIT
  */
App::import('Lib', 'Facebook.FB');
class ConnectComponent extends Object {
  
  /**
    * uid is the Facebook ID of the connected Facebook user, or null if not connected
    */
  var $uid = null;
  
  /**
    * me is the Facebook user object for the connected Facebook user
    */
  var $me = null;
  
  /**
    * hasAccount is true if the connected Facebook user has an account in your application
    */
  var $hasAccount = false;
  
  /**
    * Error log
    */
  var $errors = array();
  
  /**
    * Initialize, load the api, decide if we're logged in
    * Sync the connected Facebook user with your application
    * @return void
    * @access public
    */
  function initialize($Controller){
  	// setup our controller object for the other methods
    $this->Controller = $Controller;
    // instanciate the Facebook API
    $this->FB = new FB();
    // get the connected user's session
    $this->session = $this->FB->getSession();
    // if they are connected, set the class properties
		if ($this->session) {
			// set the connected user's Facebook UID
	    $this->uid = $this->FB->getUser();
	    // set the connected user's data object
	    $this->me = $this->FB->api('/me');
	    // store their Facebook user object in the session
	    $this->Controller->Session->write('FB.Me', $this->me);
	    // store their Facebook session in the session
	    $this->Controller->Session->write('FB.Session', $this->session);
		}
		// if we successfully obtained the user's data object sync them with your application
		if ($this->me) {
		  $this->__syncFacebookUser();
		  return;
		}
		// let's delete their Facebook session info if they are not connected
		$this->Controller->Session->delete('FB');
  }

  
  /**
    * Sync the connected Facebook user
    * @return boolean true if successful, false otherwise
    * @access protected
    */
  function __syncFacebookUser(){
    if(!isset($this->Controller->Auth)){
    	return false;
    }
  	// set Auth to a convenience variable
    $Auth = $this->Controller->Auth;
  	if (!$this->__initUserModel()) {
  		return false;
  	}
    // if you don't have a facebook_id field in your user table, throw an error
    if(!$this->User->hasField('facebook_id')){
      $this->__error("Facebook.Connect handleFacebookUser Error.  facebook_id not found in {$Auth->userModel} table.");
      return false;
    }
    
    // check if the user already has an account
    // User is logged in but doesn't have a 
    if($Auth->user()){
    	$this->hasAccount = true;
    	$this->User->id = $Auth->user('id');
    	if (!$this->User->field('facebook_id')) {
    		$this->User->saveField('facebook_id', $this->uid);
    	}
    	return true;
    } 
    else {
	  	// attempt to find the user by their facebook id
	  	$user = $this->User->findByFacebookId($this->uid);
	  	$this->hasAccount = true;
	  	
	  	//create the user if we don't have one
	  	if(empty($user)) {
		    $user[$this->User->alias]['facebook_id'] = $this->uid;
		    $user[$this->User->alias][$Auth->fields['password']] = $Auth->password('disabled');
		    $this->User->save($user, array('validate' => false));
	  	}
	  	
	  	$Auth->fields = array('username' => 'facebook_id', 'password' => $Auth->fields['password']);    		
      $Auth->login($user);
      return true;
	  }
  }
  
  /**
    * Read the logged in user
    * @param field key to return (xpath without leading slash)
    * @param mixed return
    */
  function user($field = null){
    if(!$this->me){
      return null;
    }
    
    if($field){
      $retval = Set::extract("/$field", $this->me);
      return empty($retval) ? null : $retval[0];
    }
    
    return $this->me;
  }
  
  /**
    * Initialize the actual User model object defined by Auth
    * @return true if successful
    * @access private
    */
  function __initUserModel(){
    $this->User = ClassRegistry::init($this->Controller->Auth->userModel);
    if (isset($this->User)) {
      $this->User->recursive = -1;
    	return true;
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
    $this->errors[] = __($msg, true);
  }

}