<?php
/**
 * KAIKO Email Styles.
 *
 * Overrides WooCommerce's default emails/email-styles.php.
 * Complete CSS override — WooCommerce inlines these styles via Emogrifier.
 *
 * Brand colours:
 *   Primary teal:      #1a5c52
 *   Cream background:  #faf8f5
 *   Dark text:         #2d2d2d
 *   Muted text:        #666666
 *   Page background:   #f4f4f4
 *
 * @package KaikoCore
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

// Emogrifier picks up all CSS echoed here and inlines it into the email HTML.
?>

/* -------------------------------------------------------
   Reset & Base
   ------------------------------------------------------- */

body {
	margin: 0;
	padding: 0;
	background-color: #f4f4f4;
	-webkit-text-size-adjust: none;
	-ms-text-size-adjust: none;
}

#wrapper {
	background-color: #f4f4f4;
	margin: 0;
	padding: 0;
	width: 100%;
	-webkit-text-size-adjust: none;
}

#template_container {
	background-color: #ffffff;
	border: none;
	border-radius: 8px;
	box-shadow: none;
}

#template_header {
	background-color: #1a5c52;
	border-radius: 8px 8px 0 0;
	color: #ffffff;
	text-align: center;
}

#template_header h1,
#template_header h1 a {
	color: #ffffff;
	font-family: Helvetica, Arial, sans-serif;
	font-size: 24px;
	font-weight: 600;
	letter-spacing: -0.3px;
	line-height: 1.3;
	margin: 0;
	padding: 0;
	text-decoration: none;
}

#template_body {
	background-color: #ffffff;
}

#template_footer {
	background-color: #1a5c52;
	border-radius: 0 0 8px 8px;
}

/* -------------------------------------------------------
   Typography
   ------------------------------------------------------- */

body,
table,
td,
th,
p,
li {
	font-family: Helvetica, Arial, sans-serif;
	font-size: 15px;
	line-height: 1.7;
	color: #2d2d2d;
}

h1 {
	font-family: Helvetica, Arial, sans-serif;
	font-size: 24px;
	font-weight: 600;
	color: #2d2d2d;
	letter-spacing: -0.3px;
	line-height: 1.3;
	margin: 0 0 24px;
}

h2 {
	font-family: Helvetica, Arial, sans-serif;
	font-size: 18px;
	font-weight: 600;
	color: #1a5c52;
	line-height: 1.4;
	margin: 32px 0 12px;
	padding: 0;
	display: block;
}

h3 {
	font-family: Helvetica, Arial, sans-serif;
	font-size: 16px;
	font-weight: 600;
	color: #2d2d2d;
	line-height: 1.4;
	margin: 24px 0 8px;
}

p {
	font-family: Helvetica, Arial, sans-serif;
	font-size: 15px;
	line-height: 1.7;
	color: #2d2d2d;
	margin: 0 0 16px;
}

a {
	color: #1a5c52;
	text-decoration: underline;
	font-weight: 500;
}

small {
	font-size: 13px;
	color: #666666;
}

/* -------------------------------------------------------
   Links & Buttons
   ------------------------------------------------------- */

.link {
	color: #1a5c52;
	text-decoration: underline;
}

.button {
	display: inline-block;
	background-color: #1a5c52;
	color: #ffffff !important;
	font-family: Helvetica, Arial, sans-serif;
	font-size: 14px;
	font-weight: 600;
	letter-spacing: 0.5px;
	text-transform: uppercase;
	text-decoration: none;
	padding: 14px 32px;
	border-radius: 4px;
	margin: 8px 0;
}

/* -------------------------------------------------------
   Order Table
   ------------------------------------------------------- */

.td {
	font-family: Helvetica, Arial, sans-serif;
	font-size: 14px;
	line-height: 1.6;
	color: #2d2d2d;
	padding: 14px 16px;
	border-bottom: 1px solid #eeeeee;
}

.order_item .td {
	padding: 14px 16px;
}

/* Alternating row colours */
.order_item:nth-child(odd) .td {
	background-color: #faf8f5;
}

.order_item:nth-child(even) .td {
	background-color: #ffffff;
}

table.td,
th.td {
	font-family: Helvetica, Arial, sans-serif;
	font-size: 14px;
	padding: 14px 16px;
	color: #2d2d2d;
}

thead th.td {
	background-color: #1a5c52;
	color: #ffffff;
	font-size: 12px;
	font-weight: 600;
	letter-spacing: 0.5px;
	text-transform: uppercase;
	padding: 12px 16px;
	border-bottom: none;
}

tfoot td.td,
tfoot th.td {
	background-color: #faf8f5;
	font-size: 14px;
	border-bottom: 1px solid #eeeeee;
}

tfoot tr:last-child td.td,
tfoot tr:last-child th.td {
	font-weight: 700;
	font-size: 16px;
	color: #2d2d2d;
	border-bottom: none;
}

/* -------------------------------------------------------
   Addresses
   ------------------------------------------------------- */

.addresses {
	margin-bottom: 24px;
}

.addresses .address {
	background-color: #faf8f5;
	border-radius: 6px;
	padding: 20px 24px;
	font-size: 14px;
	line-height: 1.7;
	color: #2d2d2d;
	border: none;
}

.addresses h2 {
	font-size: 13px;
	font-weight: 600;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	color: #1a5c52;
	margin: 0 0 8px;
}

/* -------------------------------------------------------
   Secondary / Info Sections
   ------------------------------------------------------- */

#body_content_inner {
	font-family: Helvetica, Arial, sans-serif;
	font-size: 15px;
	line-height: 1.7;
	color: #2d2d2d;
}

.wc-item-meta {
	font-size: 13px;
	color: #666666;
	margin: 4px 0 0;
	padding: 0;
	list-style: none;
}

.wc-item-meta li {
	margin: 0;
	padding: 0;
}

.wc-item-meta li p {
	font-size: 13px;
	color: #666666;
	margin: 0;
}

/* -------------------------------------------------------
   Footer
   ------------------------------------------------------- */

#credit {
	padding: 24px 48px;
	text-align: center;
}

#credit p {
	font-size: 12px;
	line-height: 1.6;
	color: rgba(255,255,255,0.7);
	margin: 0;
}

#credit a {
	color: #ffffff;
	text-decoration: underline;
}

/* -------------------------------------------------------
   Responsive
   ------------------------------------------------------- */

@media only screen and (max-width: 620px) {
	#template_container {
		width: 100% !important;
	}

	.td,
	th.td,
	td.td {
		padding: 10px 12px !important;
		font-size: 13px !important;
	}

	h1 {
		font-size: 22px !important;
	}

	h2 {
		font-size: 16px !important;
	}

	.addresses .address {
		padding: 16px !important;
	}
}
