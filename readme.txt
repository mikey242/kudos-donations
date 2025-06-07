=== Kudos Donations ===
Contributors: iseardmedia
Tags: donation, mollie, payment, ideal, credit card
Requires at least: 6.6
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 4.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Add a donation button to any page on your website. Easy & fast setup. Works with Mollie payments.

== Description ==

= A modern and easy to use donation plugin =

*Kudos Donations* is a powerful, easy-to-use WordPress plugin that lets you accept donations effortlessly. Whether you're running a charity, nonprofit, or a personal project, this plugin provides everything you need to create and manage effective donation campaigns. Fully integrated with Mollie, it supports a wide range of payment methods, allowing your donors to contribute through their preferred option.

= Key Features =

* Easy Mollie Integration: Accept payments via credit card, PayPal, iDEAL, and more, using the Mollie payment gateway.
* Shortcode & Block Support: Choose between a shortcode or Gutenberg block to easily place donation forms anywhere on your site.
* Campaign Management: Set up and manage multiple donation campaigns with flexible options, including one-off and subscription-based donations.
* Customizable Donation Forms: Personalize the look of your donation forms by adjusting colors, text, and even applying custom CSS for each campaign.
* Campaign Goals & Progress Tracking: Set campaign-specific fundraising goals and show progress bars to your visitors.
* Multi-Currency Support: Accept donations in multiple currencies for a global reach.
* Comprehensive Donor Management: Access detailed insights into donors, transactions, and subscriptions.
* Automated Donation Receipts: Automatically generate and send donation receipts via email, complete with PDF attachments.
* PDF Donation Summaries: Generate downloadable PDFs for every successful donation, providing donors with clear and professional records.

= Why Choose Kudos Donations? =

Kudos Donations is designed to make online fundraising easier than ever. With seamless integration into your WordPress site, customizable options, and real-time tracking of donations, you can focus on growing your cause without the hassle of complicated setups.

