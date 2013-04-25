<?php

/*
Plugin Name: HTML Titles
Plugin URI: http://wordpress.org/extend/plugins/html-titles
Version: 0.2
Description: A new tiny editor input is created. If used, it will replace the title in get_the_title() and the_title() tags.
Author: lightningspirit
Author URI: http://profiles.wordpress.org/lightningspirit
Text Domain: html-titles
Domain Path: /languages/
Tags: plugin, html title, html titles, html, title, title filter
License: GPLv2 - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/


/*
 * @package HTML Titles
 * @author lightningspirit
 * @copyright lightningspirit 2012-2013
 * This code is released under the GPL licence version 2 or later
 * http://www.gnu.org/licenses/gpl.txt
 */



if ( ! class_exists ( 'WP_HTML_Titles' ) ) :
/**
 * WP_HTML_Titles
 *
 * @package WordPress
 * @subpackage HTML Titles
 * @since 0.2
 */
class WP_HTML_Titles {
	
	/** 
	 * {@internal Missing Short Description}}
	 * 
	 * @since 0.2
	 * 
	 * @return void
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( __CLASS__, 'init' ) );

	}
	
	/** 
	 * {@internal Missing Short Description}}
	 * 
	 * @since 0.2
	 * 
	 * @return void
	 */
	public static function init() {
		
		// Load the text domain to support translations
		load_plugin_textdomain( 'html-titles', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		
		// if new upgrade
		if ( version_compare( (int) get_option( 'html_titles_plugin_version' ), '0.2', '<' ) )
			add_action( 'admin_init', array( __CLASS__, 'do_upgrade' ) );
		
		
		/** Register actions for admin pages */
		if ( is_admin() ) {
			/** Add html title ditor CSS and JS */
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts' ) );
			/** Add html title editor */
			add_action( 'edit_form_after_title', array( __CLASS__, 'edit_form_after_title' ) );
			/** Save the HTML title */
			add_action( 'edit_post', array( __CLASS__, 'save_html_title_post' ) );
			
		}
		
		add_action( 'init', array( __CLASS__, 'add_default_post_type_support' ) );
		add_filter( 'the_title', array( __CLASS__, 'the_title' ), 10, 2 );
		
	}
	
	/** 
	 * {@internal Missing Short Description}}
	 * 
	 * @since 0.1
	 * 
	 * @return void
	 */
	public static function do_upgrade() {
		update_option( 'html_titles_plugin_version', '0.2' );
		
	}
	
	/** 
	 * {@internal Missing Short Description}}
	 * 
	 * @since 0.1
	 * 
	 * @return void
	 */
	public static function admin_enqueue_scripts() {
		global $current_screen;
		
		if ( 'post' == $current_screen->base && post_type_supports( $current_screen->post_type, 'html_title' ) ) {
			wp_enqueue_script( 'wp-html-title', plugin_dir_url( __FILE__ ) . 'html-title-editor.js', array(), '0.1' );
			wp_enqueue_style( 'wp-html-title', plugin_dir_url( __FILE__ ) . 'html-title-editor.css', array(), '0.1', 'all' );
			
		}
		
		
	}
	
	/** 
	 * {@internal Missing Short Description}}
	 * 
	 * @since 0.1
	 * 
	 * @return void
	 */
	public static function edit_form_after_title() {
		global $post_id, $post_type;
		
		if ( false == post_type_supports( $post_type, 'html_title' ) )
			return;
		
		if ( ! $html_title = get_post_meta( $post_id, '_wp_html_title', true ) )
			$html_title = '';
		
		?>
		<h3 class="wp-html-title"><?php _e( 'HTML Title:', 'html-titles' ); ?></h3>
		
		<?php if ( 'page' == $post_type ) : ?>
		<p class="description wp-html-title"><?php _e( 'Including an HTML Title replaces the Title provided above in the page template using the <span class="code">the_title()</span> and the <span class="code">get_the_title()</span> tags.', 'html-titles' ); ?></p>		
		<?php endif; ?>
		
		<?php
		
		//wp_editor( $html_title, 'html-title-edifor', array( 'teeny' => true,'textarea_name' => '_wp_html_title', 'wpautop' => false, 'media_buttons' => false, 'quicktags' => false, ) );
		
		wp_editor( $html_title, 'html-title-editor', 
			apply_filters( 'wp_html_title_editor_args', array(
				'wpautop' => false,
				'media_buttons' => false,
				'textarea_name' => '_wp_html_title',
				'textarea_rows' => 1,
				'tabindex' => 1,
				'quicktags' => false,
				'tinymce' => array(
					'theme_advanced_buttons1' => 'bold,italic,underline,|,forecolor,|,link,unlink,charmap,|,undo,redo',
					'theme_advanced_buttons2' => '',
					'content_css' => plugin_dir_url( __FILE__ ) . 'editor-content.css',
					//'forced_root_block' => false,
					//'force_br_newlines' => false,
					//'force_p_newlines' => false,
					//'convert_newlines_to_brs' => false,
					//'valid_elements' => 'strong/b,em/i,span,a,del',
					//'theme_advanced_statusbar_location' => 'none',
					)
				)
			)
		);
		
		wp_nonce_field( plugin_basename( __FILE__ ), '_wp_html_title_nonce' );
		
	}

	/** 
	 * {@internal Missing Short Description}}
	 * 
	 * @since 0.1
	 * 
	 * @param int $post_id
	 * @return int
	 */
	public static function save_html_title_post( $post_id ) {
		if ( ! isset( $_REQUEST['_wp_html_title_nonce'] ) )
			return;
		
		if ( ! wp_verify_nonce( $_POST['_wp_html_title_nonce'], plugin_basename( __FILE__ ) ) )
			return;
		
		
		$post_type_object = get_post_type_object( get_post_type( $post_id ) );
		if ( ! current_user_can( $post_type_object->cap->edit_post, $post_id ) )
			return;
		
		
		if ( ! post_type_supports( get_post_type( $post_id ), 'html_title' ) )
			return;
		
		
		$new_html_title = '';
		if ( isset( $_REQUEST['_wp_html_title'] ) )
			$new_html_title = wp_kses( $_REQUEST['_wp_html_title'], array(
					'a' => array(),	'em' => array(), 'strong' => array(), 'span' => array( 'style' => array() ),
				)
			);
		
		
		$old_html_title = get_post_meta( $post_id, '_wp_html_title', true );
			
		if ( $old_html_title && '' == $new_html_title )
			delete_post_meta( $post_id, '_wp_html_title' );
			
		elseif ( $old_html_title && $new_html_title )
			update_post_meta( $post_id, '_wp_html_title', $new_html_title, $old_html_title );
			
		else
			add_post_meta( $post_id, '_wp_html_title', $new_html_title );
		
		
		return $post_id;
		
	}

	/** 
	 * {@internal Missing Short Description}}
	 * 
	 * @since 0.1
	 * 
	 * @return void
	 */
	public static function add_default_post_type_support() {
		$default_post_types = apply_filters( 'wp_html_title_post_types', array( 'page' ) );
		
		foreach ( (array) $default_post_types as $post_type )
			add_post_type_support( $post_type, 'html_title' );
			
	}
	
	/** 
	 * {@internal Missing Short Description}}
	 * 
	 * @since 0.1
	 * 
	 * @param string $title
	 * @param int $post_id
	 * @return string
	 */
	public static function the_title( $title, $post_id ) {
		if ( ! is_singular() || is_admin() )
			return $title;
		
		if ( $html_title = get_post_meta( $post_id, '_wp_html_title', true ) )
			return $html_title;
		
		return $title;
	 	
	}
	
}

new WP_HTML_Titles;

endif;


/**
 * html_titles_plugin_activation_hook
 *
 * Register activation hook for plugin
 *
 * @since 0.1
 */
function html_titles_plugin_activation_hook() {
	// Wordpress version control. No compatibility with older versions. ( wp_die )
	if ( version_compare( get_bloginfo( 'version' ), '3.5', '<' ) ) {
		wp_die( 'HTML Title is not compatible with versions prior to 3.5' );

	}

}
register_activation_hook( __FILE__, 'html_titles_plugin_activation_hook' );
