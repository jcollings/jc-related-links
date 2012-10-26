<?php

/**
 * @package Related Links
 * @author James Collings <james@jclabs.co.uk>
 * @version 1.0
 */
class RelatedLinksCore
{
	var $current_post_type = '';
	var $meta_key = 'related_service_page';
	var $meta_id = 'related-services-meta';
	var $settings_page_id = 'related-links-settings';
	var $options_group_id = 'jcrl_optiongroup';

	function __construct()
	{
	}

	/**
	 * Get all save plugin options
	 * @return array
	 */
	function get_options()
	{
	    $default_options = array(
	    	'post_visible' => 1,
	    	'page_visible' => 1
	    );
	    $options =  (array)get_option($this->options_group_id);
	    $options = array_merge($default_options, $options);
	    return $options;  
	}

	/**
     * Inject Javascript
     * @return void
     */
	function admin_scripts()
	{
		wp_enqueue_script('related-services-script', plugins_url('/jc-related-links/assets/js/related-links.js'));
	}

	/**
	 * Inject Stylesheets
	 * @return void
	 */
	function admin_styles()
	{
		wp_enqueue_style( 'related-services-style', plugins_url('/jc-related-links/assets/css/related-links.css'));
	}

	/**
	 * List all active wordpress post_types
	 * @return array
	 */
	function get_post_types()
    {
        $post_types = array('post' => 'post', 'page' => 'page');
        $post_types = array_merge($post_types, get_post_types(array('_builtin' => false)));
        return $post_types;
    }

    function get_related_posts($post_id = null, $post_type = array('post'))
	{
		if(!$post_id)
			return array();

		$page_ids = get_post_meta( $post_id, $this->meta_key, true );

		if(empty($page_ids))
			return array();
		
		$args= array(
			'post__in'=> $page_ids
		);
		$query = new WP_Query($args);
		return $query->posts;
	}
}