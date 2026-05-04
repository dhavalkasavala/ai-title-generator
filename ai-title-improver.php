<?php
/**
 * Plugin Name: AI Title Improver
 * Description: Generate and replace WordPress post/page titles using OpenAI.
 * Version: 1.0
 */

if (!defined('ABSPATH')) exit;a

$api_key = get_option('ai_title_openai_key');


require_once plugin_dir_path( __FILE__ ). 'admin/settings.php';

/* ---------------------------------------------------------
 * META BOX (Posts + Pages)
 * --------------------------------------------------------- */
add_action('add_meta_boxes', function () {

    foreach (['post', 'page'] as $screen) {
        add_meta_box(
            'ai_title_improver',
            'AI Title Improver',
            'ai_title_box_html',
            $screen,
            'normal'
        );
    }
});


function ai_title_box_html($post) {
    ?>
    <div>
        <label><strong>Current Title</strong></label>
        <input type="text"
               id="ai-title-input"
               value="<?php echo esc_attr($post->post_title); ?>"
               style="width:100%; margin-top:5px;" />

        <label style="margin-top:10px; display:block;"><strong>Tone</strong></label>
        <select id="ai-tone" style="width:100%;">
            <option value="seo">SEO Optimized</option>
            <option value="viral">Viral / Clickbait</option>
            <option value="professional">Professional</option>
            <option value="blog">Blog Style</option>
        </select>

        <button class="button button-primary" id="ai-generate-btn" style="width:100%; margin-top:10px;">
            Generate Titles
        </button>

        <ul id="ai-results" style="margin-top:10px;"></ul>
    </div>
    <?php
}

/* ---------------------------------------------------------
 * LOAD JS
 * --------------------------------------------------------- */
add_action('admin_enqueue_scripts', function ($hook) {

    if (!in_array($hook, ['post.php', 'post-new.php'])) return;

    wp_enqueue_script(
        'ai-title-js',
        plugin_dir_url(__FILE__) . 'assets/js/editor.js',
        ['jquery'],
        '1.0',
        true
    );

    wp_localize_script('ai-title-js', 'aiTitle', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('ai_title_nonce')
    ]);
});

/* ---------------------------------------------------------
 * OPENAI AJAX HANDLER
 * --------------------------------------------------------- */
add_action('wp_ajax_ai_generate_titles', function () {

    check_ajax_referer('ai_title_nonce', 'nonce');

    $title = sanitize_text_field($_POST['title']);
    $tone  = sanitize_text_field($_POST['tone']);

    if (!$title) {
        wp_send_json_error('Missing title');
    }

    $prompt = "Generate 5 high-quality blog titles.

Tone: $tone
Base title: $title

Return as numbered list only.";

    $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
        'headers' => [
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type'  => 'application/json'
        ],
        'body' => json_encode([
            'model' => 'gpt-4o-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an expert SEO title generator.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.8
        ]),
        'timeout' => 20
    ]);

    if (is_wp_error($response)) {
        wp_send_json_error($response->get_error_message());
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    $text = $body['choices'][0]['message']['content'] ?? '';

    if (!$text) {
        wp_send_json_error($body);
    }

    preg_match_all('/\d+\.\s*(.+)/', $text, $matches);

    $titles = $matches[1] ?? explode("\n", trim($text));

    wp_send_json_success($titles);
});
