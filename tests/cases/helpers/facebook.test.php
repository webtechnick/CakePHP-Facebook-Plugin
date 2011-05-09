<?php
App::import('Helper', 'Facebook.Facebook');
App::import('Helper', 'Html');
App::import('Helper', 'Session');
Mock::generate('SessionHelper');
class FacebookHelperTest extends CakeTestCase {
  var $Facebook = null;

  function startTest(){
    $this->Facebook = new FacebookHelper();
    $this->Facebook->Html = new HtmlHelper();
    $this->Facebook->Session = new MockSessionHelper();
    Configure::delete('Facebook.appId');
    Configure::write('Facebook.appId', '12345');
  }
  
  function testLocale(){
  	$this->Facebook->__construct(array('locale' => 'es_LA'));
  	$this->assertEqual('es_LA', $this->Facebook->locale);
  }
  
  function testDisconnect(){
    $results = $this->Facebook->disconnect();
    $this->assertEqual('<a href="#" onclick="FB.api({ method: &#039;Auth.revokeAuthorization&#039; }, function(response) {window.location.reload();});">logout</a>', $results);
    
    $results = $this->Facebook->disconnect(array('label' => 'disconnect'));
    $this->assertEqual('<a href="#" onclick="FB.api({ method: &#039;Auth.revokeAuthorization&#039; }, function(response) {window.location.reload();});">disconnect</a>', $results);
    
    $results = $this->Facebook->disconnect(array('redirect' => array('controller' => 'users', 'action' => 'logout')));
    $this->assertEqual('<a href="#" onclick="FB.api({ method: &#039;Auth.revokeAuthorization&#039; }, function(response) {window.location = &#039;/users/logout&#039;});">logout</a>', $results);
    
    $results = $this->Facebook->disconnect(array('confirm' => 'Are you sure?'));
    $this->assertEqual('<a href="#" onclick="if(confirm(&quot;Are you sure?&quot;)){FB.api({ method: &#039;Auth.revokeAuthorization&#039; }, function(response) {window.location.reload();});}">logout</a>', $results);
  }
  
