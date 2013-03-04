<?php
/**
  * Facebook.Api Library used to create an instanciated facebook API available to the user
  *
  * @author Nick Baker <nick [at] webtechnick [dot] com>
  * @version 2.5.0
  * @link http://www.webtechnick.com
  * @license MIT
  */

App::uses('Facebook', 'Facebook.Vendor');
App::uses('FacebookInfo', 'Facebook.Lib');
class FB {

  /**
    * Facebook Api
    */
  public static $Facebook = null;
  
  public function __construct() {
    $this->__initInstance();
  }
  
  /**
   * Initialize a new instance if none exists
   */
  private function __initInstance() {
  	if (empty(self::$Facebook)) {
		self::$Facebook = new Facebook(FacebookInfo::getConfig());
  	}
  }
  
  /**
    * Forward any call to the Facebook API
    * @param string method name
    * @param mixed params passed into method
    * @return mixed return value of result from Facebook API
    */
  public function __call($method, $params){
  	try {
  		return call_user_func_array(array(self::$Facebook, $method), $params);
  	} catch (FacebookApiException $e) {
	    error_log($e);
	  }
  }
  
  /**
    * Retrieve the property of the Facebook API
    * @param string name of property
    * @return mixed property of Facebook API
    */
  public function __get($name){
    return self::$Facebook->$name;
  }
  
  /**
    * PHP 5.3.0 only
    * Usage: 
    * - FB::method(params);
    * Example:
    * - FB::getUser();
    */
  public static function __callstatic($method, $params){  	
  	try {
  		if (empty(self::$Facebook)) {
  			$this->__initInstance();
  		}
  		return call_user_func_array(array(self::$Facebook, $method), $params);
  	} catch (FacebookApiException $e) {
	    error_log($e);
	  }
  }
}
