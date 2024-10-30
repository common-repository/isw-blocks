<?php
/**
 *  Settings base class.
 *
 */

if ( !class_exists('ISWBlockSettingsPage') ) :

class ISWBlockSettingsPage extends ISWOptionsPage {

	/**
	 * Holds the values to be used in the fields callbacks
	 */
	private $is_network_admin;
	private $admin_url;
	private $manage_options_capability;

	private $defaults = array(
		'options' => array(
			'hierarchical' => false,
			'supports' => array( 'title', 'editor' ),
		),
		'features' => array(
			'output_mode' => true,
			'sort_order' => true,
			'widget' => true
		)
	);

	/**
	 * Start up
	 */
	public function __construct()
	{
		parent::__construct( ISW_BLOCKS_UNDERSCORE );

		// lets us know if we're dealing with a multisite and on the network admin page
		// also, defines admin url and capability for users to be able to edit options
		if ( is_multisite() && is_network_admin() ) {
			
			$this->is_network_admin = true;
			$this->admin_url = network_admin_url( 'settings.php' );
			$this->manage_options_capability = 'manage_network_options';
			
			// get network user settings (only if multisite AND plugin is network activated)
			if ( $plugins = get_site_option( 'active_sitewide_plugins' ) && isset( $plugins[ ISW_BLOCKS_PLUGIN_FILE ] ) ){
				$this->user_settings = ( $user_settings = get_site_option( ISW_BLOCKS_UNDERSCORE . '_settings' ) ) ? $user_settings : array();
			} else { 
				$this->user_settings = ( $user_settings = get_option(  ISW_BLOCKS_UNDERSCORE . '_settings' ) ) ? $user_settings : array(); 
			}
			
		} else {
		
			$this->is_network_admin = false;
			// $this->admin_url = admin_url( 'edit.php?post_type=block' );
			$this->admin_url = 'edit.php?post_type=block';
			$this->manage_options_capability = 'manage_options';
			$this->user_settings = ( $user_settings = get_option(  ISW_BLOCKS_UNDERSCORE . '_settings' ) ) ? $user_settings : array() ; 
		}
	}

	protected function on_register_menus() {

		// This page will be under "Blocks"
		add_submenu_page(
			$this->admin_url, 
			__('Block Settings', ISW_BLOCKS_TEXTDOMAIN),
			__('Settings', ISW_BLOCKS_TEXTDOMAIN),
			$this->manage_options_capability, 
			'settings', 
			array( $this, 'render_settings_page' )

		);

	}

	public function render_settings_page() {
		$page = ISW_BLOCKS_UNDERSCORE . '_'. "settings";
		$this->block_settings = (array) get_post_type_object( 'block' );
		?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2><?php _e( 'Block Settings', ISW_BLOCKS_TEXTDOMAIN );?></h2>
			<?php settings_errors(); ?>         
			<form method="post" action="options.php">
			<?php
				// This prints out all hidden setting fields
				settings_fields( $page );   
				do_settings_sections( $page );
				submit_button(); 
			?>
			</form>
		</div>
		<?php
	}

