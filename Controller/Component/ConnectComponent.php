<?php
/**
* Facebook.Connect
* Uses the Facebook Connect API to log in a user through the Auth Component.
*
* The user MUST create a new field in their user model called 'facebook_id'
*
* @author Nick Baker <nick [at] webtechnick [dot] come>
* @link http://www.webtechnick.com
* @since 3.0.0
* @license MIT
*/
App::uses('FB', 'Facebook.Lib');
App::uses('FacebookInfo', 'Facebook.Lib');
class ConnectComponent extends Component {
	
	/**
	* uid is the Facebook ID of the connected Facebook user, or null if not connected
	*/
	public $uid = null;
	
	/**
	* me is the Facebook user object for the connected Facebook user
	*/
	public $me = null;
	
	/**
	* hasAccount is true if the connected Facebook user has an account in your application
	*/
	public $hasAccount = false;
	
	/**
	* The authenticated User using Auth
	*/
	public $authUser = null;
	
	/**
	* No Auth, if set to true, syncFacebookUser will NOT be called
	*/
	public $noAuth = false;
	
	/**
	* Error log
	*/
	public $errors = array();
	
	/**
	* createUser is true you want the component to attempt to create a CakePHP Auth user
	* account by introspection on the Auth component.  If false, you can use $this->hasAccount
	* as a reference to decide what to do with that user. (default true)
	*/
	public $createUser = true;
	
	/**
	* name of the authentication model, false by default,
	* set to model alias to init the model.
	*/
	public $model = false;
	
	/**
	* Fields for the model if you want to save the Auth component.
	*/
	public $modelFields = array(
		'password' => 'password',
		'username' => 'username'
	);
	
	/**
	* Initialize, load the api, decide if we're logged in
	* Sync the connected Facebook user with your application
	* @param Controller object to attach to
	* @param settings for Connect
	* @return void
	* @access public
	*/
	public function initialize(&$Controller, $settings = array()){
		$this->Controller = $Controller;
		$this->_set($settings);
		$this->FB = new FB();
		$this->uid = $this->FB->getUser();
	}
	
	/**
	* Sync the connected Facebook user with your application.
	*
	* Attempt to authenticate user using Facebook.
	* Currently the uid is fetched from $this->uid.
	*
	* @param Controller object to attach to
	* @return void
	*/
	public function startup() {
		// Prevent using Auth component only if there is noAuth setting provided
		if (!$this->noAuth && !empty($this->uid)) {
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
	private function __syncFacebookUser(){
		if(!isset($this->Controller->Auth)){
			return false;
		}
		// set Auth to a convenience publiciable
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
		if($Auth->user('id')){
			$this->hasAccount = true;
			$this->User->id = $Auth->user($this->User->primaryKey);
			if (!$this->User->field('facebook_id')) {
				$this->User->saveField('facebook_id', $this->uid);
			}
			return true;
		} 
		else {
			// attempt to find the user by their facebook id
			$this->authUser = $this->User->findByFacebookId($this->uid);
			//if we have a user, set hasAccount
			if(!empty($this->authUser)){
				$this->hasAccount = true;
			}
			//create the user if we don't have one
			elseif(empty($this->authUser) && $this->createUser) {
				$this->authUser[$this->User->alias]['facebook_id'] = $this->uid;
				$this->authUser[$this->User->alias][$this->modelFields['password']] = $Auth->password(FacebookInfo::randPass());
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
				$Auth->authenticate = array(
					'Form' => array(
						'fields' => array('username' => 'facebook_id', 'password' => $this->modelFields['password'])
					)
				);
				if($Auth->login($this->authUser[$this->model])){
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
	public function user($field = null){
		if(isset($this->uid)){
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
	* @param mixed passed in publiciable (optional)
	* @return mixed result of the callback function
	*/ 
	private function __runCallback($callback, $passedIn = null){
		if(is_callable(array($this->Controller, $callback))){
			return call_user_func_array(array($this->Controller, $callback), array($passedIn));
		}
		return true;
	}
	
	/**
	* Initialize the actual User model object defined by the plugin
	* @return true if successful
	* @access private
	*/
	private function __initUserModel(){
		if($this->model){
			App::uses($this->model,'Model');
			$this->User = ClassRegistry::init($this->model);
		}
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
	private function __error($msg){
		$this->errors[] = __($msg, true);
	}
}