<?php
/*
Plugin Name: ChatGPT LO1 Feedback Form (Student Style Output)
Description: LO1 form with teacher-style summary and final score label.
Version: 1.2
Author: You
*/

session_start(); // Put this at the very top of the plugin file if not already there

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
        $prompt = "You are an ICT teacher reviewing a student's performance on Learning Outcome 5. The teacher gave feedback and a score for each of the 2 criteria below.\n";
        $prompt .= "Combine these into a single, cohesive paragraph of feedback as if written directly by the teacher to the student. The goal is to make the feedback sound supportive, structured, and professional — not overly summarized.\n";
        $prompt .= "Keep most of the content but improve grammar, flow, and tone.\n";
        $prompt .= "Then calculate the average of the scores (round to the nearest whole number), and return the final result as a label from the scale:\n";
        $prompt .= "0 = Undefined, 1 = Orienting, 2 = Beginning, 3 = Proficient, 4 = Advanced\n\n";
        $prompt .= "Format your output like this:\n";
        $prompt .= "Final Score: [0–4] – [Label]\nFeedback: [Single paragraph combining both criteria]\n";
        

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

    $criteria = [
        "Prototyping & User Testing" => [
            // 4 - Advanced
            "Develops multiple prototypes with increasing fidelity and complexity across projects. User testing is consistently planned, executed with real users, and clearly influences design iterations. Feedback is documented and translated into design improvements.",
            // 3 - Proficient
            "Builds at least two functional prototypes and tests them with users using realistic tasks or scenarios. Feedback is used to improve the product and is reflected in deliverables or documentation.",
            // 2 - Beginning
            "Creates one basic prototype and performs a user test. There is some evidence that user input was gathered and considered, but follow-up actions are limited or unclear.",
            // 1 - Orienting
            "Delivers a rough prototype or idea sketch and tests it informally or with peers. Testing lacks structure or documentation. Limited understanding of how to use feedback to improve.",
            // 0 - Undefined
            "No prototype is developed or tested with users. No indication of any validation activity."
        ],
        "UX Evaluation & Documentation" => [
            // 4 - Advanced
            "Evaluates the full user experience, including emotional, functional, and contextual aspects. Documentation is clear, visual, and adapted to stakeholders. Choices are supported with insights and evidence.",
            // 3 - Proficient
            "Conducts solid usability and UX evaluations across relevant touchpoints. Documentation is complete and well-organized, showing a clear line of reasoning behind design choices.",
            // 2 - Beginning
            "Focuses mainly on usability and surface-level UX elements. Documentation exists but is basic, fragmented or hard to follow.",
            // 1 - Orienting
            "Attempts some form of UX evaluation, but it is vague or superficial. Documentation is minimal, unclear, or lacks context.",
            // 0 - Undefined
            "No UX evaluation or documentation is present."
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
        $prompt = "You are an ICT teacher reviewing a student's performance on Learning Outcome 5. The teacher gave feedback and a score for each of the 2 criteria below.\n";
        $prompt .= "Combine these into a single, cohesive paragraph of feedback as if written directly by the teacher to the student. The goal is to make the feedback sound supportive, structured, and professional — not overly summarized.\n";
        $prompt .= "Keep most of the content but improve grammar, flow, and tone.\n";
        $prompt .= "Then calculate the average of the scores (round to the nearest whole number), and return the final result as a label from the scale:\n";
        $prompt .= "0 = Undefined, 1 = Orienting, 2 = Beginning, 3 = Proficient, 4 = Advanced\n\n";
        $prompt .= "Format your output like this:\n";
        $prompt .= "Final Score: [0–4] – [Label]\nFeedback: [Single paragraph combining both criteria]\n";

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

    $criteria = [
        "Technical Implementation & Use of Libraries" => [
            // 4 - Advanced
            "Delivers a fully working product with multiple, well-integrated features. Code is clean, structured, and reusable. Libraries or components are carefully chosen, applied effectively, and well documented.",
            // 3 - Proficient
            "Creates a working product that meets key functional requirements. At least one external library is meaningfully integrated into the codebase. Code is functional and maintainable.",
            // 2 - Beginning
            "Delivers a partially working product with basic functionality. A library or component is used but lacks depth or proper implementation. Code shows basic understanding but may be messy or inconsistent.",
            // 1 - Orienting
            "Builds a prototype or static version with limited functionality. Libraries may be mentioned but are not effectively used. Code structure is unclear or incomplete.",
            // 0 - Undefined
            "No functional product is presented. No relevant code or use of libraries can be identified."
        ],
        "Quality & Version Control" => [
            // 4 - Advanced
            "Applies relevant quality standards (e.g. usability, performance, security) throughout development. Uses version control in a structured way, including branches, descriptive commits, and clear collaboration history.",
            // 3 - Proficient
            "Addresses key quality aspects such as usability or performance. Version control is used actively with regular commits and visible progress.",
            // 2 - Beginning
            "Mentions or touches on quality considerations, but implementation is basic or inconsistent. Uses version control minimally, with a few commits and unclear structure.",
            // 1 - Orienting
            "Quality standards are barely addressed. Version control is used in a limited or incorrect way (e.g. only one or two commits, no commit messages).",
            // 0 - Undefined
            "No evidence of attention to quality or version control is present."
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
        $prompt = "You are an ICT teacher reviewing a student's performance on Learning Outcome 5. The teacher gave feedback and a score for each of the 2 criteria below.\n";
        $prompt .= "Combine these into a single, cohesive paragraph of feedback as if written directly by the teacher to the student. The goal is to make the feedback sound supportive, structured, and professional — not overly summarized.\n";
        $prompt .= "Keep most of the content but improve grammar, flow, and tone.\n";
        $prompt .= "Then calculate the average of the scores (round to the nearest whole number), and return the final result as a label from the scale:\n";
        $prompt .= "0 = Undefined, 1 = Orienting, 2 = Beginning, 3 = Proficient, 4 = Advanced\n\n";
        $prompt .= "Format your output like this:\n";
        $prompt .= "Final Score: [0–4] – [Label]\nFeedback: [Single paragraph combining both criteria]\n";

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

    $criteria = [
        "Research Approach & Reflection" => [
            // 4 - Advanced
            "Uses a clear and well-substantiated research method that fits the context. Applies multiple sources and methods across projects. Reflects deeply and critically on both outcomes and personal growth, making visible improvements.",
            // 3 - Proficient
            "Uses a structured and suitable research approach. Reflects on outcomes and feedback and adjusts accordingly in future iterations.",
            // 2 - Beginning
            "Applies a basic method to conduct research. Reflection is present but limited to description, with few or unclear actions based on feedback.",
            // 1 - Orienting
            "Research method is vague or underdeveloped. Some reflection is present, but lacks depth or connection to next steps.",
            // 0 - Undefined
            "No recognisable research method or reflection is shown."
        ],
        "Ethical and Future-Oriented Thinking" => [
            // 4 - Advanced
            "Consistently shows awareness of ethical, sustainable, and inclusive impact of decisions. Proactively integrates these values into the design and communication.",
            // 3 - Proficient
            "Demonstrates consideration for ethics or sustainability in decisions and documentation. Makes thoughtful choices and acknowledges broader impact.",
            // 2 - Beginning
            "Mentions ethics or future-oriented topics but without much application or depth. Some understanding is present but not fully integrated.",
            // 1 - Orienting
            "Superficial or one-time mention of ethics or sustainability without further elaboration. No clear effect on choices made.",
            // 0 - Undefined
            "No attention given to ethics, sustainability or future-oriented thinking."
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
        $prompt = "You are an ICT teacher reviewing a student's performance on Learning Outcome 5. The teacher gave feedback and a score for each of the 2 criteria below.\n";
        $prompt .= "Combine these into a single, cohesive paragraph of feedback as if written directly by the teacher to the student. The goal is to make the feedback sound supportive, structured, and professional — not overly summarized.\n";
        $prompt .= "Keep most of the content but improve grammar, flow, and tone.\n";
        $prompt .= "Then calculate the average of the scores (round to the nearest whole number), and return the final result as a label from the scale:\n";
        $prompt .= "0 = Undefined, 1 = Orienting, 2 = Beginning, 3 = Proficient, 4 = Advanced\n\n";
        $prompt .= "Format your output like this:\n";
        $prompt .= "Final Score: [0–4] – [Label]\nFeedback: [Single paragraph combining both criteria]\n";

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

    $criteria = [
        "Self-direction & Goal-setting" => [
            // 4 - Advanced
            "Sets and revises SMART goals for both the semester and each project. Uses them to guide and reflect on learning. Tracks progress actively.",
            // 3 - Proficient
            "Formulates clear goals for projects and takes ownership. Reflects on progress and updates direction if needed.",
            // 2 - Beginning
            "Writes down some general or vague goals. May not track them actively, but they relate to the project or personal development.",
            // 1 - Orienting
            "Goals are unclear, externally set, or missing. Limited effort in tracking or personal reflection.",
            // 0 - Undefined
            "No personal or project goals are present."
        ],
        "Initiative & Development" => [
            // 4 - Advanced
            "Takes initiative in all projects, actively seeks feedback, and pursues learning beyond expectations. Shows continuous growth.",
            // 3 - Proficient
            "Shows initiative during group or individual work. Responds well to feedback and looks for learning opportunities.",
            // 2 - Beginning
            "Sometimes takes initiative or reacts to feedback. Participates in work but mostly follows instructions.",
            // 1 - Orienting
            "Waits for guidance or only does what is asked. Little sign of personal drive or development.",
            // 0 - Undefined
            "No visible signs of initiative, growth, or effort toward development."
        ],
        "Professional Presentation" => [
            // 4 - Advanced
            "Presents clearly and confidently both live and online. Has a strong, reflective vision for their future in ICT.",
            // 3 - Proficient
            "Communicates in a professional manner. Has an idea of their role and strengths within ICT.",
            // 2 - Beginning
            "Shows basic understanding of professional behavior or role. Portfolio or presentation lacks depth.",
            // 1 - Orienting
            "Presents without clear intention. Professional identity is unclear or missing.",
            // 0 - Undefined
            "No evidence of professional presentation or identity."
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
        $prompt = "You are an ICT teacher reviewing a student's performance on Learning Outcome 5. The teacher gave feedback and a score for each of the 3 criteria below.\n";
        $prompt .= "Combine these into a single, cohesive paragraph of feedback as if written directly by the teacher to the student. The goal is to make the feedback sound supportive, structured, and professional — not overly summarized.\n";
        $prompt .= "Keep most of the content but improve grammar, flow, and tone.\n";
        $prompt .= "Then calculate the average of the scores (round to the nearest whole number), and return the final result as a label from the scale:\n";
        $prompt .= "0 = Undefined, 1 = Orienting, 2 = Beginning, 3 = Proficient, 4 = Advanced\n\n";
        $prompt .= "Format your output like this:\n";
        $prompt .= "Final Score: [0–4] – [Label]\nFeedback: [Single paragraph combining both criteria]\n";
        

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
