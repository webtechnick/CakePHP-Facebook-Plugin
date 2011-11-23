<?php
/**
* Facebook.Facebook helper generates fbxml and loads javascripts
*
* @author Nick Baker <nick [at] webtechnick [dot] com>
* @CakePHP 2.0 support by Stefan Zhelyazkov <stz [at] upenn.edu>
* @version since 2.5.0
* @license MIT
* @link http://www.webtechnick.com
*/
App::uses('FacebookInfo', 'Facebook.Lib');
App::uses('AppHelper','View/Helper');
class FacebookHelper extends AppHelper {
	/**
	* Helpers to load with this helper.
	*/
	public $helpers = array('Html', 'Session');
	
	/**
	* Default Facebook.Share javascript URL
	* @access private
	*/
	public $__fbShareScript = 'http://static.ak.fbcdn.net/connect.php/js/FB.Share';
	
	/**
	* locale, settable in the constructor
	* @link http://developers.facebook.com/docs/internationalization/
	* @access public
	*/
	public $locale = null;
	
	/**
	* Loadable construct, pass in locale settings
	* Fail safe locale to 'en_US'
	*/
	public function __construct(View $View, $settings = array()){
		$this->_set($settings);
		
		if(!$this->locale){
			$this->locale = FacebookInfo::getConfig('locale');
		}
		if(!$this->locale){
			$this->locale = 'en_US';
		}
		parent::__construct($View, $settings);
	}
	
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
	public function info($name = 'version'){
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
	public function html(){
		return '<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://ogp.me/ns/fb#">';
	}
	
	/**
	* Login Button
	* $this->Facebook->init() is required for this
	* @param array of options
	* - redirect string: to your app's logout url (default null)
	* - label string: text to use in link (default logout)
	* - custom boolean: Used to create custom link instead of standart fbml. 
	    if redirect option is set this one is not required.
	* - img string: Creates fortmatted image tag. 'img' should be
		relative to /app/webroot/img/
	* - alt string: Image caption
	* - id string: Tag CSS id
	* - show-faces bool: Show pictures of the user's friends who have joined your application
	* - width int: The width of the plugin in pixels
	* - max-rows int: The maximum number of rows of profile pictures to show
	* - perms list: of permissions to ask for when logging in separated by commas (eg: 'email,read_stream,publish_stream'). (http://developers.facebook.com/docs/authentication/permissions)
	* @param string label
	* @return string XFBML tag
	* @access public
	*/
	public function login($options = array(), $label = ''){
		$options = array_merge(
			array(
				'label' => '',
				'custom' => false,
				'redirect' => false,
				'img' => false,
				'alt' => '',
				'id' => '',
				'show-faces' => true,	// fb button only
				'width' => 200,			// fb button only
				'max-rows' => 1			// fb button only
			),
			$options
		);
		if((isset($options['redirect']) && $options['redirect']) || $options['custom']){
			$options['redirect'] = Router::url($options['redirect']);
			$onclick = "login('".$options['redirect']."');";
			if($options['img']){
				$source = '/Facebook/img/'.$options['img'];
				return $this->Html->image($source, array(
				'alt' => $options['alt'],
				'id' => $options['id'],
				'url' => '#',
				'onclick' => $onclick));
			}
			else {
				return $this->Html->link($options['label'], '#', array(
					'onclick' => $onclick, 'id' => $options['id']));
			}
		}
		else {
			if(!$options['id']){ unset($options['id']); }
			unset($options['label'], $options['custom'], $options['redirect'], $options['img'], $options['alt']);
			return $this->__fbTag('fb:login-button', $label, $options);
		}
	}
	
	
	/**
	* Logout Button
	* $this->Facebook->init() is required for this
	* @param array of options
	* - redirect string: to your app's logout url (default null)
	* - label string: text to use in link (default logout)
	* - confirm string: Alert dialog which will be visible if user clicks on the button/link
	* - custom boolean: Used to create custom link instead of standart fbml. 
	    if redirect option is set this one is not required.
	* - img string: Creates fortmatted image tag. 'img' should be
		relative to /app/webroot/img/
	* - alt string: Image caption
	* - id string: Tag CSS Id
	* @param string label
	* @return string XFBML tag for logout button
	* @access public
	*/
	public function logout($options = array(), $label = ''){
		$options = array_merge(
			array(
				'label' => '',
				'custom' => false,
				'redirect' => false,
				'img' => false,
				'alt' => '',
				'id' => ''
			), 
			$options
		);
		if((isset($options['redirect']) && $options['redirect']) || $options['custom']){
			$options['redirect'] = Router::url($options['redirect']);
			$onclick = "logout('".$options['redirect']."');";
			if(isset($options['confirm'])){
				$onclick = 'if(confirm("'.$options['confirm'].'")){'.$onclick.'}';
			}
			if($options['img']){
				$source = '/Facebook/img/'.$options['img'];
				return $this->Html->image($source, array(
				'alt' => $options['alt'],
				'id' => $options['id'],
				'url' => '#',
				'onclick' => $onclick));
			}
			else {
				return $this->Html->link($options['label'], '#', array(
					'onclick' => $onclick, 'id' => $options['id']));
			}
		} else {
			$source = '/Facebook/img/facebook-logout.png';
			return $this->Html->image($source, array(
				'alt' => 'Facebook logout',
				'url' => '#',
				'id' => $options['id'],
				'onclick' => 'logout();'));
		}
	}
	
	/**
	* Unsubscribe Button - Function which creates link for disconnecting user from the specific application
	* $this->Facebook->init() is required for this
	* @param array of options
	* - redirect string to your app's logout url (default null)
	* - label string of text to use in link (default logout)
	* - confirm string Alert dialog which will be visible if user clicks on the button/link
	* @return string Link for disconnect button
	* @access public
	*/
	public function disconnect($options = array()){
		$options = array_merge(
			array(
				'label' => 'logout'
			), 
			$options
		);
		if(isset($options['redirect']) && $options['redirect']){
			$options['redirect'] = Router::url($options['redirect']);
			$response = "window.location = '{$options['redirect']}'";
		} else {
			$response = "window.location.reload();";
		}
		$onclick = "FB.api({ method: 'Auth.revokeAuthorization' }, function(response) {".$response."});";
		if(isset($options['confirm'])){
			$onclick = 'if(confirm("'.$options['confirm'].'")){'.$onclick.'}';
		}
		return $this->Html->link($options['label'], '#', array('onclick' => $onclick));
	}
	
	/**
	* Share this page
	* @param string url: url to share with facebook (default current page) 
	* @param array options to pass into share
	* - style: 'button' or 'link' (default'button')
	* - label: title of text to link(default 'share')
	* - anchor: a href anchor name (default 'fb_share')
	* - fbxml: true or false.  If true, use fb:share-button xml style instead of javascript share (default false)
	* @return string XFBML tag along with shareJs script
	* @access public
	*/
	public function share($url = null, $options = array()){
		if(empty($url)){
			$url = Router::url(null, true);
		}
		$defaults = array(
			'style' => 'button',
			'label' => 'share',
			'anchor' => 'fb_share',
			'fbxml' => false
		);
		$options = array_merge($defaults, $options);
		
		if(!$options['fbxml']){
			switch($options['style']){
			case 'link': $options['type'] = 'icon_link'; break;
				default: $options['type'] = 'button'; break;
			}
		}
		
		if($options['fbxml']){
			unset($options['fbxml']);
			$retval = $this->__fbTag('fb:share-button','',$options);
		}
		else {
			$retval = $this->Html->link($options['label'], 'http://www.facebook.com/sharer.php', array('share_url' => $url, 'type' => $options['type'], 'name' => $options['anchor']));
			$retval .= $this->Html->script($this->__fbShareScript);
		}
		
		return $retval;
	}
	
	/**
	* Profile Picture of Facebook User
	* $facebook->init() is required for this
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
	public function picture($uid = null, $options = array()){
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
	* Build a like box
	* $facebook->init() is required for this
	* @link http://developers.facebook.com/docs/reference/plugins/like-box
	* @param array of options to pass into likebox
	* - stream : 1 turns stream on, 0 turns stream off (default false)
	* - header : 1 turns header on, 0 turns logobar off (default false)
	* - width : width of the box (default 300)
	* - connections : number of connections to show (default 10)
	* - colorscheme : dark | light (default light)
	*/
	public function likebox($url = null, $options = array()){
		$options = array_merge(
			array(
				'href' => $url,
				'stream' => 'false',
				'header' => 'false',
				'width' => '300',
				'connections' => '10'
			),
			$options
		);
		return $this->__fbTag('fb:like-box', '', $options);
	}
	
	/**
	* Build a become a fan, fanbox
	* $facebook->init() is required for this
	* @param array options to pass into fanbox
	* - stream : 1 turns stream on, 0 turns stream off (default 0)
	* - connections : 1 turns connections on, 0 turns connections off (default 0)
	* - logobar : 1 turns logobar on, 0 turns logobar off (default 0)
	* - profile_id : Your Application Id (default Configure::read('Facebook.app_id')
	* @return string xfbhtml tag
	* @access public
	*/
	public function fanbox($options = array()){
		$options = array_merge(
			array(
				'profile_id' => FacebookInfo::getConfig('appId'),
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
	* $facebook->init() is required for this
	* @param array options to pass into livestream
	* - event_app_id : Your Application Id (default Configure::read('Facebook.appId')
	* - xid : Your event XID
	* - width : width of window in pixels
	* - height: height of window in pixels
	* @return string xfbhtml tag
	* @access public
	*/
	public function livestream($options = array()){
		$options = array_merge(
			array(
				'event_app_id' => FacebookInfo::getConfig('appId'),
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
	* $facebook->init() is required for this
	* @param array of options for comments
	* - numposts : number of posts to show (default 10)
	* - width : int width of comments blog (default 550)
	* @return string xfbhtml tag
	* @access public
	*/
	public function comments($options = array()){
		return $this->__fbTag('fb:comments', '', $options);
	}
	
	/**
	* Build a facebook recommendations area.
	* $facebook->init() is required for this
	* @param array of options for recommendations
	* - width : int width of object (default 300)
	* - height : int height of object (default 300)
	* - header : boolean (default true)
	* - colorscheme : light, dark (default light)
	* - font : default arial
	* - bordercolor : color of border (black, white, grey)
	* @return string xfbhtml tag
	* @access public
	*/
	public function recommendations($options = array()){
		return $this->__fbTag('fb:recommendations', '', $options);
	}
	
	/**
	* Build a facebook friendpile area.
	* $facebook->init() is required for this
	* @param array of options for recommendations
	* - numrows : int of rows object (default 1)
	* - width : int width of object (default 300)
	* @return string xfbhtml tag
	* @access public
	*/
	public function friendpile($options = array()){
		return $this->__fbTag('fb:friendpile', '', $options);
	}
	
	/**
	* Build a facebook activity feed area.
	* $facebook->init() is required for this
	* @param array of options for recommendations
	* - width : int width of object (default 300)
	* - height : int height of object (default 300)
	* - header : boolean (default true)
	* - colorscheme : light, dark (default light)
	* - font : default arial
	* - bordercolor : color of border (black, white, grey)
	* - recommendations : show recommendations default "false"
	* @return string xfbhtml tag
	* @access public
	*/
	public function activity($options = array()){
		return $this->__fbTag('fb:activity', '', $options);
	}
	
	/**
	* Build a facebook like box
	* $facebook->init() is required for this
	* @param array of options for like box
	* - href : URL to like (default same page)
	* - show_faces : boolean (default true)
	* - font : font type (arial, lucida grande, segoe ui, tahoma, trebuchet ms, verdana)
	* - layout : the layout type if the button (button_count, standard, default: standard)
	* - action : the title of the action (like or recommend, default: like)
	* - colorscheme : the look of the button (dark or light, default: light)
	* @return string xfbhtml tag
	* @access public
	*/
	public function like($options = array()){
		return $this->__fbTag('fb:like', '', $options);
	}
	
	/**
	* HTML XMLNS tag (required)
	* Facebook Auth 2.0 support
	* @param array $options
	* @example $this->Facebook->init();
	* @return string of scriptBlock for FB.init() or error
	*/
	
	public function init($options = null, $reload = true) {
		if (empty($options)) {
			$options = array();
		}
		if ($appId = FacebookInfo::getConfig('appId')) {
			$init = '<div id="fb-root"></div>';
			$init .= '<script src="//connect.facebook.net/en_US/all.js"></script>';
			$init .= $this->Html->scriptBlock("
	window.fbAsyncInit = function() {
		FB.init({
			appId      : '$appId', // App ID
			channelURL : '../../Vendor/channel.php', // Channel File
			status     : true, // check login status
			cookie     : true, // enable cookies to allow the server to access the session
			oauth      : true, // enable OAuth 2.0
			xfbml      : true  // parse XFBML
		});
		
		
		// Checks whether the user is logged in
		FB.getLoginStatus(function(response) {
			if (response.authResponse) {
				// logged in and connected user, someone you know
				// alert('You are connected');
			} else {
				// no user session available, someone you dont know
				// alert('You are disconnected');
			}
		});
			   
		FB.Event.subscribe('auth.authResponseChange', function(response) {
			if (response.authResponse) {
				// the user has just logged in
				// alert('You just logged in facebook from somewhere');
			} else {
				// the user has just logged out
				// alert('You just logged out from faceboook');
			}
		});
		
		// Other javascript code goes here!

	};

	// logs the user in the application and facebook
	function login(redirection){
		FB.login(function (response) {
			if(response.authResponse) {
				// user is logged in
				// console.log('Welcome!');
				if(redirection != null && redirection != ''){
					top.location.href = redirection;
				}
			} else {
				// user could not log in
				console.log('User cancelled login or did not fully authorize.');
			}
		}, {scope: 'email'});
	}

	// logs the user out of the application and facebook
	function logout(redirection){
		FB.logout(function(response) {
			// user is logged out
			// redirection if any
			if(redirection != null && redirection != ''){
				top.location.href = redirection;
			}
		});
	}

	// Load the SDK Asynchronously
	(function() {
	var e = document.createElement('script'); e.async = true;
	e.src = document.location.protocol 
	+ '//connect.facebook.net/en_US/all.js';
	document.getElementById('fb-root').appendChild(e);
	}());");
			return $init;
		} else {
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