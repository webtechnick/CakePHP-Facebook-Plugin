<?php
/* Facebook Test cases generated on: 2011-11-22 21:31:37 : 1322022697*/
App::uses('FacebookHelper', 'Facebook.View/Helper');
App::uses('HtmlHelper', 'View/Helper');
App::uses('SessionHelper', 'View/Helper');
App::uses('View','View');
/**
* FacebookHelper Test Case
*
*/
class FacebookHelperTestCase extends CakeTestCase {
	/**
	* setUp method
	*
	* @return void
	*/
	public function setUp() {
		parent::setUp();
		@$View = new View();
		$this->Facebook = new FacebookHelper($View);
		$this->Facebook->Html = new HtmlHelper($View);
		$this->Facebook->Html->request = new CakeRequest(null, false);
		$this->Facebook->Html->request->webroot = '';
		Configure::delete('Facebook.appId');
    Configure::write('Facebook.appId', '12345');
	}
	
	/**
	* tearDown method
	*
	* @return void
	*/
	public function tearDown() {
		unset($this->Facebook);
		parent::tearDown();
	}
	
	public function testLocal(){
		@$View = new View();
		$this->Facebook->__construct($View, array('locale' => 'es_LA'));
		$this->assertEqual('es_LA', $this->Facebook->locale);
	}
	
	public function testDisconnect(){
		$results = $this->Facebook->disconnect();
		$this->assertEqual('<a href="#" onclick="FB.api({ method: &#039;Auth.revokeAuthorization&#039; }, function(response) {window.location.reload();});">logout</a>', $results);
		
		$results = $this->Facebook->disconnect(array('label' => 'disconnect'));
		$this->assertEqual('<a href="#" onclick="FB.api({ method: &#039;Auth.revokeAuthorization&#039; }, function(response) {window.location.reload();});">disconnect</a>', $results);
		
		$results = $this->Facebook->disconnect(array('redirect' => array('controller' => 'users', 'action' => 'logout')));
		$this->assertEqual('<a href="#" onclick="FB.api({ method: &#039;Auth.revokeAuthorization&#039; }, function(response) {window.location = &#039;/users/logout&#039;});">logout</a>', $results);
		
		$results = $this->Facebook->disconnect(array('confirm' => 'Are you sure?'));
		$this->assertEqual('<a href="#" onclick="if(confirm(&quot;Are you sure?&quot;)){FB.api({ method: &#039;Auth.revokeAuthorization&#039; }, function(response) {window.location.reload();});}">logout</a>', $results);
	}
	
	public function testInfo(){
		$error_text = " is not an available option";
		$results = $this->Facebook->info();
		$this->assertNotEqual('version' . $error_text, $results);
		
		$results = $this->Facebook->info('author');
		$this->assertNotEqual('author' . $error_text, $results);
		
		$results = $this->Facebook->info('email');
		$this->assertNotEqual('email' . $error_text, $results);
		
		$results = $this->Facebook->info('name');
		$this->assertNotEqual('name' . $error_text, $results);
		
		$results = $this->Facebook->info('description');
		$this->assertNotEqual('description' . $error_text, $results);
		
		$results = $this->Facebook->info('license');
		$this->assertNotEqual('license' . $error_text, $results);
		
		$results = $this->Facebook->info('notvalid');
		$this->assertEqual('notvalid'. $error_text, $results);
	}
	
	public function testHtml(){
		$results = $this->Facebook->html();
		$this->assertEqual('<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://ogp.me/ns/fb#">', $results);
	}
	
	public function testLogin(){
		$results = $this->Facebook->login();
		$this->assertEqual("<fb:login-button show-faces='1' width='200' max-rows='1'></fb:login-button>", $results);	
	}
	
	public function testLogout(){
		$results = $this->Facebook->logout();
		$this->assertEqual('<a href="#"><img src="/Facebook/img/facebook-logout.png" alt="Facebook logout" id="" onclick="logout();" /></a>', $results);
	}
	
	public function testShare(){
    $results = $this->Facebook->share('http://www.example.com/some_where');
    $this->assertEqual('<a href="http://www.facebook.com/sharer.php" share_url="http://www.example.com/some_where" type="button" name="fb_share">share</a><script type="text/javascript" src="http://static.ak.fbcdn.net/connect.php/js/FB.Share"></script>', $results);
    
    $results = $this->Facebook->share();
    $this->assertEqual('<a href="http://www.facebook.com/sharer.php" share_url="http://localhost/" type="button" name="fb_share">share</a>', $results);
    
    //assert the script isn't loaded again on next call
    $results = $this->Facebook->share('not_here');
    $this->assertEqual('<a href="http://www.facebook.com/sharer.php" share_url="not_here" type="button" name="fb_share">share</a>', $results);
    
    $results = $this->Facebook->share('not_here', array('style' => 'link'));
    $this->assertEqual('<a href="http://www.facebook.com/sharer.php" share_url="not_here" type="icon_link" name="fb_share">share</a>', $results);
    
    $results = $this->Facebook->share('not_here', array('style' => 'link', 'label' => 'Click'));
    $this->assertEqual('<a href="http://www.facebook.com/sharer.php" share_url="not_here" type="icon_link" name="fb_share">Click</a>', $results);
  }
  