  function testInfo(){
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
  
  function testHtml(){
    $results = $this->Facebook->html();
    $this->assertTrue('<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml">', $results);
  }
  
  function testLogin(){
    $results = $this->Facebook->login();
    $this->assertEqual("<fb:login-button></fb:login-button>", $results);
    
    $results = $this->Facebook->login(array('size' => 'small'));
    $this->assertEqual("<fb:login-button size='small'></fb:login-button>", $results);
    
    $results = $this->Facebook->login(array('size' => 'large'));
    $this->assertEqual("<fb:login-button size='large'></fb:login-button>", $results);
    
    $results = $this->Facebook->login(array('background' => 'dark'));
    $this->assertEqual("<fb:login-button background='dark'></fb:login-button>", $results);
    
    $results = $this->Facebook->login(array('onlogin' => 'blah'));
    $this->assertEqual("<fb:login-button onlogin='blah'></fb:login-button>", $results);
  }
  
  function testLogout(){
    $results = $this->Facebook->logout();
    $this->assertEqual("<fb:login-button autologoutlink='true'></fb:login-button>", $results);
    
    $results = $this->Facebook->logout(array('redirect' => 'users/logout'));
    $this->assertEqual('<a href="#" onclick="FB.logout(function(response){window.location = &#039;/users/logout&#039;});">logout</a>', $results);
    
    $results = $this->Facebook->logout(array('redirect' => 'users/logout', 'label' => 'Sign Out'));
    $this->assertEqual('<a href="#" onclick="FB.logout(function(response){window.location = &#039;/users/logout&#039;});">Sign Out</a>', $results);
    
    $results = $this->Facebook->logout(array('redirect' => array('controller' => 'users', 'action' => 'logout'), 'label' => 'Sign Out'));
    $this->assertEqual('<a href="#" onclick="FB.logout(function(response){window.location = &#039;/users/logout&#039;});">Sign Out</a>', $results);
  }
  
  function testShare(){
    $results = $this->Facebook->share('http://www.example.com/some_where');
    $this->assertEqual('<a href="http://www.facebook.com/sharer.php" share_url="http://www.example.com/some_where" type="button" name="fb_share">share</a><script type="text/javascript" src="http://static.ak.fbcdn.net/connect.php/js/FB.Share"></script>', $results);
    
    $results = $this->Facebook->share();
    $this->assertEqual('<a href="http://www.facebook.com/sharer.php" share_url="/" type="button" name="fb_share">share</a>', $results);
    
    //assert the script isn't loaded again on next call
    $results = $this->Facebook->share('not_here');
    $this->assertEqual('<a href="http://www.facebook.com/sharer.php" share_url="not_here" type="button" name="fb_share">share</a>', $results);
    
    $results = $this->Facebook->share('not_here', array('style' => 'link'));
    $this->assertEqual('<a href="http://www.facebook.com/sharer.php" share_url="not_here" type="icon_link" name="fb_share">share</a>', $results);
    
    $results = $this->Facebook->share('not_here', array('style' => 'link', 'label' => 'Click'));
    $this->assertEqual('<a href="http://www.facebook.com/sharer.php" share_url="not_here" type="icon_link" name="fb_share">Click</a>', $results);
  }
  
  function testFanBox(){
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
  
  function testPicture(){
    $results = $this->Facebook->picture('12345');
    $this->assertEqual("<fb:profile-pic uid='12345' facebook-logo='1'></fb:profile-pic>", $results);
    
    $results = $this->Facebook->picture('12345', array('size' => 'small'));
    $this->assertEqual("<fb:profile-pic uid='12345' facebook-logo='1' size='small'></fb:profile-pic>", $results);
    
    $results = $this->Facebook->picture('12345', array('width' => '150', 'height' => '150'));
    $this->assertEqual("<fb:profile-pic uid='12345' facebook-logo='1' width='150' height='150'></fb:profile-pic>", $results);
    
    $results = $this->Facebook->picture('12345', array('facebook-logo' => false));
    $this->assertEqual("<fb:profile-pic uid='12345' facebook-logo='0'></fb:profile-pic>", $results);
  }
  
  function testLike(){
    $results = $this->Facebook->like();
    $this->assertEqual('<fb:like></fb:like>', $results);
    
    $results = $this->Facebook->like(array('layout' => 'button_count'));
    $this->assertEqual("<fb:like layout='button_count'></fb:like>", $results);
  }
  
  function testActivity(){
    $results = $this->Facebook->activity();
    $this->assertEqual('<fb:activity></fb:activity>', $results);
    
    $results = $this->Facebook->activity(array('colorscheme' => 'dark'));
    $this->assertEqual("<fb:activity colorscheme='dark'></fb:activity>", $results);
  }
  
  function testFriendPile(){
    $results = $this->Facebook->friendpile();
    $this->assertEqual('<fb:friendpile></fb:friendpile>', $results);
    
    $results = $this->Facebook->friendpile(array('colorscheme' => 'dark'));
    $this->assertEqual("<fb:friendpile colorscheme='dark'></fb:friendpile>", $results);
  }
  
  function testRecommendantions(){
    $results = $this->Facebook->recommendations();
    $this->assertEqual('<fb:recommendations></fb:recommendations>', $results);
    
    $results = $this->Facebook->recommendations(array('colorscheme' => 'dark'));
    $this->assertEqual("<fb:recommendations colorscheme='dark'></fb:recommendations>", $results);
  }
  
  function testLivestream(){
    $results = $this->Facebook->livestream();
    $this->assertEqual("<fb:live-stream event_app_id='12345' xid='YOUR_EVENT_XID' width='300' height='500'></fb:live-stream>", $results);
    
    $results = $this->Facebook->livestream(array('width' => '200', 'height' => '300'));
    $this->assertEqual("<fb:live-stream event_app_id='12345' xid='YOUR_EVENT_XID' width='200' height='300'></fb:live-stream>", $results);
  }
  
  function testComments(){
    $results = $this->Facebook->comments();
    $this->assertEqual("<fb:comments></fb:comments>",$results);
  }
  
  function testInit(){
    $this->Facebook->Session->setReturnValue('read', '4567');
    $locale = $this->Facebook->locale;
		$this->Facebook->locale = 'en_US';

		$results = $this->Facebook->init();
		$expected = "<div id=\"fb-root\"></div><script type=\"text/javascript\">
//<![CDATA[
window.fbAsyncInit = function() {
	FB.init({
		appId : '12345',
		session : \"4567\", // don't refetch the session when PHP already has it
		status : true, // check login status
		cookie : true, // enable cookies to allow the server to access the session
		xfbml : true // parse XFBML
	});
	FB.Event.subscribe('auth.login',function(){window.location.reload()});
};
(function() {
	var e = document.createElement('script');
	e.src = document.location.protocol + '//connect.facebook.net/en_US/all.js';
	e.async = true;
	document.getElementById('fb-root').appendChild(e);
}());
//]]>
</script>";
		$this->assertEqual($expected, $results);
  }
  
  function endTest(){
    unset($this->Facebook);
    FacebookInfo::$configs = null;
    Configure::delete('Facebook');
  }
}
?>