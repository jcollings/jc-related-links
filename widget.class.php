<?php
/**
 * @package Related Links
 * @author James Collings <james@jclabs.co.uk>
 * @version 1.0
 */
class RelatedLinksWidget
{
	private $_core;
	function __construct(RelatedLinksCore $core)
	{
		$this->_core = $core;

		// register_sidebar_widget($name, $output_callback, $classname = '')
		register_sidebar_widget('related-links', array($this, 'show_widget'));
	}

	function show_widget($args)
	{
		extract($args);

		$post_id = get_the_ID();
		$current_post_type = get_post_type();
		$options = $this->_core->get_options();

		$active_post_types = array_keys($options[$current_post_type.'_type'], 'true');

		$pages = $this->_core->get_related_posts($post_id, $active_post_types);

		$output .= $before_widget;

		$output .= $before_title . 'Related Articles' .$after_title;

		$output .= '<ul>';
		if(!empty($pages))
		{
			foreach($pages as $page){
				$output .= '<li><a href="'.get_permalink($page->ID).'">'.$page->post_title.'</a></li>';
			}
		}else{
			$output .= '<li>No related links</li>';
		}
		$output .= '</ul>';
		$output .= $after_widget;

		if(is_single() || is_page())
		{
			echo $output;
		}
	}
}
?>