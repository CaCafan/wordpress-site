<?php
/**
 * Plugin Name: My AI Assistant
 * Description: Integrates OpenAI with WordPress and provides n8n webhook support
 * Version: 1.0.0
 * Author: CaCafan
 * License: GPL v2 or later
 */

// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

// Plugin class to avoid namespace conflicts
class My_AI_Assistant {
    // Singleton instance
    private static $instance = null;
    
    // Constructor
    private function __construct() {
        // Initialize plugin
        add_action('rest_api_init', array($this, 'register_rest_routes'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    // Get singleton instance
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Register REST API endpoints
    public function register_rest_routes() {
        register_rest_route('my-ai/v1', '/chat', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_chat_request'),
            'permission_callback' => array($this, 'check_permission'),
        ));

        register_rest_route('my-ai/v1', '/webhook', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_webhook'),
            'permission_callback' => array($this, 'check_webhook_permission'),
        ));
    }

    // Handle chat requests
    public function handle_chat_request($request) {
        $params = $request->get_json_params();
        $message = isset($params['message']) ? sanitize_text_field($params['message']) : '';
        
        if (empty($message)) {
            return new WP_Error('missing_message', 'Message is required', array('status' => 400));
        }

        // Get OpenAI API key from settings
        $api_key = get_option('my_ai_openai_api_key');
        if (empty($api_key)) {
            return new WP_Error('missing_api_key', 'OpenAI API key not configured', array('status' => 500));
        }

        // TODO: Add actual OpenAI API call here
        // For now, return a test response
        return array(
            'success' => true,
            'message' => "Echo: " . $message,
        );
    }

    // Handle webhook requests from n8n
    public function handle_webhook($request) {
        $params = $request->get_json_params();
        
        // TODO: Add webhook handling logic
        return array(
            'success' => true,
            'message' => 'Webhook received',
            'data' => $params
        );
    }

    // Check permissions for chat endpoint
    public function check_permission($request) {
        // For now, require user to be logged in
        return is_user_logged_in();
    }

    // Check permissions for webhook endpoint
    public function check_webhook_permission($request) {
        // TODO: Add proper webhook authentication
        return true;
    }

    // Add admin menu
    public function add_admin_menu() {
        add_options_page(
            'AI Assistant Settings',
            'AI Assistant',
            'manage_options',
            'my-ai-assistant',
            array($this, 'render_settings_page')
        );
    }

    // Register settings
    public function register_settings() {
        register_setting('my_ai_settings', 'my_ai_openai_api_key');
        register_setting('my_ai_settings', 'my_ai_webhook_secret');
    }

    // Render settings page
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('my_ai_settings');
                do_settings_sections('my_ai_settings');
                ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">OpenAI API Key</th>
                        <td>
                            <input type="password" 
                                   name="my_ai_openai_api_key" 
                                   value="<?php echo esc_attr(get_option('my_ai_openai_api_key')); ?>" 
                                   class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Webhook Secret</th>
                        <td>
                            <input type="password" 
                                   name="my_ai_webhook_secret" 
                                   value="<?php echo esc_attr(get_option('my_ai_webhook_secret')); ?>" 
                                   class="regular-text">
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}

// Initialize the plugin
My_AI_Assistant::get_instance();