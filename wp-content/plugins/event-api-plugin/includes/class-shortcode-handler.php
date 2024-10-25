<?php

if (!defined('ABSPATH')) {
    exit;
}

class Event_Shortcode_Handler {

    private $api;
    private $events_per_page = 9; // Number of events to display per page

    public function __construct() {
        $this->api = new Event_API();
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_get_event_details', [$this, 'get_event_details']);
        add_action('wp_ajax_nopriv_get_event_details', [$this, 'get_event_details']);
    }

    public function register_shortcodes() {
        add_shortcode('event_list', [$this, 'render_event_list']);
    }

    public function enqueue_scripts() {
        wp_enqueue_style('dashicons');
        wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap', array(), null);
        wp_enqueue_style('event-styles', plugin_dir_url(dirname(__FILE__)) . 'assets/css/style.css');
        wp_enqueue_script('event-script', plugin_dir_url(dirname(__FILE__)) . 'assets/js/event-script.js', array('jquery'), '1.0', true);
        wp_localize_script('event-script', 'event_ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
    }

    public function render_event_list($atts) {
        $atts = shortcode_atts([
            'category' => '',
            'sort' => 'date',
            'view' => 'grid',
        ], $atts);

        // Fetch events from the API
        $events = $this->api->get_events($atts['category'], $atts['sort']);

        // Get unique categories
        $categories = array_unique(array_column($events, 'category'));

        // Pagination
        $current_page = isset($_GET['event_page']) ? max(1, intval($_GET['event_page'])) : 1;
        $total_events = count($events);
        $total_pages = ceil($total_events / $this->events_per_page);
        $offset = ($current_page - 1) * $this->events_per_page;
        $paged_events = array_slice($events, $offset, $this->events_per_page);

        ob_start();

        echo '<div class="event-container">';
        
        // Add search, sort, and filter controls
        echo '<div class="event-controls">';
        echo '<div class="event-search">';
        echo '<input type="text" id="event-search-input" placeholder="Search events...">';
        echo '<button id="event-search-btn"><span class="dashicons dashicons-search"></span></button>';
        echo '</div>';

        echo '<div class="event-sort">';
        echo '<select id="event-sort-select">';
        echo '<option value="date">Sort by Date</option>';
        echo '<option value="title">Sort by Title</option>';
        echo '</select>';
        echo '</div>';
        

        echo '<div class="event-filter">';
        echo '<select id="event-category-select">';
        echo '<option value="">All Categories</option>';
        foreach ($categories as $category) {
            echo '<option value="' . esc_attr($category) . '">' . esc_html($category) . '</option>';
        }
        echo '</select>';
        echo '</div>';
        echo '</div>';

        // View toggle buttons
        echo '<div class="view-toggle">';
        echo '<span class="view-toggle-label">View:</span>';
        echo '<button class="toggle-btn ' . ($atts['view'] == 'grid' ? 'active' : '') . '" data-view="grid"><span class="dashicons dashicons-grid-view"></span> Grid</button>';
        echo '<button class="toggle-btn ' . ($atts['view'] != 'grid' ? 'active' : '') . '" data-view="list"><span class="dashicons dashicons-list-view"></span> List</button>';
        echo '</div>';
        
        // Events wrapper
        echo '<div class="events-wrapper ' . ($atts['view'] == 'grid' ? 'grid-view' : 'list-view') . '">';

        // Loop through paged events and display them
        foreach ($paged_events as $event) {
            $category_color = $this->get_category_color($event['category']);
            echo '<div class="event-card" style="border-top: 5px solid ' . $category_color . ';" 
                data-title="' . esc_attr(strtolower($event['title'])) . '" 
                data-date="' . esc_attr($event['date']) . '" 
                data-id="' . esc_attr($event['id']) . '" 
                data-category="' . esc_attr($event['category']) . '"
                data-description="' . esc_attr($event['description']) . '"
                data-time="' . esc_attr($event['time']) . '"
                data-location="' . esc_attr($event['location']) . '"
                data-created-at="' . esc_attr($event['created_at']) . '"
                data-updated-at="' . esc_attr($event['updated_at']) . '">';
            
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
            
            // View event button
            echo '<a href="#" class="view-event-btn" style="background-color: ' . $category_color . ';">View Event</a>';
            echo '</div>';
        }

        echo '</div>';

        // Pagination
        $this->render_pagination($current_page, $total_pages);

        echo '</div>';

        // Event detail modal
        echo '<div id="event-detail-modal" class="modal">';
        echo '<div class="modal-content">';
        echo '<span class="close">&times;</span>';
        echo '<div id="event-detail-content"></div>';
        echo '</div>';
        echo '</div>';

        // JavaScript for search, sort, filter, and modal functionality
        echo '<script>
            jQuery(document).ready(function($) {
                function filterEvents() {
                    var searchTerm = $("#event-search-input").val().toLowerCase();
                    var category = $("#event-category-select").val().toLowerCase();
                    $(".event-card").each(function() {
                        var title = $(this).data("title");
                        var eventCategory = $(this).data("category").toLowerCase();
                        if (title.indexOf(searchTerm) > -1 && (category === "" || eventCategory === category)) {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    });
                }

                function sortEvents() {
                    var sortBy = $("#event-sort-select").val();
                    var $wrapper = $(".events-wrapper");
                    var $cards = $wrapper.children(".event-card");

                    $cards.sort(function(a, b) {
                        if (sortBy === "date") {
                            return new Date($(a).data("date")) - new Date($(b).data("date"));
                        } else {
                            return $(a).data("title").localeCompare($(b).data("title"));
                        }
                    });

                    $wrapper.empty().append($cards);
                }

                $("#event-search-input, #event-category-select").on("input change", filterEvents);
                $("#event-search-btn").on("click", filterEvents);

                $("#event-sort-select").change(sortEvents);

                $(".toggle-btn").click(function() {
                    $(".toggle-btn").removeClass("active");
                    $(this).addClass("active");
                    var view = $(this).data("view");
                    $(".events-wrapper").removeClass("grid-view list-view").addClass(view + "-view");
                });

                // Event detail modal
                $(".event-card, .view-event-btn").click(function(e) {
                    e.preventDefault();
                    var $eventCard = $(this).closest(".event-card");
                    var eventDetails = {
                        id: $eventCard.data("id"),
                        title: $eventCard.data("title"),
                        description: $eventCard.data("description"),
                        date: $eventCard.data("date"),
                        time: $eventCard.data("time"),
                        location: $eventCard.data("location"),
                        category: $eventCard.data("category"),
                        created_at: $eventCard.data("created-at"),
                        updated_at: $eventCard.data("updated-at")
                    };
                    showEventDetails(eventDetails);
                });

                function showEventDetails(event) {
                    var categoryColor = getCategoryColor(event.category);
                    var content = `
                        <div class="event-modal-content">
                            <h2>${event.title}</h2>
                            <span class="event-category" style="background-color: ${categoryColor};">${event.category}</span>
                            <p class="event-description">${event.description}</p>
                            <div class="event-details">
                                <p><strong>Date:</strong> ${event.date}</p>
                                <p><strong>Time:</strong> ${event.time}</p>
                                <p><strong>Location:</strong> ${event.location}</p>
                                <p><strong>Created:</strong> ${event.created_at}</p>
                                <p><strong>Updated:</strong> ${event.updated_at}</p>
                            </div>
                        </div>
                    `;
                    $("#event-detail-content").html(content);
                    $("#event-detail-modal").show();
                }

                function getCategoryColor(category) {
                    var colors = {
                        "Music": "#FF1168",
                        "Sports": "#00A3E0",
                        "Food": "#F05537",
                        "Technology": "#6B7A8F",
                        "Art": "#7AC142",
                        "Business": "#2C3E50",
                        "Education": "#3498DB",
                        "Health": "#2ECC71",
                        "Science": "#9B59B6",
                        "Social": "#E67E22",
                        "Entertainment": "#9C27B0"
                    };
                    return colors[category] || "#6C757D";
                }

                $(".close").click(function() {
                    $("#event-detail-modal").hide();
                });

                $(window).click(function(e) {
                    if ($(e.target).is("#event-detail-modal")) {
                        $("#event-detail-modal").hide();
                    }
                });
            });
        </script>';

        return ob_get_clean();
    }

    private function render_pagination($current_page, $total_pages) {
        if ($total_pages > 1) {
            echo '<div class="event-pagination">';
            
            // Previous page
            if ($current_page > 1) {
                echo '<a href="' . add_query_arg('event_page', $current_page - 1) . '" class="pagination-link">&laquo; Previous</a>';
            }

            // Page numbers
            for ($i = 1; $i <= $total_pages; $i++) {
                if ($i == $current_page) {
                    echo '<span class="pagination-link current">' . $i . '</span>';
                } else {
                    echo '<a href="' . add_query_arg('event_page', $i) . '" class="pagination-link">' . $i . '</a>';
                }
            }

            // Next page
            if ($current_page < $total_pages) {
                echo '<a href="' . add_query_arg('event_page', $current_page + 1) . '" class="pagination-link">Next &raquo;</a>';
            }

            echo '</div>';
        }
    }

    public function get_event_details() {
        if (isset($_POST['event_id'])) {
            $event_id = intval($_POST['event_id']);
            $event = $this->api->get_event($event_id);

            if ($event) {
                $category_color = $this->get_category_color($event['category']);
                $output = '<div class="event-modal-content">';
                $output .= '<h2>' . esc_html($event['title']) . '</h2>';
                $output .= '<span class="event-category" style="background-color: ' . $category_color . ';">' . esc_html($event['category']) . '</span>';
                $output .= '<p class="event-description">' . esc_html($event['description']) . '</p>';
                $output .= '<div class="event-details">';
                $output .= '<p><strong>Date:</strong> ' . esc_html($event['date']) . '</p>';
                $output .= '<p><strong>Time:</strong> ' . esc_html($event['time']) . '</p>';
                $output .= '<p><strong>Location:</strong> ' . esc_html($event['location']) . '</p>';
                $output .= '</div>';
                $output .= '</div>';
                echo $output;
            } else {
                echo '<p>Event not found.</p>';
            }
        } else {
            echo '<p>Invalid request.</p>';
        }
        wp_die();
    }

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
            'Entertainment' => '#9C27B0'
        ];

        return isset($colors[$category]) ? $colors[$category] : '#6C757D';
    }
}