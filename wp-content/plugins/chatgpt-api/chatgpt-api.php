<?php
/*
Plugin Name: ChatGPT LO1 Feedback Form (Student Style Output)
Description: LO1 form with teacher-style summary and final score label.
Version: 1.2
Author: You
*/

add_shortcode('form_learning_outcome_1', 'form_learning_outcome_1_shortcode');

function form_learning_outcome_1_shortcode() {
    ob_start();

    $criteria = [
        "User Research & Use of Technology" => [
            "Extensive research using 3+ methods. 2+ advanced technologies explored and critically applied.",
            "2 methods used with valid insights. 1 relevant technology applied meaningfully.",
            "1 method with limited insights. Technology mentioned, unclear application.",
            "Minimal research, mostly assumptions. Technology mentioned but unused.",
            "No research or technology present."
        ],
        "Design Process & Advice Communication" => [
            "Iterative process, multiple versions, well-argued, visual advice tailored to stakeholders.",
            "Process followed with iteration. Advice understandable and supported.",
            "Process present but not consistent. Advice is vague.",
            "Unclear or random process. Advice confusing.",
            "No process or advice provided."
        ]
    ];

    echo "<form method='post' style='margin-top:0; padding-top:0;'>";
    echo "<table style='width:100%; border-collapse: collapse; margin-top:0; padding-top:0;' border='1'>";    
    echo "<tr>
            <th>Criteria</th>
            <th>4 - Advanced</th>
            <th>3 - Proficient</th>
            <th>2 - Beginning</th>
            <th>1 - Orienting</th>
            <th>0 - Undefined</th>
            <th>Result</th>
          </tr>";

    foreach ($criteria as $criterion => $descriptions) {
        $field_key = strtolower(str_replace(' ', '_', $criterion));
        echo "<tr>";
        echo "<td><strong>$criterion</strong></td>";
        foreach ($descriptions as $desc) {
            echo "<td style='padding: 6px;'>$desc</td>";
        }
        echo "<td>
                <label>Score:
                    <select name='{$field_key}_score' required>";
        for ($i = 0; $i <= 4; $i++) {
            echo "<option value='$i'>$i</option>";
        }
        echo "      </select>
                </label><br><br>
                <label>Feedback:</label><br>
                <textarea name='{$field_key}_feedback' rows='4' cols='30' required></textarea>
              </td>";
        echo "</tr>";
    }

    echo "</table><br>";
    echo "<input type='submit' name='submit_lo1' value='Generate Student Summary'>";
    echo "</form>";

    if (isset($_POST['submit_lo1'])) {
        $prompt = "You are an ICT teacher writing student feedback for a portfolio review.\n\n";
        $prompt .= "Below is your feedback on two criteria from Learning Outcome 1.\n";
        $prompt .= "Write one clear, short paragraph as if written by the teacher, in plain and supportive language.\n";
        $prompt .= "Then suggest a final score (label only) for the overall learning outcome using this scale:\n";
        $prompt .= "0 = Undefined, 1 = Orienting, 2 = Beginning, 3 = Proficient, 4 = Advanced.\n\n";

        foreach ($criteria as $criterion => $desc) {
            $field_key = strtolower(str_replace(' ', '_', $criterion));
            $score = sanitize_text_field($_POST["{$field_key}_score"]);
            $feedback = sanitize_textarea_field($_POST["{$field_key}_feedback"]);

            $prompt .= "Criterion: $criterion\n";
            $prompt .= "Given Score: $score\n";
            $prompt .= "Teacher Feedback: $feedback\n\n";
        }

        $prompt .= "Format your reply like this:\n";
        $prompt .= "Final Score: [0–4] – [Label]\nFeedback: [Short, rewritten summary to student]\n";

        $response = chatgpt_get_response_lo1($prompt);

        echo "<h3>Final Student-Facing Summary:</h3>";
        echo "<div style='white-space: pre-wrap; border: 1px solid #ccc; padding: 10px; background: #f9f9f9;'>" . esc_html($response) . "</div>";
    }

    return ob_get_clean();
}

function chatgpt_get_response_lo1($prompt) {
    require_once plugin_dir_path(__FILE__) . 'secret-config.php';
    $api_key = CHATGPT_API_KEY;

    $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
        'headers' => [
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type'  => 'application/json',
        ],
        'body' => json_encode([
            'model' => 'gpt-3.5-turbo',
            'messages' => [['role' => 'user', 'content' => $prompt]],
            'temperature' => 0.5,
        ])
    ]);

    if (is_wp_error($response)) {
        return 'Error: ' . $response->get_error_message();
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    return $data['choices'][0]['message']['content'] ?? 'No response from API.';
}
