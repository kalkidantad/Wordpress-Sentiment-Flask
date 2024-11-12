<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @package    Api_Training
 * @subpackage Api_Training/public
 * @author     Khalid <khalinoid@gmail.com>
 */

 class Api_Training_APIs {

    public function __construct()
    {
        // Register shortcode and action
        add_shortcode('my_shortcode', [$this, 'api_training_shortcodes']);
        add_action('the_content', [$this, 'display_sentiment_result']);
    }

    // Function to call the Flask API
    function call_flask_api($text) {
        $flask_url = 'http://flask:5000/predict';

        $response = wp_remote_post($flask_url, array(
            'method'    => 'POST',
            'headers'   => array('Content-Type' => 'application/json'),
            'body'      => json_encode(array('text' => $text)),
        ));

        if (is_wp_error($response)) {
            error_log('API Request Error: ' . $response->get_error_message());
            return 'Error in API request';
        }

        $body = wp_remote_retrieve_body($response);
        error_log('API Response: ' . $body); 
        $result = json_decode($body, true);

        return $result['sentiment result'] ?? 'No response';
    }

    // Function to display the sentiment result on the WordPress site
    function display_sentiment_result($content) {
        // Example text for testing
        $sentiment = $this->call_flask_api('This is a test message');
        return $content  ;
    }

    // Shortcode function for the plugin
    function api_training_shortcodes() {
        ob_start();

        /// Initialize sentiment variable
        $sentiment = '';

        // Check if the form is submitted
        if (isset($_POST['user_input'])) {
            $user_input = sanitize_text_field($_POST['user_input']);
            $sentiment = $this->call_flask_api($user_input);
        }


        // Display the form
        ?>
        <form method="post">
            <label for="user_input">Enter your text:</label>
            <textarea id="user_input" name="user_input" rows="4" cols="50" required></textarea>
            <br>
            <input type="submit" value="Submit">
        </form>
        <?php
        // Only display the sentiment if it has been set
        if ($sentiment) {
            echo "<div> The result of the analysis is: " . esc_html($sentiment) . "</div>";
        }

        return ob_get_clean();
    }
}