= Source =
The Git repository for this plugin can be found [here](https://gitlab.iseard.media/michael/kudos-donations).

== Installation ==

Need a Mollie account? Visit their website to [get started](https://www.mollie.com/).

1. Install using the WordPress plugin control panel or manually download the plugin and upload the *kudos-donations* folder to the /wp-content/plugins/ directory
1. Activate the plugin through the *Plugins* menu in WordPress.
1. Go to the 'Kudos Settings' menu in the dashboard and follow the instructions to get started.

== Frequently Asked Questions ==

= Is Kudos Donations free? =

Yes, Kudos Donations is a free open-source plugin.

= Does Kudos Donations take a commission from my donations?

No, we do not touch your donations.

= Which payment methods are supported? =

This plugin uses Mollie as a payment vendor. Mollie allows you to pay using a wide range of payment methods such as iDeal, credit card and PayPal. For a full list of payment methods please check out [this link](https://www.mollie.com/en/payments).

= How can I use recurring payments?

In order to use recurring payments Mollie requires that you have either SEPA Direct Debit OR credit card payment methods enabled on your account. For more information visit [this link](https://help.mollie.com/hc/articles/214558045-What-are-the-conditions-for-the-use-of-Recurring-).

= I've enabled the required recurring payment methods, but I still cannot use recurring.

If you modified your Mollie account after adding it to Kudos Donations your will need to re-sync the settings. To do this visit the Mollie tab under the settings page and click the "Refresh API" button.

= How much does Mollie cost? =

Mollie registration is free, and you only pay a small fee per transaction. See [here](https://www.mollie.com/pricing) for details.

= Can anyone use Mollie? =

Mollie is available to anyone with a registered company.

= Can I use Kudos Donations in my country? =

You can use this plugin in any country that is supported by Mollie. For a complete list please see [here](https://help.mollie.com/hc/articles/115002116105-Can-I-use-Mollies-services-in-my-country-)

= How do I use the shortcode? =

There is a handy "Copy shortcode" button at the top and bottom of the campaign edit screen that will copy the shortcode into your clipboard.

To get started add the Kudos Donations shortcode:

~~~
[kudos campaign_id="242"]
~~~

Here is a list of the shortcode attributes:

* *button_label* = The label to display for the button.
* *campaign_id* = ID of the campaign to use for this button.
* *type* = Whether to show as a button with pop-up or just the form. Can be "button" or "form".
* *alignment* = How to align the button. Can be "left", "right" or "center"

e.g:

~~~~
[kudos button_label="Donate now" campaign_id="default" alignment="center"]
~~~~

For general information on how to use shortcodes, please visit [this page](https://codex.wordpress.org/shortcode).

== Screenshots ==

1. Donation form
2. Recurring/Subscription payments
3. Goal progression
4. Settings page
5. Embed form directly
6. Automated email receipt

== Changelog ==

= 4.1.0 =
* Major rework of admin pages - Transactions, Donors and Subscriptions are now rendered in react
* Add ability to view individual post details (for Transactions, Donors, Subscriptions)
* Add pagination to all admin tables
* Update HeadlessUI to v2.2.4


= 4.0.11 =
* Debug page renamed to advanced
* Added action for bulk moving transactions between campaigns
* Added confirmation dialogue to advanced actions
* Fix recurring payments setting always disabled

= 4.0.10 =
* React-based admin pages now rewritten to TypeScript
* Fix a compatibility issue with WooCommerce where creating campaigns would return a 403 error
* Improve donation form look on small screens
* Update various dependencies

= 4.0.9 =
* Use portal in footer for rendering modal if possible
* Re-implement Jetpack Autoloader
* Implement TypeScript for the Kudos Donations block

= 4.0.8 =
* Add php scoping for Mollie and Monolog packages reducing conflicts with other plugins
* dev: Update php_codesniffer to 3.13.0

= 4.0.7 =
* Add debug action for assigning orphaned transactions to selected campaign
* Fix bug where message tab is shown even when disabled
* Update various dependencies

= 4.0.6 =
* Improve vendor ready detection in admin (now uses same logic as front-end)
* Fix 'undefined' error when only adding a live Mollie key
* Fix Mollie being in unready state after migration
* Fix deprecation warning for various block editor controls

= 4.0.5 =
* Fix issue with name and email input placeholders showing 'false'

= 4.0.4 =
* Improve campaign and settings page layout
* Show 'optional' next to Name/Email when anonymous donations enabled
* Fix Mollie settings not migration correctly
* Fix incorrect invoice number after migration

= 4.0.3 =
* Add ability to set button alignment
* Migration notice now shows "Resume migration" instead of "Update now" if already started
* Minor security fixes

= 4.0.2 =
* Fix button/form appearing before a campaign is loaded
* Add missing translation calls and domain
* Remove limit of 10 campaigns visible on campaigns page
* Show admin notice if EncryptionAwareTrait unable to decrypt option (e.g due to salt change)
* Add compatibility check during activation
* Improve migration logic for larger datasets
* Increase minimum PHP version to 7.4

= 4.0.1 =
* Update mollie-api-php to 3.0.2
* Fix deprecation warning for ToggleGroupControl component

= 4.0.0 =
* New campaigns admin page with ability to create / edit / duplicate and delete campaigns
* Can now configure most settings per-campaign rather than globally
* Complete rewrite of front-end rendering to react
* Reduced conflict with other themes and plugins by using the Shadow DOM
* Support for multiple currencies
* Improved settings pages look and feel
* Easily generate shortcodes per campaign with a helpful pop-up
* New minimum donation setting
* Allow for partial donation amounts (e.g €1.5)
* Email "from name" header can now be configured and defaults to website name
* Passwords and API Keys are now encrypted

= 3.4.2 =
* Add new filter for adjusting the frequency options (kudos_frequency_options)
* Fix issue with subscriptions sometimes failing to create
* Update dependencies

= 3.4.1 =
* Add campaign name to kudos_payment_description filter parameters

= 3.4.0 =
* Add ability to show campaign name in the email receipt
* Update dependencies

= 3.3.0 =
* Fix various security issues
* Update dependencies

= 3.2.9 =
* Add notification with information about upcoming 4.0.0 release
* Update tested up to version to 6.7
* Update Mollie php library
* Update other dependencies

= 3.2.8 =
* Show available payment methods in Mollie tab under Refresh API

= 3.2.7 =
* Fix modal sometimes not in front of content
* Update dependencies

= 3.2.6 =
* Add maximum donation setting
* Update dependencies

= 3.2.5 =
* Add notice for upcoming major Kudos Donations update
* Add various missing translation strings
* Update WordPress tested up to version to 6.6
* Fix potential security issues
* Remove unused code and settings

= 3.2.3 =
* Update dependencies
* Add hooks for accessing the container

= 3.2.2 =
* Fix issue where sometimes the payment summary would not display

= 3.2.1 =
* Add new filter for changing the subscription cancellation URL (kudos_cancel_subscription_url)
* Upgrade postcss to 8.*
* Upgrade WordPress packages

= 3.2.0 =
* Subscriptions can now be canceled by donor (via email receipt) without restrictions
* Fix email subscriptions sometimes not canceling from email link
* Update dependencies

= 3.1.7 =
* Add 'kudos_payment_description' filter
* Fix 'kudos_email_from_name' filter
* Update dependencies

= 3.1.6 =
* Test compatability with WordPress 6.2
* Update dependencies

= 3.1.5 =
* Allow Mollie to connect with either test or live key. Previously both were required.
* Tested compatibility with WordPress 6.0
* Update dependencies

= 3.1.4 =
* Improve email compatibility
* Fix Kudos icon issues with iOS
* Update dependencies (Mollie, Twig)

= 3.1.3 =
* Tested compatibility with WordPress 5.9
* Fix theme colour panel in settings
* Update dependencies (Mollie, Tailwind CSS, Twig etc.)

= 3.1.2 =
* Remove Mollie settings from export
* Rename 'Debug' page to 'Tools' and always make visible
* Remove unused plugin files

= 3.1.1 =
* Prevent interacting with form once submit button pressed
* Display goal in localized format
* Fix various CSRF security flaws
* Various memory usage optimizations
* Update Mollie API client to 2.39.0
* Update ActionScheduler to 3.4.0

= 3.1.0 =
* *NEW* Spam protection option (enabled by default). Automatically adds a honeypot field and timer for form to prevent/reduce spam.
* *NEW* Improved "Welcome Guide" making it easier to get started with Kudos Donations
* Option to disable [kudos] shortcode. If not needed this will prevent CSS/JS from loading on pages without Kudos.
* Upgrade to Block API version 2. This increases the min WordPress version to 5.6.
* Simplify completed payments settings (it is no longer possible to have a custom return url AND a pop-up message)
* Fix issue with recurring payments not getting added to transactions
* Fix bug with subscription cancellation
* Add "Sync payments" and "Add missing transactions" commands to Debug actions
* Change logging to database storage
* Misc logging improvements including more information when emails fail
* Add "Clear object cache" to debug actions
* Add white background to form elements
* Rearrange debug actions page
* Update Mollie API client to 2.38.0
* Update ActionScheduler to 3.3.0
* Update TailwindCSS to 2.2.17
* Minor block bug fixes

= 3.0.0 =
* *NEW* You can now add a donation form without using a button or popup. This can be selected in the side panel when placing a Kudos block, or if using the shortcode by adding the attribute type="form".
* *NEW* Allow adding additional funds to campaign. Useful if you receive donations for your campaign outside of Kudos.
* *NEW* Add ability to choose custom theme color.
* Log file is cleared once it reaches 2MB.
* Implementation of dependency injection using PHP-DI. This will result in faster, cleaner and easier to maintain code.
* Switch to Laravel-Mix for building assets.
* Move goal icon in front of goal amount.
* Update Mollie API client to 2.36.1
* Update ActionScheduler to 3.2.1
* Update TailwindCSS to 2.2.4
* Update Monolog to 2.3.1

= 2.7.0 =
* Add goal progression to campaigns. Disabled by default, this can be enabled per campaign and shows a percentage bar of how much the campaign has already raised as well as how much the currently selected amount will progress the campaign.
* Improve navigation buttons on mobile
* Upgrade TailwindCSS to 2.1.4
* Upgrade Mollie API client to 2.32.0 (reduces plugin filesize)
* Upgrade Action Scheduler to 3.2.0
* Fix amount not resetting if Amount type "Open" selected

= 2.6.0 =
* Improve look and use on mobile
* Increase size on larger screens
* Add option to place donate modal markup in footer (for compatibility troubleshooting)
* Accessibility related improvements
* Add asterisk to required fields
* Pressing enter on form now same as clicking 'next' button
* Add more missing translation function calls
* Fix uncaught exception on transactions table if campaign empty
* Fix empty line in address if no business name (Donors table)

= 2.5.1 =
* Add missing translation function calls
* Fix payment cancelled message not appearing
* Improve subscription cancellation logic
* Replace deprecated array_key_exists function
* Various minor visual tweaks

= 2.5.0 =
* Add optional "message" field to donation form. This can be enabled per campaign under "Campaign details".
* Change 'country' field to drop-down select
* Change 'back' and 'close' icon colors on modal to grey
* Fix missing screen options pull-down on table pages
* Upgrade TailwindCSS to 2.1.2
* Upgrade Twig 2 to Twig 3
* Upgrade Mollie API client to 2.31.1
* Change minimum PHP version to 7.2
* Switch from node-sass to Dart Sass
* Switch to using sanitize_callbacks to sanitize all rest data

= 2.4.7 =
* Various visual tweaks to the donation form
* Add 'backface-visibility' css rule to logo to ensure it remains visible when animating
* Fix some buttons on settings page not animating when busy
* Revert to using css rule 'initial' instead of 'unset' to avoid style clashes
* Resolve jQuery.fn.focus() event shorthand deprecation
* Update dependencies

= 2.4.6 =
* Add error message if campaign not found or Mollie not connected, displayed on front end and only to admins
* Add icon to 'select' form elements
* Fix campaign id sometimes changing on save
* Fix subscriptions allowing a total of 1 payment being created when using multiple buttons per page
* Change new campaign donation type to 'One-off'
* Tweak logo animation
* Update dependencies

= 2.4.5 =
* Fix clash with jQuery
* More logging improvements

= 2.4.4 =
* Accessibility improvements
* Improve javascript to avoid conflicts
* Reduce size of public-facing javascript file
* Correct typo on address slide

= 2.4.3 =
* Increase CSS selector specificity to help prevent clashes with theme/plugins
* Reduce number of css files and overall size
* Add "Secure payment by Mollie" to payment page
* First value field now in focus when modal opened
* Upgrade jquery-validation to 1.19.3 - fixes deprecation warnings

= 2.4.2 =
* Improve settings sanitization
* Fix query var handling for front-end kudos_action
* Logging improvements
* Upgrade Twig to 2.14.4

= 2.4.1 =
* Fix migration issue with Mollie connected status

= 2.4.0 =
* Add API check for Mollie recurring payments' ability. Please see [here](https://help.mollie.com/hc/articles/214558045-What-are-the-conditions-for-the-use-of-Recurring-) for more detail.
* Add additional theme colours
* Change default donation type to oneoff
* Improve campaign settings sanitization
* Fix critical error when incorrect API key format used
* Fix minor theme inconsistencies
* Move Mollie settings to serialized array
* Added more debug actions / debug page now accessible without being enabled

= 2.3.8 =
* Allow more flexibility in translation of 'I agree' boxes

= 2.3.7 =
* Add separate "privacy policy" option
* Update Mollie API client
* Fix translation error
* Fix incorrect business name database constraint

= 2.3.6 =
* Compatibility with WordPress 5.7
* Filter improvements

= 2.3.5 =
* Fix settings import
* Update TailwindCSS to 2.0.3

= 2.3.4 =
* Add 'Business name' to address fields
* Fix campaign table warnings
* Combine Advanced/Help settings tabs
* Minor improvements to Mollie settings
* Improvements to REST routes

= 2.3.3 =
* Fix address fields not showing
* Improve settings defaults for campaign and theme

= 2.3.2 =
* Add 'goal' to campaigns
* Further improvements to subscription cancellation
* Update hooks and filters

= 2.3.1 =
* Display campaign_id in campaign header on settings page
* Fix issues with canceling subscriptions
* Fix various missing translations caused by webpack compression
* Various other minor fixes

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
* Significantly reduced size and increased speed
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
