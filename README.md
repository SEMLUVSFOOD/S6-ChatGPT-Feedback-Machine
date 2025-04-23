# ChatGPT LO Feedback Form (Student Style Output) Plugin

## Overview

The **ChatGPT LO Feedback Form** plugin allows educators to generate structured and professional feedback for students based on predefined learning outcomes (LOs). The plugin uses OpenAI's GPT-3.5-turbo model to analyze teacher-provided criteria and scores, and it generates a written, cohesive feedback summary along with a final score label.

This plugin is designed for use with WordPress websites and integrates seamlessly with the backend to calculate scores and feedback for up to 5 learning outcomes.

## Features

- **Dynamic Feedback Forms**: Generate feedback forms based on different Learning Outcomes (LOs), including criteria and scoring.
- **AI-Powered Feedback**: Uses OpenAI's GPT-3.5-turbo model to analyze scores and teacher feedback, generating cohesive feedback and a final score label.
- **Customizable Learning Outcomes**: Easily modify the learning outcome criteria by adjusting the `learning_outcome_criteria.php` file.
- **Final Feedback Summary**: Collect feedback for all learning outcomes and generate a final summary report with overall feedback and score.

## Requirements

- WordPress version 5.0 or higher.
- PHP version 7.4 or higher.
- A valid **OpenAI API key** for accessing the GPT-3.5-turbo model.

## Installation

### 1. Install the Plugin

1. Download the plugin files.
2. Upload the plugin folder to your WordPress plugins directory (typically `/wp-content/plugins/`).
3. Go to your WordPress dashboard, navigate to **Plugins > Installed Plugins**, and activate **ChatGPT LO Feedback Form (Student Style Output)**.

### 2. Set Up the OpenAI API Key

You need a valid API key from OpenAI to use the plugin's AI-powered feedback feature. Follow these steps to configure the API key:

1. Create an OpenAI account if you don't have one: [OpenAI](https://beta.openai.com/signup/)
2. Once logged in, navigate to the **API Keys** section and generate a new key.
3. **Important**: Place the API key in the `secret-config.php` file, which **should not be committed to version control (Git)**. The `secret-config.php` file is located in the plugin directory:
   ```php
   <?php
   define('CHATGPT_API_KEY', 'your-api-key-here');

## Usage

### Adding the Form to Pages or Posts

To add the feedback form for each learning outcome to a page or post, use the following shortcodes:

- **Learning Outcome 1**:
  ```plaintext
  [form_learning_outcome_1]
  [form_learning_outcome_2]
  [form_learning_outcome_3]
  [form_learning_outcome_4]
  [form_learning_outcome_5]
  [learning_outcomes_conclusion]


