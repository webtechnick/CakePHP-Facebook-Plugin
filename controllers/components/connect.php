<?php
App::import('Vendor', 'Facebook.facebook/php/facebook');
Configure::load('facebook');
class ConnectComponent extends Object {
  
  var $facebookUser = null;
  
  function initialize($controller){
    $this->Controller = $controller;
    $this->Facebook = new Facebook(Configure::read('Facebook.api_key'), Configure::read('Facebook.secret'));
    
    $this->facebookUser = $this->Facebook->get_loggedin_user();
    
    if($this->facebookUser){
      //Do something with Auth
    }
    debug($this->facebookUser);
  }
  
  
}
?>