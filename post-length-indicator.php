<?php
/*
 * Plugin Name: Post Length Indicator
 * Version: 1.0
 * Plugin URI: http://www.hughlashbrooke.com/
 * Description: Displays a dual-coloured bar alongside the scrollbar to indicate how much of the page contains the post and how much contains the comments.
 * Author: Hugh Lashbrooke
 * Author URI: http://www.hughlashbrooke.com/
 * Requires at least: 3.0
 * Tested up to: 4.0
 *
 * @package WordPress
 * @author Hugh Lashbrooke
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

require_once( 'classes/class-post-length-indicator.php' );
require_once( 'classes/class-post-length-indicator-settings.php' );

global $pli;
$pli = new Post_Length_Indicator( __FILE__ );
$pli_settings = new Post_Length_Indicator_Settings( __FILE__ );