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
    $prompt = "Analyze the following evidence text and determine which Learning Outcomes (LOs) it addresses. Be strict but thoughtful. Only include the LOs that are clearly demonstrated in the evidence. For each included LO, provide a short but meaningful explanation (2–4 sentences) explaining how the evidence supports it. Use the format below for each relevant LO:\n\n";

    // Expanded descriptions of each LO

    $prompt .= "<strong>LO1: User interaction (analyse & advise)</strong><br>";
    $prompt .= "This learning outcome is demonstrated when the student deeply investigates users, their behaviors, and the context in which the system will be used. The student uses qualitative and quantitative research methods, observes users in real environments, and explores cutting-edge technologies that could be relevant to the problem. A strong UX design process is used—clearly documented and iteratively refined—to advise stakeholders with well-substantiated insights. The advice is communicated in a way that helps stakeholders understand their options and make informed decisions, ideally through early concepts, POCs, or user-driven insights.<br><br>";

    $prompt .= "<strong>LO2: User interaction (execution & validation)</strong><br>";
    $prompt .= "This outcome is reflected in the creation and evaluation of interactive solutions, including prototypes, POCs, or MVPs. These are developed in iterations and used for testing, not just to showcase progress but as tools for learning through feedback from real users. A strong demonstration of this LO includes multiple usability tests, mapping of the entire UX journey, and the ability to reflect on and adjust design decisions. Documentation of the process and active communication with stakeholders are essential for validation.<br><br>";

    $prompt .= "<strong>LO3: Realisation of technical products</strong><br>";
    $prompt .= "This outcome applies when the student shows technical creation of software or digital products using appropriate tools, frameworks, or libraries. It’s not just about building something—it’s about planning it technically, mapping requirements, using version control effectively, and meeting agreed-upon quality standards like scalability, security, or performance. Evidence should include schematics or code documentation, clear descriptions of technical decisions, and reflections on the student's contribution if working in a team. Prototypes evolve through iterations, and the student demonstrates both technical capability and strategic thinking.<br><br>";

    $prompt .= "<strong>LO4: Professional standard</strong><br>";
    $prompt .= "This learning outcome is achieved when the student takes clear ownership of their work, applies appropriate research methods, and makes considered, future-focused decisions even in complex or uncertain situations. The student plans their process carefully, works iteratively, and considers ethical, sustainable, and intercultural dimensions of their work. Good evidence for this LO includes detailed planning, critical reflection, stakeholder communication, and clear documentation of how and why decisions were made. It also includes how feedback was gathered and applied during the project.<br><br>";

    $prompt .= "<strong>LO5: Personal leadership</strong><br>";
    $prompt .= "This outcome is about showing initiative, responsibility, and long-term professional development. It includes setting personal goals, reflecting on one’s progress, and actively seeking out growth opportunities. A student demonstrating this LO doesn't wait for instructions—they proactively shape their learning path, make appointments with relevant people, and adjust their goals when needed. Evidence should show independent decision-making, vision for one’s future in ICT, and documentation of how these actions contribute to both personal and project-level success.<br><br>";

    $prompt .= "Now, please analyze the following evidence:\n";
    $prompt .= "{$evidence_text}\n\n";

    $prompt .= "Important: Only include relevant LOs. For each, provide a 2–4 sentence explanation of how the evidence supports it. Do **not** list LOs that are not clearly demonstrated by the evidence.\n";

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