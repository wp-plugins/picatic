<?php
// picatic-plugin/uninstall.php

/**
 * Fired when the plugin is uninstalled.
 *
 * @package   Picatic
 * @author    Picatic E-Ticket Inc.
 * @license   MIT
 * @link      http://www.picatic.com/
 * @copyright 2014 Picatic E-Ticket Inc.
 */

// If uninstall not called from WordPress, then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
  exit;
}

delete_option('picatic_settings');
delete_option('picatic_settings_cache');
