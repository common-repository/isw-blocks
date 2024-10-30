<?php

if ( !class_exists('ISWBlocks') ) :

/**
 * Base class which others extend on. Contains functions shared across all objects.
 *
 */
class ISWBlocks {
 
    // Singleton
    private static $instance = null;

    // variables
    protected $type_registered = false;
    protected $wpautop_removed = false;
    protected $options = null;
    protected $features = null;

    protected $defaults = array(
        'options' => array(
            'hierarchical' => false,
            'supports' => array( 'title', 'editor' ),
        ),
        'features'=> array(
            'output_mode' => true,
            'sort_order' => true,
            'widget'    => true,
        )
    );

    private function __construct($ext = false)
    {
        // Create Custom Post Types
        add_action( 'init',  array( &$this, 'init' ) );
        add_action( 'widgets_init', array( &$this, 'register_widgets' ) );
    
        // get network user settings (only if multisite AND plugin is network activated)
        if ( is_multisite() && is_network_admin() && $plugins = get_site_option( 'active_sitewide_plugins' ) && isset( $plugins[ ISW_BLOCKS_PLUGIN_FILE ] ) ){
                $this->features = get_site_option( 'isw_block_features' ,  $this->defaults['features'] );
                $this->options = get_site_option( 'isw_block_options' ,  $this->defaults['options'] );

        } else { 
                $this->features = get_option( 'isw_block_features' ,  $this->defaults['features'] );
                $this->options = get_option( 'isw_block_options' , $this->defaults['options'] ); 
        }
    }

    function __destruct()
    {
        return;
    }
    
    public static function get_instance()
    {
        if ( self::$instance === null )
            self::$instance = new ISWBlocks();
        return self::$instance;
    }

    public function init() 
    {
        if ( $this->type_registered == false ) {

            if ( is_admin() ) :

                // Extend block saving process
                add_action( 'save_post', array( &$this, 'on_save_post'), 1, 2 );
    
                // Register block metaboxes
                add_action( 'admin_init', array( &$this,'register_metaboxes') );
                
                    // Register block columns
                add_filter( 'manage_edit-block_columns', array( &$this, 'edit_columns' ));

                // Populate block columns
                add_action( 'manage_posts_custom_column', array( &$this, 'populate_columns' ));

                // Enqueue required scripts
                add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_scripts'), 10, 1 );
            
            endif; 
            
            $this->register_post_types();
            $this->register_taxonomies();

            if ($this->features['output_mode']){
                add_filter('the_content', array( &$this, 'filter_block_content'), 1, 1);
                if ( !defined('WPV_VERSION')) {
                    add_filter('the_content', array( &$this, 'restore_wpautop'), 998, 1);
                }
            }

            add_shortcode( 'isw-block', array( &$this, 'render_block_shortcode' ));

            $this->type_registered = true;
        }
    }

    public function register_widgets()
    {
        if ($this->features['widget']){
            register_widget( 'ISWBlockWidget' );
        }
    }

    public function enqueue_scripts ( $hook )
    {
        global $post;
        if ( is_admin() && isset($hook) && isset($post) && ($hook == 'post-new.php' || $hook == 'post.php' )) {
            if ( 'block' === $post->post_type ) {     
                wp_enqueue_script( 'isw-blocks', ISW_BLOCKS_URL . '/js/isw-blocks.js', array( 'jquery' ), ISW_BLOCKS_VERSION );
            }
        }
    }

