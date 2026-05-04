jQuery(document).ready(function ($) {

    $('#ai-generate-btn').on('click', function () {

        let title = $('#ai-title-input').val();
        let tone = $('#ai-tone').val();

        $('#ai-results').html('<li>Generating AI titles...</li>');

        $.post(aiTitle.ajax_url, {
            action: 'ai_generate_titles',
            nonce: aiTitle.nonce,
            title: title,
            tone: tone
        }, function (res) {

            if (!res.success) {
                $('#ai-results').html('<li>Error generating titles</li>');
                return;
            }

            let html = '';

            res.data.forEach(function (t) {
                html += `<li class="ai-item" style="cursor:pointer; padding:5px; border-bottom:1px solid #ddd;">
                            ${t}
                         </li>`;
            });

            $('#ai-results').html(html);
        });
    });

    /**
     * Click → Replace real WP title
     */
    $(document).on('click', '.ai-item', function () {

        let newTitle = $(this).text();

        $('#ai-title-input').val(newTitle);

        // Gutenberg support
        if (wp.data && wp.data.dispatch('core/editor')) {
            wp.data.dispatch('core/editor').editPost({
                title: newTitle
            });
        }

        // Classic editor fallback
        $('#title').val(newTitle);
    });

});
