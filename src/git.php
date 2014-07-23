<?php
/**
 * Plugin Name: git
 * Plugin URI: http://insideout.io
 * Description: Automatically deploy plugins using Git
 * Version: 1.0.0-SNAPSHOT
 * Author: InsideOut10
 * Author URI: http://insideout.io
 * License: GPL
 */

// Add logging capabilities.
require_once( 'git_log.php' );

// Add constants.
require_once( 'git_constants.php' );

// Add configuration functions.
require_once( 'git_config.php' );

// Add the AJAX post hook.
require_once( 'ajax/git_ajax_post_hook.php' );

// Add the settings.
require_once( 'admin/git_admin_settings.php' );