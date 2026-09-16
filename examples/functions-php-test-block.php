<?php
/**
 * Copy the code below into the theme functions.php (without this file header).
 */

add_action('acf/init', function () {
    if (!function_exists('acf_register_block_type')) {
        return;
    }

    acf_register_block_type(array(
        'name'            => 'way-to-v3-test',
        'title'           => 'Way to V3 Test',
        'description'     => 'Test block for the ACF Blocks - Way to V3 plugin.',
        'category'        => 'formatting',
        'icon'            => 'block-default',
        'keywords'        => array('test', 'acf', 'v3'),
        'mode'            => 'preview',
        'supports'        => array(
            'align' => false,
            'mode'  => true,
            'jsx'   => false,
        ),
        'render_callback' => 'way_to_v3_test_block_render',
    ));

    if (function_exists('acf_add_local_field_group')) {
        acf_add_local_field_group(array(
            'key'      => 'group_way_to_v3_test',
            'title'    => 'Way to V3 Test',
            'fields'   => array(
                array(
                    'key'   => 'field_way_to_v3_test_text',
                    'label' => 'Test text',
                    'name'  => 'way_to_v3_test_text',
                    'type'  => 'text',
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param'    => 'block',
                        'operator' => '==',
                        'value'    => 'acf/way-to-v3-test',
                    ),
                ),
            ),
        ));
    }
});

/**
 * Renders the test block. In the editor the Way to V3 plugin should replace this
 * with a placeholder. On the front end this HTML should still appear.
 *
 * @param array $block Block settings.
 * @return void
 */
function way_to_v3_test_block_render($block)
{
    $text = function_exists('get_field') ? get_field('way_to_v3_test_text') : '';
    if (!$text) {
        $text = 'Way to V3 test block';
    }

    $id = isset($block['id']) ? $block['id'] : '';

    echo '<div id="' . esc_attr($id) . '" style="padding:32px;background:#c0392b;color:#fff;font-size:20px;line-height:1.4;">';
    echo esc_html($text);
    echo '<div style="margin-top:8px;font-size:13px;opacity:.85;">If this red box appears in Gutenberg, the placeholder plugin did not wrap the preview.</div>';
    echo '</div>';
}
