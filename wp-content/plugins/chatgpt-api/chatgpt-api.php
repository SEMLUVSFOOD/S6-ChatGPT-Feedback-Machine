<?php
/*
Plugin Name: ChatGPT LO's Feedback Form
Description: LO's rubriks with teacher-style summary and final score label using ChatGPT.
Version: 1.5
Author: You
*/

require_once plugin_dir_path(__FILE__) . 'learning_outcome_criteria.php';
require_once plugin_dir_path(__FILE__) . 'secret-config.php';

session_start();

for ($i = 1; $i <= 5; $i++) {
    add_shortcode("form_learning_outcome_$i", function () use ($i) {
        return render_lo_form($i);
    });
}

function render_lo_form($lo_number) {
    ob_start();
    global $learning_outcome_criteria;

    $lo_key = "Learning Outcome $lo_number";
    $criteria = $learning_outcome_criteria[$lo_key] ?? null;
    if (!$criteria) return "<p>Error: Criteria for $lo_key not found.</p>";

    echo "<form method='post'>";
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

    foreach ($criteria as $criterion => $levels) {
        $field_key = strtolower(str_replace([' ', '&'], ['_', 'and'], $criterion));
        echo "<tr>";
        echo "<td><strong>$criterion</strong></td>";
        for ($i = 4; $i >= 0; $i--) {
            $desc = $levels["$i - " . feedback_label($i)] ?? '';
            echo "<td style='padding: 6px;'>$desc</td>";
        }
        echo "<td>
                <label>Score:
                    <select name='{$field_key}_score' required>";
        for ($i = 0; $i <= 4; $i++) {
            echo "<option value='$i'>$i</option>";
        }
        echo "</select>
                </label><br><br>
                <label>Feedback:</label><br>
                <textarea name='{$field_key}_feedback' rows='4' cols='30' style='font-size: 13px;' required></textarea>
              </td>";
        echo "</tr>";
    }

    echo "</table><br>";
    echo "<input type='submit' class='submitButton' name='submit_lo$lo_number' value='Calculate Result and Generate Feedback (Summary)'>";
    echo "</form>";

    if (isset($_POST["submit_lo$lo_number"])) {
        $prompt = "You are an ICT teacher evaluating a student's performance on **$lo_key**.\n";
        $prompt .= "For each criterion below, the teacher gave a score (0–4) and a feedback note. Your task is to:\n";
        $prompt .= "1. Combine the feedback into a cohesive, supportive paragraph.\n";
        $prompt .= "2. Improve grammar and flow, keeping the feedback content intact.\n";
        $prompt .= "3. Calculate the average score, round to nearest whole number, and assign a label:\n";
        $prompt .= "   0 = Undefined, 1 = Orienting, 2 = Beginning, 3 = Proficient, 4 = Advanced\n\n";

        $valid_criteria_count = 0;
        $total_score = 0;

        foreach ($criteria as $criterion => $desc) {
            $field_key = strtolower(str_replace([' ', '&'], ['_', 'and'], $criterion));

            $score = isset($_POST["{$field_key}_score"]) ? sanitize_text_field($_POST["{$field_key}_score"]) : null;
            $feedback = isset($_POST["{$field_key}_feedback"]) ? sanitize_textarea_field($_POST["{$field_key}_feedback"]) : null;

            if ($score !== null && $feedback !== null && $feedback !== '') {
                $prompt .= "Criterion: $criterion\n";
                $prompt .= "Score: $score\n";
                $prompt .= "Teacher Feedback: $feedback\n\n";
                $valid_criteria_count++;
                $total_score += intval($score);
            }
        }

        if ($valid_criteria_count === 0) {
            return "<p style='color:red;'>No valid feedback submitted. Please complete the form before submitting.</p>";
        }

        $prompt .= "Format:\n";
        $prompt .= "Final Score: [0–4] – [Label]\nFeedback: [Combined feedback paragraph to student]\n";

        $response = chatgpt_get_response($prompt);
        $_SESSION["lo{$lo_number}_feedback"] = $response;

        echo "<br>";
        echo "<div style='white-space: pre-wrap; border: 1px solid #ccc; padding: 10px; background: #f9f9f9;'>" . esc_html($response) . "</div>";
    }

    return ob_get_clean();
}

function feedback_label($num) {
    $labels = ['Undefined', 'Orienting', 'Beginning', 'Proficient', 'Advanced'];
    return $labels[$num] ?? 'Unknown';
}

add_shortcode('learning_outcomes_conclusion', 'learning_outcomes_conclusion_shortcode');

function learning_outcomes_conclusion_shortcode() {
    ob_start();
    session_start();

    echo "<h2>📘 Final Summary of All Learning Outcomes</h2><p>Below is the collected feedback:</p><br>";

    for ($i = 1; $i <= 5; $i++) {
        $key = "lo{$i}_feedback";
        $value = $_SESSION[$key] ?? 'No feedback found for this learning outcome.';
        echo "<h3 style='background-color: #F9C926; padding: 10px;'>Learning Outcome $i</h3>";
        echo "<div style='border: 1px solid #ccc; padding: 10px; background: #fdfdfd; font-size: 13px; white-space: pre-wrap;'>" . esc_html($value) . "</div><br>";
    }

    echo "<h3>🧾 Final Opinion / Wrap-up</h3>";
    echo "<div style='display: flex; justify-content: center; margin-top: 20px;'>
            <textarea name='final_opinion' form='finalFeedbackForm' rows='4' style='width:100%; font-size:13px; padding: 10px; border-radius: 5px; border: 1px solid #ccc;' placeholder='Write your overall impression...'></textarea>
          </div><br><br>";

    echo "<form method='post' id='finalFeedbackForm'>";
    echo "<input type='submit' class='submitButton' name='generate_final_opinion' value='Generate Final Message 📝'>";
    echo "</form>";

    if (isset($_POST['generate_final_opinion'])) {
        $prompt = "You are an ICT teacher giving final feedback on a student's semester performance.\n\n";

        for ($i = 1; $i <= 5; $i++) {
            $key = "lo{$i}_feedback";
            $prompt .= "LO$i Feedback:\n" . ($_SESSION[$key] ?? 'Missing.') . "\n\n";
        }

        $prompt .= "Teacher Final Opinion:\n" . sanitize_textarea_field($_POST['final_opinion']) . "\n\n";
        $prompt .= "Please write a concise final paragraph combining all feedback and advice.\n";
        $prompt .= "Calculate the average LO score, round to the nearest whole number, and assign:\n";
        $prompt .= "0 = Undefined, 1 = Inefficient, 2 = Proficient, 3 = Good, 4 = Outstanding\n\n";
        $prompt .= "Format:\nFinal Evaluation: [Score] – [Label]\nFeedback: [Final paragraph]";

        $response = chatgpt_get_response($prompt);

        $formatted = preg_replace('/(Final Evaluation:)/i', '<strong>$1</strong>', $response);
        $formatted = preg_replace('/(Feedback:)/i', '<strong>$1</strong>', $formatted);

        echo "<h3>🎓 Final ChatGPT-Generated Summary:</h3>";
        echo "<div style='white-space: pre-wrap; border: 1px solid #ccc; background: #f9f9f9; padding: 10px; font-size: 13px;'>" . wp_kses_post($formatted) . "</div>";
    }

    return ob_get_clean();
}

function chatgpt_get_response($prompt) {
    $api_key = defined('CHATGPT_API_KEY') ? CHATGPT_API_KEY : null;
    if (!$api_key) return 'Missing API key.';

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
