<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Admin_Menu_Handler {

    private $api;

    public function __construct() {
        $this->api = new Event_API();
    }

    public function register_menus() {
        add_menu_page(
            'Events',
            'Events',
            'manage_options',
            'event_api_plugin',
            [$this, 'render_event_list_page'],
            'dashicons-calendar',
            6
        );

        add_submenu_page(
            'event_api_plugin',
            'List Events',
            'List Events',
            'manage_options',
            'event_api_plugin_list',
            [$this, 'render_event_list_page']
        );

        add_submenu_page(
            'event_api_plugin',
            'Create Event',
            'Create Event',
            'manage_options',
            'event_api_plugin_create',
            [$this, 'render_event_create_page']
        );
    }

    public function render_event_list_page() {
        echo '<div class="wrap">';
        echo '<h1 class="wp-heading-inline">List of Events</h1>';
        echo '<hr class="wp-header-end">';
        
        $events = $this->api->get_events();

        if (!empty($events)) {
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead><tr><th>Title</th><th>Description</th><th>Category</th><th>Date</th><th>Time</th><th>Location</th></tr></thead>';
            echo '<tbody>';
            foreach ($events as $event) {
                echo '<tr>';
                echo '<td><strong>' . esc_html($event['title']) . '</strong></td>';
                echo '<td>' . esc_html(wp_trim_words($event['description'], 20)) . '</td>';
                echo '<td>' . esc_html($event['category']) . '</td>';
                echo '<td>' . esc_html($event['date']) . '</td>';
                echo '<td>' . esc_html($event['time']) . '</td>';
                echo '<td>' . esc_html($event['location']) . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        } else {
            echo '<p>No events found.</p>';
        }

        echo '</div>';
    }

    public function render_event_create_page() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_title'])) {
            $title = sanitize_text_field($_POST['event_title']);
            $description = sanitize_textarea_field($_POST['event_description']);
            $category = sanitize_text_field($_POST['event_category']);
            $date = sanitize_text_field($_POST['event_date']);
            $time = sanitize_text_field($_POST['event_time']);
            $location = sanitize_text_field($_POST['event_location']);
            
            $response = $this->api->create_event($title, $description, $category, $date, $time, $location);

            if ($response) {
                echo '<div class="notice notice-success is-dismissible"><p>Event created successfully!</p></div>';
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>Failed to create event. Please check the error log for more details.</p></div>';
                error_log('Event Creation Data: ' . print_r($_POST, true));
            }
        }

        echo '<div class="wrap">';
        echo '<h1 class="wp-heading-inline">Create New Event</h1>';
        echo '<hr class="wp-header-end">';
        echo '<form method="post" class="event-form">';
        echo '<table class="form-table">';
        
        echo '<tr><th scope="row"><label for="event_title">Event Title</label></th>';
        echo '<td><input type="text" name="event_title" id="event_title" class="regular-text" required /></td></tr>';
        
        echo '<tr><th scope="row"><label for="event_description">Event Description</label></th>';
        echo '<td><textarea name="event_description" id="event_description" class="large-text" rows="5" required></textarea></td></tr>';
        
        echo '<tr><th scope="row"><label for="event_category">Category</label></th>';
        echo '<td><select name="event_category" id="event_category" class="regular-text" required>';
        echo '<option value="">Select a category</option>';
        $categories = ['Music', 'Sports', 'Food', 'Technology', 'Art', 'Business', 'Education', 'Health', 'Science', 'Social'];
        foreach ($categories as $category) {
            echo '<option value="' . esc_attr($category) . '">' . esc_html($category) . '</option>';
        }
        echo '</select></td></tr>';
        
        echo '<tr><th scope="row"><label for="event_date">Date</label></th>';
        echo '<td><input type="date" name="event_date" id="event_date" class="regular-text" required /></td></tr>';
        
        echo '<tr><th scope="row"><label for="event_time">Time</label></th>';
        echo '<td><input type="time" name="event_time" id="event_time" class="regular-text" required /></td></tr>';
        
        echo '<tr><th scope="row"><label for="event_location">Location</label></th>';
        echo '<td><input type="text" name="event_location" id="event_location" class="regular-text" required /></td></tr>';
        
        echo '</table>';
        
        echo '<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Create Event" /></p>';
        echo '</form>';
        echo '</div>';
    }
}