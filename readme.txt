=== Kudos Donations - Easy donations and payments with Mollie ===
Contributors: iseardmedia
Tags: donation, mollie, payment, ideal, credit card
Requires at least: 5.5
Tested up to: 5.6
Requires PHP: 7.1
Stable tag: 2.3.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Add a donation button to any page on your website. Easy & fast setup. Works with Mollie payments.

== Description ==

= A modern and easy to use donation plugin =

*Kudos Donations* allows you to add a donate button anywhere on your website. Once a user clicks this button they will be greeted with a pop-up window where they can enter their details and how much they would like to donate.

= Features and highlights =

* Smart and modern design.
* Can add as many different buttons as you like.
* Connect with Mollie for secure payments by credit card, iDEAL and [many others](https://www.mollie.com/payments).
* Toggle Automated email receipts.
* Use custom SMTP email settings.
* Gutenberg block or shortcode to place your button virtually anywhere.
* Customize the donation form, button and thank you message.
* Choose a colour scheme.
* View and manage individual transactions, subscriptions, donors and campaigns from the admin menu.

= Easy to use =
Just enter your API key from Mollie, then add one or more buttons anywhere on you website. You can add a button using either the provided *Kudos Button* block or with a shortcode. Please see the FAQ below for shortcode use.

Need a Mollie account? Visit their website to [get started](https://www.mollie.com/).

= Campaigns =
Create campaigns to easily group and track donations made by your donors. Each campaign can have a unique configuration which can be changed from the settings page and automatically applied to all your buttons.

= Recurring Payments =
Allow donors to create a donation subscription to support your organization on a regular basis. Kudos Donations will automatically create the subscription and take payments from Mollie. You can also disable this and use only one-off payments if you prefer.

= Source =
The Git repository for this plugin can be found [here](https://gitlab.iseard.media/michael/kudos-donations).

== Installation ==

1. Install using the WordPress plugin control panel or manually download the plugin and upload the *kudos-donations* folder to the /wp-content/plugins/ directory
1. Activate the plugin through the *Plugins* menu in WordPress.
1. Go to the 'Kudos Settings' menu in the dashboard and follow the instructions to get started.

== Frequently Asked Questions ==

= Is Kudos Donations free? =

Yes, Kudos Donations is a free open-source plugin.

= Which payment methods are supported? =

This plugin uses Mollie as a payment vendor. Mollie allows you to pay using a wide range of payment methods such as iDeal, Credit Card and PayPal. For a full list of payment methods please check out [this link](https://www.mollie.com/en/payments).

= How much does Mollie cost? =

Mollie registration is free and you only pay a small fee per transaction. See [here](https://www.mollie.com/pricing) for details.

= Can anyone use Mollie? =

Mollie is available to anyone with a registered company.

= Can I use Kudos Donations in my country? =

You can use this plugin in any country that is supported by Mollie. For a complete list please see [here](https://help.mollie.com/hc/articles/115002116105-Can-I-use-Mollies-services-in-my-country-)

= How do I use the shortcode? =

For general information on how to use shortcodes, please visit [this page](https://codex.wordpress.org/shortcode).

To get started add the Kudos Donations shortcode:

~~~
[kudos]
~~~

Here is a list of the shortcode attributes:

* *button_label* = The label to display for the button.
* *campaign_id* = Id of the campaign to use for this button.

e.g:

~~~~
[kudos button_label="Donate now" campaign_id="default"]
~~~~

== Screenshots ==

1. Donation form
2. Kudos Donations button block
3. Settings page
4. Automated email receipt

== Changelog ==

= 2.3.0 =
* Reworked campaigns, greatly simplifying shortcodes and configuration
* Improved settings page
* Switch to REST api to create transactions
* Added welcome guide when settings page first visited
* Added "Help" tab to settings page with useful links
* Fixed issue with email used in test mode not working in live
* Fixed issue with email when "From address" left blank

= 2.2.0 =
* Add ability to change donation type (subscription, one-off or both) to each button
* Add ability to export/import settings
* Terms and conditions checkbox not shown if no URL provided in settings
* Remove hook for clearing log as this did not work consistently
* Fix transactions link on campaigns page
* Various text/translation fixes

= 2.1.1 =
* Fix missing Dutch translations
* Remove unnecessary option to disable Action Scheduler

= 2.1.0 =
* Add option to select 'both' for amount type
* Workaround to fix missing translations from translate.wordpress.org
* Log is now cleared every midnight
* Fix issues with payments over 999
* Fix email address not validating correctly
* Fix various jQuery deprecations
* Fix twig cache issue by clearing cache on activation

= 2.0.8 =
* Add 'Recreate database' action to debug menu
* Add confirmation pop-up to debug actions
* Update libraries
* Minor bug fixes

= 2.0.7 =
* Campaign label no longer defaults to page/post title
* Improve look of fixed amount buttons (now limited to 4)
* Fix REST_API error for mollie/admin in WordPress 5.6
* Fix some visual changes introduced in Twenty Twenty-One
* Text changes
* Minor bug fixes

= 2.0.6 =
* Fix unknown index errors on tables
* Fix 'Sync campaign labels' action adding blank campaigns
* Update TailwindCSS to 2.0.1
* Update Dutch translations

= 2.0.5 =
* Fixed error deleting donors
* Fix slashes appearing in address
* Add select column for table search
* Add 'Last Donation' column to campaign table
* Add debug option to sync campaigns
* Add more Dutch translations
* Add more hooks
* Use object cache for database queries
* Improve debug logging

= 2.0.4 =
* Added campaigns table
* New campaign block settings allows you to select previous campaigns or add new one

= 2.0.3 =
* Fix modal header not showing correct text
* Fix translations
* Fix debug actions not redirecting to correct tab

= 2.0.2 =
* Add ability to search by campaign in transactions table
* Update Dutch translation

= 2.0.1 =
* Add ability to clear twig cache from the debug menu

= 2.0.0 =
* *Plugin completely rewritten*
* Significantly reduced size and speed
* Brand new settings page built on React
* Ability for customers to create subscriptions
* Ability to switch between open and fixed donation amounts
* Action scheduler integration for quicker payments/emails

= 1.1.0 =
* *Added* - Email receipts - You can now configure email server settings and enable automatic email receipts
* *Added* - Invoices - PDFs now generated for each successful transaction and are available from the transactions table
* *Added* - Donor search bar to transactions
* *Fixed* - Input checks for API Key type

= 1.0.2 =
* *Added* - transactions table now shows record count per filter
* *Added* - return message now shows currency symbol
* *Fixed* - if log file cannot be written this no longer prevents plugin from working
* *Fixed* - export now only exports transaction in current view (all/live/test)
* *Fixed* - incorrect record count on transaction table

= 1.0.1 =
* *Added* - ability to export transactions
* *Fixed* - missing defaults from Kudos button block
* *Fixed* - missing Dutch translations

= 1.0 =
* Initial release