    /**
     * Register Seminar Post Type
     * @return [type] [description]
     */
    protected function register_post_types()
    {
        // Register the seminar post type
        $labels = array(
            'name'                  => __( 'Blocks', ISW_BLOCKS_TEXTDOMAIN ),
            'singular_name'         => __( 'Block', ISW_BLOCKS_TEXTDOMAIN ),
            'menu_name'             => _x( 'Blocks', 'Admin menu name', ISW_BLOCKS_TEXTDOMAIN ),
            'add_new'               => __( 'Add New', ISW_BLOCKS_TEXTDOMAIN ),
            'add_new_item'          => __( 'Add New Block', ISW_BLOCKS_TEXTDOMAIN ),
            'edit'                  => __( 'Edit', ISW_BLOCKS_TEXTDOMAIN ),
            'edit_item'             => __( 'Edit Block', ISW_BLOCKS_TEXTDOMAIN ),
            'new_item'              => __( 'New Block', ISW_BLOCKS_TEXTDOMAIN ),
            'view'                  => __( 'View', ISW_BLOCKS_TEXTDOMAIN ),
            'view_item'             => __( 'View Block', ISW_BLOCKS_TEXTDOMAIN ),
            'search_items'          => __( 'Search Blocks', ISW_BLOCKS_TEXTDOMAIN ),
            'not_found'             => __( 'No blocks found', ISW_BLOCKS_TEXTDOMAIN ),
            'not_found_in_trash'    => __( 'No blocks found in trash', ISW_BLOCKS_TEXTDOMAIN ),
            'parent'                => __( 'Parent Block', ISW_BLOCKS_TEXTDOMAIN )
        );

        $args = array(
            'labels'                => $labels,
            'description'           => __( 'A block of information to be displayed on a page or a post', ISW_BLOCKS_TEXTDOMAIN ),
            'public'                => true,
            'exclude_from_search'   => true,
            'publicly_queryable'    => false,
            'show_ui'               => true,
            'show_in_nav_menus'     => false,
            'hierarchical'          => $this->options['hierarchical'],
            'supports'              => $this->options['supports'],
            'capability_type'       => 'post',
            'rewrite'               => array("slug" => "block"), // Permalinks format
            'menu_position'         => 20,
            'menu_icon'             => '',  // Icon Path (overriden in css)
            'has_archive'           => false,
            'taxonomies'            => array(),
            'rewrite'               => array( 'slug' => 'blocks', 'with_front' => false ),
            'query_var'             => false
        );

        register_post_type( 'block', $args );

    }

    protected function register_taxonomies()
    {
         $labels = array(
            'name'                          => __( 'Block Regions', ISW_BLOCKS_TEXTDOMAIN  ),
            'singular_name'                 => __( 'Block Region', ISW_BLOCKS_TEXTDOMAIN  ),
            'menu_name'                     => _x( 'Regions', 'Admin menu name', ISW_BLOCKS_TEXTDOMAIN ),
            'search_items'                  => __( 'Search Block Regions', ISW_BLOCKS_TEXTDOMAIN  ),
            'popular_items'                 => __( 'Popular Block Regions', ISW_BLOCKS_TEXTDOMAIN  ),
            'all_items'                     => __( 'All Block Regions', ISW_BLOCKS_TEXTDOMAIN  ),
            'parent_item'                   => __( 'Parent Block Region', ISW_BLOCKS_TEXTDOMAIN  ),
            'parent_item_colon'             => __( 'Parent Block Region', ISW_BLOCKS_TEXTDOMAIN  ),
            'edit_item'                     => __( 'Edit Block Region', ISW_BLOCKS_TEXTDOMAIN  ),
            'update_item'                   => __( 'Update Block Region', ISW_BLOCKS_TEXTDOMAIN  ),
            'add_new_item'                  => __( 'Add New Block Region', ISW_BLOCKS_TEXTDOMAIN  ),
            'new_item_name'                 => __( 'New Block Region Name', ISW_BLOCKS_TEXTDOMAIN  ),
            'separate_items_with_commas'    => __( 'Separate block regions with commas', ISW_BLOCKS_TEXTDOMAIN  ),
            'add_or_remove_items'           => __( 'Add or remove block regions', ISW_BLOCKS_TEXTDOMAIN  ),
            'choose_from_most_used'         => __( 'Choose from the most used block regions', ISW_BLOCKS_TEXTDOMAIN  ),
          );

         $object_types = array(
                'block',
            );

         $args = array(
            'hierarchical' => true,
            'labels' => $labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_tag_cloud' => false,
            'show_in_nav_menus' => false,
            'update_count_callback' => '_update_post_term_count',
            'query_var' => true,
            'rewrite' => array( 'slug' => 'block-region' ),
          );

          register_taxonomy( 'block_region', $object_types , $args );
    }

