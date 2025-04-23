<?php
/*
Plugin Name: ChatGPT LO1 Feedback Form (Student Style Output)
Description: LO1 form with teacher-style summary and final score label.
Version: 1.2
Author: You
*/

require_once plugin_dir_path(__FILE__) . 'learning_outcome_criteria.php';

session_start(); // Put this at the very top of the plugin file if not already there

define("FEEDBACK_PROMPT", "You are an ICT teacher reviewing a student's performance on Learning Outcome 5. The teacher gave feedback and a score for each of the 3 criteria below.\n" .
"Combine these into a single, cohesive paragraph of feedback as if written directly by the teacher to the student. The goal is to make the feedback sound supportive, structured, and professional — not overly summarized.\n" .
"Keep most of the content but improve grammar, flow, and tone.\n" .
"Then calculate the average of the scores (round to the nearest whole number), and return the final result as a label from the scale:\n" .
"0 = Undefined, 1 = Orienting, 2 = Beginning, 3 = Proficient, 4 = Advanced\n\n" .
"Format your output like this:\n" .
"Final Score: [0–4] – [Label]\nFeedback: [Single paragraph combining both criteria]\n");

add_shortcode('form_learning_outcome_1', 'form_learning_outcome_1_shortcode');

function form_learning_outcome_1_shortcode() {
    ob_start();

    // Declare the global variable so we can access it inside the functions
    global $learning_outcome_criteria;
    $criteria = $learning_outcome_criteria['Learning Outcome 1'];
 
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
        $prompt = FEEDBACK_PROMPT;

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

        // 👇 Store response in session
        session_start();
        $_SESSION['lo1_feedback'] = $response;

        echo "<br>";
        echo "<div style='white-space: pre-wrap; border: 1px solid #ccc; padding: 10px; background: #f9f9f9;'>" . esc_html($response) . "</div>";
    }

    return ob_get_clean();
}

add_shortcode('form_learning_outcome_2', 'form_learning_outcome_2_shortcode');

function form_learning_outcome_2_shortcode() {
    ob_start();

    // Declare the global variable so we can access it inside the functions
    global $learning_outcome_criteria;
    $criteria = $learning_outcome_criteria['Learning Outcome 2'];

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
                    <select name='{$field_key}_score' style='font-size: 13px;' required>";
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
    echo "<input type='submit' class='submitButton' name='submit_lo2' value='Calculate Result and Generate Feedback (Summary) 👨‍🎓'>";
    echo "</form>";

    if (isset($_POST['submit_lo2'])) {
        $prompt = FEEDBACK_PROMPT;

        foreach ($criteria as $criterion => $desc) {
            $field_key = strtolower(str_replace(' ', '_', $criterion));
            $score = sanitize_text_field($_POST["{$field_key}_score"]);
            $feedback = sanitize_textarea_field($_POST["{$field_key}_feedback"]);

            $prompt .= "Criterion: $criterion\n";
            $prompt .= "Given Score: $score\n";
            $prompt .= "Teacher Feedback: $feedback\n\n";
        }

        $prompt .= "Format your reply like this:\n";
        $prompt .= "Final Score: [0–4] – [Label]\nFeedback: [Rewritten feedback for student]\n";

        $response = chatgpt_get_response_lo1($prompt); // using same function as LO1
        
        // 👇 Store response in session
        session_start();
        $_SESSION['lo2_feedback'] = $response;

        echo "<br>";
        echo "<div style='white-space: pre-wrap; border: 1px solid #ccc; padding: 10px; background: #f9f9f9;'>" . esc_html($response) . "</div>";
    }

    return ob_get_clean();
}

add_shortcode('form_learning_outcome_3', 'form_learning_outcome_3_shortcode');

