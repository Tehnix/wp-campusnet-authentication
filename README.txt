=== CampusNet Authentication ===
Contributors: Tehnix
Tags: authentication, university, campusnet
Requires at least: 3.0
Tested up to: 3.6.1
Stable tag: trunk
License: BDS 2-clause
License URI: http://opensource.org/licenses/BSD-2-Clause

This plugin is for universities that uses CampusNet for their infrastructure and allows them to administer who has access to their wp site.

== Description ==

Please be aware that this plugin uses your universities CampusNet system by contacting it directly. It also uses your universities authentication system. Usernames and passwords are stored as Wordpress users, so, they are just as secure as a normal Wordpress user would be.

This plugin is for universities that uses CampusNet for their infrastructure. 

Use your universities CampusNet login via their API. It requires a user to be a member of a specific CampusNet group (defineable) to get access, that way, the administration of users is kept in one place (or, use group/elementid 0 to not use a group). For each user logging in, it makes sure that a wordpress user is created/exists, so if the CampuseNet API ever breaks, you can disable it and the users can login using the username and password they used earlier. 

NOTE: since it tries to detect if a user is a student by checking if the first letter is s, you cannot have normal users with usernames that start with s.

== Installation ==

1. Upload the folder `wp-campusnet-authentication` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Create an app token at your universities CampusNet page by requesting API credentials
4. Fill out details in the settings menu item "CampusNet Authentication"

== Frequently Asked Questions ==

= Can I choose not to have it limited to the users of a group =

You can set the element id to 0, and then it will ignore the group restriction.

= I cannot login with my username that starts with s =

This is because student ids start with s, and therefore all usernames starting with s are seen as potential ids and checked against the CampusNet authentication system.

== Support ==

https://github.com/Tehnix/wp-campusnet-authentication

== Changelog ==

= 0.1 =
 * Authentication against the CampusNet API is implemented
 * It creates a wordpress user with the users information in the database upon first login