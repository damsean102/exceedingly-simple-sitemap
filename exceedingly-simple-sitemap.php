<?php

/**
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


//Create a shortcode for HTML sitemap
function ess_html_sitemap() {
	global $post;

	// Build list of pages where 'ess-checkbox' metabox doesn't equal true
	$args = array(
		'posts_per_page'  => -1,
		'post_type'				=> array('page'),
		'post_status'			=> 'publish',
		'title_li'  			=> '',
		'orderby' 				=> 'menu_order, post_title',
		'order' 					=> 'ASC',
		'meta_key'				=> 'ess-checkbox',
		'meta_value'			=> 'true',
		'meta_compare'		=> '!='
	);

	$allPosts = get_posts( $args );

	$html = '';

	if ($allPosts):

		$html = "<div class='ess-sitemap'><ul>";
		foreach ($allPosts as $post):

			$html .= "<li><a href='" . get_permalink($post->ID) . "'>" . get_the_title($post->ID) . "</a></li>";

		endforeach;
		$html .= "</ul></div>";
		
	endif;


	return $html;

}

add_shortcode('ess-sitemap', 'ess_html_sitemap');


//Create XML sitemap - http://www.sitemaps.org/protocol.html

add_action( 'save_post', 'ess_xml_sitemap' );
function ess_xml_sitemap() {
		global $post;

		$sitemapURL = ABSPATH . 'sitemap.xml';

		// Build list of pages where 'ess-checkbox' metabox doesn't equal true
		$args = array(
			'posts_per_page'  => -1,
			'post_type'				=> array('page'),
			'post_status'			=> 'publish',
			'title_li'  			=> '',
			'orderby' 				=> 'menu_order, post_title',
			'order' 					=> 'ASC',
			'meta_key'				=> 'ess-checkbox',
			'meta_value'			=> 'true',
			'meta_compare'		=> '!='
		);

		$allPosts = get_posts( $args );

    if ($allPosts):

	    $sitemap = new DOMDocument("1.0", "UTF-8");

	    //Settings
	    $sitemap->formatOutput = TRUE;

	    //Creat URLSET element and add in any attributes
			$urlset = $sitemap->createElement("urlset");
			$urlset->setAttribute("xmlns", "http://www.sitemaps.org/schemas/sitemap/0.9");

			//START LOOP HERE
			foreach ($allPosts as $post):

				//Get Values for Elements
				$locValue = get_permalink($post->ID);
				$lastModValue = get_the_modified_date('Y-m-d');
				$priorityValue = '0.5';

				//Create a Elements
				$url = $sitemap->createElement("url");
				$loc = $sitemap->createElement("loc", $locValue);
				$lastMod = $sitemap->createElement("lastmod", $lastModValue);
				//$changefreq = $sitemap->createElement("changefreq", "WORK OUT CHANGE FREQUENCY");
				$priority = $sitemap->createElement("priority", $priorityValue);

				//Add these elements as children to the URL element
				$url->appendChild($loc);
				$url->appendChild($lastMod);
				//$url->appendChild($changefreq);
				$url->appendChild($priority);

				//Add it to the URLSET
				$urlset->appendChild($url);

			endforeach;

			//ADD THE URLSET to the XML
			$sitemap->appendChild($urlset);

			//Create
			$sitemap->save($sitemapURL);

		endif; //allposts

}