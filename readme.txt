=== FM: QR Code Gateway for WooCommerce ===
Contributors: fmthecoder
Tags: upi, qr code, payments, woocommerce, checkout
Requires at least: 5.8
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Accept UPI payments via QR code in WooCommerce. Customers enter Transaction ID at checkout. Lightweight & easy to configure.

== Description ==

**FM: QR Code Gateway for WooCommerce** lets your customers pay using any UPI app by scanning a QR code.  
This simple, lightweight gateway integrates directly into WooCommerce and supports both **Classic** and **Block Checkout**.

Customers scan the QR code, make payment, and enter their **Transaction ID** — which is securely saved in the order details.

> **Note:** This plugin is an independent open-source project created and maintained by the developer.  
> It is **not affiliated with, endorsed by, or dependent on any company or organization**.

### 🔹 Features

* Accept UPI payments via any QR code.
* Works with both Classic and Block Checkout.
* Capture and store customer-entered Transaction ID.
* Add custom instructions for users during checkout.
* Secure, lightweight, and easy to configure.
* Fully integrated with WooCommerce Payment Settings.

### 🧩 Compatibility

* WordPress 5.8 or higher  
* WooCommerce 6.0 or higher  
* PHP 7.4 or higher  
* Compatible with all UPI apps (Google Pay, PhonePe, Paytm, BHIM, etc.)

---

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/fm-qr-code-gateway/`, or install it directly through the WordPress “Plugins” screen.  
2. Activate the plugin through the “Plugins” menu in WordPress.  
3. Go to **WooCommerce → Settings → Payments**.  
4. Enable **FM: QR Code Gateway** and configure your QR code image and payment instructions.  

---

== Frequently Asked Questions ==

= Does this plugin verify UPI payments automatically? =
No. Customers scan and pay manually. You can verify payments using the entered Transaction ID.

= Can I use my own UPI QR code? =
Yes. You can upload any static or dynamic UPI QR code from your bank or payment provider.

= Where can I view the Transaction ID? =
The Transaction ID is saved with the order details and can be viewed in the WooCommerce admin order page.

= Is it compatible with the new WooCommerce Block Checkout? =
Yes, this plugin supports both Classic and Block Checkout experiences.

---

== Screenshots ==

1. QR Code displayed at checkout.  
2. Customer entering Transaction ID.  
3. Admin settings for QR code setup.  
4. Order details showing saved Transaction ID.

---

== Changelog ==

= 1.0.1 =
* Fixed issues related to the textdomain.

= 1.0.0 =
* Initial release.
* Added support for Classic and Block Checkout.
* Added Transaction ID capture and display.

---

== Upgrade Notice ==

= 1.0.1 =
Fixed issues related to the textdomain.

= 1.0.0 =
Initial stable release with full UPI QR payment support and transaction ID storage.

---

== License ==

This plugin is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, version 2 of the License or any later version.

This plugin is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
