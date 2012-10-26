<?php
/**
 * @package Related Links
 * @author James Collings <james@jclabs.co.uk>
 * @version 1.0
 */
class RelatedLinksSettings
{
	private $_core;
	function __construct(RelatedLinksCore $core)
	{
		$this->_core = $core;

		add_action('admin_print_scripts-settings_page_'.$this->_core->settings_page_id, array($this->_core, 'admin_scripts'));
		add_action('admin_print_styles-settings_page_'.$this->_core->settings_page_id, array($this->_core, 'admin_styles'));

		// add admin meny
		add_action( 'admin_menu', array($this, 'create_menu' ));

        // regiser settings
        add_action( 'admin_init', array($this, 'register_settings'));
	}

	/**
	 * Register all plugin options and settings
	 * @return void
	 */
	function register_settings()
	{
		register_setting($this->_core->options_group_id, $this->_core->options_group_id, array($this, 'save_settings'));
		$this->register_sections();
		$this->register_fields();
	}

	/**
	 * Register all user post types as section
	 * @return void 
	 */
	function register_sections()
	{
		$post_types = $this->_core->get_post_types();
	    foreach($post_types as $post_type)
	    {
	    	//add_settings_section($id, $title, $callback, $page)
	        add_settings_section( $post_type, __( ucfirst( $post_type ).' (Post Type)'), array( $this, 'section_callback' ), __FILE__ );
	    }
	}

	/**
	 * Register fields for each section
	 * @return void
	 */
	function register_fields()
	{
		$fields = $this->get_fields();

		foreach($fields as $f)
		{
			// add_settings_field($id, $title, $callback, $page, $section = 'default', $args = array)
			add_settings_field($f['id'] , $f['title'] , array( $this, 'field_callback' ) , __FILE__ , $f['section'] , $f );
		}
	}

	/**
	 * Save user input
	 * @param  array $args 
	 * @return void
	 */
	function save_settings($args)
	{	
		$valid_output = array();

		$fields = $this->get_fields();
		foreach($fields as $f)
		{
			$id = $f['id'];

			// check what input type it is
			switch($f['type'])
			{
				case 'select':
				{
					$valid_output[$id] = isset($args[$id]) ? intval($args[$id]) : 0;
					break;
				}
				case 'text':
				{
					$valid_output[$id] = isset($args[$id]) ? $args[$id] : '';
					break;
				}
				case 'checkbox':
				{
					$valid_output[$id] = isset($args[$id]) && $args[$id] == 1 ? 1 : 0;
					break;
				}
				case 'multi-checkbox':
				{
					$choices = $f['choices'];

					foreach($choices as $v)
					{
						if(!empty($args[$id.'|'.$v]))
						{
							$valid_output[$id][$v] = 1;
						}else
						{
							$valid_output[$id][$v] = 0;
						}
					}
					break;
				}
				default:
				{
					break;
				}
			}
		}
		return $valid_output;
	}

	/**
	 * Render section description text
	 * @param  array $args arguments passed from add_settings_section
	 * @return void
	 */
	function section_callback($args)
	{
		echo '<p>Edit what you would like to be displayed when adding/editing the post type ' . ucfirst($args['id']).'.</p>';
	}

	/**
	 * Render fields
	 * @param  array $args arguments pass from add_settings_field
	 * @return void
	 */
	function field_callback($args)
	{
		extract($args);

		$options = $this->_core->get_options();
		
		switch($args['type'])
		{
			case 'select':
			{
				echo '<select id="'.$id.'" name="'.$this->_core->options_group_id.'['.$id.']">';
				foreach($choices as $key => $choice)
				{
					$data = explode('|', $choice);
					$selected = $options[$id] == $data[1] ? 'selected="selected"' : '';
					echo '<option value="'.$data[1].'" '.$selected.'>'.$data[0].'</option>';
				}
				echo '</select>';
				break;
			}
			case 'multi-checkbox':
			{
				foreach($choices as $key=>$choice)
				{
					$checked = $options[$id][$key] == 1 ? 'checked="checked"' : '';
					echo "<input class='checkbox' type='checkbox' id='$id|$key' name='" . $this->_core->options_group_id . "[$id|$key]' value='1' ". $checked . " />" . '<label> '. $choice. '</label><br />';
				}
				break;
			}
			case 'checkbox':
			{
				echo "<input class='checkbox' type='checkbox' id='$id' name='" . $this->_core->options_group_id . "[$id]' value='1' ". checked( $options[$id], 1, false ) . " />";
				break;
			}
			case 'text':
			{
				echo "<input class='regular-text' type='text' id='$id' name='" . $this->_core->options_group_id . "[$id]' value='$options[$id]' />";
				break;
			}
		}
	}

