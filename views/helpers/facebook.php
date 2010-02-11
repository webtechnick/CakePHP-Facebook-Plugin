<?php
/**
  * Facebook.Facebook helper generates fbxml and loads javascripts
  *
  * @author Nick Baker <nick [at] webtechnick [dot] com>
  * @version 1.3
  * @license MIT
  * @link http://www.webtechnick.com
  */
App::import('Lib', 'Facebook.FacebookInfo');
class FacebookHelper extends AppHelper {
  /**
    * Helpers to load with this helper.
    */
  var $helpers = array('Html');
  
  /**
    * Default Locale
    * @access public
    */
  var $locale = 'en_US';
  
  /**
    * Default Facebook.Share javascript URL
    * @access protected
    */
  var $_fbShareScript = 'http://static.ak.fbcdn.net/connect.php/js/FB.Share';
  
  /**
    * Default Facebook Loader javascript URL
    * @access protected
    */
  var $_fbFeatureLoaderScript = 'http://static.ak.connect.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php/';
  
  /**
    * Default xd_receiver.htm location (already installed with plugin)
    * @access protected
    */
  var $_fXdReceiver = 'facebook/receiver/xd_receiver.htm';
  
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
    * html header tag for xmlns of facebook.  This is necessary for IE
    * @param array of options
    * @return string of html header
    * @access public
    */
  function html(){
    return '<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml">';
  }
  
  /**
    * Create a facebook login button
    * $facebook->loader() and $facebook->init() are required for this
    * @param array of options
    * - size string specifing the button size, small, medium, large, or xlarge (default medium)
    * - conditions string indicuates wiether the button is visible or hidden
    * - background string specifies the image background, white, dark, light (default light)
    * @return string xfbhtml tag
    * @access public
    */
  function login($options = array()){
    $options = array_merge(
      array('onlogin' => 'window.location.reload();'), 
      $options
    );
    return $this->__fbTag('fb:login-button', '', $options);
  }
  
  /**
    * Create Logout facebook button
    * $facebook->loader() and $facebook->init() are required for this
    * @param array of options
    * - redirect string to your app's logout url (default null)
    * - label string of text to use in link (default logout)
    * - size string specifing the button size, small, medium, large, or xlarge (default medium)
    * - conditions string indicuates wiether the button is visible or hidden
    * - background string specifies the image background, white, dark, light (default light)
    * @return string xfbhtml tag for logout button
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
    * Build a share link/button for the current page
    * @param string url: url to share with facebook (default current page) 
    * @param array options to pass into share
    * - style: 'button' or 'link' (default'button')
    * - label: title of text to link(default 'share')
    * - anchor: a href anchor name (default 'fb_share')
    * @return string xfbhtml tag along with shareJs script
    * @access public
    */
  function share($url = null, $options = array()){
    if(!$url) $url = $this->here;
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
    $retval .= $this->Html->script($this->_fbShareScript);
    return $retval;
  }
  
  /**
    * Build a become a fan, fanbox
    * $facebook->loader() and $facebook->init() are required for this
    * @param array options to pass into fanbox
    * - stream : 1 turns stream on, 0 turns stream off (default 0)
    * - connections : 1 turns connections on, 0 turns connections off (default 0)
    * - logobar : 1 turns logobar on, 0 turns logobar off (default 0)
    * - profile_id : Your Application Id (default Configure::read('Facebook.app_id')
    * @return string xfbhtml tag
    * @access public
    */
  function fanbox($options = array()){
    $options = array_merge(
      array(
        'profile_id' => Configure::read('Facebook.app_id'),
        'stream' => 0, 
        'logobar' => 0, 
        'connections' => 0,
      ),
      $options
    );
    return $this->__fbTag('fb:fan', '', $options);
  }
  
  /**
    * Profile Picture of Facebook User
    * $facebook->loader() and $facebook->init() are required for this
    * @param int facebook user id.
    * @param array options to pass into pic
    * - uid : user_id to view profile picture
    * - size : size of the picture represented as a string. 'thumb','small','normal','square' (default thumb)
    * - facebook-logo: (default true)
    * - width: width of the picture in pixels 
    * - height: height of the picture in pixels 
    * @return string fb tag for profile picture or empty string if uid is not present
    * @access public
    */
  function picture($uid = null, $options = array()){
    $options = array_merge(
      array(
        'uid' => $uid,
        'facebook-logo' => 1,
      ),
      $options
    );
    if($options['uid']){
      return $this->__fbTag('fb:profile-pic', '', $options);
    }
    else {
      return "";
    }
  }
  
  /**
    * Build a livestream window to your live stream app on facebook
    * $facebook->loader() and $facebook->init() are required for this
    * @param array options to pass into livestream
    * - event_app_id : Your Application Id (default Configure::read('Facebook.app_id')
    * - xid : Your event XID
    * - width : width of window in pixels
    * - height: height of window in pixels
    * @return string xfbhtml tag
    * @access public
    */
  function livestream($options = array()){
    $options = array_merge(
      array(
        'event_app_id' => Configure::read('Facebook.app_id'),
        'xid' => 'YOUR_EVENT_XID',
        'width' => '300',
        'height' => '500',
      ),
      $options
    );
    return $this->__fbTag('fb:live-stream','',$options);
  }
  
  /**
    * Build a facebook comments area.
    * $facebook->loader() and $facebook->init() are required for this
    * @param array of options for comments
    * @return string xfbhtml tag
    * @access public
    */
  function comments($options = array()){
    return $this->__fbTag('fb:comments', '', $options);
  }
  
  /**
    * Required at the bottom of your page if you plan to use any feature other than 'share'
    * @param array of options
    * @return string of scriptBlock for FB.init() or error
    * @access public
    */
  function init($options = array()){
    $this->api_key = Configure::read('Facebook.api_key');
    if($this->api_key){
      return $this->Html->scriptBlock("FB.init('$this->api_key','$this->webroot$this->_fXdReceiver')", $options); 
    }
    else {
      return "<span class='error'>No Facebook.api_key detected.  Please add Configure::write('Facebook.api_key', YOUR_API_KEY_HERE) somewhere in your application.</span>";
    } 
  }
  
  /**
    * Required for all features except for 'share'
    * @param array of options
    * - locale : locale for facebook helper (default en_US
    * - any_other_html_script option : Use any other options valid for Html->script
    * @return string of html script
    * @access public
    */
  function loader($options = array()){
    $locale = array_merge(array('locale' => $this->locale), $options);
    unset($options['locale']);
    return $this->Html->script($this->_fbFeatureLoaderScript . $locale['locale'], $options);
  }
  
  /**
    * Generate a facebook tag for me
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
?>