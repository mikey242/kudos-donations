=== Kudos Donations ===
Contributors: iseardmedia
Tags: donation, mollie, payment, ideal, credit card
Requires at least: 5.4
Tested up to: 5.6
Requires PHP: 7.1
Stable tag: 2.0.7
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
Just enter your API key from Mollie, then add one or more buttons anywhere on you website. Need a Mollie account? Visit their website to [get started](https://www.mollie.com/).

= Campaign Labels =
Assign a campaign label to each donation button to group and track donations made. This is useful for seeing the effectiveness of different buttons or tracking donations made to different donation campaigns.

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

Yes, Kudos Donations is a free plugin. A premium upgrade will be available soon that adds more features.

= Which payment methods are supported? =

This plugin uses Mollie as a payment vendor. Mollie allows you to pay using a wide range of payment methods such as iDeal, Credit Card and PayPal. For a full list of payment methods please check out [this link](https://www.mollie.com/en/payments).

= How much does Mollie cost? =

Mollie registration is free and you only pay a small fee per transaction. See [here](https://www.mollie.com/pricing) for details.

= Can anyone use Mollie? =

Mollie is available to anyone with a registered company.

= Can I use Kudos Donations in my country? =

You can use this plugin in any country that is supported by Mollie. For a complete list please see [here](https://help.mollie.com/hc/articles/115002116105-Can-I-use-Mollies-services-in-my-country-)

== Screenshots ==

1. Donation form
2. Kudos Donations button block
3. Settings page
4. Automated email receipt

== Changelog ==

= 2.0.8 =
* Add 'Recreate database' action to debug menu
* Add confirmation pop-up to debug actions
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