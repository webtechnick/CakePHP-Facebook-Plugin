<?php
/**
* Facebook.Connect
* Uses the Facebook Connect API to log in a user through the Auth Component.
*
* The user MUST create a new field in their user model called 'facebook_id'
*
* @author Nick Baker <nick [at] webtechnick [dot] come>
* @link http://www.webtechnick.com
* @since 2.5.0
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
	* The authenticated User using Auth
	*/
	var $authUser = null;
	
	/**
	* No Auth, if set to true, syncFacebookUser will NOT be called
	*/
	var $noAuth = false;
	
	/**
	* Error log
	*/
	var $errors = array();
	
	/**
	* createUser is true you want the component to attempt to create a CakePHP Auth user
	* account by introspection on the Auth component.  If false, you can use $this->hasAccount
	* as a reference to decide what to do with that user. (default true)
	*/
	var $createUser = true;
	
	/**
	* Initialize, load the api, decide if we're logged in
	* Sync the connected Facebook user with your application
	* @param Controller object to attach to
	* @param settings for Connect
	* @return void
	* @access public
	*/
	function initialize(&$Controller, $settings = array()){
		$this->Controller = $Controller;
		$this->_set($settings);
		$this->FB = new FB();
		$this->session = $this->FB->getSession();
	}
	
	/**
	* Sync the connected Facebook user with your application.
	*
	* Attempt to authenticate user using Facebook.
	* Currently the uid is fetched from $this->session['uid'].
	*
	* @param Controller object to attach to
	* @return void
	*/
	function startup() {
		// Prevent using Auth component only if there is noAuth setting provided
		if (!$this->noAuth && !empty($this->session['uid'])) {
			$this->__syncFacebookUser();
		}
	}
	
	/**
	* Sync the connected Facebook user.
	*
	* If User is logged in:
	*  a. but doesn't have a facebook account associated, try to associate it.
	*
	* If User is not logged in:
	*  b. but have a facebook account associated, try to log the user in.
	*  c. and doesn't have a facebook account associated,
	*    1. try to automatically create an account and associate it (if $this->createUser).
	*    2. try to log the user in, afterwards.
	*
	* @return boolean True if successful, false otherwise.
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
			$this->User->id = $Auth->user($this->User->primaryKey);
			if (!$this->User->field('facebook_id')) {
				$this->User->saveField('facebook_id', $this->session['uid']);
			}
			return true;
		} 
		else {
			// attempt to find the user by their facebook id
			$this->authUser = $this->User->findByFacebookId($this->session['uid']);
			
			//if we have a user, set hasAccount
			if(!empty($this->authUser)){
				$this->hasAccount = true;
			}
			//create the user if we don't have one
			elseif(empty($this->authUser) && $this->createUser) {
				$this->authUser[$this->User->alias]['facebook_id'] = $this->session['uid'];
				$this->authUser[$this->User->alias][$Auth->fields['password']] = $Auth->password(FacebookInfo::randPass());
				if($this->__runCallback('beforeFacebookSave')){
					$this->hasAccount = ($this->User->save($this->authUser, array('validate' => false)));
				}
				else {
					$this->authUser = null;
				}
			}
			//Login user if we have one
			if($this->authUser){
				$this->__runCallback('beforeFacebookLogin', $this->authUser);
				$Auth->fields = array('username' => 'facebook_id', 'password' => $Auth->fields['password']);    		
				if($Auth->login($this->authUser)){
					$this->__runCallback('afterFacebookLogin');
				}
			}
			return true;
		}
	}
	
	/**
	* Read the logged in user
	* @param field key to return (xpath without leading slash)
	* @param mixed return
	*/
	function user($field = null){
		if(isset($this->session)){
			$this->uid = $this->session['uid'];
			if($this->Controller->Session->read('FB.Me') == null){
				$this->Controller->Session->write('FB.Me', $this->FB->api('/me'));
			}
			$this->me = $this->Controller->Session->read('FB.Me');
		} 
		else {
			$this->Controller->Session->delete('FB');
		}
		
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
	* Run the callback if it exists
	* @param string callback
	* @param mixed passed in variable (optional)
	* @return mixed result of the callback function
	*/ 
	function __runCallback($callback, $passedIn = null){
		if(is_callable(array($this->Controller, $callback))){
			return call_user_func_array(array($this->Controller, $callback), array($passedIn));
		}
		return true;
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