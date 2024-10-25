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
        echo '<div class="wrap event-api-wrap">';
        echo '<h1 class="wp-heading-inline">Event Management</h1>';
        echo '<a href="' . admin_url('admin.php?page=event_api_plugin_create') . '" class="page-title-action">Add New Event</a>';
        echo '<hr class="wp-header-end">';
        
        $events = $this->api->get_events();

        if (!empty($events)) {
            echo '<div class="event-grid">';
            foreach ($events as $event) {
                echo '<div class="event-card">';
                echo '<h3>' . esc_html($event['title']) . '</h3>';
                echo '<p class="event-description">' . esc_html(wp_trim_words($event['description'], 20)) . '</p>';
                echo '<div class="event-meta">';
                echo '<span class="event-category">' . esc_html($event['category']) . '</span>';
                echo '<span class="event-date">' . esc_html($event['date']) . ' at ' . esc_html($event['time']) . '</span>';
                echo '</div>';
                echo '<p class="event-location"><i class="dashicons dashicons-location"></i> ' . esc_html($event['location']) . '</p>';
                echo '<div class="event-actions">';
                echo '<button class="button edit-event" data-event=\'' . json_encode($event) . '\'>Edit</button>';
                echo '<button class="button delete-event" data-id="' . esc_attr($event['id']) . '">Delete</button>';
                echo '</div>';
                echo '</div>';
            }
            echo '</div>';
        } else {
            echo '<div class="event-empty-state">';
            echo '<i class="dashicons dashicons-calendar-alt"></i>';
            echo '<p>No events found. Create your first event to get started!</p>';
            echo '<a href="' . admin_url('admin.php?page=event_api_plugin_create') . '" class="button button-primary">Create Event</a>';
            echo '</div>';
        }

        $this->render_edit_modal();
        echo '</div>';
        $this->render_event_styles();
        $this->render_event_scripts();
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
                echo '<div class="notice notice-error is-dismissible"><p>Failed to create event. Please try again.</p></div>';
            }
        }

        echo '<div class="wrap event-api-wrap">';
        echo '<h1 class="wp-heading-inline">Create New Event</h1>';
        echo '<hr class="wp-header-end">';
        echo '<form method="post" class="event-form">';
        
        echo '<div class="form-group">';
        echo '<label for="event_title">Event Title</label>';
        echo '<input type="text" name="event_title" id="event_title" required />';
        echo '</div>';
        
        echo '<div class="form-group">';
        echo '<label for="event_description">Event Description</label>';
        echo '<textarea name="event_description" id="event_description" required></textarea>';
        echo '</div>';
        
        echo '<div class="form-row">';
        echo '<div class="form-group">';
        echo '<label for="event_category">Category</label>';
        echo '<select name="event_category" id="event_category" required>';
        echo '<option value="">Select a category</option>';
        $categories = ['Music', 'Sports', 'Food', 'Technology', 'Art', 'Business', 'Education', 'Health', 'Science', 'Social'];
        foreach ($categories as $category) {
            echo '<option value="' . esc_attr($category) . '">' . esc_html($category) . '</option>';
        }
        echo '</select>';
        echo '</div>';
        
        echo '<div class="form-group">';
        echo '<label for="event_date">Date</label>';
        echo '<input type="date" name="event_date" id="event_date" required />';
        echo '</div>';
        
        echo '<div class="form-group">';
        echo '<label for="event_time">Time</label>';
        echo '<input type="time" name="event_time" id="event_time" required />';
        echo '</div>';
        echo '</div>';
        
        echo '<div class="form-group">';
        echo '<label for="event_location">Location</label>';
        echo '<input type="text" name="event_location" id="event_location" required />';
        echo '</div>';
        
        echo '<div class="form-group">';
        echo '<input type="submit" name="submit" id="submit" class="button button-primary" value="Create Event" />';
        echo '</div>';
        
        echo '</form>';
        echo '</div>';
        $this->render_event_styles();
    }


    private function render_edit_modal() {
        ?>
        <div id="edit-event-modal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Edit Event</h2>
                <form id="edit-event-form">
                    <input type="hidden" id="edit-event-id" name="event_id">
                    <div class="form-group">
                        <label for="edit-event-title">Event Title</label>
                        <input type="text" id="edit-event-title" name="event_title" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-event-description">Event Description</label>
                        <textarea id="edit-event-description" name="event_description" required></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit-event-category">Category</label>
                            <select id="edit-event-category" name="event_category" required>
                                <option value="">Select a category</option>
                                <?php
                                $categories = ['Music', 'Sports', 'Food', 'Technology', 'Art', 'Business', 'Education', 'Health', 'Science', 'Social'];
                                foreach ($categories as $category) {
                                    echo '<option value="' . esc_attr($category) . '">' . esc_html($category) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit-event-date">Date</label>
                            <input type="date" id="edit-event-date" name="event_date" required>
                        </div>
                        <div class="form-group">
                            <label for="edit-event-time">Time</label>
                            <input type="time" id="edit-event-time" name="event_time" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit-event-location">Location</label>
                        <input type="text" id="edit-event-location" name="event_location" required>
                    </div>
                    <div class="form-group">
                        <input type="submit" class="button button-primary" value="Update Event">
                    </div>
                </form>
            </div>
        </div>
        <?php
    }

    private function render_event_scripts() {
        ?>
        <script>
        jQuery(document).ready(function($) {
            var modal = $('#edit-event-modal');
            var span = $('.close');

            $('.edit-event').click(function() {
                var eventData = $(this).data('event');
                $('#edit-event-id').val(eventData.id);
                $('#edit-event-title').val(eventData.title);
                $('#edit-event-description').val(eventData.description);
                $('#edit-event-category').val(eventData.category);
                $('#edit-event-date').val(eventData.date);
                $('#edit-event-time').val(eventData.time);
                $('#edit-event-location').val(eventData.location);
                modal.show();
            });

            span.click(function() {
                modal.hide();
            });

            $(window).click(function(event) {
                if (event.target == modal[0]) {
                    modal.hide();
                }
            });

            $('#edit-event-form').submit(function(e) {
                e.preventDefault();
                var formData = $(this).serialize();
                var eventId = $('#edit-event-id').val();

                $.ajax({
                    url: 'http://127.0.0.1:8000/api/v1/events/' + eventId,
                    type: 'PUT',
                    data: formData,
                    success: function(response) {
                        alert('Event updated successfully!');
                        modal.hide();
                        location.reload();
                    },
                    error: function() {
                        alert('Failed to update event. Please try again.');
                    }
                });
            });

            $('.delete-event').click(function() {
                if (confirm('Are you sure you want to delete this event?')) {
                    var eventId = $(this).data('id');

                    $.ajax({
                        url: 'http://127.0.0.1:8000/api/v1/events/' + eventId,
                        type: 'DELETE',
                        success: function(response) {
                            alert('Event deleted successfully!');
                            location.reload();
                        },
                        error: function() {
                            alert('Failed to delete event. Please try again.');
                        }
                    });
                }
            });
        });
        </script>
        <?php
    }

    private function render_event_styles() {
        echo '<style>
             .event-api-wrap {
                max-width: 1200px;
                margin: 0 auto;
                padding: 20px;
            }
            .event-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
                gap: 20px;
                margin-top: 20px;
            }
            .event-card {
                background: #fff;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                padding: 20px;
                transition: box-shadow 0.3s ease;
            }
            .event-card:hover {
                box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            }
            .event-card h3 {
                margin-top: 0;
                color: #23282d;
            }
            .event-description {
                color: #646970;
                margin-bottom: 15px;
            }
            .event-meta {
                display: flex;
                justify-content: space-between;
                font-size: 0.9em;
                color: #646970;
                margin-bottom: 10px;
            }
            .event-location {
                display: flex;
                align-items: center;
                color: #646970;
                font-size: 0.9em;
                margin: 0;
            }
            .event-location .dashicons {
                margin-right: 5px;
            }
            .event-empty-state {
                text-align: center;
                padding: 50px 20px;
                background: #fff;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .event-empty-state .dashicons {
                font-size: 48px;
                width: 48px;
                height: 48px;
                color: #646970;
            }
            .event-form {
                max-width: 600px;
                margin: 0 auto;
                background: #fff;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .form-group {
                margin-bottom: 20px;
            }
            .form-group label {
                display: block;
                margin-bottom: 5px;
                font-weight: 600;
            }
            .form-group input[type="text"],
            .form-group input[type="date"],
            .form-group input[type="time"],
            .form-group select,
            .form-group textarea {
                width: 100%;
                padding: 8px;
                border: 1px solid #ddd;
                border-radius: 4px;
            }
            .form-group textarea {
                height: 120px;
            }
            .form-row {
                display: flex;
                gap: 20px;
            }
            .form-row .form-group {
                flex: 1;
            }
            .button-primary {
                background: #2271b1;
                border-color: #2271b1;
                color: #fff;
                padding: 10px 15px;
                font-size: 14px;
            }
            .button-primary:hover {
                background: #135e96;
                border-color: #135e96;
            }

            .event-actions {
                margin-top: 15px;
            }
            .event-actions .button {
                margin-right: 10px;
            }
            .modal {
                display: none;
                position: fixed;
                z-index: 1000;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                overflow: auto;
                background-color: rgba(0,0,0,0.4);
            }
            .modal-content {
                background-color: #fefefe;
                margin: 10% auto;
                padding: 20px;
                border: 1px solid #888;
                width: 60%;
                max-width: 600px;
                border-radius: 8px;
            }
            .close {
                color: #aaa;
                float: right;
                font-size: 28px;
                font-weight: bold;
                cursor: pointer;
            }
            .close:hover,
            .close:focus {
                color: #000;
                text-decoration: none;
                cursor: pointer;
            }
        </style>';
    }
 
}