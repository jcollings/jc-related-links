<?php
/**
 * @package Related Links
 * @author James Collings <james@jclabs.co.uk>
 * @version 1.0
 */
class RelatedLinksMeta
{
	private $_core;
	function __construct(RelatedLinksCore $core)
	{
		$this->_core = $core;

		// add metabox
		add_action( 'load-post.php', array($this, 'setup_metabox' ));
		add_action( 'load-post-new.php', array($this, 'setup_metabox' ));

		// save meta box
		add_action( 'save_post', array($this, 'save_metabox'), 10, 2 );

		// add admin js/css
		add_action( 'admin_print_scripts-post.php', array($this->_core, 'admin_scripts' ));
		add_action( 'admin_print_scripts-post-new.php', array($this->_core, 'admin_scripts'));

		// add admin javascripts
		add_action( 'admin_print_styles-post.php', array($this->_core, 'admin_styles' ));
		add_action( 'admin_print_styles-post-new.php', array($this->_core, 'admin_styles'));
	}

	/**
	 * Add hook to add a metabox
	 * @return void 
	 */
	function setup_metabox()
	{
		add_action( 'add_meta_boxes', array($this, 'add_metabox' ));
	}

	/**
	 * Register metabox for adding related links admin pages
	 * @return void 
	 */
	function add_metabox()
	{
		$this->current_post_type = get_post_type();
		$options = $this->_core->get_options();
		if(isset($options[$this->current_post_type.'_visible']) && $options[$this->current_post_type.'_visible'] == 1)
		{
			//add_meta_box($id, $title, $callback, $screen = null, $context = 'advanced', $priority = 'default', $callback_args = null)
			add_meta_box($this->_core->meta_id, esc_html__( 'Related Links'), array($this, 'show_metabox'), '', 'side', 'default');
		}
	}

	/**
	 * Render admin meta box
	 * @return void
	 */
	function show_metabox($object, $box)
	{
		wp_nonce_field( basename( __FILE__ ), $this->_core->meta_key.'_nonce' );
		$values = get_post_meta( $object->ID, $this->_core->meta_key, true );
		$args = array();	// add extra options

		$current_post_type = get_post_type();
		$options = $this->_core->get_options();

		$allowed_post_types = array();
		if(is_array($options[$current_post_type.'_type']) && !empty($options[$current_post_type.'_type']))
		{
			foreach($options[$current_post_type.'_type'] as $pt => $value)
			{
				if($value == 1)
				{
					$allowed_post_types[] = $pt;
				}
			}
		}
		$parent_page = '';

		if($options[$current_post_type.'_type']['page'] == 1)
			$parent_page = $options[$current_post_type.'_page'];	

		if($parent_page == 0)
			$parent_page = '';
		
		if(!empty($allowed_post_types))
		{
			if(count($allowed_post_types) >= 2)
			{
				if($parent_page >= 1)
				{
					unset($allowed_post_types[array_search('page', $allowed_post_types)]);
					$pages = get_posts(array('numberposts' => -1, 'post_type' => $allowed_post_types));
					$pages = array_merge(get_posts(array('numberposts' => -1, 'post_type' => 'page', 'post_parent' => $parent_page)), $pages);
				}else
				{
					$pages = get_posts(array('numberposts' => -1, 'post_type' => $allowed_post_types));
				}
			}else{
				$pages = get_posts(array('numberposts' => -1, 'post_type' => $allowed_post_types, 'post_parent' => $parent_page));
			}
		}else{
			$pages = array();
		}
		?>
			<p>
				<label for="<?php echo $this->_core->meta_id; ?>"><?php _e( "Select a related page from the list below:"); ?></label>
				<ul id="active-related-services"></ul>
				<select class="widefat" multiple="true" name="<?php echo $this->_core->meta_id; ?>[]" id="<?php echo $this->_core->meta_id; ?>">
					<?php foreach($pages as $page): ?>
						<option value="<?php echo $page->ID; ?>" <?php if(isset($values) && is_array($values) && in_array($page->ID, $values)): ?>selected="selected"<?php endif; ?> ><?php echo $page->post_title; ?> | <?php echo $page->post_type; ?></option>
					<?php endforeach; ?>
				</select>
			</p>
		</div>
		<hr id="splitter"/>
		<div class="inside border">
			<?php if(empty($pages)): ?>
			<p><a href="options-general.php?page=<?php echo $this->_core->settings_page_id; ?>">Edit whats displayed</a></p>
			<?php endif; ?>
			<ul id="inactive-related-services"></ul>
		<?php 
	}

	/**
	 * Save metabox on post_types save
	 * @param  int $post_id
	 * @param  object $post    current post_type
	 * @return void
	 */
	function save_metabox($post_id, $post)
	{
		if ( !isset( $_POST[$this->_core->meta_key.'_nonce'] ) || !wp_verify_nonce( $_POST[$this->_core->meta_key.'_nonce'], basename( __FILE__ ) ) )
			return $post_id;
		// Get the post type object. 
		
		$post_type = get_post_type_object( $post->post_type );
		// Check if the current user has permission to edit the post. 
		if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
			return $post_id;

		// Get the posted data and sanitize it for use as an HTML class. 
		$new_meta_value = ( isset( $_POST[$this->_core->meta_id] ) ? sanitize_html_class( $_POST[$this->_core->meta_id] ) : '' );

		// Get the meta key. 
		$meta_key = $this->_core->meta_key;

		// Get the meta value of the custom field key. 
		$meta_value = get_post_meta( $post_id, $meta_key, true );

		// If a new meta value was added and there was no previous value, add it. 
		if ( $new_meta_value && '' == $meta_value )
			add_post_meta( $post_id, $meta_key, $new_meta_value, true );

		// If the new meta value does not match the old value, update it. 
		elseif ( $new_meta_value && $new_meta_value != $meta_value )
			update_post_meta( $post_id, $meta_key, $new_meta_value );

		// If there is no new meta value but an old value exists, delete it. 
		elseif ( '' == $new_meta_value && $meta_value )
			delete_post_meta( $post_id, $meta_key, $meta_value );

	}
}
?>