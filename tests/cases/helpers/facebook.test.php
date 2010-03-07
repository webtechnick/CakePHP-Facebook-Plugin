<?php
App::import('Helper', 'Facebook.Facebook');
App::import('Helper', 'Html');
class FacebookHelperTest extends CakeTestCase {
  var $Facebook = null;

  function startTest(){
    $this->Facebook = new FacebookHelper();
    $this->Facebook->Html = new HtmlHelper();
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
    $this->assertEqual("<fb:login-button onlogin='window.location.reload();'></fb:login-button>", $results);
    
    $results = $this->Facebook->login(array('size' => 'small'));
    $this->assertEqual("<fb:login-button onlogin='window.location.reload();' size='small'></fb:login-button>", $results);
    
    $results = $this->Facebook->login(array('size' => 'large'));
    $this->assertEqual("<fb:login-button onlogin='window.location.reload();' size='large'></fb:login-button>", $results);
    
    $results = $this->Facebook->login(array('background' => 'dark'));
    $this->assertEqual("<fb:login-button onlogin='window.location.reload();' background='dark'></fb:login-button>", $results);
    
    $results = $this->Facebook->login(array('onlogin' => 'blah'));
    $this->assertEqual("<fb:login-button onlogin='blah'></fb:login-button>", $results);
  }
  
  function testLogout(){
    $results = $this->Facebook->logout();
    $this->assertEqual("<fb:login-button autologoutlink='true'></fb:login-button>", $results);
    
    $results = $this->Facebook->logout(array('redirect' => 'users/logout'));
    $this->assertEqual('<a href="#" onclick="FB.Connect.logoutAndRedirect(&#039;users/logout&#039;)">logout</a>', $results);
    
    $results = $this->Facebook->logout(array('redirect' => 'users/logout', 'label' => 'Sign Out'));
    $this->assertEqual('<a href="#" onclick="FB.Connect.logoutAndRedirect(&#039;users/logout&#039;)">Sign Out</a>', $results);
  }
  
  function testShare(){
    $this->Facebook->here = 'some_where';
    
    $results = $this->Facebook->share();
    $this->assertEqual('<a href="http://www.facebook.com/sharer.php" share_url="some_where" type="button" name="fb_share">share</a><script type="text/javascript" src="http://static.ak.fbcdn.net/connect.php/js/FB.Share"></script>', $results);
    
    //assert the script isn't loaded again on next call
    $results = $this->Facebook->share('not_here');
    $this->assertEqual('<a href="http://www.facebook.com/sharer.php" share_url="not_here" type="button" name="fb_share">share</a>', $results);
    
    $results = $this->Facebook->share('not_here', array('style' => 'link'));
    $this->assertEqual('<a href="http://www.facebook.com/sharer.php" share_url="not_here" type="icon_link" name="fb_share">share</a>', $results);
    
    $results = $this->Facebook->share('not_here', array('style' => 'link', 'label' => 'Click'));
    $this->assertEqual('<a href="http://www.facebook.com/sharer.php" share_url="not_here" type="icon_link" name="fb_share">Click</a>', $results);
  }
  
  function testFanBox(){
    Configure::write('Facebook.app_id', '12345');
    
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
  
  function testLivestream(){
    Configure::write('Facebook.app_id', '12345');
    
    $results = $this->Facebook->livestream();
    $this->assertEqual("<fb:live-stream event_app_id='12345' xid='YOUR_EVENT_XID' width='300' height='500'></fb:live-stream>", $results);
    
    $results = $this->Facebook->livestream(array('width' => '200', 'height' => '300'));
    $this->assertEqual("<fb:live-stream event_app_id='12345' xid='YOUR_EVENT_XID' width='200' height='300'></fb:live-stream>", $results);
  }
  
  function testComments(){
    $results = $this->Facebook->comments();
    $this->assertEqual("<fb:comments></fb:comments>",$results);
  }
  
  function testPromptPermission(){
    $results = $this->Facebook->promptPermission('email');
    $this->assertEqual("<fb:prompt-permission perms='email'></fb:prompt-permission>", $results);
  }
  
  function testStatus(){
    $results = $this->Facebook->status('12345');
    $this->assertEqual("<fb:user-status uid='12345' linked='true'></fb:user-status>", $results);
  }
  
  function testInit(){
    Configure::write('Facebook.api_key', 'KEY');
    Configure::write('Facebook.secret', 'SECRET');
    $results = $this->Facebook->init();
    $expected = "<script type=\"text/javascript\">
//<![CDATA[
FB.init('KEY','facebook/receiver/xd_receiver.htm')
//]]>
</script>";
    $this->assertEqual($expected, $results);
    
    $results = $this->Facebook->init(array('perms' => 'email'));
    $expected = "<script type=\"text/javascript\">
//<![CDATA[
FB.init('KEY','facebook/receiver/xd_receiver.htm', {permsToRequestOnConnect : \"email\",})
//]]>
</script>";
    $this->assertEqual($expected, $results);
  }
  
  function testGetApiKey(){
    $this->Facebook->api_key = 'KEYSET';
    Configure::write('Facebook.api_key', 'Ignore');
    $results = $this->Facebook->__getApiKey();
    $this->assertEqual('KEYSET', $results);
    
    $this->Facebook->api_key = null;
    Configure::write('Facebook.api_key', 'KEYSET');
    $results = $this->Facebook->__getApiKey();
    $this->assertEqual('KEYSET', $results);
  }
  
  function testLoader(){
    $results = $this->Facebook->loader();
    $this->assertEqual('<script type="text/javascript" src="http://static.ak.connect.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php/en_US"></script>', $results);
    
    $results = $this->Facebook->loader(array('locale' => 'eu_FR'));
    $this->assertEqual('<script type="text/javascript" src="http://static.ak.connect.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php/eu_FR"></script>', $results);
  }
  
  function endTest(){
    unset($this->Facebook);
  }
}
?>