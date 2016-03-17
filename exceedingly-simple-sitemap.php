<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://samdean.co.uk/
 * @since             1.0.0
 * @package           Exceedingly_Simple_Sitemap
 *
 * @wordpress-plugin
 * Plugin Name:       Exceedingly Simple Sitemap
 * Plugin URI:        https://github.com/damsean102/exclude-from-sitemap
 * Description:       A plugin that generates HTML and XML sitemaps and allows pages to be excluded from the sitemaps,
 * Version:           1.0.0
 * Author:            Sam Dean
 * Author URI:        http://samdean.co.uk/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       exceedingly-simple-sitemap
 * Domain Path:       /languages
 */


/*
* ADMIN FUNCTIONS - Create and save metabox information
*/

function ess_metabox_markup($object) {

	wp_nonce_field(basename(__FILE__), "ess-metabox-nonce");

	$checkboxVal = get_post_meta($object->ID, "ess-checkbox", true); ?>

		<fieldset>
			<legend class="screen-reader-text"><span>Exclude this page from the sitemap?</span></legend>
			<label for="exclude-sitemap-checkbox">
				<input id="exclude-sitemap-checkbox" name="ess-checkbox" type="checkbox" value="true" <?php checked( $checkboxVal, 'true', TRUE ); ?>>
				<span><?php esc_attr_e('Yes'); ?></span>
			</label>
		</fieldset>

	<?php
}

function create_ess_metabox() {
	 add_meta_box("ess-simple-sitemap", "Exclude from sitemap?", "ess_metabox_markup", "page", "side", "default", null);
}

add_action("add_meta_boxes", "create_ess_metabox");


function save_ess_metabox($post_id, $post, $update){
	//If nonce field cannot be verfied or isn't set
	if (!isset($_POST["ess-metabox-nonce"]) || !wp_verify_nonce($_POST["ess-metabox-nonce"], basename(__FILE__)))
		return $post_id;

	//If the user can't edit the post/page
	if(!current_user_can("edit_posts", $post_id) || !current_user_can("edit_pages", $post_id))
		return $post_id;

	//If the post is doing an autosave
	if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE)
		return $post_id;

	$slug = "page";
	//If the post type is page
	if($slug != $post->post_type)
		return $post_id;

	$essCheckboxVal = "";

	if(isset($_POST["ess-checkbox"])) {
		$essCheckboxVal = $_POST["ess-checkbox"];
	}   
	update_post_meta($post_id, "ess-checkbox", $essCheckboxVal);
}

add_action("save_post", "save_ess_metabox", 10, 3);


/*
* GENERATE SITEMAPS
*/
