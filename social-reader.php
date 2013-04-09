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

// https://src.personalliberty.com/FacebookSocialReaderPostsPut.ashx?FacebookID=11&PostID=13
// https://src.personalliberty.com/FacebookSocialReaderPostsGet.ashx?FacebookID=11&PostID=13

if( ! class_exists( 'FB_Social_Reader' ) ) :

	class FB_Social_Reader {
		
		var $plugin_page_name = 'fb-social-reader';

		var $settings = array(
			array(
				'id' => 'fb_app_id',
				'title' => 'Facebook App ID'
			),
			
			array(
				'id' => 'fb_app_namespace',
				'title' => 'Facebook App Namespace'
			),

			array(
				'id' => 'fb_app_delay',
				'title' => 'Facebook App Delay'
			)
		
		);

		function __construct() {
			add_action( 'admin_menu' , array( $this, 'create_settings_menu' ) );

			add_action( 'admin_init', array( $this, 'settings_api_init' ) );

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			add_action( 'wp_head', array( $this, 'add_meta_tags' ) );
	
			add_action( 'jetpack_open_graph_tags', array( $this, 'modify_jetpack_open_graph_tags' ) );
		}

		function settings_api_init() {
			add_settings_section(
				'social-reader-settings-section',
				'Facebook Social Sharing',
				array( $this, 'settings_section_callback' ),
				$this->plugin_page_name
			);

			foreach( $this->settings as $setting ) {
				add_settings_field(
					$setting[ 'id' ],//'fb_app_id',
					$setting[ 'title' ],//'Facebook App ID',
					array( $this, 'settings_field_callback' ),
					$this->plugin_page_name,
					'social-reader-settings-section',
					$setting
				);
			}

			register_setting( $this->plugin_page_name, $this->plugin_page_name, array( $this, 'validate_setting_input' ) );
		}

		function settings_section_callback() {

		}

		function validate_setting_input( $input ) {
			if( isset( $input[ 'fb_app_id'] ) )
				absint( $input[ 'fb_app_id' ] );
			elseif( isset( $input[ 'fb_app_delay' ] ) )
				absint( $input[ 'fb_app_delay' ] );
			elseif( isset( $input[ 'fb_app_namespace' ] ) )
				sanitize_text_field( $input[ 'fb_app_namespace' ] );

			return $input;
		}

		function settings_field_callback( $setting ) {
			$settings = get_option( $this->plugin_page_name );

			if( 'fb_app_id'=== $setting[ 'id' ] ) : ?>
				<input 
					type="text"
					style="width: 200px; display: block; margin: 0 0 20px 0;"
					name="<?php echo $this->plugin_page_name; ?>[fb_app_id]" 
					value="<?php echo esc_attr( $settings[ 'fb_app_id' ] ); ?>" /><?php

			elseif( 'fb_app_namespace' === $setting[ 'id' ] ) : ?>
				<input 
					type="text"
					style="width: 200px; display: block; margin: 0 0 20px 0;"
					name="<?php echo $this->plugin_page_name; ?>[fb_app_namespace]" 
					value="<?php echo esc_attr( $settings[ 'fb_app_namespace' ] ); ?>" /><?php

			elseif( 'fb_app_delay' === $setting[ 'id' ] ) : ?>		
				<input 
					type="text"
					style="width: 200px; display: block; margin: 0 0 20px 0;"
					name="<?php echo $this->plugin_page_name; ?>[fb_app_delay]" 
					value="<?php echo esc_attr( $settings[ 'fb_app_delay' ] ); ?>" /><?php

			elseif( 'fb_settings_submit' === $settings[ 'id' ] ) : ?>
				<input style="display: block; margin: 20px 0;" type="submit" value="Update Settings" /><?php
			endif;
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

		function create_settings_page() { ?>
			<div>
				<form method="post" action="options.php" >
					<?php settings_fields( $this->plugin_page_name ); ?>
					<?php do_settings_sections( $this->plugin_page_name ); ?>
					<input name="submit" type="submit" value="Save Settings" />
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