    public function register_metaboxes()
    {
        if ($this->features['output_mode'] || $this->features['sort_order']) {
            add_meta_box( 'block-settings', __('Block Settings', ISW_BLOCKS_TEXTDOMAIN), array( &$this ,'render_block_settings_meta_box' ), 'block', 'side', 'high' );
        }
    }


    public function render_block_settings_meta_box ( $post )
    {

        if (!isset($post))
            return;

        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
            return $post->ID;

        // Use nonce for verification
        wp_nonce_field( plugin_basename( __FILE__ ), '_isw_blocks_nonce' );

        // Default output mode to true
        $output_mode = get_post_meta( $post->ID, '_isw_block_output_mode', true );
        if ( !$output_mode ) {
            $output_mode = 'default_mode';
        }
        
        $sort_order = get_post_meta( $post->ID, '_isw_block_sort_order', true);;
        if (!$sort_order)
             $sort_order = 10;
        ?>
        <?php if ($this->features['output_mode']): ?>
        <p>
            <label for="_isw_block_output_mode[]"><?php _e( 'Output Mode', ISW_BLOCKS_TEXTDOMAIN ); ?>:</label>
            <select name="_isw_block_output_mode[]" class="isw_block_output_mode">
                <option value="default_mode"<?php echo $output_mode == 'default_mode' ? ' selected="selected"':''; ?>>
                    <?php _e( 'Auto-insert Paragraphs', ISW_BLOCKS_TEXTDOMAIN ); ?>
                </option>
                <option value="raw_mode"<?php echo $output_mode == 'raw_mode' ? ' selected="selected"' : ''; ?>>
                    <?php _e( 'Manual Paragraphs', ISW_BLOCKS_TEXTDOMAIN ); ?>
                </option>
            </select>
            <?php
                $tooltips = array();
                $tooltips[] = sprintf('<strong>%s</strong><br/><span style="font-size:9px">(%s)</span><br/>%s' ,
                                __('Auto-insert Paragraphs', ISW_BLOCKS_TEXTDOMAIN) ,
                                __('Normal WordPress Output' , ISW_BLOCKS_TEXTDOMAIN) ,
                                __('add paragraphs an breaks and resolve shortcodes.', ISW_BLOCKS_TEXTDOMAIN )
                              );
                $tooltips[] = sprintf('<strong>%s</strong><br/><span style="font-size:9px">(%s)</span><br/>%s' ,
                                __('Manual Paragraphs', ISW_BLOCKS_TEXTDOMAIN) ,
                                __('Raw Output' , ISW_BLOCKS_TEXTDOMAIN) ,
                                __('only resolve shortcodes without adding line breaks or paragraphs.', ISW_BLOCKS_TEXTDOMAIN )
                              );
            ?>
            <span class="icon-more-info" title="<?php _e( 'Output Mode', ISW_BLOCKS_TEXTDOMAIN ); ?>" data-isw-tooltip='<?php echo json_encode($tooltips);?>'></span>
        </p>
        <?php endif; ?>
        <?php if ($this->features['sort_order']): ?>
        <p>
            <label for="_isw_block_sort_order"><?php _e( 'Sort Order', ISW_BLOCKS_TEXTDOMAIN ); ?>:</label>
            <input type="number" name="_isw_block_sort_order" min="10" max="99" value="<?php echo $sort_order ?>" />
            <?php
                $tooltips = array();
                $tooltips[] = __('An arithmetic value between 10 and 99 that can be used in queries for sorting blocks in ascending or descending order.', ISW_BLOCKS_TEXTDOMAIN );
                              
            ?>
            <span class="icon-more-info" title="<?php _e( 'Sort Order', ISW_BLOCKS_TEXTDOMAIN ); ?>" data-isw-tooltip='<?php echo json_encode($tooltips);?>'></span>
        </p>
        <?php endif; ?>
        <?php
    }
    
