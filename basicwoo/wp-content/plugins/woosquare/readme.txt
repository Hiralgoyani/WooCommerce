=== WooSquare - Square for WooCommerce ===
Plugin URI: https://wpexperts.io/
Contributors: wpexpertsio
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=pay@objects.ws&item_name=DonationForPlugin
Tags: inventory management, point of sale, pos, square,squareup, woocommerce, woosquare, sync square, sync wootosquare, sync squaretowoo,synchronization square
Requires at least: 4.5.0 
Tested up to: 5.2.4
Stable tag: 3.2.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WooSquare - Simply helps you synchronize your products between WooCommerce and Square.

== Description ==

WooCommerce Square Integration helps you to migrate & synchronize your products and categories between Square & WooCommerce. Square is free point of sale software which facilitate the process of selling products.

**Announcement:** In version 3.2 you can connect Square account with WooSquare on a single click without creating an application on Square developer dashboard, we have introduced Square Connect API which will give you the ability to Connect & Disconnect WooSquare on a single [click here](https://apiexperts.io/documentation/topics/woosquare/) is a link of Documentation. Also, Keeping the testing phase in mind we have integrated Sandbox support With the latest Version of SQUARE V2 API in order to see test transactions in Square Dashboard.

= Features: =
* Sandbox API Support: Sandbox API is for developers who test transactions before moving towards a live transactions. 
* Create Refund from WooCommerce for Square Orders: Give refund on orders of square using WooCommerce dashboard .
* Square Payment Gateway: Customers are allowed to pay via credit card at WooCommerce Checkout with Square API.
* Square Authorization: Connect your Square application with WooSquare with a single click.
* Manually Synchronized Variable Product From Woocommerce To Square Or Vice Versa With Custom Attribute Using Multiple Variations.
* Manually Synchronized Simple Product From Woocommerce To Square Or Vice Versa With Custom Attribute And It’S Values.
* Manually Synchronized Variable Product From Woocommerce To Square Or Vice Versa With Global Attribute Using Multiple Variations.
* Manually Synchronized Simple Product From Woocommerce To Square Or Vice Versa With Global Attribute And It’S Values.

= Demo Video =
Here is demo video on how to Authorized your Square Account!.

[youtube https://youtu.be/6A0yB-eHNvQ]

Here is demo video on how to synchronize your products between WooCommerce and Square.

[youtube https://www.youtube.com/watch?v=6bw3rZ8Ki1E]


= Documentation =
[Click here](https://apiexperts.io/documentation/topics/woosquare/) to visit detailed documentation of WooSquare (Including WooSquare Pro Features)


= Requirements: =
* WooCommerce 2.6.0+
* Square account.
* WordPress 4.4+
* PHP version 5.5+
* All your products must have SKU value


= Premium Features =

* With [WooSquare Pro](https://wpexperts.io/link/woosquare-pro-square-for-woocommerce/) you can even pay with Square at WooCommerce Checkout
* [WooSquare Pro](https://wpexperts.io/link/woosquare-pro-square-for-woocommerce/) have support for Square Sandbox API as well to make development phase easy.
* Synchronize orders from Square to WooCommerce.
* Manage refunds between Square and WooCommerce.
* Auto synchronization of products from Square to WooCommerce. 
* Auto synchronization of products from WooCommerce to Square.

[**Click here to get WooSquare Pro now**](https://wpexperts.io/link/woosquare-pro-square-for-woocommerce/)

**NOTE :** If you are looking for only WooCommerce Square payment gateway functionality then [**WooCommerce Square Up Payment Gateway**](https://wpexperts.io/link/woocommerce-square-up-payment-gateway-2/) is the right plugin for you.

**Interested in contributing to WooSquare?**	
Head over to the [WooCommerce Square Integration **GitHub Repository**](https://github.com/wpexpertsio/woosquare) to find out how you can pitch in ;)


= More Square Integrations = 

* [Square Recurring Payments For WooCommerce Subscriptions](https://apiexperts.io/solutions/square-recurring-payments-for-woocommerce-subscriptions)
* [Square Payment Gateway for WooCommerce ](https://apiexperts.io/solutions/square-recurring-payments-for-woocommerce-subscriptions)
* [Square Payments For Gravity Forms](https://apiexperts.io/solutions/square-for-gravity-forms)
* [Square Payment Gateway For Caldera Forms ](https://apiexperts.io/solutions/pay-with-square-in-caldera-form/)
* [Square Payments For GIVEWP](https://apiexperts.io/solutions/square-for-givewp)
* [Square Payment Gateway for Contact form 7 ](https://codecanyon.net/item/woocommerce-square-up-payment-gateway/19692778)

= Disclaimer =

WPExperts offer solutions as a third party service provider, we are NOT affiliated, associated, authorized, endorsed by, or in any way officially connected with Square, Inc. The name “Square” as well as related marks and images are registered trademarks of Square, Inc.

== Installation ==

1. Upload the `woosquare` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Create a Square account. If you don't have an account, go to https://squareup.com/signup to create one. Register your application with Square.
4. Then go to https://connect.squareup.com/apps and sign in to your Square account. Then click New Application and enter a name for your application and Create App. The application dashboard displays your new app's credentials. One of these credentials is the personal access token. This token gives your application full access to your own Square account.


== Screenshots ==
1. Woo Square Settings
2. Start Synchronize
3. Payment Settings

== Changelog ==
= 3.2.2 =
* Update - SDk updated version 2.3.1

= 3.2.1 =
Fix - Selected payment location.

= 3.2 =

* Added - SCA Integration.
* Added - replace depreciated with new one api endpoints. 
* Added - Square Sandbox dashboard integration with v2 api. 
* Added - Create payment V2 API. 
* Added - Get payment V2 API. 
* Added - Cancel payment V2 API. 
* Added - Refund payment V2 API. 

= 3.1.8 =

* Added - WooSquare Official documentation link added.
* Added - Refund support for reason optional.
* Fixed- Some error fixed.

= 3.1.4 =

* Added - Square Payment Gateways Support.
* Added - Refund support.
* Update- SSL notice code update.

= 3.1.2 =

* Improvement - Square connect authorization.

= 3.1.1 =

* Update - SDk updated.

= 3.1 =

* Improvement - Square connect authorization.
* Added - Square connect revoke.

= 3.0 =

* Added - Square connect authorization.

= 2.8 =

* Enhancement – CURL code to HTTP API request.

= 2.6 =

* Enhancement – Added compatibility for Wordpress 5.0.1 
* Enhancement – Added compatibility for WooCommerce 3.5.2
* Enhancement - Manage Stock management conditions.	 

= 2.4 =
 
* Added - Html escape check.
* Remove - Conflicts with square multi location.
* Remove - font size bold conflicts.
* Added - Support for wc_update_product_stock.
* Manage - Stock management according to square rule RECEIVE_STOCK,SALE.

= 2.2 =
 
* Added - Synchronization Compatibility Multiple attribute with multiples variations WooCommerce to Square.
* Added - Synchronization Compatibility Multiple attribute with multiples variations Square to WooCommerce.


= 2.0 =

* Added - Compatibility single attribute with multiples variations.
* Added - Sync to square on Added,edit,update or delete event.
* Update - WooCommerce to Square variations uppercase lowercase compatibility. 
* Update - Square to WooCommerce variations sync space trims.
* Update - Improvement in product synchronization.
* Update - Terminate or delay when other synchronization with.

= 1.5.5 =
* Optimization - Added compatibility for the latest version of woocommerce.

= 1.5.1 =
* Optimization - Optimization of code with free and paid conflicts.

= 1.5 =
* Added - filtration validation with global and custom attributes.
* Optimization - Organized the code.

= 1.4 =
* Added - PHP version compatibility checked.

= 1.3 =
* Added - Compatibility with woocommerce 3.0x .
* Added - sync multiples variations with starting 2 attributes variable product in free completed in pro version.
* Update - Add Woosquare Double attributes with variable product with variations.

= 1.2 =
* Commit to clean and update code upto standards

= 1.1 =
* Fixed Confliction with WooSquare Pro
* Tested upto WooCommerce 3.0
* Tested for WordPress 4.7.3 Compatibility

= 1.0.3 =
* Small update in backend

= 1.0.1 =
* Added some backend features

= 1.0 =
* Initial release