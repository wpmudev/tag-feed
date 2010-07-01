<?php
/*
Plugin Name: Tag Feed
Plugin URI: 
Description: RSS2 feeds
Author: Andrew Billits (Incsub)
Version: 1.0.2
Author URI:
*/ 

/* 
Copyright 2007-2009 Incsub (http://incsub.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if (empty($wp)) {
	require_once('../wp-load.php');
	wp('feed=rss2');
}

//------------------------------------------------------------------------//
//---Config---------------------------------------------------------------//
//------------------------------------------------------------------------//
$tag = $_GET['tag'];
if ( empty( $tag ) ) {
	$tag = 'uncategorized';
}

$number = $_GET['number'];
if ( empty( $number ) ) {
	$number = '25';
}
//------------------------------------------------------------------------//
//---Functions------------------------------------------------------------//
//------------------------------------------------------------------------//

$tag_id = $wpdb->get_var("SELECT cat_ID FROM " . $wpdb->base_prefix . "sitecategories WHERE category_nicename = '" . $tag . "'");

if ( empty( $tag_id ) || !is_numeric( $tag_id ) || $tag_id == 0 ) {
	$tag_id = $wpdb->get_var("SELECT cat_ID FROM " . $wpdb->base_prefix . "sitecategories WHERE cat_name = '" . $tag . "'");
}

$query = "SELECT * FROM " . $wpdb->base_prefix . "site_posts WHERE site_id = '" . $current_site->id . "' AND post_terms LIKE '%|" . $tag_id . "|%' AND blog_public = '1' ORDER BY post_published_gmt DESC LIMIT " . $number;
$posts = $wpdb->get_results( $query, ARRAY_A );

if ( count( $posts ) > 0 ) {
	$last_published_post_date_time = $wpdb->get_var("SELECT post_published_gmt FROM " . $wpdb->base_prefix . "site_posts WHERE site_id = '" . $current_site->id . "' AND post_terms LIKE '%|" . $tag_id . "|%' AND blog_public = '1' ORDER BY post_published_gmt DESC LIMIT 1");
}

header('Content-Type: text/xml; charset=' . get_option('blog_charset'), true);
$more = 1;

?>
<?php echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>'; ?>
<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
>

<channel>
	<title><?php bloginfo_rss('name'); ?> <?php _e('Posts'); ?></title>
	<atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+xml" />
	<link><?php bloginfo_rss('url') ?></link>
	<description><?php bloginfo_rss("description") ?></description>
	<pubDate><?php echo mysql2date('D, d M Y H:i:s +0000', $last_published_post_date_time, false); ?></pubDate>
	<?php the_generator( 'rss2' ); ?>
	<language><?php echo get_option('rss_language'); ?></language>
    <?php
	//--------------------------------------------------------------------//
	if ( count( $posts ) > 0 ) {
		foreach ($posts as $post) {
			$author_display_name = $wpdb->get_var("SELECT display_name FROM " . $wpdb->base_prefix . "users WHERE ID = '" . $post['post_author'] . "'");
			?>
			<item>
				<title><?php echo stripslashes($post['post_title']); ?></title>
				<link><?php echo $post['post_permalink']; ?></link>
				<comments><?php echo $post['post_permalink'] . '#comments'; ?></comments>
				<pubDate><?php echo mysql2date('D, d M Y H:i:s +0000', $post['post_published_gmt'], false); ?></pubDate>
				<dc:creator><?php echo $author_display_name; ?></dc:creator>
				<guid isPermaLink="false"><?php echo $post['post_permalink']; ?></guid>
                <description><![CDATA[<?php echo wp_trim_excerpt($post['post_content']); ?>]]></description>
                <content:encoded><![CDATA[<?php echo $post['post_content']; ?>]]></content:encoded>
				<wfw:commentRss><?php echo $post['post_permalink'] . 'feed/'; ?></wfw:commentRss>
			</item>
			<?php
		}
	}
	//--------------------------------------------------------------------//
	?>
</channel>
</rss>
<?php
//------------------------------------------------------------------------//
?>
