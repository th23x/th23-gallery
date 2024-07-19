<?php
/*
Plugin Name: th23 Gallery
Description: Replace standard Gallery block and legacy [gallery] shortcode handler. Open galleries using <a href="http://www.photoswipe.com/">PhotoSwipe</a>. Add [th23-gallery-random-image] shortcode to show a random image out of a given selection.
Version: 4.0.0
Author: Thorsten Hartmann (th23)
Author URI: http://th23.net/
Text Domain: th23-gallery
Domain Path: /lang

Coded 2011-2024 by Thorsten Hartmann (th23)
http://th23.net/

License: GNU General Public License v3.0
*/

class th23_gallery {

	// Initialize class-wide variables
	public $plugin = array(); // plugin (setup) information
	public $options = array(); // plugin options (user defined, changable)
	public $data = array(); // data exchange between plugin functions

	function __construct() {

		// Setup basics
		$this->plugin['file'] = __FILE__;
		$this->plugin['basename'] = plugin_basename($this->plugin['file']);
		$this->plugin['dir_url'] = plugin_dir_url($this->plugin['file']);
		$this->plugin['version'] = '3.2.0';

		// Load plugin options
		$this->plugin['options'] = get_option('th23_gallery');

		// Localization
		load_plugin_textdomain('th23-gallery', false, dirname($this->plugin['basename']) . '/lang');

		// Let's get the job done
		add_action('init', array(&$this, 'register_js_css'));
		add_action('wp_enqueue_scripts', array(&$this, 'load_css'));
		add_filter('post_gallery', array(&$this, 'output_gallery'), 1, 3); // get in early (with priority "1"), to catch also other plugins replacing standard output
		add_filter('render_block_core/gallery', array(&$this, 'th23_gallery_block'), 1, 3); // handle gallery blocks
		add_shortcode('th23-gallery-random-image', array(&$this, 'output_random_image')); // add random image functionality via shortcode

	}

	// Ensure PHP <5 compatibility
	function th23_gallery() {
		self::__construct();
	}

	// Register JS and CSS
	function register_js_css() {
		wp_register_script('th23-gallery-photoswipe-js', $this->plugin['dir_url'] . 'photoswipe/photoswipe.min.js', array('jquery'), $this->plugin['version'], true);
		wp_register_script('th23-gallery-photoswipe-ui-js', $this->plugin['dir_url'] . 'photoswipe/photoswipe-ui-default.min.js', array('jquery'), $this->plugin['version'], true);
		wp_register_script('th23-gallery-js', $this->plugin['dir_url'] . 'th23-gallery.js', array('jquery'), $this->plugin['version'], true);
		wp_register_style('th23-gallery-photoswipe-css', $this->plugin['dir_url'] . 'photoswipe/photoswipe.css', array(), $this->plugin['version']);
		wp_register_style('th23-gallery-photoswipe-skin-css', $this->plugin['dir_url'] . 'photoswipe/default-skin/default-skin.css', array(), $this->plugin['version']);
		wp_register_style('th23-gallery-css', $this->plugin['dir_url'] . 'th23-gallery.css', array(), $this->plugin['version']);
	}

	// Load CSS - as it is not possible to determine (easily) if shortcode used on page, include CSS by default
	function load_css() {
		wp_enqueue_style('th23-gallery-photoswipe-css');
		wp_enqueue_style('th23-gallery-photoswipe-skin-css');
		wp_enqueue_style('th23-gallery-css');
	}

