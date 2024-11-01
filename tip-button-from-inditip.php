<?php
/*
 * Plugin Name: Tip button from IndiTip
 * Version: 2.0
 * Plugin URI: https://wordpress.org/plugins/tip-button-from-inditip/
 * Description: 5 Rupee tip button for WordPress
 * Author: IndiTip
 * Author URI: https://inditip.com/
 * Requires at least: 4.0
 * Tested up to: 4.8
 *
 * Text Domain: tip-button-from-inditip
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Rohit Chatterjee
 * @since 1.0.0
 * @since 1.1 Shortcode support
 * @since 1.1.1 Remove restriction to single posts
 * @since 2.0 Larger asset, enhanced messaging, login tipping payment and publisher's dashboard
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Load plugin class files
require_once( 'includes/class-tip-button-from-inditip.php' );
require_once( 'includes/class-tip-button-from-inditip-settings.php' );

// Load plugin libraries
require_once( 'includes/lib/class-tip-button-from-inditip-admin-api.php' );
require_once( 'includes/lib/class-tip-button-from-inditip-post-type.php' );
require_once( 'includes/lib/class-tip-button-from-inditip-taxonomy.php' );

/**
 * Returns the main instance of Tip_button_from_IndiTip to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object Tip_button_from_IndiTip
 */
function Tip_button_from_IndiTip () {
	$instance = Tip_button_from_IndiTip::instance( __FILE__, '1.0.0' );

	if ( is_null( $instance->settings ) ) {
		$instance->settings = Tip_button_from_IndiTip_Settings::instance( $instance );
	}

	return $instance;
}

Tip_button_from_IndiTip();
