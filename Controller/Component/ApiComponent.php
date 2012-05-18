<?php
/**
  * Facebook.Api component used to create an instanciated facebook API available to the user
  *
  * @author Nick Baker <nick [at] webtechnick [dot] com>
  * @version 1.1
  * @link http://www.webtechnick.com
  * @license MIT
  */
App::uses('FacebookApi', 'Facebook.Lib');
class ApiComponent extends Object {
  
  /**
    * Allow direct access to the facebook API
    * @link http://wiki.developers.facebook.com/index.php/Main_Page
    * @access public
    */
  public $FB = null;
  
  /**
    * Load the API into a class property and allow access to it.
    */
  public function initialize($controller){
    $this->FB = new FacebookApi();
  }
  
  
}
?>