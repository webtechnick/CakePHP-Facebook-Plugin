<?php
/**
  * Facebook.Api Library used to create an instanciated facebook API available to the user
  *
  * @author Nick Baker <nick [at] webtechnick [dot] com>
  * @version 1.1
  * @link http://www.webtechnick.com
  * @license MIT
  */
App::import('Vendor', 'Facebook.facebook/php/facebook');
Configure::load('facebook');
class FacebookApi {

  /**
    * Facebook Variable
    */
  public static $Facebook = null;
  
  /**
    * Forward any call to the Facebook API
    */
  public function __call($method, $params){
    self::buildFacebook();
    return self::$Facebook->$method($params);
  }
  
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
    return self::$Facebook->$method($params);
  }
  
  public static function buildFacebook(){
    if(!self::$Facebook){
      self::$Facebook = new Facebook(Configure::read('Facebook.api_key'), Configure::read('Facebook.secret'));
    }
  }
}
?>