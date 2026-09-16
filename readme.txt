=== ACF Blocks - Way to V3 ===
Contributors: pLavrenov
Tags: acf, acf blocks, gutenberg, block editor, wordpress 7.1
Requires at least: 6.3
Tested up to: 7.1
Requires PHP: 8.0
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Smooth the move to ACF Blocks V3 after WordPress 7.1: compact editor placeholders instead of heavy live previews.

== Description ==

WordPress 7.1 always loads the post editor canvas inside an iframe. ACF Blocks V2 relied on wp-admin scripts inside that canvas (edit mode, TinyMCE, date pickers, and similar). That path no longer works: fields are pushed into the narrow sidebar, Repeaters and Flexible Content become hard to use, and many existing blocks feel broken.

ACF Blocks V3 is the supported way forward. In V3 the canvas always shows a preview, and fields open in the sidebar or the expanded editor. That is the right long-term model, but the jump is painful: every block starts rendering its full PHP template in the editor. On content-heavy sites that means slow, noisy previews while you retrain editors and update block code.

This plugin is a migration aid, not a way to stay on V2.

In the Gutenberg editor it replaces ACF block markup with a compact placeholder that shows the block title and name. Editors still open fields in the sidebar or modal. The front end is unchanged: visitors still see the real block output.

Use it while you move blocks to V3 (`blockVersion` 3 / `acf_block_version` 3), then remove it when previews are what you want.

= Requirements =

* WordPress 6.3 or later (built for the WordPress 7.1 iframe editor)
* ACF PRO 6.6 or later (ACF Blocks V3)

= Exclude a block =

`
add_filter( 'acf_blocks_way_to_v3/exclude', function( $exclude ) {
    $exclude[] = 'acf/example-block';
    return $exclude;
} );
`

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/acf-blocks-way-to-v3`.
2. Activate **ACF Blocks - Way to V3**.
3. Open a post or page in the block editor. ACF blocks should show a compact placeholder instead of full HTML.

== Frequently Asked Questions ==

= Does this restore ACF Blocks V2 edit mode? =

No. WordPress 7.1 keeps the editor iframed, so V2 edit mode cannot work there reliably. This plugin helps you live with V3: fields in the sidebar or expanded editor, and a light placeholder in the canvas.

= Does it change the front end? =

No. Placeholders are for the block editor preview only.

= Why not ACF’s own `renderPreview: false`? =

ACF PRO 6.8.9 can skip the preview per block. This plugin applies a compact named placeholder to ACF blocks at once, which is useful while you migrate a large library.

== Changelog ==

= 1.0.1 =
* Load editor styles inside the iframed block canvas.
* Align plugin identifiers with the `acf-blocks-way-to-v3` slug.

= 1.0.0 =
* Initial release: replace ACF block previews with compact placeholders during V3 migration.
