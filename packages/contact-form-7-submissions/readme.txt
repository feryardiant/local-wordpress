=== Contact Form 7 Submissions ===
Contributors: feryardiant
Tags: contact form 7, cf7, submissions, database, leads, capture, form storage, contact form, cf7 database
Requires at least: 6.8
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 0.0.0
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Never lose a lead again. Save, manage, and convert every Contact Form 7 submission directly in your WordPress dashboard.

== Description ==

Stop relying on unreliable email notifications. **Contact Form 7 Submissions** acts as your ultimate safety net, capturing every single submission and storing it securely in your WordPress database.

Whether it's a server error, a spam filter, or a full inbox, you can rest easy knowing your data is safe and accessible right from your dashboard.

= Smart Lead Generation =
Unlike basic storage plugins, this plugin allows you to map submissions to WordPress users. Automatically register your leads as "Subscribers" and capture their Phone Numbers, making it the perfect bridge between your forms and your CRM.

= Key Features =
*   **Zero-Loss Capture:** Every submission is recorded before the email is even sent.
*   **Smart Author Mapping:** Automatically create or update WordPress users from form entries.
*   **Custom Field Mapping:** Map your form tags to specific submission properties (Subject, Name, Email, Phone).
*   **Read/Unread Status:** Keep track of which leads you've already handled.
*   **Per-Form Control:** Choose exactly which forms should record data and which shouldn't.
*   **Developer Friendly:** Lightweight architecture with local SMTP support for dev environments.

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Edit your Contact Form 7 form and look for the new "Submissions" tab.
4. Enable "Record" and map your fields.

== Frequently Asked Questions ==

= Does this work with any CF7 form? =
Yes! You can configure the submission settings for each form individually.

= Where are the submissions stored? =
Submissions are stored as a private Custom Post Type called `form-submissions`, ensuring they are indexed and secure without bloating your options table.

= Can I export the data? =
In the current version (0.0.0), you can manage them via the dashboard. CSV Export is a planned feature for future updates.

== Changelog ==

= 0.0.0 =
* Initial release.
