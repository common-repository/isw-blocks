<?php
/**
 *  Settings base class.
 *
 */

if ( !class_exists('ISWOptionsPage') ) :

class ISWOptionsPage {

	/**
	 * Holds the values to be used in the fields callbacks
	 */
	protected $prefix = null;
	protected $pages = array();
	protected $options = array();
	private $sanitize_options = array();

	/**
	 * Start up
	 */
	public function __construct( $prefix = null )
	{
		$this->prefix = $prefix;
		add_action( 'admin_menu', array( $this, '_register_menus' ) );
		add_action( 'admin_init', array( $this, '_init_pages' ) );
	}

	/**
	 * Add options page
	 */
	public function _register_menus()
	{
		if ( is_admin() ):
			$this->on_register_menus();
		endif;
	}

	public function _init_pages(){
		if ( is_admin()) :

			$this->on_register_pages();

			foreach ( $this->pages as $page_key => $page ) {
			
				$page_id = sprintf( "%s_%s", $this->prefix , $page_key );

				foreach ( $page->sections as $section_key => $section ) {
				 
					$section_id = sprintf("%s_%s", $this->prefix , $section_key);

					register_setting( $page_id, $section_id,  array( &$this, '_sanitize_field_input' ) );
					
					// Keep track of section order for sanitization
					$this->sanitize_options[] = $section_key;

					add_settings_section(
						$section_id,			 					// Section ID
						$section->label, 							// Section Title
						array ( &$this, '_render_section'), 		// Section Render Callback
						$page_id 									// Group ID
					);

					foreach ($section->fields as $field_id => $field ) {
						
						$callback_args = array(
							'id' => $field_id,
							'type' => $field->type,
							'label' => $field->label,
							'description' => $field->description,
							'default' => $field->default,
							'data' => isset($field->data) ? $field->data : array(),
							'page_id' => $page_id,
							'section_id' => $section_id,
							'page_key' => $page_key,
							'section_key' => $section_key
						);

						add_settings_field(
							$field_id, 									    // Field ID
							$field->label, 									// Field Title 
							array( &$this, '_render_field' ),				// Field Render Callback
							$page_id,	 									// Group ID
							$section_id, 									// Section ID 
							$callback_args									// Field Callback Arguments        
						);  
					}
				}
			}

		endif;
	}

	public function _render_section ( $section ) {
		if ( is_admin()) :
			$section = (object) $section;
			$this->on_render_section( $section );
		endif;
	}

	public function _render_field ( $field ) {
		if ( is_admin()) :
			$field = (object) $field;
			$this->on_render_field( $field );
		endif;
	}

	/**
	 * Sanitize each setting field as needed
	 *
	 * @param array $input Contains all settings fields as array keys
	 */
	public function _sanitize_field_input( $input )
	{
		static $index = 0;
		$option = $this->sanitize_options[$index];
		$index ++;

		return $this->on_sanitize_field_input( $option, $input );
	}

	/**** Class Events **********************************************************************/
	protected function on_register_menus() {
		die('function ISWSettings::on_register_menus() must be over-ridden in a sub-class.');
	}

	protected function on_register_pages() {
		die('function ISWSettings::on_register_pages() must be over-ridden in a sub-class.');
	}

	protected function on_sanitize_field_input( $option, $input ) {
		die('function ISWSettings::on_sanitize_field_input() must be over-ridden in a sub-class.');
	}

	protected function on_render_section( $section ) {
		$this->options = get_option( $section->id );
	}

	protected function on_render_field( $field ) {
		
		switch( $field->type ) {
			case 'onoff':

				$input_id = sprintf( "%s[%s]" ,  $field->section_id, $field->id );
				$input_name = sprintf( "%s[%s]" , $field->section_id , $field->id );

				$is_set = false;
				if (isset( $this->options[$field->id ] ) && $this->options[$field->id] == true ) {
					$is_set = true;
				}

				?>
				<div class="onoffswitch">
					<input type="checkbox" name="<?php echo $input_name; ?>" class="onoffswitch-checkbox" id="<?php echo $input_id; ?>" <?php checked( $is_set, true )?>>
					<label class="onoffswitch-label" for="<?php echo $input_id; ?>">
						<div class="onoffswitch-inner"></div>
						<div class="onoffswitch-switch"></div>
					</label>
				</div>
				<?php
				break;

			case 'radio':
			case 'checkbox':

				// If no data is available, which could happen via filter, displays message
				if ( ! isset( $field->data ) || empty( $field->data ) ) {
				 
					echo "<p><strong>There are no options available for selection.</strong></p>";
							
				} else {
					
					$index = 1;
					echo '<fieldset>';
					echo '<legend class="screen-reader-text"><span>' . $field->label .'</span></legend>';
					foreach( $field->data as $data_name => $data ) {

						$input_id = sprintf( "%s[%s][%s]" ,  $field->section_id, $field->id , $index );
						$input_type = $field->type;
						$input_name = sprintf( "%s[%s]" , $field->section_id , $field->id );
						$input_name .=  $input_type == "checkbox" ? '[]' : '';

						$input_value = '';
						switch ($data_name) {
							case 'true':
								$input_value = '1';
								break;
							
							case 'false':
								$input_value = '0';
								break;

							default:
								$input_value= $data_name;
								break;
						}

						$is_set = false;
						if ( isset( $this->options[$field->id ] ) && is_array($this->options[ $field->id ]) && in_array($input_value, $this->options[ $field->id ]) ) {
							$is_set = true;
						} elseif (isset( $this->options[$field->id ] ) && $this->options[$field->id] == $input_value ) {
							$is_set = true;
						}
 
						echo "<label title=\"$data->label\"><input type=\"$input_type\" id=\"$input_id\" name=\"$input_name\" value=\"$input_value\"" . checked( $is_set, true , false ) . "><span> $data->label</span></input></label><br/>";

						$index++;
					}
					echo '</fieldset>';

				}
				break;
		
		}

	}

}

endif;