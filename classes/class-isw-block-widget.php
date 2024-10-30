<?php
/**
 * ISW Block Widget class.
 *
 */

if ( class_exists('WP_Widget') && !class_exists('ISWBlockWidget') ) :

class ISWBlockWidget extends WP_Widget{
	
	function __construct(){
		$widget_name = $this->is_icl_translation_enabled() ?  __( 'Multilingual Block', ISW_BLOCKS_TEXTDOMAIN )  :  __( 'Block', ISW_BLOCKS_TEXTDOMAIN );
		$widget_options = array('classname' => 'isw_block_widget', 'description' => __( 'Displays a Block', ISW_BLOCKS_TEXTDOMAIN) );
		$control_options= array();
		parent::__construct('isw_block_widget', $widget_name, $widget_options, $control_options);
	}
	
	function widget( $args, $instance ) {
		global $ISWBlocks;
		if ( isset($ISWBlocks) ) {
			extract($args);

			$render_widget = false;
			if ($this->is_icl_translation_enabled()) {
				$block_language = $instance['block_language'] == 'multilingual' ? ICL_LANGUAGE_CODE : $instance['block_language'];
				$block_id = icl_object_id((int)$instance['block_id'] , 'block', false, $block_language);
				if ( $block_id != 0 && $block_language == ICL_LANGUAGE_CODE )
					$render_widget = true;
			} else {
				$block_id = (int)$instance['block_id'];
				if ( $block_id != 0 ) 
					$render_widget = true;
			}

			if ( $render_widget ) :
	
				// render widget
				echo $before_widget;

				if ( $instance['show_title'] ) {

					$title = apply_filters('widget_title', $instance['title'], $instance, $this->id_base);
					if ( $title )
						echo $before_title . $title . $after_title;
				
				}
				
				if ( $instance['show_content'] ) {
					$content =  apply_filters('widget_content', $ISWBlocks->render_block( $block_id), $instance, $this->id_base);
					if ( $content )
						echo $content;
				}

				if ( $instance['show_edit_link'] ) {
					$post_type_object = get_post_type_object( 'block' );
					if ( current_user_can( $post_type_object->cap->edit_post , $block_id ) ) {
						$edit_link =  apply_filters('widget_edit_link', edit_post_link( null, '<div class="edit-link">', '</div>', $block_id ), $instance, $this->id_base);
						if ( $edit_link )
							echo $edit_link;
					}
				}
				
				echo $after_widget;

			endif;
		}
	}
	
	function form( $instance ) {
		global $ISWBlocks;
		if ( isset($ISWBlocks) ) {
		  
			$instance = wp_parse_args( (array) $instance, 
				array( 
					'title' 		 => '',
					'block_id'  	 => 0,
					'block_language' => 'multilingual',
					'show_title' 	 => 1,
					'show_content' 	 => 1,
					'show_edit_link' => 1,
				) 
			);
					
			if ($this->is_icl_translation_enabled()) {
				$block_language = $instance['block_language'] == 'multilingual' ? ICL_LANGUAGE_CODE : $instance['block_language'];
				$block_id = icl_object_id((int)$instance['block_id'] , 'block', true, $block_language);
				$blocks = $ISWBlocks->get_blocks_by_language( $block_language );
			} else {
				$block_id = (int)$instance['block_id'];
				$blocks = $ISWBlocks->get_blocks( false );  
			}

			?>
			<p>
				<label for="<?php echo $this->get_field_id('block_id'); ?>"><?php _e('Select a Block:' , ISW_BLOCKS_TEXTDOMAIN); ?></label>
				<select class="widefat" id="<?php echo $this->get_field_id('block_id'); ?>" name="<?php echo $this->get_field_name('block_id'); ?>">
				<?php if ( $blocks ): ?>
					<option value=\"0\"><?php _e('(no block selected)', ISW_BLOCKS_TEXTDOMAIN) ?></option>
					<?php foreach($blocks as $block): ?>
						<option value="<?php echo $block->ID ?>"<?php if( $block_id ==  $block->ID ): ?> selected="selected"<?php endif;?>><?php echo esc_html($block->post_title) ?></option>
					<?php endforeach;?> 
				<?php else: ?>
					<option value=\"0\"><?php _e('(no blocks defined)', ISW_BLOCKS_TEXTDOMAIN) ?></option>
				<?php endif; ?> 
				</select>
			</p>
			<?php if ( $this->is_icl_translation_enabled() ):?>
			<p>
				<?php icl_widget_text_language_selectbox( $instance['block_language'], $this->get_field_name('block_language') . '" id="' . $this->get_field_id('block_language') . '" onchange="' . $this->icl_language_selectbox_script()); ?>
				<span id="<?php echo $this->get_field_id('language_spinner')?>" class="widget-language-spinner" ></span>
			</p>
			<?php endif; ?>
			<p>
				<a href="javascript: var obj = jQuery('#<?php echo $this->get_field_id('advanced'); ?>'); if(!obj.is(':visible')) {var a = obj.show('slow');} else {var a = obj.hide('slow');}"><?php _e( 'advanced options', ISW_BLOCKS_TEXTDOMAIN ); ?></a>
			</p>
			<p id="<?php echo $this->get_field_id('advanced')?>" style="display: none;">
				<input type="checkbox" name="<?php echo $this->get_field_name('show_title'); ?>" id="<?php echo $this->get_field_id('show_title'); ?>" value="1"<?php if( $instance['show_title'] ): ?> checked="checked"<?php endif;?>>
				<label for="<?php echo $this->get_field_id('show_title'); ?>"><?php _e('Display block title', ISW_BLOCKS_TEXTDOMAIN); ?></label>
				<br/>
				<input type="checkbox" name="<?php echo $this->get_field_name('show_content'); ?>" id="<?php echo $this->get_field_id('show_content'); ?>" value="1"<?php if( $instance['show_content'] ): ?> checked="checked"<?php endif;?>>
				<label for="<?php echo $this->get_field_id('show_content'); ?>"><?php _e('Display block content', ISW_BLOCKS_TEXTDOMAIN); ?></label>
				<br/>
				<input type="checkbox" name="<?php echo $this->get_field_name('show_edit_link'); ?>" id="<?php echo $this->get_field_id('show_edit_link'); ?>" value="1"<?php if( $instance['show_edit_link'] ): ?> checked="checked"<?php endif;?>>
				<label for="<?php echo $this->get_field_id('show_edit_link'); ?>"><?php _e('Display block edit link', ISW_BLOCKS_TEXTDOMAIN); ?></label>
			</p>
			<?php
		}
		//$enabled = $this->is_icl_translation_enabled();
	}
	
