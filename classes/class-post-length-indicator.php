<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Post_Length_Indicator {
	private $dir;
	private $file;
	private $assets_dir;
	private $assets_url;

	public function __construct( $file ) {
		$this->file = $file;
		$this->dir = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

		// Handle localisation
		$this->load_plugin_textdomain();
		add_action( 'init', array( $this, 'load_localisation' ), 0 );

		// Load CSS & JS
		add_action( 'wp_enqueue_scripts', array( $this, 'load_assets' ) );

		// Load indicator
		add_action( 'wp_footer', array( $this, 'load_indicator' ), 100 );
	}

	/**
	 * Load CSS & JS for frontend
	 * @return void
	 */
	public function load_assets() {
		global $post;

		if( $this->show_indicator( $post ) ) {
			wp_register_script( 'pli-frontend-js', $this->assets_url . 'js/frontend.js', array( 'jquery' ), '1.0.0', true );
			wp_enqueue_script( 'pli-frontend-js' );

			wp_enqueue_style( 'dashicons' );

			wp_register_style( 'pli-frontend-css', $this->assets_url . 'css/frontend.css', array(), '1.0.0' );
			wp_enqueue_style( 'pli-frontend-css' );
		}
	}

	/**
	 * Load markup for indicator
	 * @return void
	 */
	public function load_indicator() {
		global $post;

		if( $this->show_indicator( $post ) ) {

			$post_type = get_post_type_object( $post->post_type );
			$content_label = $post_type->labels->singular_name;

			$post_colour = get_option( 'pli_post_indicator_color', '#21759B' );
			$comments_colour = get_option( 'pli_comments_indicator_color', '#D54E21' );

			$html = '<div id="post_length_indicator">
						<div class="post_length indicator" style="background:' . esc_attr( $post_colour ) . '">
							<div><span>' . esc_html( $content_label ) . '</span></div>
						</div>
						<div class="comments_length indicator" style="background:' . esc_attr( $comments_colour ) . '">
							<div><span>' . __( 'Comments', 'post-length-indicator') . '</span></div>
						</div>
				 	 </div>';

			echo $html;
		}
	}

	/**
	 * Check if indicator is allowed to be shown on the given post
	 * @param  object  $post Post object
	 * @return boolean
	 */
	private function show_indicator( $post ) {

		$show = false;

		$allowed_types = get_option( 'pli_allowed_post_types', 'all' );

		if( is_single() && ( comments_open() || 0 < intval( get_comments_number( $post->ID ) ) ) && ( 'all' == $allowed_types || in_array( $post->post_type, $allowed_types ) ) ) {
			$show = true;
		}

		return apply_filters( 'pli_show_indicator', $show, $post );
	}

	/**
	 * Load plugin localisation
	 * @return void
	 */
	public function load_localisation () {
		load_plugin_textdomain( 'post-length-indicator' , false , dirname( plugin_basename( $this->file ) ) . '/lang/' );
	}

	/**
	 * Load plugin textdomain
	 * @return void
	 */
	public function load_plugin_textdomain () {
	    $domain = 'post-length-indicator';

	    $locale = apply_filters( 'plugin_locale' , get_locale() , $domain );

	    load_textdomain( $domain , WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain , FALSE , dirname( plugin_basename( $this->file ) ) . '/lang/' );
	}

}