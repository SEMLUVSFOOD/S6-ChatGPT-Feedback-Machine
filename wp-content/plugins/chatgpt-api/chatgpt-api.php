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
            // 4 - Advanced
            "Consistently uses 3+ qualitative and/or quantitative methods across projects (e.g. interviews, observations, expert input, field research). Creates clear personas, identifies user pain points, explores 2+ new or relevant technologies and applies them critically to context.",
            // 3 - Proficient
            "Uses multiple methods in most projects. Delivers solid user insights and applies at least 1 relevant technology meaningfully. Shows good awareness of user needs and project context.",
            // 2 - Beginning
            "Uses 1–2 basic methods (e.g. a survey or a single interview). Identifies some user needs. Mentions a relevant technology and tries to apply it in a limited or early way.",
            // 1 - Orienting
            "Starts using research (e.g. simple desk research or short user interaction). Understands basic user needs. Technology is explored but not clearly applied.",
            // 0 - Undefined
            "No user research performed. No analysis of technology or user needs available."
        ],
        "Design Process & Advice Communication" => [
            // 4 - Advanced
            "Chooses an appropriate design process tailored to the project. Shows full documentation of multiple iterations. Advice is clear, visual, justified and tailored to stakeholders (e.g. through deliverables like POCs or concept videos).",
            // 3 - Proficient
            "Follows a structured process with at least one clear iteration. Advice is understandable and based on research. Feedback is addressed and reflected upon in documentation.",
            // 2 - Beginning
            "Applies a simple process (e.g. double diamond or HCD steps). Shows some logical steps and early advice based on limited user input or prototype. Advice is somewhat generic.",
            // 1 - Orienting
            "Shows an early start of a design process, but unclear structure or goals. Advice is short, basic, or not yet grounded in research.",
            // 0 - Undefined
            "No process or advice is shown. No communication of insights or decisions."
        ]
    ];    

    echo "<form method='post' style='margin-top:0; padding-top:0;'>";
    echo "<table style='width:100%; border-collapse: collapse; font-size: 13px;' border='1'>";
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
                <textarea name='{$field_key}_feedback' rows='4' cols='30' style='font-size: 13px;' required></textarea>
              </td>";
        echo "</tr>";
    }

    echo "</table><br>";
    echo "<input type='submit' class='submitButton' name='submit_lo1' value='Calculate Result and Generate Feedback (Summary) 👨‍🎓'>";
    echo "</form>";

    if (isset($_POST['submit_lo1'])) {
        $prompt = "You are an ICT teacher reviewing a student's work. Based on the feedback for each criterion below, rewrite the feedback in a clear, professional tone — as if written by the teacher directly to the student.\n";
        $prompt .= "Keep the content largely the same, but improve structure, grammar, and clarity. Do not shorten or overly summarize.\n";
        $prompt .= "At the end, provide a final score for Learning Outcome 1 using this scale:\n";
        $prompt .= "0 = Undefined, 1 = Orienting, 2 = Beginning, 3 = Proficient, 4 = Advanced\n\n";
        

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
