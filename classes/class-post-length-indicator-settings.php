<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Post_Length_Indicator_Settings {
	private $dir;
	private $file;
	private $assets_dir;
	private $assets_url;

	public function __construct( $file ) {
		$this->file = $file;
		$this->dir = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );
		$this->settings_base = 'pli_';

		add_action( 'admin_init', array( $this, 'init' ) );

		// Register plugin settings
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		// Add settings page to menu
		add_action( 'admin_menu', array( $this, 'add_menu_item' ) );

		// Add settings link to plugins page
		add_filter( 'plugin_action_links_' . plugin_basename( $this->file ), array( $this, 'add_settings_link' ) );
	}

	public function init() {
		$this->settings = $this->settings_fields();
	}

	/**
	 * Add settings page to admin menu
	 * @return void
	 */
	public function add_menu_item() {
		$page = add_options_page( __( 'Post Length Indicator', 'post-length-indicator' ) , __( 'Post Length Indicator', 'post-length-indicator' ) , 'manage_options' , 'pli_settings' ,  array( $this, 'settings_page' ) );
		add_action( 'admin_print_styles-' . $page, array( $this, 'settings_assets' ) );
	}

	public function settings_assets() {
		wp_enqueue_style( 'farbtastic' );
    	wp_enqueue_script( 'farbtastic' );

    	wp_register_script( 'pli-admin-js', $this->assets_url . 'js/admin.js', array( 'farbtastic', 'jquery' ), '1.0.0' );
    	wp_enqueue_script( 'pli-admin-js' );
	}

	/**
	 * Add settings link to plugin list table
	 * @param  array $links Existing links
	 * @return array 		Modified links
	 */
	public function add_settings_link( $links ) {
		$settings_link = '<a href="options-general.php?page=pli_settings">' . __( 'Settings', 'post-length-indicator' ) . '</a>';
  		array_push( $links, $settings_link );
  		return $links;
	}

	/**
	 * Build settings fields
	 * @return array Fields to be displayed on settings page
	 */
	private function settings_fields() {

		$post_types = get_post_types( array( 'public' => true ) );
		$type_options = array();
		$default_types = array();
		foreach( $post_types as $type ) {

			if( in_array( $type, array( 'wooframework' ) ) ) continue;

			if( ! post_type_exists( $type ) ) continue;

			$type_obj = get_post_type_object( $type );
			$type_options[ $type ] = $type_obj->labels->name;
			$default_types[] = $type;
		}

		$settings['customise'] = array(
			'title'					=> __( 'Customise Indicator', 'post-length-indicator' ),
			'description'			=> __( 'Customise where and how the post length indicator is displayed', 'post-length-indicator' ),
			'fields'				=> array(
				array(
					'id' 			=> 'post_indicator_color',
					'label'			=> __( 'Post indicator colour', 'post-length-indicator' ),
					'description'	=> __( 'The colour for the post (top) section of the indicator.', 'post-length-indicator' ),
					'type'			=> 'color',
					'default'		=> '#21759B'
				),
				array(
					'id' 			=> 'comment_indicator_color',
					'label'			=> __( 'Comment indicator colour', 'post-length-indicator' ),
					'description'	=> __( 'The colour for the comments (bottom) section of the indicator.', 'post-length-indicator' ),
					'type'			=> 'color',
					'default'		=> '#D54E21'
				),
				array(
					'id' 			=> 'allowed_post_types',
					'label'			=> __( 'Post types', 'post-length-indicator' ),
					'description'	=> __( 'The post types on which the indicator will display.', 'post-length-indicator' ),
					'type'			=> 'checkbox_multi',
					'options'		=> $type_options,
					'default'		=> $default_types
				)
			)
		);

		$settings = apply_filters( 'pli_settings_fields', $settings );

		return $settings;
	}

	/**
	 * Register plugin settings
	 * @return void
	 */
	public function register_settings() {
		if( is_array( $this->settings ) ) {
			foreach( $this->settings as $section => $data ) {

				// Add section to page
				add_settings_section( $section, $data['title'], '', 'pli_settings' );

				foreach( $data['fields'] as $field ) {

					// Validation callback for field
					$validation = '';
					if( isset( $field['callback'] ) ) {
						$validation = $field['callback'];
					}

					// Register field
					$option_name = $this->settings_base . $field['id'];
					register_setting( 'pli_settings', $option_name, $validation );

					// Add field to page
					add_settings_field( $field['id'], $field['label'], array( $this, 'display_field' ), 'pli_settings', $section, array( 'field' => $field ) );
				}
			}
		}
	}

	/**
	 * Generate HTML for displaying fields
	 * @param  array $args Field data
	 * @return void
	 */
	public function display_field( $args ) {

		$field = $args['field'];

		$html = '';

		$option_name = $this->settings_base . $field['id'];
		$option = get_option( $option_name );

		$data = '';
		if( isset( $field['default'] ) ) {
			$data = $field['default'];
			if( $option  ) {
				$data = $option;
			}
		}

		switch( $field['type'] ) {

			case 'checkbox_multi':
				foreach( $field['options'] as $k => $v ) {
					$checked = false;
					if( in_array( $k, $data ) ) {
						$checked = true;
					}
					$html .= '<label for="' . esc_attr( $field['id'] . '_' . $k ) . '"><input type="checkbox" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $option_name ) . '[]" value="' . esc_attr( $k ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" /> ' . $v . '</label> ';
				}
			break;

			case 'color':
				?><div class="color-picker" style="position:relative;">
			        <input type="text" name="<?php esc_attr_e( $option_name ); ?>" class="color" value="<?php esc_attr_e( $data ); ?>" />
			        <div style="position:absolute;background:#FFF;z-index:99;border-radius:100%;" class="colorpicker"></div>
			    </div>
			    <?php
			break;

		}

		switch( $field['type'] ) {

			case 'checkbox_multi':
				$html .= '<br/><span class="description">' . $field['description'] . '</span>';
			break;

			default:
				$html .= '<label for="' . esc_attr( $field['id'] ) . '"><span class="description">' . $field['description'] . '</span></label>' . "\n";
			break;
		}

		echo $html;
	}

	/**
	 * Load settings page content
	 * @return void
	 */
	public function settings_page() {

		echo '<div class="wrap">
				<h2>' . __( 'Post Length Indicator Settings', 'post-length-indicator' ) . '</h2>
				<form method="post" action="options.php" enctype="multipart/form-data">';

				settings_fields( 'pli_settings' );
				do_settings_sections( 'pli_settings' );

			  echo '<p class="submit">
						<input name="Submit" type="submit" class="button-primary" value="' . esc_attr( __( 'Save Settings' , 'plugin_textdomain' ) ) . '" />
					</p>
				</form>
			  </div>';
	}

}