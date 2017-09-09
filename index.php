<?php

/*
Plugin Name: ACF Unforgetable Select
Description: Allow custom select values and store them for reuse
Author: Ionuț Staicu
Version: 1.0.1
Author URI: http://ionutstaicu.com
Slug: acf_unforgetable_select
 */

if (!defined('ABSPATH')) {
	exit;
}

define('ACF_UNFORGETABLE_SELECT_VERSION', '1.0.0');

define('ACF_UNFORGETABLE_SELECT_BASEFILE', __FILE__);
define('ACF_UNFORGETABLE_SELECT_URL', plugin_dir_url(__FILE__));
define('ACF_UNFORGETABLE_SELECT_PATH', plugin_dir_path(__FILE__));

add_action('plugins_loaded', function () {
	load_plugin_textdomain('acf_unforgetable_select', false, dirname(plugin_basename(__FILE__)) . '/lang');
});

add_action('acf/input/admin_enqueue_scripts', function () {
	wp_register_script('acf_unforgetable_select', plugins_url('assets/unforgettable-select.js', ACF_UNFORGETABLE_SELECT_BASEFILE), [], ACF_UNFORGETABLE_SELECT_VERSION, false);
	wp_enqueue_script('acf_unforgetable_select');
});

add_filter('acf/update_value/type=select', function ($value, $post_id, $field) {
	if (strpos($field['wrapper']['class'], 'js-unforgettable-select') === false) {
		return $value;
	}

	$value = trim($value);

	$saved = array_filter(get_option("acf_unforgettable_{$field['key']}", []));

	$uniqID = uniqid();

	if (isset($saved[$value])) {
		return $value;
	}

	if (!in_array($value, array_values($saved))) {
		$saved[$uniqID] = $value;
		update_option("acf_unforgettable_{$field['key']}", $saved);
	}

	return $uniqID;
}, 1, 3);

add_filter('acf/format_value/type=select', function ($value, $post_id, $field) {
	if (strpos($field['wrapper']['class'], 'js-unforgettable-select') === false) {
		return $value;
	}

	$saved = get_option("acf_unforgettable_{$field['key']}", []);

	return $saved[$value] ?? $value;
}, 10, 3);

add_filter('acf/load_value/type=select', function ($value, $post_id, $field) {
	if (strpos($field['wrapper']['class'], 'js-unforgettable-select') === false) {
		return $value;
	}

	$saved = array_filter(get_option("acf_unforgettable_{$field['key']}", []));

	return $value;
}, 1, 3);

add_filter('acf/load_field', function ($field) {
	$isAcfPostType = isset($_GET['post']) && get_post_type(absint($_GET['post'])) == 'acf-field-group';
	$isAcfPostType = $isAcfPostType || isset($_GET['post_type']) && $_GET['post_type'] == 'acf-field-group';

	if ($isAcfPostType || strpos($field['wrapper']['class'], 'js-unforgettable-select') === false) {
		return $field;
	}

	$field['choices'] = array_filter(get_option("acf_unforgettable_{$field['key']}", []));

	return $field;
}, 90);
