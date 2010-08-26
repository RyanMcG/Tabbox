<?php
/*
Plugin Name: Tabbox
Plugin URI: http://honors-scholars.osu.edu/studioct/portfolio.html
Description: A simple tabbed box for displaying content and an image.
Version: 1.4
Author: Ryan McGowan
Author URI: http://smi.th-ro.in/
License: GPL2
*/


//This call and the following function enqueue the necessary script and style for this plugin.
add_action('init', 'setupTabbox');
add_shortcode('tabbox', 'shortcodeTabbox');

//Add actions for dynamicly loading more tabs
add_action('wp_ajax_getmoretabs', 'getMoreTabs');
add_action('wp_ajax_nopriv_getmoretabs', 'getMoreTabs');



function setupTabbox()
{
	$tb_vers = '1.3';
	wp_enqueue_script('tabbox', WP_PLUGIN_URL.'/tabbox/tabbox.js', array('jquery'), $tb_vers);
	wp_enqueue_style('tabbox', WP_PLUGIN_URL.'/tabbox/tabbox.css', $tb_vers, 'all');
	wp_enqueue_style('tabbox-mod', get_bloginfo('stylesheet_directory').'/css/tabbox-mod.css');
	wp_localize_script('tabbox', 'tabbox_stuff', array('ajaxurl' => admin_url('admin-ajax.php')));

//UNCOMMENT THE SECTION BELOW TO CREATE THE "TAB" POST TYPE 	
/*
	register_post_type( 'tab',
		array(
			'labels' => array(
					'name' => __('Tabs'),
					'singular_name' => __('Tab'),
					'add_new_item' => __('Add New Tab'),
					'edit_item' => __('Edit Tab'),
					'new_item' => __('New Tab'),
					'view_item' => __('View Tab'),
					'search_items' => __('Search Tabs'),
					'not_found' => __('No tabs found'),
					'not_found_in_trash' => __('No tabs found in Trash'),
				),
			'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments'),
			'public' => true,
			'publicly_queryable' => true,
		  )
	);
*/
}

//This function can be placed anywhere to generate a Tabbox
function placeTabbox($tb_query_args) //If the commented section above is uncommented the $tb_query_args['post_type'] value can be set to 'tab'.
{
	$tb_query_args = array_merge(array('post_type' => 'study', 'post_status' => 'publish', 'posts_per_page' => 3), (array) $tb_query_args);
	echo '
	<div class="tb">';
	placeTabboxContents($tb_query_args);
	echo '
	</div>';
}

function placeTabboxContents($tb_query_args)
{
	//Filters are added (and later removed) to change the output of The Loop function calls.
	add_filter('the_title', 'tb_title');
	add_filter('the_content', 'tb_content');
	add_filter('post_thumbnail_html', 'tb_thumbnail');
	
	$doquery = true;
	if(isset($tb_query_args['next'])) 	//If this is an ajax request asking for more tabs this block executes.
	{
		if($tb_query_args['next'] == 'true')
			$tb_query_args['offset'] = $tb_query_args['offset'] + $tb_query_args['posts_per_page'];
		else if($tb_query_args['offset'] == 0)
		{
			$doquery = false;
			echo("-2");
		}
		else if($tb_query_args['offset'] - $tb_query_args['posts_per_page'] > 0)
			$tb_query_args['offset'] = $tb_query_args['offset'] - $tb_query_args['posts_per_page'];
		else
			$tb_query_args['offset'] = 0;
		unset($tb_query_args['next']);	//Get rid of the "next" argument so it does not get sent as part of the database query.
	}			
	
	if($doquery)
	{
		$tb_query = new WP_Query($tb_query_args);
		//If the Query returns too few posts then requery witht the correct offest. (Should only execute when the number of total posts is greater than posts per page.)
		if($tb_query->post_count == 0 && $tb_query_args['offset'] != 0)
		{
			echo("-3");  //This is interpreted as the end and is not diplayed by jQuery
		}
		else
		{
			//START CONTENT OUTPUT
			echo '
				<form class="tb-data" name="tb_data">';
				
			foreach($tb_query_args as $key => $value)
			{
				echo '
				<input type="hidden" name="'.$key.'" value="'.$value.'" />';
			}
				
			echo '
				</form>
				<div class="tb-selector-bar">
					<div class="tb-selector-piece arrow">
						&laquo;
					</div>';
			
			if($tb_query->have_posts())
			{
				while($tb_query->have_posts())
				{
					$tb_query->the_post();
					the_title();
				}
			}
			echo '
					<div class="tb-selector-piece right arrow" >
						&raquo;
					</div>
				</div>';
			
			$tb_query->rewind_posts();
			if($tb_query->have_posts())
			{	
				while($tb_query->have_posts())
				{
					$tb_query->the_post();
					echo '
					<div class="tb-content">';
					the_post_thumbnail(array(180, 180));
					the_content('<span class="readmore">Read More &raquo;</span>');
					echo'
					</div>';
				}
			}
			//END CONTENT OUTPUT
		}
	}
	//Then the filters are removed so they don't change other posts.
	remove_filter('the_title', 'tb_title');
	remove_filter('the_content', 'tb_content');
	remove_filter('post_thumbnail_html', 'tb_thumbnail');
}

function tb_title($title)
{
	return '
	<div class="tb-selector-piece">
		'.$title.'
	</div>';
}

function tb_thumbnail($thumbnail)
{
	return '
	<div class="tb-image">
		'.$thumbnail.'
	</div>';
}

function tb_content($content)
{
	return '
	<div class="tb-text">
		'.$content.'
	</div>
	<div class="clear"></div>';
}

function shortcodeTabbox($atts)
{
	$atts = (array) $atts;
	unset($a[0]);
	placeTabbox($atts);
}

function getMoreTabs()
{
	$a = array_merge(array('post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 3), $_POST);	
	unset($a['action']);
	placeTabboxContents($a);
	exit;
}

?>
