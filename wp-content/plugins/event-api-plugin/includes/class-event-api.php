<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Event_API {
    
    private $api_base_url = 'http://127.0.0.1:8000/api/v1/';
    private $token = 'kG9Nn76y9vNbHmAQolXQGgXPoyzEy6nmk95khvRb04fc44de';
    
    public function get_events($category = '', $sort = 'date') {
        $url = $this->api_base_url . 'events';
        
        if (!empty($category)) {
            $url = $this->api_base_url . 'events/category/' . $category;
        }
        
        $response = wp_remote_get($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
            ],
        ]);
        
        if (is_wp_error($response)) {
            error_log('Event API Error (get_events): ' . $response->get_error_message());
            return [];
        }
    
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!isset($data['data'])) {
            error_log('Event API Unexpected Response (get_events): ' . print_r($data, true));
            return [];
        }
        
        return $data['data'];
    }
    
    public function get_event($id) {
        $url = $this->api_base_url . 'events/' . $id;
        $response = wp_remote_get($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
            ],
        ]);

        if (is_wp_error($response)) {
            error_log('Event API Error (get_event): ' . $response->get_error_message());
            return null;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!isset($data['data'])) {
            error_log('Event API Unexpected Response (get_event): ' . print_r($data, true));
            return null;
        }
        
        return $data['data'];
    }

    public function create_event($title, $description, $category, $date, $time, $location) {
        $url = $this->api_base_url . 'events';
    
        $body = json_encode([
            'title' => $title,
            'description' => $description,
            'category' => $category,
            'date' => $date,
            'time' => $time,
            'location' => $location,
        ]);
    
        error_log('Event API Request URL: ' . $url);
        error_log('Event API Request Body: ' . $body);

        $response = wp_remote_post($url, [
            'body' => $body,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->token,
            ],
        ]);
    
        if (is_wp_error($response)) {
            error_log('Event API Error (create_event): ' . $response->get_error_message());
            return false;
        }
    
        $body = wp_remote_retrieve_body($response);
        error_log('Event API Response: ' . $body);

        $data = json_decode($body, true);
    
        if (!isset($data['data'])) {
            error_log('Event API Unexpected Response (create_event): ' . print_r($data, true));
            return false;
        }
    
        return $data['data'];
    }
}