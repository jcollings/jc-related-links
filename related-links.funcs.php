<?php

function list_related_posts()
{
	global $RelatedLinks;
	$output = '';
	$post_id = get_the_ID();
	$current_post_type = get_post_type();
	$options = $RelatedLinks->_core->get_options();

	$active_post_types = array_keys($options[$current_post_type.'_type'], 'true');

	$pages = $RelatedLinks->_core->get_related_posts($post_id, $active_post_types);

	if(!empty($pages))
	{
		$output = '
		<div class="related">
			<h2>Relevant Articles</h2>
				<ul class="rel-posts">';
				foreach($pages as $page){
					$output .= '<li><a href="'.get_permalink($page->ID).'">'.$page->post_title.'</a></li>';
				}
			$output .= '</ul>
		</div>
		';
	}
	return $output;
}

?>