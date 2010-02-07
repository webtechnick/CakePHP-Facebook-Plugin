<?php
class FacebookHelper extends AppHelper {
  
  var $helpers = array('Html');
  var $locale = 'en_US';
  
  var $_fbFeatureLoaderUrl = 'http://static.ak.connect.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php/';
  var $_fXdReceiver = 'facebook/receiver/xd_receiver.htm';

  /**
  * TODO make this nicer
  */
  function html($options = array()){
    return '<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml">';
  }
  
  function loader($options = array()){
    $options = array_merge($options, array('locale' => $this->locale));
    return $this->Html->script($this->_fbFeatureLoaderUrl . $options['locale'], $options);
  }
  
  function login(){
    return "<fb:login-button onlogin='facebook_onlogin();'></fb:login-button>";
  }
  
  function init($options = array()){
    $this->api_key = Configure::read('Facebook.api_key');
    if($this->api_key){
      return $this->Html->scriptBlock("FB.init('$this->api_key','$this->_fXdReceiver')", $options); 
    }
    else {
      return "<span class='error'>No Facebook.api_key detected.  Please add Configure::write('Facebook.api_key', YOUR_API_KEY_HERE) somewhere in your application.</span>";
    } 
  }
  
}
?>