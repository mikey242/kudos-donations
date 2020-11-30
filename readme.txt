=== Kudos Donations ===
Contributors: iseardmedia
Tags: donation, mollie, payment, ideal, creditcard
Requires at least: 5.4
Tested up to: 5.5.3
Requires PHP: 7.1
Stable tag: 2.0.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Add a donation button to any page on your website. Easy & fast setup. Works with Mollie payments.

== Description ==

*Kudos Donations* allows you to add a donate button anywhere on your website. Once a user clicks this button they will be greeted with a configurable pop-up window, where they can enter their details and how much they would like to donate. Features of this plugin include:

* Smart and modern design
* Connect with Mollie for secure payments by credit card, iDEAL and many others
* Automated email receipts
* Use the provided Gutenberg block or shortcode to place your button
* Buttons and form can be configured all at once or individually
* View all your transactions from the WordPress dashboard

= Dashboard Features =
Change a wealth of options from the settings page. You can configure the look and feel of the donation form, enable subscriptions, configuring email receipts and change text and colours.
View all your donations right from the dashboard. You can see the status of all payments, manage subscriptions and view donor information all from their respective pages.

= Recurring Payments =
Allow donors to create a donation subscription to support your organization on a regular basis. Kudos Donations will automatically create the subscription and take payments from Mollie based on the donors preferences. Of course you can also disable this and use only one-off payments if you prefer.

= Source =
The Git repository for this plugin can be found [here](https://gitlab.iseard.media/michael/kudos-donations).

== Installation ==

1. Install using the WordPress plugin control panel or manually download the plugin and upload the *kudos-donations* folder to the /wp-content/plugins/ directory
1. Activate the plugin through the *Plugins* menu in WordPress.
1. Go to the 'Kudos Settings' menu in the dashboard and follow the instructions to get started.

== Frequently Asked Questions ==

= What vendors can I use with this plugin? =

This plugin uses Mollie as a payment vendor. Mollie allows you to pay using a wide range of payment methods such as iDeal, Credit Card and PayPal. For a full list of payment methods please check out [this link](https://www.mollie.com/en/payments).

== Screenshots ==

1. Kudos modal
2. Thank you pop-up
3. Settings page
4. The Kudos block
5. Transactions screen

== Changelog ==

= 2.0.6 =
* Fix unknown index errors on tables
* Fix 'Sync campaign labels' action adding blank campaigns
* Update TailwindCSS to 2.0.1

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