function form_learning_outcome_3_shortcode() {
    ob_start();

    // Declare the global variable so we can access it inside the functions
    global $learning_outcome_criteria;
    $criteria = $learning_outcome_criteria['Learning Outcome 3'];

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
                    <select name='{$field_key}_score' style='font-size: 13px;' required>";
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
    echo "<input type='submit' class='submitButton' name='submit_lo3' value='Calculate Result and Generate Feedback (Summary) 👨‍💻'>";
    echo "</form>";

    if (isset($_POST['submit_lo3'])) {
        $prompt = FEEDBACK_PROMPT;

        foreach ($criteria as $criterion => $desc) {
            $field_key = strtolower(str_replace(' ', '_', $criterion));
            $score = sanitize_text_field($_POST["{$field_key}_score"]);
            $feedback = sanitize_textarea_field($_POST["{$field_key}_feedback"]);

            $prompt .= "Criterion: $criterion\n";
            $prompt .= "Given Score: $score\n";
            $prompt .= "Teacher Feedback: $feedback\n\n";
        }

        $prompt .= "Format your reply like this:\n";
        $prompt .= "Final Score: [0–4] – [Label]\nFeedback: [Rewritten feedback for student]\n";

        $response = chatgpt_get_response_lo1($prompt); // reused from LO1

        // 👇 Store response in session
        session_start();
        $_SESSION['lo3_feedback'] = $response;

        echo "<br>";
        echo "<div style='white-space: pre-wrap; border: 1px solid #ccc; padding: 10px; background: #f9f9f9;'>" . esc_html($response) . "</div>";
    }

    return ob_get_clean();
}

add_shortcode('form_learning_outcome_4', 'form_learning_outcome_4_shortcode');

function form_learning_outcome_4_shortcode() {
    ob_start();

    // Declare the global variable so we can access it inside the functions
    global $learning_outcome_criteria;
    $criteria = $learning_outcome_criteria['Learning Outcome 4'];

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
                    <select name='{$field_key}_score' style='font-size: 13px;' required>";
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
    echo "<input type='submit' class='submitButton' name='submit_lo4' value='Calculate Result and Generate Feedback (Summary) 🧠'>";
    echo "</form>";

    if (isset($_POST['submit_lo4'])) {
        $prompt = FEEDBACK_PROMPT;

        foreach ($criteria as $criterion => $desc) {
            $field_key = strtolower(str_replace(' ', '_', $criterion));
            $score = sanitize_text_field($_POST["{$field_key}_score"]);
            $feedback = sanitize_textarea_field($_POST["{$field_key}_feedback"]);

            $prompt .= "Criterion: $criterion\n";
            $prompt .= "Given Score: $score\n";
            $prompt .= "Teacher Feedback: $feedback\n\n";
        }

        $prompt .= "Format your reply like this:\n";
        $prompt .= "Final Score: [0–4] – [Label]\nFeedback: [Rewritten feedback for student]\n";

        $response = chatgpt_get_response_lo1($prompt); // using shared function from LO1

        // 👇 Store response in session
        session_start();
        $_SESSION['lo4_feedback'] = $response;

        echo "<br>";
        echo "<div style='white-space: pre-wrap; border: 1px solid #ccc; padding: 10px; background: #f9f9f9;'>" . esc_html($response) . "</div>";
    }

    return ob_get_clean();
}

add_shortcode('form_learning_outcome_5', 'form_learning_outcome_5_shortcode');

function form_learning_outcome_5_shortcode() {
    ob_start();

     // Declare the global variable so we can access it inside the functions
     global $learning_outcome_criteria;
     $criteria = $learning_outcome_criteria['Learning Outcome 5'];

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
                    <select name='{$field_key}_score' style='font-size: 13px;' required>";
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
    echo "<input type='submit' class='submitButton' name='submit_lo5' value='Calculate Result and Generate Feedback (Summary) 🎯'>";
    echo "</form>";

    if (isset($_POST['submit_lo5'])) {
        $prompt = FEEDBACK_PROMPT;

        foreach ($criteria as $criterion => $desc) {
            $field_key = strtolower(str_replace(' ', '_', $criterion));
            $score = sanitize_text_field($_POST["{$field_key}_score"]);
            $feedback = sanitize_textarea_field($_POST["{$field_key}_feedback"]);

            $prompt .= "Criterion: $criterion\n";
            $prompt .= "Given Score: $score\n";
            $prompt .= "Teacher Feedback: $feedback\n\n";
        }

        $prompt .= "Format your reply like this:\n";
        $prompt .= "Final Score: [0–4] – [Label]\nFeedback: [Rewritten feedback for student]\n";

        $response = chatgpt_get_response_lo1($prompt); // reuse shared function

        // 👇 Store response in session
        session_start();
        $_SESSION['lo5_feedback'] = $response;

        echo "<br>";
        echo "<div style='white-space: pre-wrap; border: 1px solid #ccc; padding: 10px; background: #f9f9f9;'>" . esc_html($response) . "</div>";
    }

    return ob_get_clean();
}