	// Create HTML output to show gallery
	/*
	* note: $attr is an array of the attributes of the gallery shortcode
	* note: $instance is a unique numeric ID of this gallery shortcode instance
	*/
	function output_gallery($output, $attr, $instance, $captions = array()) {

		// Build gallery as we would like to have it - replaces function "gallery_shortcode" in "/wp-includes/media-php"
		/*
		* note: refines default "size" and "link" attributes, does not allow/use "link" and "columns" attribute
		* note: simplified output format to use "<div class='gallery'><a class='gallery-item'><img class='gallery-preview'></a>...</div>" as structure
		* note: adds additional information about target image/file onto <a> element as "data-" fields (width, height, title)
		*/

		// Get current post/page
		$post = get_post();
		// Prepare gallery attributes
		$atts = shortcode_atts(array(
			'order' => '',
			'orderby' => 'post__in', // preserves order as given in gallery
			'id' => ($post) ? $post->ID : 0,
			'align' => '',
			/* "size" parameter determines size of preview images - allows for all registered image sizes, but usage of following ones is recommended
				"medium" - ideal for "plain" style (works well with opening transition, as it starts from image with same aspect ratio as original)
				"thumbnail" - ideal for "polaroid" style (disables opening transition via JS)
			*/
			'size' => 'thumbnail',
			/* "style" parameter determines style of preview images
				"plain" - limited markup
				"polaroid" - use randomly turned "Polaroid" style (see CSS)
			*/
			'style' => 'polaroid',
			'include' => '', // contains "ids" attribute of shortcode - see function "gallery_shortcode" in "/wp-includes/media-php"
			'exclude' => '',
		), $attr, 'gallery');
		$id = intval($atts['id']);

		// Get the attachments/images
		if(!empty($atts['include'])) {
			$_attachments = get_posts(array('include' => $atts['include'], 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $atts['order'], 'orderby' => $atts['orderby']));

			$attachments = array();
			foreach($_attachments as $key => $val) {
				$attachments[$val->ID] = $_attachments[$key];
			}
		}
		elseif(!empty($atts['exclude'])) {
			$attachments = get_children(array('post_parent' => $id, 'exclude' => $atts['exclude'], 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $atts['order'], 'orderby' => $atts['orderby']));
		}
		else {
			$attachments = get_children(array('post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $atts['order'], 'orderby' => $atts['orderby']));
		}

		// End here, if there is nothing to show
		if(empty($attachments)) {
			return '';
		}

		// Special handling for showing a feed
		if(is_feed()) {
			$output = "\n";
			foreach($attachments as $att_id => $attachment) {
				$output .= wp_get_attachment_link($att_id, $atts['size'], true) . "\n";
			}
			return $output;
		}

		// Generate HTML output - in simplified format using "<div class='gallery'><a class='gallery-item'><img class='gallery-preview'></a>...</div>" as structure
		$align = (!empty($atts['align'])) ? ' ' . sanitize_html_class('gallery-align-' . $atts['align']) : '';
		$output = '<div id="gallery-' . wp_unique_id('gallery-') . '" class="gallery th23-gallery galleryid-' . $id . ' gallery-style-' . sanitize_html_class($atts['style']) . ' gallery-size-' . sanitize_html_class($atts['size']) . $align . '">';
		$i = 0;
		foreach($attachments as $id => $attachment) {
			$item = wp_get_attachment_image_src($id, 'full', false);
			if(!empty($captions[$id])) {
				$title = $captions[$id];
			}
			elseif(!empty($attachment->post_excerpt)) {
				$title = $attachment->post_excerpt;
			}
			else {
				$title = $attachment->post_title;
			}
			$preview = wp_get_attachment_image_src($id, $atts['size'], false);
			if(!empty($item) && !empty($preview)) {
				$output .= '<a class="gallery-item" href="' . esc_url($item[0]) . '" data-width="' . (int) $item[1] . '" data-height="' . (int) $item[2] . '" data-title="' . esc_attr($title) . '"><img class="gallery-preview" src="' . esc_url($preview[0]) . '" width="' . (int) $preview[1] . '" height="' . (int) $preview[2] . '"></a>';
			}
		}
		if(!empty($captions['gallery'])) {
			$output .= '<div class="gallery-caption">' . $captions['gallery'] . '</div>';
		}
		$output .= '</div>';

		// Load JS for PhotoSwipe overlay into footer, if required (at least one gallery shortcode is present on page)
		// note: WP ensures that JS gets only loaded once, but localization would otherwise be executed multiple times!
		if(!isset($this->data['js']) || empty($this->data['js'])) {
			wp_enqueue_script('th23-gallery-photoswipe-js');
			wp_enqueue_script('th23-gallery-photoswipe-ui-js');
			wp_enqueue_script('th23-gallery-js');
			wp_localize_script('th23-gallery-js', 'th23_gallery_js', array(
				// note: enable the following for including localized titles - requires respective changes in JS
				/*
				'close' => __('Close (Esc key)', 'th23-gallery'),
				'share' => __('Share', 'th23-gallery'),
				'fullscreen' => __('Toggle fullscreen', 'th23-gallery'),
				'zoom' => __('Zoom in/out', 'th23-gallery'),
				'previous' => __('Previous (arrow left key)', 'th23-gallery'),
				'next' => __('Next (arrow right key)', 'th23-gallery'),
				*/
				'error_loading' => sprintf(__('%1$sThe image%2$s could not be loaded.', 'th23-gallery'), '<a href="%url%" target="_blank">', '</a>'),
			));
			$this->data['js'] = 'done';
		}

		return $output;

	}

	// Handle Gallery blocks - leverages previous gallary_output function
	function th23_gallery_block($block_content, $block, $instance) {

		$atts = array();
		$images = array();
		$captions = array();

		// gather image data in gallery
		foreach((array) $block['innerBlocks'] as $inner) {
			if('core/image' == $inner['blockName']) {
				$images[] = $inner['attrs']['id'];
				// extract image caption
				if(preg_match('/<figcaption.*?>(.*)<\/figcaption>/', $inner['innerHTML'], $matches)) {
					$captions[$inner['attrs']['id']] = $matches[1];
				}
			}
		}
		$atts['include'] = implode(',', $images);

		// gallery alignment
		$align = (!empty($block['attrs']['align'])) ? $block['attrs']['align'] : '';
		if(in_array($align, array('left', 'right'))) {
			$atts['align'] = $align;
		}

		// workaround for selcted default image size for new galleries
		// note: inconsistency in wordpress - newly created gallery sizeSlug is empty by default
		// note: size dropdown does not always show correct value
		// note: media_view_settings filter does not work properly for $settings['galleryDefaults']['size']
		$size = (!empty($block['attrs']['sizeSlug'])) ? $block['attrs']['sizeSlug'] : $this->plugin['options']['default_size'];

		// gallery support focused on thumbnail (ideal with polaroid style) or medium / large image size (as plain style)
		if(!in_array($size, array('medium', 'large'))) {
			$atts['size'] = 'thumbnail';
			$atts['style'] = 'polaroid';
		}
		else {
			$atts['size'] = $size;
			$atts['style'] = 'plain';
		}

		// extract gallery caption
		if(preg_match('/<figcaption.*?>(.*)<\/figcaption>/', $block['innerHTML'], $matches)) {
			$captions['gallery'] = $matches[1];
		}

		return $this->output_gallery('', $atts, null, $captions);

	}

	// Create HTML output translating "th23-gallery-random-image" shortcode
	/*
	* [th23-gallery-random-image ids="{IDS}" size="{SIZE}" link="{LINK}" align="{ALIGN}"]
	*   {IDS} list of IDs for images to choose from, separated by commata
	*   {SIZE} keyword for the size of the image to embedd (thumbnail, medium, large or full) - defaults to full if not specified
	*   {LINK} link the image should lead to (none, file, attachment) - defaults to none if not specified
	*   {ALIGN} alignment for the picture in relation to the text (none, left, center, right) - uses standard CSS classes, defaults to none if not specified
	*/
	function output_random_image($atts = array()) {

		// select random picture ID
		if(!isset($atts['ids'])) {
			return '';
		}
		$picture_ids = explode(',', $atts['ids']);
		if(empty($picture_ids)) {
			return '';
		}
		$random_picture_id = $picture_ids[array_rand($picture_ids, 1)];

		// get random picture post
		$random_picture_post = get_post($random_picture_id);
		if(!isset($random_picture_post) || empty($random_picture_post)) {
			return '';
		}

		// get picture details
		if(!isset($atts['size']) || empty($atts['size']) || !in_array($atts['size'], array('thumbnail', 'medium', 'large', 'full'))) {
			$atts['size'] = 'full';
		}
		$random_picture_src = wp_get_attachment_image_src($random_picture_post->ID, $atts['size']);
		$random_picture_alt = get_post_meta($random_picture_post->ID, '_wp_attachment_image_alt', true);

		// check for valid alignment
		if(!isset($atts['align']) || empty($atts['align']) || !in_array($atts['align'], array('none', 'left', 'center', 'right'))) {
			$atts['align'] = 'none';
		}
		if($atts['align'] == 'none') {
			$random_picture_align = '<p>%s</p>';
		}
		else {
			$random_picture_align = '%s';
		}

		// get picture link
		if(!isset($atts['link']) || empty($atts['link']) || !in_array($atts['link'], array('none', 'file', 'attachment'))) {
			$atts['link'] = 'none';
		}
		if($atts['link'] == 'file') {
			$random_picture_url = '<a href="' . esc_attr(wp_get_attachment_url($random_picture_post->ID)) . '">%s</a>';
		}
		elseif($atts['link'] == 'attachment') {
			$random_picture_url = '<a href="' . esc_attr(get_attachment_link($random_picture_post->ID)) . '" rel="attachment wp-att-' . esc_attr($random_picture_post->ID) . '">%s</a>';
		}
		else {
			$random_picture_url = '%s';
		}

		return sprintf($random_picture_align, sprintf($random_picture_url, '<img class="th23-random-image" src="' . esc_attr($random_picture_src[0]) . '" alt="' . esc_attr($random_picture_alt) . '" width="' . (int) $random_picture_src[1] . '" height="' . (int) $random_picture_src[2] . '" class="align' . esc_attr($atts['align']) . ' size-' . esc_attr($atts['size']) . ' wp-image-' . esc_attr($random_picture_post->ID) . '" />'));

	}

}

// === INITIALIZATION ===

$th23_gallery_path = plugin_dir_path(__FILE__);

// Load additional PRO class, if it exists
if(file_exists($th23_gallery_path . 'th23-gallery-pro.php')) {
	require($th23_gallery_path . 'th23-gallery-pro.php');
}
// Mimic PRO class, if it does not exist
if(!class_exists('th23_gallery_pro')) {
	class th23_gallery_pro extends th23_gallery {
		function __construct() {
			parent::__construct();
		}
		// Ensure PHP <5 compatibility
		function th23_gallery_pro() {
			self::__construct();
		}
	}
}

// Load additional admin class, if required...
if(is_admin() && file_exists($th23_gallery_path . 'th23-gallery-admin.php')) {
	require($th23_gallery_path . 'th23-gallery-admin.php');
	$th23_gallery = new th23_gallery_admin();
}
// ...or initiate plugin via (mimiced) PRO class
else {
	$th23_gallery = new th23_gallery_pro();
}

?>
