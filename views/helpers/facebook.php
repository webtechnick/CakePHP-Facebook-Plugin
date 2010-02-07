<?php
class FacebookHelper extends AppHelper {
  
  var $helpers = array('Html');
  var $locale = 'en_US';
  
  var $_fbShareScript = 'http://static.ak.fbcdn.net/connect.php/js/FB.Share';
  var $_fbFeatureLoaderScript = 'http://static.ak.connect.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php/';
  var $_fXdReceiver = 'facebook/receiver/xd_receiver.htm';

  /**
  * TODO make this nicer
  */
  function html($options = array()){
    return '<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml">';
  }
  
  /**
    * Create a facebook login button
    * $facebook->loader() and $facebook->init() are required for this
    * @param array of options
    */
  function login($options = array()){
    $options = array_merge(array('onlogin' => 'window.location.reload();'), $options);
    return $this->__fbTag('fb:login-button', '', $options);
  }
  
  
  /**
    * Build a share link/button for the current page
    * url: url to share with facebook (default current page) 
    * options array.
    * - style: 'button' or 'link' (default'button')
    * - label: title of text to link(default 'share')
    * - anchor: a href anchor name (default 'fb_share')
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
    * - profile_id : Your Application Id (default Configure::read('Facebook.application_id')
    */
  function fanbox($options = array()){
    $options = array_merge(
      array(
        'profile_id' => Configure::read('Facebook.application_id'),
        'stream' => 0, 
        'logobar' => 0, 
        'connections' => 0,
      ),
      $options
    );
    return $this->__fbTag('fb:fan', '', $options);
  }
  
  /**
    * Build a livestream window to your live stream app on facebook
    * $facebook->loader() and $facebook->init() are required for this
    * @param array options to pass into livestream
    * - event_app_id : Your Application Id (default Configure::read('Facebook.application_id')
    * - xid : Your event XID
    * - width : width of window in pixels
    * - height: height of window in pixels
    */
  function livestream($options = array()){
    $options = array_merge(
      array(
        'event_app_id' => Configure::read('Facebook.application_id'),
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
    */
  function comments($options = array()){
    return $this->__fbTag('fb:comments', '', $options);
  }
  
  /**
    * Required somewhere on your page if you want to to add some of the other features other than just 'share'
    */
  function init($options = array()){
    $this->api_key = Configure::read('Facebook.api_key');
    if($this->api_key){
      return $this->Html->scriptBlock("FB.init('$this->api_key','$this->_fXdReceiver')", $options); 
    }
    else {
      return "<span class='error'>No Facebook.api_key detected.  Please add Configure::write('Facebook.api_key', YOUR_API_KEY_HERE) somewhere in your application.</span>";
    } 
  }
  
  /**
    * Required for almost all features except for 'share'
    */
  function loader($options = array()){
    $options = array_merge($options, array('locale' => $this->locale));
    return $this->Html->script($this->_fbFeatureLoaderScript . $options['locale'], $options);
  }
  
  /**
    * TODO make this a little nicer, pron to errors if a value has a ' in it.
    */
  private function __fbTag($tag, $label, $options){
    $retval = "<$tag";
    foreach($options as $name => $value){
      $retval .= " " . $name . "='" . $value . "'";
    }
    $retval .= ">$label</$tag>";
    return $retval;
  }
  
}
?>