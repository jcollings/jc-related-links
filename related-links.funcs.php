<?php

function list_related_posts()
{
	global $RelatedLinks;
	$output = '';
	$post_id = get_the_ID();
	$current_post_type = get_post_type();
	$options = $RelatedLinks->_core->get_options();

	if(!is_array($options[$current_post_type.'_type']))
		return;
	
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

/**
 * Get Specific Post Type Relation
 * @return array id
 */
function get_related_post_type($post_type = '')
{
	global $RelatedLinks;
	$post_id = get_the_ID();

	$current_post_type = get_post_type();
	$options = $RelatedLinks->_core->get_options();
	$output = array();

	if(empty($post_type)){
		$active_post_types = array_keys($options[$current_post_type.'_type'], 'true');
		foreach($active_post_types as $value){
			if($options[$value.'_type'][$current_post_type] == 1){
				$q = new WP_Query(array(
					'post_type' => $value
				));

				while($q->have_posts()){
					$q->the_post();
					$test = get_post_meta( get_the_id(), 'related_service_page', true);
					
					if(in_array($post_id, $test)){
						$output[] = array(
							'id' => get_the_ID(),
							'count' => count($test)
						);
					}
				}

				wp_reset_postdata();
			}
		}
	}else{
		if($options[$post_type.'_type'][$current_post_type] == 1){
			$q = new WP_Query(array(
				'post_type' => $post_type
			));

			while($q->have_posts()){
				$q->the_post();
				$test = get_post_meta( get_the_id(), 'related_service_page', true);
				
				if(in_array($post_id, $test)){
					$output[] = array(
						'id' => get_the_ID(),
						'count' => count($test)
					);
				}
			}

			wp_reset_postdata();
		}
	}

	if(empty($output))
		return false;

	$selected = null;
	foreach($output as $o){
		if(is_array($selected)){
			if($o['count'] < $selected['count']){
				$selected = $o;
			}
		}else{
			$selected = $o;
		}
	}
	
	return $selected;
}
?>