add_shortcode('learning_outcomes_conclusion', 'learning_outcomes_conclusion_shortcode');

function learning_outcomes_conclusion_shortcode() {
    session_start();
    ob_start();

    echo "<h2>📘 Final Summary of All Learning Outcomes</h2>";
    echo "<p>Below is the collected feedback for each Learning Outcome. </p> <br> ";

    for ($i = 1; $i <= 5; $i++) {
        $key = "lo{$i}_feedback";
        $value = $_SESSION[$key] ?? 'No feedback found for this learning outcome. Please complete the LO page first.';
    
        echo "<h3 style='background-color: #F9C926; padding: 10px; width: 100%;'>Learning Outcome $i</h3>";
        echo "<div style='border: 1px solid #ccc; padding: 10px; background: #fdfdfd; margin-block-start: 0px; margin-bottom: 20px; white-space: pre-wrap; font-size: 13px;'>
                " . esc_html($value) . "
              </div> <br>";
    }
    

    echo "<h3>🧾 Final Opinion / Wrap-up</h3>";
    echo "
    <div style='display: flex; justify-content: center; margin-top: 20px;'>
        <textarea name='final_opinion' form='finalFeedbackForm' rows='4' style='width:100%; font-size:13px; padding: 10px; border-radius: 5px; border: 1px solid #ccc;' placeholder='Write your overall impression, strengths, advice, or final verdict here...'></textarea>
    </div><br><br>
    ";

    echo "<form method='post' id='finalFeedbackForm'>";
    echo "<input type='submit' class='submitButton' name='generate_final_opinion' value='Generate Final Message 📝'>";
    echo "</form>";

    // Handle the POST
    if (isset($_POST['generate_final_opinion'])) {
        $prompt = "You are an ICT teacher giving final feedback on a student's semester performance. You will receive a feedback summary for each of the five learning outcomes, as well as an optional overall note from the teacher.\n\n";

        for ($i = 1; $i <= 5; $i++) {
            $key = "lo{$i}_feedback";
            $feedback = $_SESSION[$key] ?? 'Missing.';
            $prompt .= "LO$i Feedback:\n$feedback\n\n";
        }

        $prompt .= "Teacher Final Opinion:\n" . sanitize_textarea_field($_POST['final_opinion']) . "\n\n";

        $prompt .= "Please combine all feedback into one concise and clearly written paragraph, as if written by the teacher directly to the student. Use a grounded, professional, and constructive tone — not formal, not overly motivational.\n";

        $prompt .= "Then calculate the average of the 5 learning outcome scores. Round it to the nearest whole number (0–4).\n";
        $prompt .= "Based on the average, give a final label:\n";
        $prompt .= "- 4 = Outstanding\n- 3 = Good\n- 2 = Proficient\n- 1 = Inefficient\n- 0 = Undefined\n\n";

        $prompt .= "Format your output like this:\n";
        $prompt .= "Final Evaluation: [Average Score] – [Label]\nFeedback: [Short, final paragraph combining all feedback and giving overall advice]\n";

        $response = chatgpt_get_response_lo1($prompt); // reuse shared function

        // Add formatting
        $formatted = preg_replace('/(Final Evaluation:)/i', '<strong>$1</strong>', $response);
        $formatted = preg_replace('/(Feedback:)/i', '<strong>$1</strong>', $formatted);

        echo "<h3>🎓 Final ChatGPT-Generated Summary:</h3>";
        echo "<div style='white-space: pre-wrap; border: 1px solid #ccc; background: #f9f9f9; padding: 10px; font-size: 13px;'>" . wp_kses_post($formatted) . "</div>";
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
