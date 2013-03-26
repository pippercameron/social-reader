<?php

/*
 * Plugin Name: Facebook Social Reader
 * Plugin URI: http://personalliberty.com
 * Description: Social Reader for Personal Liberty Media Group.
 * Author: Spencer Cameron
 * Author URI: http://personalliberty.com
 * Version: 1.0
 * License: GPL2
 */

if( ! class_exists( 'FB_Social_Reader' ) ) :

	class FB_Social_Reader {
		
		var $plugin_page_name = 'fb-social-reader';

		var $settings = array(
								'fb_app_id' => 0,
								'fb_app_namespace' => '',
								'fb_app_delay' => 0 );

		function __construct() {
			add_action( 'admin_menu' , array( $this, 'create_settings_menu' ) );

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			add_action( 'wp_head', array( $this, 'add_meta_tags' ) );
	
			add_action( 'jetpack_open_graph_tags', array( $this, 'modify_jetpack_open_graph_tags' ) );

			$this->settings = get_option( $this->plugin_page_name . '-settings' );
		}

		function enqueue_scripts() {
			if( ! is_single() )
				return;

			wp_enqueue_script( 'jquery' );

			wp_enqueue_script( 'social-reader-javascript', plugins_url( '/js/social-reader.js', __FILE__ ) );

			wp_localize_script( 'social-reader-javascript', 'social_reader_data', json_encode( $this->settings ) );

			wp_enqueue_style( 'social-reader-css', plugins_url( '/css/style.css', __FILE__ ) );
		}

		function add_meta_tags() {
			if( ! is_single() || has_filter( 'jetpack_enable_open_graph' ) )
				return; ?>

			<meta property="fb:app_id" content="<?php echo esc_attr( $this->settings[ 'fb_app_id' ] ); ?>" />
			<meta property="og:type" content="<?php echo esc_attr( $this->settings[ 'fb_app_namespace' ] ); ?>:article" />
			<meta property="og:title" content="<?php the_title(); ?>" />
			<meta property="og:description" content="<?php the_excerpt(); ?>" />
			<meta property="og:url" content="<?php the_permalink(); ?>"><?php
		}

		function modify_jetpack_open_graph_tags( $tags ) {
			if( is_single() ) {
				$tags[ 'fb:app_id' ] = $this->settings[ 'fb_app_id' ];
				$tags[ 'og:type' ] = $this->settings[ 'fb_app_namespace' ];
			}

			return $tags;
	
		}

		function create_settings_menu() {
			add_options_page( 'Social Reader Settings', 'Social Reader', 'manage_options', $this->plugin_page_name, array( $this, 'create_settings_page' ) );
		}

		function create_settings_page() {
			if(  ! empty( $_POST ) )
				$this->process_form_input();

			$this->generate_settings_form();
		}

		function process_form_input() {
			check_admin_referer( $this->plugin_page_name );

			$this->settings[ 'fb_app_id' ] = isset( $_POST[ 'fb_app_id' ] ) ? absint( $_POST[ 'fb_app_id' ] ) : 0;
			$this->settings[ 'fb_app_namespace' ] = isset( $_POST[ 'fb_app_namespace' ] ) ? sanitize_text_field( $_POST[ 'fb_app_namespace' ] ) : '';
			$this->settings[ 'fb_app_delay' ] = isset( $_POST[ 'fb_app_delay' ] ) ? absint( $_POST[ 'fb_app_delay' ] ) : 0;

			update_option( $this->plugin_page_name . '-settings', $this->settings );
		}

		function generate_settings_form() { ?>
			<div>
				<p>Social Reader Settings</p>
				<form method="post" action="<?php menu_page_url( $this->plugin_page_name ); ?>" >

					<span style="font-size: 14px;">Facebook App ID:</span>
					<input 
						type="text"
						style="width: 200px; display: block; margin: 0 0 20px 0;"
						name="fb_app_id" 
						value="<?php echo esc_attr( $this->settings[ 'fb_app_id' ] ); ?>" />

					<span style="font-size: 14px;">App Namespace:</span>
					<input 
						type="text"
						style="width: 200px; display: block; margin: 0 0 20px 0;"
						name="fb_app_namespace" 
						value="<?php echo esc_attr( $this->settings[ 'fb_app_namespace' ] ); ?>" />

					<span style="font-size: 14px;">Time to wait before the post is considered read:</span>
					<input 
						type="text"
						style="width: 200px; display: block; margin: 0 0 20px 0;"
						name="fb_app_delay" 
						value="<?php echo esc_attr( $this->settings[ 'fb_app_delay' ] ); ?>" />

					<input style="display: block; margin: 20px 0;" type="submit" value="Update Settings" />

					<?php wp_nonce_field( $this->plugin_page_name ); ?>

				</form>
			</div><?php
		}
	
		function display_social_reader() { ?>			
			<div id="social-reader" style="display: none;">
				<span id="social-reader-login-button"><fb:login-button show-faces="false" width="200" max-rows="1" scope="publish_actions" ></fb:login-button></span>
				<span id="social-reader-state"><img id="social-reader-status" src="http://plimages.blob.core.windows.net/images/social-reader/off.png" alt="" /><span>Not shared with Facebook friends</span></span>
				<img id="social-reader-settings" src="http://plimages.blob.core.windows.net/images/social-reader/settings.png" alt="" />
				<div id="social-reader-settings-options">
					<div id="social-reader-share-on" class="social-reader-option"><span><p>Automatically share with friends.</p></span></div>
					<div id="social-reader-share-off" class="social-reader-option"><span><p>Don't share with friends.</p></span></div>
				</div>
			</div><?php
		}

	}

	new FB_Social_Reader;

endif;

?>
