<?php
/*
Plugin Name: Event API Plugin
Description: Fetches and displays events from Laravel API.
Version: 1.0
Author: Mahmudur 
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Include necessary files
require_once plugin_dir_path(__FILE__) . 'includes/class-event-api.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-shortcode-handler.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-admin-menu-handler.php';

// Register the shortcode
add_action('init', function() {
    $shortcode_handler = new Event_Shortcode_Handler();
    $shortcode_handler->register_shortcodes();
});

// Register the admin menu
add_action('admin_menu', 'register_event_api_menus');

// Callback function to add the admin menus
function register_event_api_menus() {
    $admin_menu_handler = new Admin_Menu_Handler();
    $admin_menu_handler->register_menus();
}