    public function on_save_post( $post_id, $post )
    {
        if ( !isset( $post ) )
            return;

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return;

        if ( !isset( $_POST['_isw_blocks_nonce'] ) )
            return;

        if ( !wp_verify_nonce( $_POST['_isw_blocks_nonce'], plugin_basename( __FILE__ ) ) )
            return;

        // Is the user allowed to edit the post or page?
        if ( !current_user_can( 'edit_post', $post->ID ) )
            return;

        // OK, we're authenticated: we need to find and save the data
        // We'll put it into an array to make it easier to loop though

        if ($post->post_type == 'block') {

            $inputs = array();
    
            if ($this->features['output_mode'])
                $inputs[] = array( 'key' => '_isw_block_output_mode', 'type' => 'select' );
            
            if ($this->features['sort_order'])
                $inputs[] = array( 'key' => '_isw_block_sort_order' , 'type' => 'single' );
        
            foreach ( $inputs as $input ) {

                $key = $input['key'];
                $type = $input['type'];
                $value = '';

                switch ($type) {
                    case 'select':
                        if (isset($_POST[$key][0]))     
                            $value = $_POST[$key][0] ;
                        break;
                    
                    case 'single':
                        if (isset($_POST[$key]))    
                            $value = $_POST[$key] ;
                        break;
                }

                if (!empty($value)) {
                    if ( get_post_meta( $post->ID, $key, false ) )
                        update_post_meta( $post->ID, $key, $value);
                    else
                        add_post_meta( $post->ID, $key, $value);

                }
            }
        }
    }


    public function edit_columns( $columns )
    {
        if ($this->features['sort_order']) {
            
            $columns['block-sort-order'] = __( 'Block Sort Order', ISW_BLOCKS_TEXTDOMAIN );
        
            // Reorder date column
            $date = $columns['date'];
            unset( $columns['date'] );
            $columns['date'] = $date;

        }
        return $columns;
    }


    public function populate_columns( $column )
    {

        switch ($column) {

            case 'block-sort-order':
                $block_sort_order = esc_html( get_post_meta( get_the_ID(), '_isw_block_sort_order', true ) );
                echo $block_sort_order;
                break;

            case 'block-test':
                break;
        }

    }

    protected function get_block_id( $atts )
    {
        global $wpdb;
        extract(
            shortcode_atts( array(
                'id'    => false,
                'name'  => false
            ), $atts )
        );

        if(empty($id) && !empty($name)){
            // lookup by post name first
            $id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_type='block' AND post_name=%s", $name));
            if (!$id) {
                // try the post title
                $id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_type='block' AND post_title=%s", $name));
            }
        }
        return $id;
    }

    public function render_block_shortcode ( $atts )
    {
        $out = '';
        $atts['id'] = $this->get_block_id($atts);

        // Switch block id based on currently selected language
        $atts = $this->switch_block_id_from_icl($atts);

        if ( get_post_type( $atts['id'] ) === 'block' ) {

            if ( function_exists( 'wpv_shortcode_wpv_post_body' ) ) {

                // Use WP Vies to render the block enabling view templates if defined
                $out = wpv_shortcode_wpv_post_body($atts);

            }
            else {
                // Use default block
                $out = $this->render_block($atts['id']);
            }
        }
        return $out;
    }

