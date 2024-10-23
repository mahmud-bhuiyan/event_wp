<?php

if (!defined('ABSPATH')) {
    exit;
}

class Event_Shortcode_Handler {

    private $api;

    public function __construct() {
        $this->api = new Event_API();
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function register_shortcodes() {
        add_shortcode('event_list', [$this, 'render_event_list']);
    }

    /**
     * Enqueue necessary styles and scripts
     */
    public function enqueue_scripts() {
        wp_enqueue_style('dashicons');
        wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap', array(), null);
        wp_enqueue_style('event-styles', plugin_dir_url(dirname(__FILE__)) . 'assets/css/style.css');
        wp_enqueue_script('event-script', plugin_dir_url(dirname(__FILE__)) . 'assets/js/event-script.js', array('jquery'), '1.0', true);
    }

    /**
     * Render the event list
     * 
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public function render_event_list($atts) {
        $atts = shortcode_atts([
            'category' => '',
            'sort' => 'date',
            'view' => 'grid',
        ], $atts);

        // Fetch events from the API
        $events = $this->api->get_events($atts['category'], $atts['sort']);

        ob_start();

        echo '<div class="event-container">';
        
        // View toggle buttons
        echo '<div class="view-toggle">';
        echo '<button class="toggle-btn ' . ($atts['view'] == 'grid' ? 'active' : '') . '" data-view="grid"><span class="dashicons dashicons-grid-view"></span> Grid View</button>';
        echo '<button class="toggle-btn ' . ($atts['view'] != 'grid' ? 'active' : '') . '" data-view="list"><span class="dashicons dashicons-list-view"></span> List View</button>';
        echo '</div>';
        
        // Events wrapper
        echo '<div class="events-wrapper ' . ($atts['view'] == 'grid' ? 'grid-view' : 'list-view') . '">';

        // Loop through events and display them
        foreach ($events as $event) {
            $category_color = $this->get_category_color($event['category']);
            echo '<div class="event-card" style="border-top: 5px solid ' . $category_color . ';">';
            
            // Make the entire card clickable
            echo '<a href="#" class="event-card-link">';
            
            // Event content
            echo '<div class="event-content">';
            echo '<span class="event-category" style="background-color: ' . $category_color . ';">' . esc_html($event['category']) . '</span>';
            echo '<h3 class="event-title">' . esc_html($event['title']) . '</h3>';
            echo '<p class="event-description">' . esc_html(wp_trim_words($event['description'], 20)) . '</p>';
            
            // Event details
            echo '<div class="event-details">';
            echo '<span class="event-date"><span class="dashicons dashicons-calendar-alt"></span> ' . esc_html($event['date']) . '</span>';
            echo '<span class="event-time"><span class="dashicons dashicons-clock"></span> ' . esc_html($event['time']) . '</span>';
            echo '<span class="event-location"><span class="dashicons dashicons-location"></span> ' . esc_html($event['location']) . '</span>';
            echo '</div>';
            echo '</div>';
            
            echo '</a>';
            
            // View event button
            echo '<a href="#" class="view-event-btn" style="background-color: ' . $category_color . ';">View Event</a>';
            echo '</div>';
        }

        echo '</div>';
        echo '</div>';

        return ob_get_clean();
    }

    /**
     * Get color for event category
     * 
     * @param string $category Event category
     * @return string Hex color code
     */
    private function get_category_color($category) {
        $colors = [
            'Music'      => '#FF1168',
            'Sports'     => '#00A3E0',
            'Food'       => '#F05537',
            'Technology' => '#6B7A8F',
            'Art'        => '#7AC142',
            'Business'   => '#2C3E50',
            'Education'  => '#3498DB',
            'Health'     => '#2ECC71',
            'Science'    => '#9B59B6',
            'Social'     => '#E67E22',
        ];

        return isset($colors[$category]) ? $colors[$category] : '#6C757D';
    }
}