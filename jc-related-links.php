<?php
/*
Plugin Name: JC Related Links
Plugin URI: http://jamescollings.co.uk/plugins/jc-related-links/
Description: This plugin allows you to choose what media relates to the current page, post, or custom post type.
Author: James Collings <james@jclabs.co.uk>
Version: 1.0
Author URI: http://www.jamescollings.co.uk
*/

/**
 * @package Related Links
 * @author James Collings <james@jclabs.co.uk>
 * @version 1.0
 */
// core class 
require_once 'core.class.php';

// child classes
require_once 'metabox.class.php';
require_once 'settings.class.php';
require_once 'widget.class.php';

// public functions
require_once 'related-links.funcs.php';

class RelatedLinks{	
	var $_core;
	
	function __construct()
	{
		$this->_core = new RelatedLinksCore();
		
		new RelatedLinksMeta($this->_core);
		new RelatedLinksWidget($this->_core);
		new RelatedLinksSettings($this->_core);

		register_deactivation_hook(__FILE__, array($this, 'deactivation'));
	}

	/**
	 * Called on plugin deactivation
	 * @return void
	 */
	function deactivate()
	{
		// unregister_setting($option_group, $option_name, $sanitize_callback = '')
		unregister_setting($this->_core->options_group_id, $this->_core->options_group_id);
	}
}

$RelatedLinks = new RelatedLinks();

add_action('the_content', 'add_ratings_to_content');
function add_ratings_to_content($content) {
	if (!is_feed() && is_single()) {
		$content = $content . list_related_posts();
	}
	return $content;
}
?>