=== Advanced Shipment Tracking for WooCommerce  ===
Contributors: zorem
Tags: woocommerce, delivery, shipping, shipment tracking, tracking, fedex, ups, usps
Requires at least: 4.0
Requires PHP: 5.2.4
Tested up to: 5.3
Stable tag: 5.3
License: GPLv2 
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Add shipment tracking information to your WooCommerce orders and provide your customers with an easy way to track their orders, reduce your customer service inquiries and to Improve your customers Post-Purchase Experience.

== Description ==

Add shipment tracking information to your WooCommerce orders and provide your customers with an easy way to track their orders.

This plugin provide Shop managers easy ways to add shipment tracking information to order, once the order is Completed (Shipped) the customer will receive the tracking details and a link to tracker their order in the order emails and on my account section.

AST provide a list of 100+ shipping providers with pre-set tracking link and image, you can add your own custom provider, customize the tracking display, created delivered order status, customize the emails and more.

== Features ==

* Add shipment tracking info to orders – shipping provider, tracking number and shipping date
* Add multiple tracking numbers to orders
* Add tracking info to orders from the orders admin (inline)
* List of 100+ default shipping providers (carriers)
* Select shipping providers to use when adding tracking info to orders
* Set the default provider when adding tracking info to orders
* Add custom shipping providers
* Sync the Providers list with TrackShip
* Display Shipment tracking info and tracking link on user accounts
* Display Shipment tracking info and tracking link on customer emails
* Customize and preview the Tracking info display on customer emails using email designer.
* Choose on which Customer emails to include the tracking info.
* Bulk import tracking info to orders with CSV file.
* WooCommerce REST API support to update shipment tracking information
* Rename the Completed Order status to Shipped
* Enable Delivered custom order status
* Customer order status Delivered email to customers
* Customize and preview the Delivered status email using email designer.

== TrackShip Integration== 

