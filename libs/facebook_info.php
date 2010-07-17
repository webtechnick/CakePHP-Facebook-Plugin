<?php
/**
  * Facebook Plugin Information
  * versioning information
  */
class FacebookInfo {
  
  public static $options = array(
      'name',
      'version',
      'author',
      'email',
      'link',
      'description',
      'license',
    );
  
  /**
    * Get the version of the Facebook Plugin
    * @return string version number
    */
  static function version(){
    return '1.7';
  }
  
  /**
    * Get the offical name of the Facebook Plugin
    * @return string plugin name
    */
  static function name(){
    return 'CakePHP Facebook Plugin';
  }
  
  /**
    * Get the name of the author
    * @return string plugin author
    */
  static function author(){
    return 'Nick Baker';
  }
  
  /**
    * Get the support email
    * @return string plugin support email
    */
  static function email(){
    return 'nick@webtechnick.com';
  }
  
  /**
    * Get the website for Facebook Plugin
    * @return string plugin link
    */
  static function link(){
    return 'http://www.webtechnick.com';
  }
  
  /**
    * Get the license for Facebook Plugin
    * @return string plugin license
    */
  static function license(){
    return 'MIT';
  }
  
  /**
    * Get the description for Facebook Plugin
    * @return string plugin description
    */
  static function description(){
    return "The purpose of the Facebook plugin is to provide a seamless way to connect your cakePHP app to everyone's favorite social networking site -- Facebook. The goal for this plugin is to not only provide extremely useful dynamic features but to also provide a complete interface to the Facebook API.";
  }
  
  /**
    * Get the available options list
    */
  static function getOptions(){
    return self::$options;
  }
  
  /**
    * Utility method to test if this is available key.
    */
  static function _isAvailable($name){
    return in_array($name, self::$options);
  }
}
?>