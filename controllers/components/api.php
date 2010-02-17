<?php
/**
  * Facebook.Api component used to create an instanciated facebook API available to the user
  *
  * @author Nick Baker <nick [at] webtechnick [dot] com>
  * @version 1.1
  * @link http://www.webtechnick.com
  * @license MIT
  */
App::import('Lib', 'Facebook.FacebookApi');
class ApiComponent extends Object {
  
  /**
    * Allow direct access to the facebook API
    * @link http://wiki.developers.facebook.com/index.php/Main_Page
    * @access public
    */
  var $FB = null;
  
  /**
    * Load the API into a class property and allow access to it.
    */
  function initialize($controller){
    $this->FB = new FacebookApi();
  }
  
  
}
?>