<?php

add_action('admin_menu', function () {

    add_menu_page(
        'AI Settings',
        'AI Title Settings',
        'manage_options',
        'ai-title-settings',
        'ai_title_settings_page',
        'dashicons-admin-generic',
        80
    );
});

add_action('admin_init', function () {

    register_setting(
        'ai_title_settings_group',
        'ai_title_openai_key',
        [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        ]
    );
});

function ai_title_settings_page() {
    ?>
    <div class="wrap">

        <h1>AI Title Improver Settings</h1>

        <form method="post" action="options.php">

            <?php settings_fields('ai_title_settings_group'); ?>

            <table class="form-table">

                <tr>
                    <th scope="row">OpenAI API Key</th>
                    <td>
                        <input type="text"
                               name="ai_title_openai_key"
                               value="<?php echo esc_attr(get_option('ai_title_openai_key')); ?>"
                               class="regular-text"
                               placeholder="sk-xxxxxxxxxxxx">

                        <p class="description">
                            Enter your OpenAI API key. This will be used for generating titles.
                        </p>
                    </td>
                </tr>

            </table>

            <?php submit_button('Save API Key'); ?>

        </form>

    </div>
    <?php
}
