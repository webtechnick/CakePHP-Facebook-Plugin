Facebook Plugin
==================
by Nick Baker (nick@webtechnick.com)
version 2.0.1
http://www.webtechnick.com
license: MIT

Changelog
==================
1.0 Initial release alpha
1.1 Added API feature
1.2 Initial release beta
1.2.1 Minor Bug fixes and tests
1.3 Added FacebookInfo class for easy reference to plugin details anywhere.
1.4.0 Added FacebookApi library. Useful for access to FacebookApi from anywhere.
1.5.0 Added facebook Permissions prompts
1.5.1 Facebook Connect can accept settings like all the core components in cake 1.3
1.5.2 Added Facebook.Connect user method to retrieve user data much like Auth.
1.5.3 Added Facebook status() function to helper.
1.5.4: Fixed a bug in Facebook.Connect that would white screen if session was in an unstable state coming in.
1.5.5: Bug fix for helper use only with init.  Was dependant on Connect Component, but not anymore
1.6: Facebook.Connect will now update a user table if it exists but a facebook_id is not found.
1.6.1: Facebook::share will now property set the beginning url if no url is passed in
1.7.0: Updated to the latest version of the official Facebook PHP SDK and made everything more future-proof (Theaxiom / https://www.kinspir.com)
1.7.1: Fixed broken tests from update 1.7.0
2.0.0: Feature updates:
         - FacebookHelper::loader() is no longer needed and is now deprecated
         - Users now created if auth is detected and no user is present with logged in facebook_id
         - FacebookHelper::logout redirect option now allows array based cakephp urls
       New Features:
         - FacebookHelper::like() creates like button with various options
         - FacebookHelper::activity() created activity feed with various options
         - FacebookHelper::friendpile() created friendpile widget with various options
         - FacebookHelper::recommendations() create recommendations widget with various options
2.0.1: New Setting ConnectComponent::createUser boolean.  If set to true (default) upon a successful
       facebook login, and the facebook_id is not found in the User table, the component will attempt
       to create a new user from introspection on the Auth component.  Turn this feature off by passing
       in 'createUser' => false when setting up the Connect component.

Docs
==================
http://projects.webtechnick.com/docs/facebook

Demo
==================
http://facebook.webtechnick.com

Get the Plugin
==================
SVN:  http://svn.github.com/webtechnick/CakePHP-Facebook-Plugin
GIT:  git@github.com:webtechnick/CakePHP-Facebook-Plugin.git
TAR:  http://projects.webtechnick.com/facebook.tar.gz

Description
==================
The purpose of the Facebook plugin is to provide a seamless way to connect your cakePHP app to everyone's favorite social networking site -- Facebook. The goal for this plugin is to not only provide extremely useful dynamic features but to also provide a complete interface to the Facebook API.


Feature list:
==================
- Full featured authentication via facebook. Facebook Authentication will work with or without a user login system in place.  Works seemlessly with your already built user authentication via AuthComponent - OR - it can work as your primary authentication system.

- Create dynamic customizable facebook content with extreme ease.
-- Share  (let your users share what they find on your site)
-- Like  (let your users like what they find on your site)
-- Login/Logout (facebook users can login and logout with a single click .. no registration required)
-- Activity (allow users to show your applications and friends activity)
-- Friend Pile (display your applications friends)
-- Recommendations (display recommended urls based on the current page)
-- Fan Boxes (allow users to become a fan of your application)
-- Profile Pictures (display a logged in user's profile picture)
-- Live Streams (create dynamic live stream events through facebook and give access through your site)
-- Comments (connect with your uses by allowing them to comment on any part of your site with facebook comments)
-- Status (display a user's status)

- Access to Full Facebook API anywhere in your app.  Built custom content directly from the Facebook API with the built in access to the full Facebook API


Configuration:
==================
To get started you'll need to first install the facebook plugin into your app/plugins/facebook directory

Once installed, if you wish to use any other features *other* than the share button you'll need to get an api_key and secret for your application.

Create an app from facebook at this url: http://wiki.developers.facebook.com/index.php/Connect/Setting_Up_Your_Site

Once you generate an api_key and secret you'll need to create a file in your app/config directory called facebook.php with a $config of these variables. You can find an example of what you'll need and how it is laid out in /facebook/config/facebook.php.example

app/config/facebook.php
$config = array(
  'Facebook' => array(
    'appId' => 'YOUR_APP_ID',
    'apiKey' => 'YOUR_API_KEY',
    'secret' => 'YOUR_SECRET',
    'cookie' => true,
  )
);

Usage:
==================
You can use all or some of the Facebook plugin as you see fit.
In its very basic, you can use just the helper to create simple share buttons.

var $helpers = array('Facebook.Facebook');

If all you want to use is the share feature of the Facebook plugin simply add Facebook.Facebook to your helpers array and then load $facebook->share($url_to_share); ($url_to_share is defaulted to your current page).  Nothing else is required for the Facebook share feature.

Hoever, to use the more advanced features you'll need to prepare your page a little to handle the fbxml tags.  

Edit your Layout to take advantage of advanced facebook features
==================
Replace <html> with <?= $facebook->html() ?>

In your default.ctp it's highly suggest you replace your <html> tag with <?= $facebook->html() ?>  This is required for some of the facebook features to work in IE.

At the bottom of the page include <?= $facebook->init(); ?> To load the facebook javascript api to scan your page for fbxml and replace them with various dynamic content.


Authentication (Facebook Connect):
==================
You will first need to update your facebook application with the connect url of your application's url.  This is done on the facebook application settings. http://www.facebook.com/developers/apps.php

Once that's complete you'll need to alter your users table (or whatever table your Auth component uses) and add a new field -> facebook_id.  Included with this plugin is a schema file you can run to create the required table.

Now all you need to do is add the Facebook.Connect component to your app_controller.

var $components = array('Auth', 'Facebook.Connect');

That's it.  You're now ready to accept facebook authentication.

Login/Logout buttons:
<?= $facebook->login() ?> Creates a login button
<?= $facebook->logout() ?> Creates a logout button

In your view, just add <?= $facebook->login(); ?> to create a login button.
To log a user out simply use <?= $facebook->logout(); ?>. to create a logout button.  Each button has multiple options, review the API to see all available options

If you already have an authentication system setup, the logout step will need to also log out the user from your authentication system.   Simply pass in a redirect to $facebook->logout() to your system's logout authentication action.

Example:
<?= $facebook->logout(array('redirect' => 'users/logout')); ?>
<?= $facebook->logout(array('redirect' => array('controller' => 'users', 'action' => 'logout'))); ?>

This will log out of the facebook authentication and then redirect to your authentication logout for you to finish the logout.

Facebook Api
==================
You can access the Facebook Api from anywhere in your app.
You'll need to include the Api first

App::import('Lib', 'Facebook.FB');

Then you can instanciate it or, if you're running PHP 5.3.x you can make static calls on it.

<= PHP 5.2.x
$Facebook = new FB();
$Facebook->api('/me');

PHP 5.3.x
FB::api('/me');


Read the Docs:
==================
I encourage you to read the documentation and API for this plugin to see all the features and options for each feature.  The API is here:

http://projects.webtechnick.com/docs/facebook/


Enjoy!
