<?php
/**
  * Facebook.Api Library used to create an instanciated facebook API available to the user
  *
  * @author Nick Baker <nick [at] webtechnick [dot] com>
  * @version 1.5
  * @link http://www.webtechnick.com
  * @license MIT
  */
App::uses('Facebook.Facebook/src/facebook', 'Vendor');
Configure::load('facebook');
class FacebookApi {

  /**
    * Facebook Api
    */
  public static $Facebook = null;

  /**
    * Forward any call to the Facebook API
    * @param string method name
    * @param mixed params passed into method
    * @return mixed return value of result from Facebook API
    */
  public function __call($method, $params){
    self::buildFacebook();
    return call_user_func_array(array(self::$Facebook, $method), $params);
  }

  /**
    * Retrieve the property of the facebookApi
    * @param string name of property
    * @return mixed property of facebookApi
    */
  public function __get($name){
    self::buildFacebook();
    return self::$Facebook->$name;
  }

  /**
    * PHP 5.3.0 only
    * Usage:
    * - FacebookApi::method(params);
    * Example:
    * - FacebookApi::get_loggedin_user();
    * - FacebookApi::require_login('myaccount');
    */
  public static function __callstatic($method, $params){
    self::buildFacebook();
    return call_user_func_array(array(self::$Facebook, $method), $params);
  }

  /**
    * Builds the facebook API if we need it
    */
  public static function buildFacebook(){
    if(!self::$Facebook){
      self::$Facebook = new Facebook(array(
        'appId' => Configure::read('Facebook.appId'),
        'secret' => Configure::read('Facebook.secret'),
      ));
    }
  }
}
?>