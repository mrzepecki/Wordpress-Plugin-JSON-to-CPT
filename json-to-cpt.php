<?php

/**
 * Plugin Name: JSON to CPT
 * Description: Wordpress developer test
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Check if ACF PRO is installed and enabled
 */

add_action('admin_init', 'check_required_plugins');
function check_required_plugins()
{
	if (is_admin() && current_user_can('activate_plugins') && !is_plugin_active('advanced-custom-fields-pro/acf.php')) {

		add_action('admin_notices', 'check_required_plugins_notice');

		deactivate_plugins(plugin_basename(__FILE__));
		if (isset($_GET['activate'])) {
			unset($_GET['activate']);
		}
	}
}
function check_required_plugins_notice()
{
?>
	<div class="error">
		<p>Plugin JSON to CPT wymaga zainstalowanej i włączonej wtyczki Advanced Custom Fields Pro.</p>
	</div>
<?php
}

/**
 * Create Custom Post Type Developers
 */

function developers_custom_post_type()
{
	register_post_type(
		'developers',
		array(
			'labels'      => array(
				'name'          => __('Developers', 'textdomain'),
				'singular_name' => __('Developer', 'textdomain'),
			),
			'public'      => true,
			'has_archive' => true,
			'supports' => array(
				'custom-fields',
			),
		)
	);
}
add_action('init', 'developers_custom_post_type');

/**
 * Create Custom Fields in ACF for Developers Custom Post Type
 */

function acf_add_fields()
{

	acf_add_local_field_group(array(
		'key' => 'group_1',
		'title' => 'Developer',
		'fields' => array(
			array(
				'key' => 'id',
				'label' => 'ID',
				'name' => 'id',
				'type' => 'text',
			),
			array(
				'key' => 'first_name',
				'label' => 'First name',
				'name' => 'first_name',
				'type' => 'text',
			),
			array(
				'key' => 'last_name',
				'label' => 'Last name',
				'name' => 'last_name',
				'type' => 'text',
			),
			array(
				'key' => 'email',
				'label' => 'Email',
				'name' => 'email',
				'type' => 'text',
			),
			array(
				'key' => 'gender',
				'label' => 'Gender',
				'name' => 'gender',
				'type' => 'text',
			),
			array(
				'key' => 'ip_address',
				'label' => 'IP Address',
				'name' => 'ip_address',
				'type' => 'text',
			)
		),
		'location' => array(
			array(
				array(
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'developers',
				),
			),
		),
	));
}

add_action('acf/init', 'acf_add_fields');

/**
 * Get data from JSON file and update Developers Custom Post Type and fields from ACF
 */

function update_json_to_cpt()
{

	$json_developers = file_get_contents(plugin_dir_path(__FILE__) . 'json/developers.json');

	$json_developers_data = json_decode($json_developers);

	foreach ($json_developers_data as $developer) {
		$id    = $developer->id;
		$first_name    = $developer->first_name;
		$last_name    = $developer->last_name;
		$email    = $developer->email;
		$gender    = $developer->gender;
		$ip_address    = $developer->ip_address;


		$new_post = array(
			'post_title'  => $id,
			'post_status' => 'publish',
			'post_type'   => 'developers',
		);

		$id_of_found_post = post_exists($id);

		if ($id_of_found_post == 0) {
			$post_id = wp_insert_post($new_post);
			update_field('id', $id, $post_id);
			update_field('first_name', $first_name, $post_id);
			update_field('last_name', $last_name, $post_id);
			update_field('email', $email, $post_id);
			update_field('gender', $gender, $post_id);
			update_field('ip_address', $ip_address, $post_id);
		} else {
			update_field('id', $id, $id_of_found_post);
			update_field('first_name', $first_name, $id_of_found_post);
			update_field('last_name', $last_name, $id_of_found_post);
			update_field('email', $email, $id_of_found_post);
			update_field('gender', $gender, $id_of_found_post);
			update_field('ip_address', $ip_address, $id_of_found_post);
		}
	}

	echo '<div id="message" class="updated fade"><p>'
		. 'Update CPT Developers z pliku JSON został zakończony.' . '</p></div>';
}

/**
 * Access from WP CLI to update JSON to CPT function
 */

if (defined('WP_CLI') && WP_CLI) {
	WP_CLI::add_command('update-json-to-cpt', 'update_json_to_cpt');
}

/**
 * Create ACF block
 */

add_action('acf/init', 'my_acf_init_block_types');
function my_acf_init_block_types()
{

	if (function_exists('acf_register_block_type')) {

		acf_register_block_type(array(
			'name'              => 'developers-list',
			'title'             => __('Developers list'),
			'description'       => __('A list with developers CPT items.'),
			'render_template'   => plugin_dir_path(__FILE__) . 'acf/developers-list.php',
			'category'          => 'formatting',
			'icon'              => 'admin-comments',
			'keywords'          => array('developers', 'list'),
		));
	}
}

/**
 * Create JSON to CPT Admin page
 */


add_action('admin_menu', 'json_to_cpt_button_menu');

function json_to_cpt_button_menu()
{
	add_menu_page('JSON to CPT', 'JSON to CPT', 'manage_options', 'json-to-cpt', 'json_to_cpt_button_admin_page');
}

function json_to_cpt_button_admin_page()
{
	if (!current_user_can('manage_options')) {
		wp_die(__('Nie masz wystarczających uprawnieńs.'));
	}

	echo '<div class="wrap">';

	echo '<h2>JSON to CPT</h2>';
	echo '<p>Plik developers.json znajduje się w katalogu <strong>' . plugin_dir_path(__FILE__) . 'json/ </strong></p>';
	echo '<p>Aby zaaktualizować dane, nadpisz ten plik i naciśnij przycisk Update JSON to CPT.</p>';
	echo '<p>Skorzystaj z WP CLI wpisując komendę <strong>wp update-json-to-cpt</strong></p>';


	if (isset($_POST['update_json_to_cpt_button']) && check_admin_referer('update_json_to_cpt_clicked')) {
		update_json_to_cpt();
	}

	echo '<form action="options-general.php?page=json-to-cpt" method="post">';

	wp_nonce_field('update_json_to_cpt_clicked');
	echo '<input type="hidden" value="true" name="update_json_to_cpt_button" />';
	submit_button('Update JSON to CPT');
	echo '</form>';

	echo '</div>';
}
