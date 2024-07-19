<?php
/*
th23 Gallery
Admin area

Copyright 2011-2024, Thorsten Hartmann (th23)
https://th23.net
*/

// Security - exit if accessed directly
if(!defined('ABSPATH')) {
    exit;
}

class th23_gallery_admin extends th23_gallery_pro {

	function __construct() {

		parent::__construct();

		// Setup basics (additions for backend)
		$this->plugin['settings_base'] = 'options-media.php';

		// Modify plugin overview page
		add_filter('plugin_action_links_' . $this->plugin['basename'], array(&$this, 'settings_link'), 10);

		// Add gallery options to media settings
		add_action('admin_init', array(&$this, 'add_media_options'));

		// Selection of default sizes for newly created galleries
		$this->data['default_sizes'] = array(
			'thumbnail' => __('Thumbnail'),
			'medium' => __('Medium'),
			'large' => __('Large'),
			'full' => __('Full Size')
		);

	}

	// Ensure PHP <5 compatibility
	function th23_gallery_admin() {
		self::__construct();
	}

	// Add settings link to plugin actions in plugin overview page
	function settings_link($links) {
		if(!empty($this->plugin['settings_base'])) {
			$url = $this->plugin['settings_base'];
			if(!empty($this->plugin['settings_handle'])) {
				$url .= '?page=' . $this->plugin['settings_handle'];
			}
			$links['settings'] = '<a href="' . esc_url($url) . '">' . __('Settings', 'th23-gallery') . '</a>';
		}
		return $links;
	}

	// Add gallery options to media settings
	function add_media_options() {
		add_settings_section('th23_gallery', __('Gallery'), null, 'media');
		register_setting('media', 'th23_gallery', array('default' => 'thumbnail', 'sanitize_callback' => array($this, 'sanitize_options')));
		add_settings_field(
			'th23-gallery-default-size',
			__('Default image size', 'th23-gallery'),
			array($this, 'default_size_field'),
			'media',
			'th23_gallery'
		);
	}

	// Sanitize options
	function sanitize_options($input) {
		$sanitized = array();
		$sanitized['default_size'] = (!empty($this->data['default_sizes'][$input['default_size']])) ? $input['default_size'] : 'thumbnail';
		return $sanitized;
	}

	// Show option settings fields in general admin
	function default_size_field($args) {
		echo '<select id="th23-gallery-default-size" name="th23_gallery[default_size]">';
		foreach($this->data['default_sizes'] as $size_slug => $size_title) {
			$selected = ($this->plugin['options']['default_size'] == $size_slug) ? ' selected="selected"' : '';
			echo '<option value="' . $size_slug . '" ' . $selected . '>' . $size_title . '</option>';
		}
		echo '</select>';
		echo '<p class="description">' . __('Default image size for newly created galleries', 'th23-gallery') . '</p>';
	}

}


?>
