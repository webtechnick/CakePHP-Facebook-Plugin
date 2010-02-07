<?php
App::import('Vendor', 'Facebook.facebook/php/facebook');
class ConnectComponent extends Object {
  function initialize($controller){
    $this->Controller = $controller;
    $this->Facebook = new Facebook(Configure::read('Facebook.api_key', Configure::read('Facebook.secret')));
  }
}
?>