	/**
	 * Get all registered fields
	 * @return array list of all registered fields and their settings
	 */
	function get_fields()
	{
		$choices = array();
	    $post_types = $this->_core->get_post_types();
	    foreach($post_types as $pt)
	    {
	        $choices[$pt] = $pt;
	    }

	    $page_list = array(
	        0 => 'All Pages'
	    );

	    $this->list_child_pages($page_list, get_pages());

	    foreach($post_types as $post_type)
	    {
	        $options[] = array(  
	            "section" => $post_type,  
	            "id"      => $post_type."_visible",  
	            "title"   => __( 'Visible' ),  
	            "desc"    => '',
	            "type"    => "checkbox",  
	            "std"    => '1',  
	        );
	        $options[] = array(  
	            "section" => $post_type,  
	            "id"      => $post_type."_title",  
	            "title"   => __( 'Title' ),  
	            "desc"    => '',
	            "type"    => "text",  
	            "std"    => 'Related Links',  
	        );
	        $options[] = array(  
	            "section" => $post_type,  
	            "id"      => $post_type.'_type',  
	            "title"   => __( 'Related Post Types' ),  
	            "desc"    => 'Select from the checkboxes above what media to display in the related links box for this page',  
	            "type"    => "multi-checkbox",  
	            "std"     => '',  
	            "choices" => $choices
	        );

	        $options[] = array(  
	            "section" => $post_type,  
	            "id"      => $post_type."_page",  
	            "title"   => __( 'Top level page' ),  
	            "desc"    => '',
	            "type"    => "select",  
	            "std"    => '',  
	            "choices" =>  $page_list 
	        );  
	    } 
	    return $options; 
	}

	/**
	 * Insert link to plugin settings on administration menu
	 * @return void
	 */
	function create_menu()
    {
        // add_options_page($page_title, $menu_title, $capability, $menu_slug, $function = '')
        add_options_page( 'Related Links', 'Related Links', 'manage_options', $this->_core->settings_page_id, array( $this, 'show_settings_page' ) );
    }

    /**
     * Add Settings link to plugin menu
     * @param  array $links an array current plugin links
     * @return array        list of plugin links
     */
    function create_settings_link( $links ) {
        $settings_link = sprintf( '<a href="%s">%s</a>', admin_url( 'options-general.php?page='.$this->_core->settings_page_id ), __( 'Settings' ) );
        array_unshift( $links, $settings_link );
        return $links;
    }

    /**
     * Display Settings Section
     * @return void
     */
    function show_settings_page() {
        if ( !current_user_can( 'manage_options' ) )
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        ?>  
        <div class="wrap">  
            <div class="icon32" id="icon-options-general"></div>  
            <h2>Related Links Settings</h2>  
            <form action="options.php" method="post">  
                <?php  
                settings_fields($this->_core->options_group_id);   
                do_settings_sections(__FILE__);  
                ?>  
                <p class="submit">  
                    <input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />  
                </p>  
            </form>  
        </div><!-- wrap -->  
        <?php
    }

    function list_child_pages(&$page_list, $pages, $page_id = 0, $depth = 0)
	{
	    $prefix = '';
	    if($depth >= 1)
	    {
	        for($x=0;$x<$depth;$x++)
	            $prefix .= '-';
	    }
	    $depth++;
	    foreach($pages as $p)
	    {
	        if($p->post_parent == $page_id)
	        {
	            $page_list[] = $prefix.' '.$p->post_title.'|'.$p->ID;
	            $this->list_child_pages($page_list, $pages, $p->ID, $depth);
	        }
	    }
	}
}
?>