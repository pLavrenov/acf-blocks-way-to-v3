<?php
/**
 * Plugin Name: ACF Blocks - Way to V3
 * Description: Helps migrate to ACF Blocks V3 by replacing block HTML in the Gutenberg editor with compact previews showing the block name.
 * Version: 1.0.1
 * Author: pLavrenov
 * Slug: acf-blocks-way-to-v3
 * Text Domain: acf-block-preview-placeholder
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * ACF Blocks - Way to V3 is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * ACF Blocks - Way to V3 is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this plugin. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
 */

/**
 * Exclude specific ACF blocks from placeholder replacement:
 *
 * add_filter( 'acf_block_preview_placeholder/exclude', function( $exclude ) {
 *     $exclude[] = 'acf/example-block';
 *     return $exclude;
 * } );
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Replaces ACF block markup in the Gutenberg editor with a compact placeholder
 * that shows the block title and name.
 */
final class Acf_Block_Preview_Placeholder
{

    /**
     * Plugin version used for editor stylesheet cache busting.
     *
     * @var string
     */
    const VERSION = '1.0.1';

    /**
     * Singleton instance.
     *
     * @var self|null
     */
    private static $instance;

    /**
     * Original ACF render callbacks keyed by block name.
     *
     * @var callable[]
     */
    private $original_callbacks = array();

    /**
     * Whether hooks have already been registered.
     *
     * @var bool
     */
    private $booted = false;

    /**
     * Returns the singleton instance.
     *
     * @return self
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Registers WordPress hooks for editor styles and ACF bootstrapping.
     */
    private function __construct()
    {
        add_action('acf/init', array($this, 'boot'), 1);
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_editor_styles'));
    }

    /**
     * Wraps ACF block rendering so editor previews can be replaced with placeholders.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->booted || !function_exists('acf_register_block_type')) {
            return;
        }

        $this->booted = true;

        add_filter('acf/register_block_type_args', array($this, 'wrap_render_callback'));

        if (function_exists('acf_block_render_template')) {
            remove_action('acf_block_render_template', 'acf_block_render_template', 10);
            add_action('acf_block_render_template', array($this, 'render_template'), 10, 6);
        }
    }

    /**
     * Stores the original render callback and replaces it with this plugin's wrapper.
     *
     * @param array $block Block type settings passed to ACF.
     * @return array
     */
    public function wrap_render_callback($block)
    {
        $original = $block['render_callback'] ?? false;

        if (!$original || 'acf_render_block_callback' === $original) {
            return $block;
        }

        $name = $block['name'] ?? '';
        if ($name) {
            $this->original_callbacks[$name] = $original;
        }

        $block['render_callback'] = array($this, 'render_callback');

        return $block;
    }

    /**
     * Renders a placeholder in the editor, or forwards to the original callback.
     *
     * @param array          $block      Block settings and attributes.
     * @param string         $content    Block inner HTML.
     * @param bool           $is_preview Whether the block is rendered in the editor.
     * @param int            $post_id    Current post ID.
     * @param WP_Block|null  $wp_block   Parsed block instance.
     * @param array|false    $context    Block context.
     * @return mixed|void
     */
    public function render_callback(
        $block,
        $content = '',
        $is_preview = false,
        $post_id = 0,
        $wp_block = null,
        $context = false
    ) {
        if ($this->should_placeholder($block, $is_preview)) {
            echo $this->placeholder_html($block); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            return;
        }

        $name = $block['name'] ?? '';
        $original = $this->original_callbacks[$name] ?? null;

        if (is_callable($original)) {
            return call_user_func($original, $block, $content, $is_preview, $post_id, $wp_block, $context);
        }
    }

    /**
     * Renders a placeholder in the editor, or falls back to ACF template rendering.
     *
     * @param array          $block      Block settings and attributes.
     * @param string         $content    Block inner HTML.
     * @param bool           $is_preview Whether the block is rendered in the editor.
     * @param int            $post_id    Current post ID.
     * @param WP_Block|null  $wp_block   Parsed block instance.
     * @param array|false    $context    Block context.
     * @return void
     */
    public function render_template($block, $content, $is_preview, $post_id, $wp_block, $context)
    {
        if ($this->should_placeholder($block, $is_preview)) {
            echo $this->placeholder_html($block); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            return;
        }

        acf_block_render_template($block, $content, $is_preview, $post_id, $wp_block, $context);
    }

    /**
     * Enqueues placeholder styles in the block editor.
     *
     * @return void
     */
    public function enqueue_editor_styles()
    {
        wp_enqueue_style(
            'acf-block-preview-placeholder',
            plugins_url('editor.css', __FILE__),
            array(),
            self::VERSION
        );
    }

    /**
     * Whether the given block should be replaced with a compact editor placeholder.
     *
     * @param array $block      Block settings and attributes.
     * @param bool  $is_preview Whether the block is rendered in the editor.
     * @return bool
     */
    private function should_placeholder($block, $is_preview)
    {
        if (!$is_preview) {
            return false;
        }

        $name = (string)($block['name'] ?? '');
        if ($name && !str_starts_with($name, 'acf/')) {
            return false;
        }

        $exclude = apply_filters('acf_block_preview_placeholder/exclude', array(), $block);

        if ($name && is_array($exclude) && in_array($name, $exclude, true)) {
            return false;
        }

        return true;
    }

    /**
     * Builds escaped placeholder markup for an ACF block preview.
     *
     * @param array $block Block settings and attributes.
     * @return string
     */
    private function placeholder_html($block)
    {
        if (!empty($block['title'])) {
            $title = $block['title'];
        } elseif (!empty($block['name'])) {
            $title = ucwords(str_replace(array('-', '_'), ' ', str_replace('acf/', '', $block['name'])));
        } else {
            $title = '';
        }

        $heading = $title;
        $slug = (string)($block['name'] ?? '');
        if ($slug) {
            $heading .= ($heading ? ' ' : '') . '(' . $slug . ')';
        }

        $html = '<div class="acf-block-preview-placeholder">';
        $html .= '<div class="acf-block-preview-placeholder__name">' . esc_html($heading) . '</div>';
        $html .= '<div class="acf-block-preview-placeholder__hint">' . esc_html__(
                'Block fields open in the sidebar or in a modal.',
                'acf-block-preview-placeholder'
            ) . '</div>';

        if (!empty($block['supports']['jsx'])) {
            $html .= '<InnerBlocks />';
        }

        $html .= '</div>';

        return apply_filters('acf_block_preview_placeholder/html', $html, $block, $title);
    }
}

Acf_Block_Preview_Placeholder::instance();
