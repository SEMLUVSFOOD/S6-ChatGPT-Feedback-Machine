<?php
/*
Plugin Name: ChatGPT API Connector
Description: Connects WordPress to the ChatGPT API.
Version: 1.0
Author: You
*/

add_shortcode('chatgpt_form', 'chatgpt_form_shortcode');

function chatgpt_form_shortcode() {
    ob_start();
    ?>
    <form method="post">
        <input type="text" name="chatgpt_prompt" placeholder="Ask ChatGPT something..." required>
        <button type="submit">Submit</button>
    </form>
    <?php
    if (!empty($_POST['chatgpt_prompt'])) {
        $response = chatgpt_get_response($_POST['chatgpt_prompt']);
        echo '<div><strong>Response:</strong> ' . esc_html($response) . '</div>';
    }
    return ob_get_clean();
}

function chatgpt_get_response($prompt) {
    require_once plugin_dir_path(__FILE__) . 'secret-config.php';
    $api_key = CHATGPT_API_KEY;
    $url = 'https://api.openai.com/v1/chat/completions';

    $data = [
        'model' => 'gpt-3.5-turbo',
        'messages' => [['role' => 'user', 'content' => $prompt]],
        'temperature' => 0.7,
    ];

    $response = wp_remote_post($url, [
        'headers' => [
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type'  => 'application/json',
        ],
        'body' => json_encode($data),
    ]);

    if (is_wp_error($response)) {
        return 'Error: ' . $response->get_error_message();
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    return $body['choices'][0]['message']['content'] ?? 'No response.';
}
