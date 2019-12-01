=== Awesome Tracker ===
Contributors: moreiradev
Donate link: https://paypal.me/devmoreira/
Tags: analytics, tracking
Requires at least: 5.0
Tested up to: 5.3
Stable tag: 1.1.0
Requires PHP: 5.6
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Track your users' navigation server-side. Also, you can track your WordPress API calls.

== Description ==

Awesome Tracker is a simple yet powerful plugin that tracks every user visit to any of your WordPress pages server-side. It captures users' navigation before serving the actual page, which allows you to know exactly what pages were served to what users of your WordPress site and when.

Awesome Tracker will track:
* Visits to your pages/posts
* Visits to your archive pages
* 404 requests
* Search requests
* API calls

= API REST compatible! =

Set your relevant API Routes to track. Especially useful for headless WordPress installations.


== Installation ==

Easy peasy! Download it, activate it, and you will have a new menu item called `Awesome Tracker`.

But for those of you who want to know even more:

1. Upload the plugin files to the `/wp-content/plugins/awesome-tracker` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the new `Awesome Tracker` menu item
4. Set the API Routes you want to track, if any!

Tracking is activated and running out of the box except for API calls. You have to configure API Routes for them to start being tracked.


== Frequently Asked Questions ==

= Users' API calls to my WordPress are not being tracked! =

You have first to set the API Routes you want to track. You can configure them on the `Awesome Tracker > API Routes` menu

= The plugin is cool, but I could use some more features =

Yay, you are cool as well! If you miss some added functionality, you can suggest it in the support section or in the [Github repository](https://github.com/moreira-dev/awesome-tracker "Github repository")


== Changelog ==

= 1.1.0 =
* Show country info for the visit! This info is populated once every hour
* Allow to filter records by country
* Settings page!
** You can set the amount of time you want to keep your records
** Option to clean the records database
* i18n support
* Translated to Spanish!!

= 1.0 =
* Track users' visits and API calls

== Upgrade Notice ==

= 1.1.0 =
* Now you can filter your records by country!
* You can set the amount of time you want to keep your records
* Translated to Spanish!!


== Screenshots ==

1. Records listing
2. API Routes configuration
