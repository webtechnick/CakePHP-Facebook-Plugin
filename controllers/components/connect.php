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
		$this->Controller->Session->delete('Facebook');
  }

  
  /**
    * Sync the connected Facebook user
    * @return void
    * @access protected
    */
  function __syncFacebookUser(){
  	// check if Auth exists
    if(!isset($this->Controller->Auth)){
    	return;
    }
  	// set Auth to a convenience variable
    $Auth = $this->Controller->Auth;
  	// initialize the User model if possible
  	if (!$this->__initUserModel()) {
  		return;
  	}
    // if you don't have a facebook_id field in your user table, throw an error
    if(!$this->User->hasField('facebook_id')){
      $this->__error("Facebook.Connect handleFacebookUser Error.  facebook_id not found in {$Auth->userModel} table.");
      return;
    }
    // check if the user already has an account
    if($Auth->user()){
    	// let's assume they have an account in your application if they are already logged in
    	$this->hasAccount = true;
    	// let's set the model to their id
    	$this->User->id = $Auth->user('id');
    	// see if they have a facebook_id in the database already
    	if (!$this->User->field('facebook_id')) {
    		// if they don't, we want to save it
    		$this->User->saveField('facebook_id', $this->uid);
    	}
    	// let's kick them out of here
    	return;
    } else {
	  	// attempt to find the user by their facebook id
	  	$user = $this->User->findByFacebookId($this->uid);
	  	// if we found the user
	  	if (!empty($user)) {
	  		// this user has an account in your application
    		$this->hasAccount = true;
    		// tell auth that the user's username is their facebook_id
		    $Auth->fields = array('username' => 'facebook_id', 'password' => $Auth->fields['password']);
    		// log the user in
		    $Auth->login($user);
	  	}
	  }
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
    return;
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

}