	function icl_language_selectbox_script () {

		$script  = "javascript: ";
		$script .= "var element_block_language = jQuery(this);";
		$script .= "var parentform = jQuery(this).parent().parent().parent();";
		$script .= "var element_block_id = jQuery('#" . $this->get_field_id('block_id') ."');";
		$script .= "var element_spinner = jQuery('#" . $this->get_field_id('language_spinner') ."');";

		$script .= "element_block_language.attr('disabled','disabled');";
		$script .= "element_block_id.attr('disabled','disabled');";
		// $script .= "jQuery('.widget-control-actions .spinner', parentform ).show();";
		$script .= "element_spinner.css('display','inline-block');";
		$script .= "jQuery('a.widget-control-remove', parentform ).click(function(e){e.preventDefault();return false;});";
		$script .= "jQuery('a.widget-control-close', parentform ).click(function(e){e.preventDefault();return false;});";
		$script .= "jQuery('input[type=submit]', parentform ).attr('disabled','disabled');";

		$script .= "var data = {";
		$script .= "action: 'isw_block_widget_get_block_options',";
		$script .= "block_language: element_block_language.find(':selected').val(),";
		$script .= "block_id: element_block_id.find(':selected').val()";
		$script .= "};";

		$script .= "jQuery.post(ajaxurl, data, function(response) {";

		$script .= "jQuery('#" . $this->get_field_id('block_id') ."').html(response);";
		$script .= "element_block_language.removeAttr('disabled');";
		$script .= "element_block_id.removeAttr( 'disabled' );";
		// $script .= "jQuery('.widget-control-actions .spinner', parentform ).hide();";
		$script .= "jQuery('a.widget-control-remove', parentform ).unbind('click');";
		$script .= "jQuery('a.widget-control-close', parentform ).unbind('click');";
		$script .= "jQuery('input[type=submit]', parentform ).removeAttr( 'disabled' );";
		
		$script .= "element_spinner.hide();";

		$script .= "})";

		return $script;
	}
	
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['block_id']  		= array_key_exists('block_id', $new_instance  )? $new_instance['block_id'] : '0';
		$instance['block_language'] = array_key_exists('block_language', $new_instance  )? $new_instance['block_language'] : 'multilingual';
		$instance['show_title'] 	= array_key_exists('show_title', $new_instance  )? $new_instance['show_title'] : '0';
		$instance['show_content'] 	= array_key_exists('show_content', $new_instance  )? $new_instance['show_content'] : '0';
		$instance['show_edit_link'] = array_key_exists('show_edit_link', $new_instance  )? $new_instance['show_edit_link'] : '0';

		if ($instance['block_id'] == 0 ) {
			$instance['title'] = '';
		} else {
			$block = get_post($instance['block_id']);
			$instance['title'] = $block->post_title;
		}

		return $instance;
	}

	private function is_icl_enabled (){
		return defined('ICL_SITEPRESS_VERSION');
	}

	private function is_icl_translation_enabled (){
		
		if ( defined('ICL_SITEPRESS_VERSION') ){
			global $sitepress;
			$settings = $sitepress->get_settings();
			return $settings['posts_slug_translation']['on'] && $settings['custom_posts_sync_option']['block'];
		}
		return false;
	}
	
}

function isw_block_widget_get_block_options_callback(){
	global $ISWBlocks;
	$block_language = $_POST['block_language'] == 'multilingual' ? ICL_LANGUAGE_CODE : $_POST['block_language'];
	$blocks = $ISWBlocks->get_blocks_by_language( $block_language );
	$block_id = icl_object_id ( $_POST['block_id'], 'block', false, $block_language );
	?>
	<?php if ( $blocks ): ?>
		<option value=\"0\"><?php _e('(no block selected)', ISW_BLOCKS_TEXTDOMAIN) ?></option>
		<?php foreach($blocks as $block): ?>
			<option value="<?php echo $block->ID ?>"<?php if( $block_id ==  $block->ID ): ?> selected="selected"<?php endif;?>><?php echo esc_html($block->post_title) ?></option>
		<?php endforeach;?> 
	<?php else: ?>
		<option value=\"0\"><?php _e('(no blocks defined)', ISW_BLOCKS_TEXTDOMAIN) ?></option>
	<?php endif; ?> 
	<?php
	die();
}
add_action ('wp_ajax_isw_block_widget_get_block_options', 'isw_block_widget_get_block_options_callback');

endif;