<?php
/**
 * Plugin Name: Evidence Analyzer with ChatGPT
 * Description: A plugin to analyze evidence and classify it into Learning Outcomes.
 * Version: 1.0
 * Author: Your Name
 */

// Include the secret config (with the API key)
include_once plugin_dir_path( __FILE__ ) . 'secret-config.php';

// Shortcode to render the input form
function evidence_analyzer_form() {
    ob_start();
    ?>
    <style>
        /* Container styling */
        .evidence-analyzer-container {
            width: 80vw;
            max-width: 80vw;
            height: 50vh;
            margin: 20px auto;
            background-color: #F9C926; /* Green background */
            border-radius: 50px; /* 50px border radius */
            padding: 40px;
            display: flex;
            gap: 10%;
            justify-content: space-between;
            align-items: flex-start;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        /* Left side (input area and button) */
        .evidence-analyzer-left {
            width: 60%;
            display: flex;
            flex-direction: column;
        }

        /* Textarea styling */
        .evidence-analyzer-left textarea {
            width: 100%;
            height: 120px;
            padding: 10px;
            border-radius: 10px;
            border: none;
            resize: none;
            font-size: 14px;
        }

        /* Button styling */
        .evidence-analyzer-left button {
            background-color: #fff;
            color: #00000;
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            margin-top: 10px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .evidence-analyzer-left button:hover {
            background-color: #e9ecef;
        }

        /* Right side (feedback area) */
        .evidence-analyzer-right {
            width: 35%;
            padding: 10px;
            height: 95% !important;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            font-size: 14px;
            line-height: 1.5;
            color: #333;
            height: 200px;
            overflow-y: auto;
        }
    </style>
    <div class="evidence-analyzer-container">
        <!-- Left side: form for input -->
        <div class="evidence-analyzer-left">
            <form method="post">
                <textarea name="evidence_text" rows="5" cols="50" placeholder="Enter your evidence here..."></textarea><br>
                <button type="submit" name="analyze_evidence">Analyze Evidence</button>
            </form>
        </div>

        <!-- Right side: ChatGPT feedback -->
        <div class="evidence-analyzer-right">
            <?php
            if (isset($_POST['analyze_evidence'])) {
                $evidence_text = sanitize_text_field($_POST['evidence_text']);
                echo "<h3>Analysis Result:</h3>";
                if (empty($evidence_text)) {
                    echo "<p>No evidence provided. Please fill in the evidence text.</p>";
                } else {
                    echo "<p>" . analyze_evidence_with_chatgpt($evidence_text) . "</p>";
                }
            }
            ?>
        </div>
    </div>

    <?php
    return ob_get_clean();
}

add_shortcode('evidence_analyzer', 'evidence_analyzer_form');

// Function to send the evidence to ChatGPT and categorize it into Learning Outcomes
function analyze_evidence_with_chatgpt($evidence_text) {
    require_once plugin_dir_path(__FILE__) . 'secret-config.php';
    $api_key = CHATGPT_API_KEY;

    // Construct a clear and detailed description for each Learning Outcome (LO)
    $prompt = "Analyze the following evidence text and assign it to the appropriate learning outcomes (strict and concise). Only provide the relevant LOs and a brief explanation of 1-2 sentences per LO. Each LO should be formatted like this:\n\n";

    // Detailed descriptions of each LO
    $prompt .= "<strong>LO1: User interaction (analyse & advise)</strong><br>";
    $prompt .= "This outcome involves analyzing the user, their needs, behaviors, and experiences. It also includes advising on user experience (UX) interventions based on research and design principles.<br><br>";

    $prompt .= "<strong>LO2: User interaction (execution & validation)</strong><br>";
    $prompt .= "This outcome focuses on executing and evaluating the user experience of an interactive product. It includes validating the design decisions based on user feedback and usability tests.<br><br>";

    $prompt .= "<strong>LO3: Realisation of technical products</strong><br>";
    $prompt .= "This outcome applies to evidence involving the creation of technical products. It includes the use of programming languages, frameworks, or tools to build products and systems.<br><br>";

    $prompt .= "<strong>LO4: Professional standard</strong><br>";
    $prompt .= "This outcome involves demonstrating responsibility, ethical behavior, and professionalism in the workplace. It includes meeting deadlines, maintaining quality, and communicating effectively with stakeholders.<br><br>";

    $prompt .= "<strong>LO5: Personal leadership</strong><br>";
    $prompt .= "This outcome focuses on personal growth, goal setting, and leadership in your own development. It includes taking responsibility for your tasks, demonstrating initiative, and managing your time and work independently.<br><br>";

    $prompt .= "Please analyze the following evidence:\n";
    $prompt .= "{$evidence_text}\n\n";

    $prompt .= "Important: Please ensure that each relevant LO is mentioned and followed by a brief explanation (1-2 sentences). If an LO does not apply to the evidence, **do not include it**. Only include the relevant LOs.\n";

    // API request to ChatGPT with the correct endpoint and message format
    $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
        'headers' => [
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type'  => 'application/json',
        ],
        'body' => json_encode([
            'model' => 'gpt-3.5-turbo',  // You can change this to 'gpt-4' if you have access
            'messages' => [['role' => 'user', 'content' => $prompt]],
            'temperature' => 0.5, // Adjust this value for more creativity
        ])
    ]);

    if (is_wp_error($response)) {
        return 'Error: ' . $response->get_error_message();
    }

    // Get the body of the response
    $body = wp_remote_retrieve_body($response);

    // Decode the response to an associative array
    $data = json_decode($body, true);

    // Check if response contains the 'choices' and return the content
    return $data['choices'][0]['message']['content'] ?? 'No response from API.';
}