	protected function on_register_pages () {
 
		$this->pages['settings'] = (object) array(
			'group' 	=> ISW_BLOCKS_UNDERSCORE,
			'sections'  => (object) array(
				'features' => (object) array(
					'label' 	=> __( 'Features', ISW_BLOCKS_TEXTDOMAIN ),
					'fields' 	=> array(
						'output_mode' => (object) array(
							'type' => 'onoff',
							'label'=> __( 'Output Mode', ISW_BLOCKS_TEXTDOMAIN ),
							'description' => __( 'This setting defines whether blocks support wpautop overriding.', ISW_BLOCKS_TEXTDOMAIN ),
							'default' => true
						),
						'sort_order' => (object) array (
							'type' => 'onoff',
							'label'=> __( 'Sort Order', ISW_BLOCKS_TEXTDOMAIN ),
							'description' => __( 'This setting defines whether blocks support sort ordering.', ISW_BLOCKS_TEXTDOMAIN ),
							'default' => true
						),
						'widget' => (object) array (
							'type' => 'onoff',
							'label'=> __( 'Widget', ISW_BLOCKS_TEXTDOMAIN ),
							'description' => __( 'This setting defines whether block widget is enabled.', ISW_BLOCKS_TEXTDOMAIN ),
							'default' => true
						)
					)
				),
				'options' => (object) array(
					'label' 	=> __( 'Advanced Options', ISW_BLOCKS_TEXTDOMAIN ),
					'fields' 	=> array(
						'hierarchical' => (object) array(
							'type' 			=> 'onoff',
							'label' 		=> __( 'Hierarchical' , ISW_BLOCKS_TEXTDOMAIN ),
							'description' 	=> __( 'This setting defines whether this post type is hierarchical, which allows a parent to be specified. In order to define a post\'s parent, the post type must support "Page Attributes".', ISW_BLOCKS_TEXTDOMAIN ),
							'default' 		=> false,
						),
						'supports' => (object) array(
							'label' => __( 'Supports', ISW_BLOCKS_TEXTDOMAIN ),
							'type' => 'checkbox',
							'description' => __( 'These settings let you register support for certain features. All features are directly associated with a functional area of the edit post screen.', ISW_BLOCKS_TEXTDOMAIN ),
							'default' => array( 'title', 'editor' ),
							'data' => array(
								'title' => (object) array(
									'label' => __( 'Title', ISW_BLOCKS_TEXTDOMAIN )
									),
								'editor' => (object) array( // Content
									'label' => __( 'Editor', ISW_BLOCKS_TEXTDOMAIN )
									),
								'author' => (object) array(
									'label' => __( 'Author', ISW_BLOCKS_TEXTDOMAIN )
									),
								'thumbnail' => (object) array( // Featured Image) (current theme must also support post-thumbnails
									'label' => __( 'Thumbnail', ISW_BLOCKS_TEXTDOMAIN )
									),
								'excerpt' => (object) array(
									'label' => __( 'Excerpt', ISW_BLOCKS_TEXTDOMAIN )
									),
								'trackbacks' => (object) array(
									'label' => __( 'Trackbacks', ISW_BLOCKS_TEXTDOMAIN )
									),
								'custom-fields' => (object) array(
									'label' => __( 'Custom Fields', ISW_BLOCKS_TEXTDOMAIN )
									),
								'comments' => (object) array(
									'label' => __( 'Comments', ISW_BLOCKS_TEXTDOMAIN )
									),
								'revisions' => (object) array( // will store revisions
									'label' => __( 'Revisions', ISW_BLOCKS_TEXTDOMAIN )
									),
								'page-attributes' => (object) array( // template and menu order (hierarchical must be true)
									'label' => __( 'Page Attributes', ISW_BLOCKS_TEXTDOMAIN )
									),
								'post-formats' => (object) array(
									'label' => __( 'Post Formats', ISW_BLOCKS_TEXTDOMAIN )
									)
							)
						),
					)
				)
			),

		);
	}
	
	protected function on_render_section( $section ) {
		// get network user settings (only if multisite AND plugin is network activated)
		if ( is_multisite() && is_network_admin() && $plugins = get_site_option( 'active_sitewide_plugins' ) && isset( $plugins[ ISW_BLOCKS_PLUGIN_FILE ] ) ){
			$this->options = get_site_option( $section->id ,  $this->defaults['options'] );
		} else { 
			$this->options = get_option( $section->id , $this->defaults['options'] ); 
		}

		switch ( $section->id ) {
			
			case $this->prefix .'_features':
				echo sprintf( "<p>%s</p>", __( 'You may enable or disable the following features', ISW_BLOCKS_TEXTDOMAIN ));
				break;

			case $this->prefix . '_options':
				echo sprintf( "<p>%s</p>", __( 'You may customize what block types support depending on your needs', ISW_BLOCKS_TEXTDOMAIN ));
				break;


		}
		
	}

	protected function on_sanitize_field_input( $option, $input ) {
		
		$new_input = array();

		switch ($option) {
			case 'features':

				if( isset( $input['output_mode'] )){
					$new_input['output_mode'] = $input['output_mode'] == 'on' ? true : false ;
				} else {
					$new_input['output_mode'] = false;
				}

				if( isset( $input['sort_order'] )){
					$new_input['sort_order'] = $input['sort_order'] == 'on' ? true : false ;
				} else {
					$new_input['sort_order'] = false;
				}

				if( isset( $input['widget'] )){
					$new_input['widget'] = $input['widget'] == 'on' ? true : false ;
				} else {
					$new_input['widget'] = false;
				}
				break;
			
			case 'options':

				if( isset( $input['hierarchical'] )){
					$new_input['hierarchical'] = $input['hierarchical'] == 'on' ? true : false ;
				} else {
					$new_input['hierarchical'] = false;
				}

				if( isset( $input['supports'] ) && is_array($input['supports'])) {
					$new_input['supports'] = $input['supports'];
				}else {
					$new_input['supports'] = array('title', 'editor');
				}
				
				break;
		}

		return $new_input;
	}

}

if ( is_admin() ) {

	global $ISWBlockSettingsPage;
	$ISWBlockSettingsPage = new ISWBlockSettingsPage();

}

endif;