[TracksShip](https://trackship.info/) is a premium shipment tracking API flatform that fully integrates with WooCommerce with the Advanced Shipment Tracking. TrackShip automates the order management workflows, reduces customer inquiries, reduces time spent on customer service, and improves the post-purchase experience and satisfaction of your customers.

You must have account [TracksShip](https://trackship.info/) and connect your store in order to activate these advanced features:

* Automatically track your shipments with 100+ shipping providers.
* Display Shipment Status and latest shipment status, update date and est. delivery date on WooCommerce orders admin.
* Option to manually get shipment tracking updates for orders.
* Automatically change order status to Delivered once the shipment is delivered to your customers.
* Option to filter orders with invalid tracking numbers or by shipment status event in orders admin
* Send personalized emails to notify the customer when their shipments are In Transit, Out For Delivery, Delivered or have an exception.
* Direct customers to a Tracking page on your store.

== Localization == 

The plugin is translation ready, we added translation to: 
Hebrew, Hindi, Italian, Norwegian (Bokmål), Russian, Swedish, Turkish, Bulgarian, Danish, German, Spanish (Spain), French (France), Greek


== Compatibility with WooCommerce email customization plugins ==

* [Kadence WooCommerce Email Designer](https://wordpress.org/plugins/kadence-woocommerce-email-designer/)
* [WP HTML Mail – Email Designer](https://wordpress.org/plugins/wp-html-mail/)
* [Decorator – WooCommerce Email Customizer](https://wordpress.org/plugins/decorator-woocommerce-email-customizer/)
* [Email Customizer for WooCommerce](https://codecanyon.net/item/email-customizer-for-woocommerce/8654473)

== Compatibility with custom order numbers plugins for WooCommerce ==
* [Custom Order Numbers for WooCommerce](https://wordpress.org/plugins/custom-order-numbers-for-woocommerce/)
* [WooCommerce Sequential Order Numbers](https://wordpress.org/plugins/woocommerce-sequential-order-numbers/)
* [WP-Lister Pro for Amazon](https://www.wplab.com/plugins/wp-lister-for-amazon/)

== Compatibility with PDF Invoices & Packing Slips  plugins ==
* [WooCommerce PDF Invoices & Packing Slips plugin](https://wordpress.org/plugins/woocommerce-pdf-invoices-packing-slips)

== Compatibility with SMS Plugins ==
* [SMS Alert Order Notifications – WooCommerce](https://wordpress.org/plugins/sms-alert/)
* [WC – APG SMS Notifications](https://wordpress.org/plugins/woocommerce-apg-sms-notifications/)
* [Twilio SMS Notifications](https://woocommerce.com/products/twilio-sms-notifications/)

https://www.youtube.com/watch?v=Mw7laecPtyw

== Frequently Asked Questions == 

= Where will my customer see the tracking info?
Tracking info and link to track the package on the shipment provider website will be added to the “Completed” (Shipped) orders emails.  We will also display the tracking info in user accounts in the order history tab (see screenshots)
= Can I add multiple tracking numbers to orders?
Yes, you can add as many tracking numbers to orders and they will all be displayed to your customers. 
= Can I add a shipping provider that is not on your list?
Yes, you can add custom providers, choose your default shipment provider, Change the providers order in the list and enable only providers that are relevant to you.
= Can I design the display of Tracking info on WooCommerce emails?
Yes, you have full control over the design and display of the tracking info and you can customize it.
= can I track my order and send shipment status and delivery notifications to my customers?
Yes, you can signup to Trackship and we provide full integration, once connected, TrackShip proactively sends shipment status updates to your WooCommerce store and streamlines your order management process and provide improved post-purchase experience to your customers.
We are currently in beta stage, [signup]https://trackship.info to get your invitation
= How do I set the custom provider URL so it will direct exactly to the tracking number results?
You can add tracking number parameter in this format:
http://shippingprovider.com?tracking_number=%number% , %number% - this variable will hold the tracking number for the order.
= is it possible to import multiple tracking numbers to orders in bulk?
Yes, you can use our Bulk import option to import multiple tracking inumbers to orders, you need to add each tracking number is one row.
=How do I use the Rest API to add/retrieve/delete tracking info to my orders?
you can use the plugin to add, retrieve, delete tracking information for orders using WooCommerce REST API. 
For example, in order to add tracking number to order:
use the order id that you wish to update in the URL instead of <order-id>, add the shipping provider and tracking code. 

curl -X POST 
http://a32694-tmp.s415.upress.link/wp-json/wc/v1/orders/<order-id>/shipment-trackings \
    -u consumer_key:consumer_secret \
    -H "Content-Type: application/json" \
    -d '{
  "tracking_provider": "USPS",
  "tracking_number": "123456789",
}'

== Installation ==

1. Upload the folder `woo-advanced-shipment-tracking` to the `/wp-content/plugins/` folder
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Select default shipping provider from setting page and add tracking number in order page.

== Changelog ==

= 2.7.5 =
* Updated Japan Post Tracking Link, if shipping country is not Japan than add language en in link
* Removed email field from Tracking Per Item Add-on section
* Removed dash validation from bulk upload csv/API
* Fixed RTL issue in Tracking info table
* Fixed issue with Delivered status color when WooCommerce Order Status Manager plugin is active 

= 2.7.4 =
* Added Add-on for Tracking Per Item
* Added functionality for search by tracking number in orders admin
* Added functionality so "Shop Manager" user role can manage AST settings
* Updated Spanish language translations
* Updated ebay meta field
* Removed validation for dash from adding tracking number

= 2.7.3 =
* Fixed issue in edit and delete custom shipping provider

= 2.7.2 =
* Added compatibility with WordPress 5.3
* Added functionality for bulk upload from tools tab in settings for "Please do connection" status orders
* Updated functionality if shipment status is "Carrier Unsupported" than use carrier tracking page instead of trackship tracking page
* Updated German language translation files
* Change the success message after "Get Shipment Tracking" bulk actions

= 2.7.1 =
* Updated Italian and Swedish language file
* Change Partial Shipped to Partially Shipped
* Fixed issue - Undefined variable: responsive_check in delivered order email
* Fixed issue in Bulk upload CSV that after upload through CSV all are showing as unsupported carrier in TrackShip
* Fixed issue in Rest API that after upload through CSV all are showing as unsupported carrier in TrackShip

= 2.7 =
* Added  Twilio SMS Notifications plugin compatibility with Order status message so user can add tracking details in order status message
* Added  Twilio SMS Notifications plugin compatibility with TrackShip so user can send shipment status change SMS to users
* Added language support for Portuguese. 
* Fixed issue with Delivered shipment status email enable/disable
* Fixed warning with Sync Providers functionality

[For the complete changelog](https://www.zorem.com/docs/woocommerce-advanced-shipment-tracking/changelog/)