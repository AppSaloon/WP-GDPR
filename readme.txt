=== WP GDPR ===
Contributors: Mieke Nijs, Sebastian Kurzynowski, AppSaloon
Tags: Personal data, GDPR, European, regulation, data
Requires at least: 4.6.10
Tested up to: 4.9.2
Stable tag: 1.1.6
Requires PHP: 5.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin wil create a page where users can request access to their personal data, stored on your website. They can update their data, download their data or ask for a removal.

The plugin gives you advice on how to be compliant with GDPR and witch actions you need to take in order to be compliant.


== Description ==
The plugin will create a page where users can request access to their personal data, stored on your website.
In the backend you'll get an overview of the requests users send and you can see which plugins collect personal data and need a 'ask for approval' checkbox.

In a first stage users who ask to view their personal data will get an email with a unique url on which they can view their comments and ask for a removal per comment.
When they ask for a removal, the admin has the ability to delete the comment through the wp-gdpr backend.
All emails will be sent automatically.

In the second stage they can view, update and download their personal data or ask for a removal and this for WP Comments and Contact Form 7 CFDB7.

In the third stage they can view, update and download their personal data or ask for a removal and this for Gravity Forms, Mailchimp, Woocommerce, The events calendar and Events manager.


== Installation ==
1. Upload the plugin files to the /wp-content/plugins, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the ‘Plugins’ screen in WordPress
3. ‘WP GDPR’ will be created to view the requests in the backend
4. The page 'GDPR – Request personal data' will be created. This page displays the form where visitors can submit their request.


== Screenshots ==
1. WP-GDPR backend - overview of requests
2. WP-GDPR frontend - form where visitors can enter their email and ask to view there personal data
3. WP-GDPR frontend - form succes message

== Frequently Asked Questions ==

== Changelog ==
Version 1.1.0
	- Add name and email field to comments list
	- Let users update their name and email
	- Add download button to comments list
	- Make it possible for the admin to choose between delete comment or make comment anonymous

Version 1.1.1
    - Add update_comments.js


Version 1.1.2
    - Update page template comments overview page
    - Add checkbox when data is requested
    - Update front-end translation
    - Add translation PL

Version 1.1.3
    - Add admin css
    - Add gdpr-translation.php file


Version 1.1.4
    - Update typing errors

Version 1.1.5
    - Delete develop code

Version 1.1.6
    - add .pot file
    - add german translation