  public function testFanBox(){
    $results = $this->Facebook->fanbox();
    $this->assertEqual("<fb:fan profile_id='12345' stream='0' logobar='0' connections='0'></fb:fan>", $results);
    
    $results = $this->Facebook->fanbox(array('stream' => true));
    $this->assertEqual("<fb:fan profile_id='12345' stream='1' logobar='0' connections='0'></fb:fan>", $results);
    
    $results = $this->Facebook->fanbox(array('connections' => true));
    $this->assertEqual("<fb:fan profile_id='12345' stream='0' logobar='0' connections='1'></fb:fan>", $results);
    
    $results = $this->Facebook->fanbox(array('logobar' => true));
    $this->assertEqual("<fb:fan profile_id='12345' stream='0' logobar='1' connections='0'></fb:fan>", $results);
    
    $results = $this->Facebook->fanbox(array('profile_id' => '789'));
    $this->assertEqual("<fb:fan profile_id='789' stream='0' logobar='0' connections='0'></fb:fan>", $results);
  }
  
  public function testPicture(){
    $results = $this->Facebook->picture('12345');
    $this->assertEqual("<fb:profile-pic uid='12345' facebook-logo='1'></fb:profile-pic>", $results);
    
    $results = $this->Facebook->picture('12345', array('size' => 'small'));
    $this->assertEqual("<fb:profile-pic uid='12345' facebook-logo='1' size='small'></fb:profile-pic>", $results);
    
    $results = $this->Facebook->picture('12345', array('width' => '150', 'height' => '150'));
    $this->assertEqual("<fb:profile-pic uid='12345' facebook-logo='1' width='150' height='150'></fb:profile-pic>", $results);
    
    $results = $this->Facebook->picture('12345', array('facebook-logo' => false));
    $this->assertEqual("<fb:profile-pic uid='12345' facebook-logo='0'></fb:profile-pic>", $results);
  }
  
  public function testLike(){
    $results = $this->Facebook->like();
    $this->assertEqual('<fb:like></fb:like>', $results);
    
    $results = $this->Facebook->like(array('layout' => 'button_count'));
    $this->assertEqual("<fb:like layout='button_count'></fb:like>", $results);
  }
  
  public function testActivity(){
    $results = $this->Facebook->activity();
    $this->assertEqual('<fb:activity></fb:activity>', $results);
    
    $results = $this->Facebook->activity(array('colorscheme' => 'dark'));
    $this->assertEqual("<fb:activity colorscheme='dark'></fb:activity>", $results);
  }
  
  public function testFriendPile(){
    $results = $this->Facebook->friendpile();
    $this->assertEqual('<fb:friendpile></fb:friendpile>', $results);
    
    $results = $this->Facebook->friendpile(array('colorscheme' => 'dark'));
    $this->assertEqual("<fb:friendpile colorscheme='dark'></fb:friendpile>", $results);
  }
  
  public function testRecommendantions(){
    $results = $this->Facebook->recommendations();
    $this->assertEqual('<fb:recommendations></fb:recommendations>', $results);
    
    $results = $this->Facebook->recommendations(array('colorscheme' => 'dark'));
    $this->assertEqual("<fb:recommendations colorscheme='dark'></fb:recommendations>", $results);
  }
  
  public function testLivestream(){
    $results = $this->Facebook->livestream();
    $this->assertEqual("<fb:live-stream event_app_id='12345' xid='YOUR_EVENT_XID' width='300' height='500'></fb:live-stream>", $results);
    
    $results = $this->Facebook->livestream(array('width' => '200', 'height' => '300'));
    $this->assertEqual("<fb:live-stream event_app_id='12345' xid='YOUR_EVENT_XID' width='200' height='300'></fb:live-stream>", $results);
  }
  
  public function testComments(){
    $results = $this->Facebook->comments();
    $this->assertEqual("<fb:comments></fb:comments>",$results);
  }
  
  public function testInit(){
  	$results = $this->Facebook->init();  	
  	$expected = <<<EOD
<div id="fb-root"></div><script src="//connect.facebook.net/en_US/all.js"></script><script type="text/javascript">
//<![CDATA[

	window.fbAsyncInit = function() {
		FB.init({
			appId      : '12345', // App ID
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
	}());
//]]>
</script>
EOD;
		$this->assertEqual($expected, $results);
  }
}
?>
