<?php
/**
  * Facebook.Facebook helper generates fbxml and loads javascripts
  *
  * @author Nick Baker <nick [at] webtechnick [dot] com>
  * @version since 1.7
  * @license MIT
  * @link http://www.webtechnick.com
  */
App::import('Lib', 'Facebook.FacebookInfo');
class FacebookHelper extends AppHelper {
  /**
    * Helpers to load with this helper.
    */
  var $helpers = array('Html', 'Session');
  
  /**
    * Default Facebook.Share javascript URL
    * @access private
    */
  var $__fbShareScript = 'http://static.ak.fbcdn.net/connect.php/js/FB.Share';
  
  /**
    * Get the info on this plugin
    * @param string name to retrieve (default 'version')
    * - 'name' => Plugin Name
    * - 'author' => Author Name
    * - 'email' => Support Email
    * - 'link' => Support Link
    * - 'license' => License Info
    * @return string plugin version
    */
  function info($name = 'version'){
    if(FacebookInfo::_isAvailable($name)){ 
      return FacebookInfo::$name();
    }
    else {
      return "$name is not an available option";
    }
  }

  /**
    * HTML XMLNS tag (required)
    * @return string of html header
    * @access public
    */
  function html(){
    return '<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml">';
  }
  
  /**
    * Login Button
    * $this->Facebook->init() is required for this
    * @param array of options
    * - show-faces bool Show pictures of the user's friends who have joined your application
    * - width int The width of the plugin in pixels
    * - max-rows int The maximum number of rows of profile pictures to show
    * @return string XFBML tag
    * @access public
    */
  function login($options = array()){
    return $this->__fbTag('fb:login-button', '', $options);
  }

  
  /**
    * Logout Button
    * $this->Facebook->init() is required for this
    * @param array of options
    * - redirect string to your app's logout url (default null)
    * - label string of text to use in link (default logout)
    * @return string XFBML tag for logout button
    * @access public
    */
  function logout($options = array()){
    $options = array_merge(
      array(
        'autologoutlink' => 'true', 
        'label' => 'logout'
      ), 
      $options
    );
    if(isset($options['redirect']) && $options['redirect']){
      return $this->Html->link($options['label'], '#', array('onclick' => "FB.Connect.logoutAndRedirect('{$options['redirect']}')"));
    }
    else {
      unset($options['label'], $options['escape']);
      return $this->__fbTag('fb:login-button', '', $options);
    }
  }
  
  /**
    * Share this page
    * @param string url: url to share with facebook (default current page) 
    * @param array options to pass into share
    * - style: 'button' or 'link' (default'button')
    * - label: title of text to link(default 'share')
    * - anchor: a href anchor name (default 'fb_share')
    * @return string XFBML tag along with shareJs script
    * @access public
    */
  function share($url = null, $options = array()){
  	// @todo this can be improved using the router
    if(!$url) $url = env('SERVER_NAME') . $this->here;
    $defaults = array(
      'style' => 'button',
      'label' => 'share',
      'anchor' => 'fb_share'
    );
    $options = array_merge($defaults, $options);
    
    switch($options['style']){
      case 'link': $options['type'] = 'icon_link'; break;
      default: $options['type'] = 'button'; break;
    }
    
    $retval = $this->Html->link($options['label'], 'http://www.facebook.com/sharer.php', array('share_url' => $url, 'type' => $options['type'], 'name' => $options['anchor']));
    $retval .= $this->Html->script($this->__fbShareScript);
    return $retval;
  }
  
  /**
    * HTML XMLNS tag (required)
    * @param array of options
    * @example $this->Facebook->init();
    * @return string of scriptBlock for FB.init() or error
    * @access public
    */
  function init($options = array()){
    if(Configure::read('Facebook')){
    	$appId = Configure::read('Facebook.appId');
    	$session = json_encode($this->Session->read('FB.Session'));
    	$init = '<div id="fb-root"></div>';
    	$init .=  $this->Html->scriptBlock(
      	"
		      window.fbAsyncInit = function() {
		        FB.init({
		          appId   : '{$appId}',
		          session : {$session}, // don't refetch the session when PHP already has it
		          status  : true, // check login status
		          cookie  : true, // enable cookies to allow the server to access the session
		          xfbml   : true // parse XFBML
		        });
		        // whenever the user logs in, we refresh the page
		        FB.Event.subscribe('auth.login', function() {
		          window.location.reload();
		        });
		      };
		      (function() {
		        var e = document.createElement('script');
		        e.src = document.location.protocol + '//connect.facebook.net/en_US/all.js';
		        e.async = true;
		        document.getElementById('fb-root').appendChild(e);
		      }());
      	",
      	$options
      );
      return $init;
    }
    else {
      return "<span class='error'>No Facebook configuration detected. Please add the facebook configuration file to your config folder.</span>";
    } 
  }
  
  /**
    * Generate a facebook tag
    * @param string fb:tag
    * @param string label to pass inbetween the tag
    * @param array of options as name=>value pairs to add to facebook tag attribute
    * @access private
    */
  private function __fbTag($tag, $label, $options){
    //TODO make this a little nicer, pron to errors if a value has a ' in it.
    $retval = "<$tag";
    foreach($options as $name => $value){
      if($value === false) $value = 0;
      $retval .= " " . $name . "='" . $value . "'";
    }
    $retval .= ">$label</$tag>";
    return $retval;
  }
  
}