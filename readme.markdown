# Facebook Plugin
* Author:  Nick Baker (nick@webtechnick.com)
* CakePHP 2.0 support by Stefan Zhelyazkov (stz@seas.upenn.edu)
* version 3.0.1
* http://www.webtechnick.com
* license: MIT

The purpose of the Facebook plugin is to provide a seamless way to connect your cakePHP app to everyone's favorite social networking site -- Facebook. The goal for this plugin is to not only provide extremely useful dynamic features but to also provide a complete interface to the Facebook API.

# Changelog
* 1.0 Initial release alpha
* 1.1 Added API feature
* 1.2 Initial release beta
* 1.2.1 Minor Bug fixes and tests
* 1.3 Added FacebookInfo class for easy reference to plugin details anywhere.
* 1.4.0 Added FacebookApi library. Useful for access to FacebookApi from anywhere.
* 1.5.0 Added facebook Permissions prompts
* 1.5.1 Facebook Connect can accept settings like all the core components in cake 1.3
* 1.5.2 Added Facebook.Connect user method to retrieve user data much like Auth.
* 1.5.3 Added Facebook status() function to helper.
* 1.5.4: Fixed a bug in Facebook.Connect that would white screen if session was in an unstable state coming in.
* 1.5.5: Bug fix for helper use only with init.  Was dependant on Connect Component, but not anymore
* 1.6: Facebook.Connect will now update a user table if it exists but a facebook_id is not found.
* 1.6.1: Facebook::share will now property set the beginning url if no url is passed in
* 1.7.0: Updated to the latest version of the official Facebook PHP SDK and made everything more future-proof (Theaxiom / https://www.kinspir.com)
* 1.7.1: Fixed broken tests from update 1.7.0
* 2.0.0: Feature updates:
	* FacebookHelper::loader() is no longer needed and is now deprecated
	* Users now created if auth is detected and no user is present with logged in facebook_id
	* FacebookHelper::logout redirect option now allows array based cakephp urls
	* New Features:
		* FacebookHelper::like() creates like button with various options
		* FacebookHelper::activity() created activity feed with various options
		* FacebookHelper::friendpile() created friendpile widget with various options
		* FacebookHelper::recommendations() create recommendations widget with various options
* 2.0.1: New Setting ConnectComponent::createUser boolean.  If set to true (default) upon a successful facebook login, and the facebook_id is not found in the User table, the component will attempt to create a new user from introspection on the Auth component.  Turn this feature off by passing in 'createUser' => false when setting up the Connect component.
* 2.0.2: New helper method getConfig() available within the FacebookInfo library.  Use this to pull in facebook configurations instead of trying to rely on loaded configuration files from other sources.
* 2.0.3: Updated Facebook::share to allow fbxml option instead of defaulting to not use fbxml.  fbxml => true is required if the page loads facebook->init();
* 2.1.0: Improved preformance and new Facebook::disconnect function.
* 2.1.1: various minor bug fixes.
* 2.2.0: Updated Facebook PHP SDK
* 2.3.0: Added new Facebook::likebox to replace Facebook::fanbox()
* 2.3.1: Security Update: facebook user creation now generates random passwords.
* 2.4.0: Adding three Authentication callbacks: beforeFacebookSave, beforeFacebookLogin, and afterFacebookLogin
* 2.4.1: Adding custom locale to plugin.
* 2.4.2: minor bug fixes.
* 2.4.3: Fixed duplicate user entry when User primaryKey is not 'id'.
* 2.5.0: Optimizations and minor fixes (special thanks to <https://github.com/bar>)
  * New callback feature for FacebookHelper::init() (page refresh is still default).
  * Nicer FacebookHelper::share() now uses Router instead of environment and $this->here
  * Optimization, Moving __syncFacebookUser to after Controller->beforeFilter() so $noAuth can be changed in the beforeFilter if need be.
* 3.0.0: Upgraded to Facebook PHP SDK v 3.1.1
* 3.0.1: Added CakePHP 2.0 support
	* the AuthComponent in 2.0 has been redesigned significantly, making the guesswork done by the FacebookPlugin much harder to acheive
	* As such, you now have to set the model in which you want the Facebook plugin to interact with if you want User/Auth integration via database
	* You must set this when defining the Facebook.Connect component
	* If you do not set a 'model' key, integration with your Auth Model will not happen automatically.

			//Example AppController setup
			public $components = array('Session',
				'Auth' => array(
					'authenticate' => array(
						'Form' => array(
							'fields' => array('username' => 'email')
						)
					),
					'authorize' => 'Controller'
				),
				'Facebook.Connect' => array('model' => 'User')
			);

# About Plugin
* Blog: <http://www.webtechnick.com/blogs/view/229/CakePHP_Facebook_Plugin_Auth_Facebook_and_more>
* Documentation: <http://projects.webtechnick.com/docs/facebook>
* Live Demo: <http://facebook.webtechnick.com> 
* Screencast: <http://tv.cakephp.org/video/webtechnick/2011/01/12/nick_baker_--_facebook_integration_with_cakephp> 


# Feature List
* Full featured authentication via facebook. Facebook Authentication will work with or without a user login system in place.  Works seemlessly with your already built user authentication via AuthComponent - OR - it can work as your primary authentication system.
* Create dynamic customizable facebook content with extreme ease.
	* Share  (let your users share what they find on your site)
	* Like  (let your users like what they find on your site)
	* Login/Logout (facebook users can login and logout with a single click .. no registration required)
	* Activity (allow users to show your applications and friends activity)
	* Friend Pile (display your applications friends)
	* Recommendations (display recommended urls based on the current page)
	* Fan Boxes (allow users to become a fan of your application)
	* Profile Pictures (display a logged in user's profile picture)
	* Live Streams (create dynamic live stream events through facebook and give access through your site)
	* Comments (connect with your uses by allowing them to comment on any part of your site with facebook comments)
	* Status (display a user's status)
* Access to Full Facebook API anywhere in your app.  Built custom content directly from the Facebook API with the built in access to the full Facebook API


# Install and Setup
* First clone the repository into your `app/Plugin/Facebook` directory

		git clone git://github.com/webtechnick/CakePHP-Facebook-Plugin.git app/Plugin/Facebook


* Load the plugin in your `app/Config/bootstrap.php` file:

		//app/Config/bootstrap.php
		CakePlugin::load('Facebook');

### Once installed, if you wish to use any other features *other* than the share button you'll need to get an api_key and secret for your application.
* Create an app from facebook at this url: <http://www.facebook.com/developers/createapp.php>
* Once you generate an api_key and secret you'll need to create a file `app/Config/facebook.php` You can find an example of what you'll need and how it is laid out in `/Facebook/Config/facebook.php.example`

		//app/Config/facebook.php
		$config = array(
			'Facebook' => array(
				'appId'  => 'YOUR_APP_ID',
				'apiKey' => 'YOUR_API_KEY',
				'secret' => 'YOUR_SECRET',
				'cookie' => true,
				'locale' => 'en_US',
			)
		);


# Usage
You can use all or some of the Facebook plugin as you see fit.
At the very least you will probably want to use the Facebook Helper

	public $helpers = array('Facebook.Facebook');

If all you want to use is the share feature of the Facebook plugin you're all done.

	$this->Facebook->share('http://www.example.com/url_to_share'); //(default is the current page).

Nothing else is required for the Facebook share feature. Hoever, to use the more advanced features you'll need to prepare your page a little to handle the fbxml tags.  

## Edit your Layout to take advantage of *advanced* facebook features
1. In your layout it's highly suggest you replace your `<html>` tag with `<?php echo $this->Facebook->html(); ?>`  This is required for some of the facebook features to work in IE.
2. At the bottom of the page include `<?php echo $this->Facebook->init(); ?>` To load the facebook javascript api to scan your page for fbxml and replace them with various dynamic content.

### Example layout
	<?php echo $this->Facebook->html(); ?>
		<head>
			<title><?php echo $title_for_layout ?></title>
		</head>
		<body>
			<?php echo $content_for_layout; ?>
		</body>
		<?php echo $this->Facebook->init(); ?>
	</html>


# Authentication (Facebook Connect/Graph System):
Despite the name, the Facebook Connect component takes immediate advantage of the new powerful Facebook Graph API <http://developers.facebook.com/docs/api>

To use this feature you will first need to update your facebook application with the connect url of your application's url.  This is done on the facebook application settings. <http://www.facebook.com/developers/apps.php>
Now all you need to do is add the `Facebook.Connect` component to your app_controller.

	public $components = array('Facebook.Connect');

That's it.  You're now ready to accept facebook authentication.

## Login/Logout buttons
Creates a login button:
	<?php echo $this->Facebook->login() ?>

Create a login button that asks for extended permissions (<http://developers.facebook.com/docs/authentication/permissions>)
	
	<?php echo $facebook->login(array('perms' => 'email,publish_stream')); ?>

Create a logout button:

	<?php echo $this->Facebook->logout() ?>

Each button has multiple options, review the API to see all available options
<http://projects.webtechnick.com/docs/facebook/default/FacebookHelper.html>

## CakePHP Auth + Facebook.Connect
Facebook.Connect will play nice with a variety of Authentication systesm.  It has seamless integration with CakePHP AuthComponent.

	public $components = array('Auth', 'Facebook.Connect');
	
To integrate with CakePHP Auth, you'll need to alter your users table (or whatever table your Auth component uses) and add a new field -> `facebook_id`.

	ALTER TABLE `users` ADD `facebook_id` BIGINT(20) UNSIGNED NOT NULL

Since you already have an authentication system, the logout step will need to also log out the user from your authentication system.
You do this by passing a redirect to `$facebook->logout()` to your system's logout authentication action.

	<?php echo $this->Facebook->logout(array('redirect' => 'users/logout')); ?>
	<?php echo $this->Facebook->logout(array('redirect' => array('controller' => 'users', 'action' => 'logout'))); ?>

This will log out of the facebook authentication and then redirect to your authentication logout for you to finish the logout.

### Facebook Auth Callbacks

There are three callbacks available to use, each are defined in the controller and are optional to use.

* `beforeFacebookSave` handle the user to save into the users table.  If returned false, creation is haulted.

		//Add an email field to be saved along with creation.
		function beforeFacebookSave(){
			$this->Connect->authUser['User']['email'] = $this->Connect->user('email');
			return true; //Must return true or will not save.
		}
	
* `beforeFacebookLogin` Handle the user before logging the user into Auth.

		function beforeFacebookLogin($user){
			//Logic to happen before a facebook login
		}
	
* `afterFacebookLogin` Handle any needed functionality right after a successful Auth Login

		function afterFacebookLogin(){
			//Logic to happen after successful facebook login.
			$this->redirect('/custom_facebook_redirect');
		}

# Advanced Helper Feature Examples
	<?php echo $this->Facebook->comments(); ?>
	<?php echo $this->Facebook->picture($facebook_id); ?>
	<?php echo $this->Facebook->recommendations(); ?>
	<?php echo $this->Facebook->like(); ?>
	<?php echo $this->Facebook->livestream(); ?>
	<?php echo $this->Facebook->activity(); ?>
	<?php echo $this->Facebook->friendpile(); ?>


# Facebook API
You can access the Facebook Api from anywhere in your app.
You'll need to include the Api first

	App::uses('FB', 'Facebook.Lib');

Then you can instanciate it or, if you're running PHP 5.3.x you can make static calls on it.

PHP version 5.2.x

		$Facebook = new FB();
		$Facebook->api('/me');

PHP 5.3.x

		FB::api('/me');
	
	
# Internationalization
You can set the locale of the plugin through the helper declaration or through the `config/facebook.php` configuration file (see top of document).

	public $helpers = array('Facebook.Facebook' => array('locale' => 'en_US'));
	
Facebook locales: <http://developers.facebook.com/docs/internationalization/>


# Read the Docs
I encourage you to read the documentation and API for this plugin to see all the features and options for each feature.  The API is here:
<http://projects.webtechnick.com/docs/facebook/>