    public function render_block ( $id = 0 ) 
    {
        $out = '';
        if ( is_numeric($id) && $id >= 0 ) {

            global $post;

            // Cache Original Post before switching
            $post_original = isset($post) ? clone $post : null;

            if (( $post = get_post( $id )) && ($post->post_type == 'block')) {

                // Remove the wpautop filter if configured
                if ($this->features['output_mode']) {
                    $wpautop_was_removed = false;
                    if ( get_post_meta( $id, '_isw_block_output_mode', true ) == 'raw_mode' ){
                        $this->remove_wpautop();
                        $wpautop_was_removed = true;
                    }
                }
                
                // Remove the icl language switcher to stop WPML from add the
                // "This post is avaiable in XXXX" twice.
                global $icl_language_switcher;
                $icl_filter_removed = false;
                if(isset($icl_language_switcher)) {
                    $icl_filter_removed = remove_filter('the_content', array($icl_language_switcher, 'post_availability'));
                }

                $out = apply_filters('the_content', $post->post_content);

                // Restore the wpautop filter
                if ( $this->features['output_mode']){
                    if ( $wpautop_was_removed ) {
                        $this->restore_wpautop('');
                    }
                }
                
                // Restore the icl language switcher
                if ( $icl_filter_removed ) {
                    add_filter('the_content', array($icl_language_switcher, 'post_availability'));
                }

            }

            // Restore Original Post
            $post = (isset($post_original) ? clone $post_original : null);
        }
        return $out;
    }

    /**
     * Get all the blocks that have been created.
     *
     */
    public function get_blocks( $icl = true )
    {
        $blocks = get_posts(array(
            'post_type'         => 'block',
            'post_status'       => 'publish',
            'numberposts'       => -1,
            'suppress_filters'  => (int) !$icl
        ));
        return $blocks;
    }

    public function get_blocks_by_language ( $language_code ) 
    {
        global $wpdb;
        $query = $wpdb->prepare('SELECT p.ID, p.post_title, p.post_content FROM ' . $wpdb->posts . ' p INNER JOIN ' . $wpdb->prefix . 'icl_translations t ON t.element_id = p.ID WHERE p.post_type = %s AND t.element_type = %s AND t.language_code = %s AND p.post_status = %s ORDER BY p.post_title ASC', 'block', 'post_block', $language_code, 'publish');
        return $wpdb->get_results($query);
    }

    protected function switch_block_id_from_icl( $atts )
    {
        if ( function_exists( 'icl_object_id' ) ) {

            extract(shortcode_atts(array(
                'id'                 => '0',
                'icl'    => 'false'
                ),
                $atts));

            if ($icl == 'true' && $id != '0')
                $atts['id'] = icl_object_id($id, 'block', true);
        }
        return $atts;
    }

    public function filter_block_content( $content )
    {
        global $post;
        if ( in_the_loop() && isset($post) && 'block' == $post->post_type ) {
            

            $wpautop_was_removed = $this->is_wpautop_removed();
            if ($wpautop_was_removed) {
                $this->restore_wpautop('');
            }

            $block_output_mode = get_post_meta( $post->ID, '_isw_block_output_mode', true );
            if ( !empty( $block_output_mode ) && $block_output_mode ==="raw_mode" ) {
                $this->remove_wpautop();
            }

        }
        return $content;
    }

    public function restore_wpautop( $content ) 
    {
        if ( defined('WPV_VERSION') ) {
            global $WPV_templates;
            $content = $WPV_templates->restore_wpautop( $content );
        
        } else {
         
            if ( $this->wpautop_removed ) {
                    
                add_filter('the_content', 'wpautop');
                add_filter('the_content', 'shortcode_unautop');
                add_filter('the_excerpt', 'wpautop');
                add_filter('the_excerpt', 'shortcode_unautop');

                $this->wpautop_removed = false;
            }
        }
        return $content;
    }

    protected function is_wpautop_removed()
    {
        if ( defined('WPV_VERSION') ){
            global $WPV_templates;
            return $WPV_templates->is_wpautop_removed();
        } else {
            return $this->wpautop_removed;
        }
    }

    protected function remove_wpautop()
    {
        if ( defined('WPV_VERSION') ) {
            global $WPV_templates;
            $WPV_templates->remove_wpautop();
        } else {
            if ( !$this->wpautop_removed ) {
                remove_filter('the_content', 'wpautop');
                remove_filter('the_content', 'shortcode_unautop');
                remove_filter('the_excerpt', 'wpautop');
                remove_filter('the_excerpt', 'shortcode_unautop');
                $this->wpautop_removed = true;
            }
        }
    }
}


global $ISWBlocks;
$ISWBlocks = ISWBlocks::get_instance();;

endif;