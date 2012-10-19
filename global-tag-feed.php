<?php
/*
Plugin Name: Global Tag Feed
Plugin URI:
Description: RSS2 feeds
Version: 3.0
Author: Barry (Incsub)
Author URI:
WDP ID: 96
Network: true
*/

/*
Copyright 2012 Incsub (http://incsub.com)

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


class globaltabfeed {

	var $build = 1;

	var $db;

	function __construct() {

		global $wpdb;

		$this->db =& $wpdb;

		if($this->db->blogid == 1) {
			// Only add the feed for the main site
			add_action('init', array(&$this, 'initialise_global_tag_feed') );
		}

	}

	function globaltagfeed() {
		$this->__construct();
	}

	function initialise_global_tag_feed() {

		global $wp_rewrite;

		$installed = get_option('globaltagfeed_version', false);

		add_feed('globaltagfeed', array( &$this, 'do_global_tag_feed' ));

		if($installed === false || $installed < $this->build) {
			// We need to flush our rewrites so that the new feed is added and recognised
			flush_rewrite_rules();
			update_option('globaltagfeed_version', $this->build);
		}

	}

	function do_global_tag_feed() {

		die('boo');

		header('Content-Type: ' . feed_content_type('rss-http') . '; charset=' . get_option('blog_charset'), true);
		$more = 1;

		echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>'; ?>

		<rss version="2.0"
			xmlns:content="http://purl.org/rss/1.0/modules/content/"
			xmlns:wfw="http://wellformedweb.org/CommentAPI/"
			xmlns:dc="http://purl.org/dc/elements/1.1/"
			xmlns:atom="http://www.w3.org/2005/Atom"
			xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
			xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
			<?php do_action('rss2_ns'); ?>
		>

		<channel>
			<title><?php bloginfo_rss('name'); wp_title_rss(); ?></title>
			<atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+xml" />
			<link><?php bloginfo_rss('url') ?></link>
			<description><?php bloginfo_rss("description") ?></description>
			<lastBuildDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_lastpostmodified('GMT'), false); ?></lastBuildDate>
			<language><?php bloginfo_rss( 'language' ); ?></language>
			<sy:updatePeriod><?php echo apply_filters( 'rss_update_period', 'hourly' ); ?></sy:updatePeriod>
			<sy:updateFrequency><?php echo apply_filters( 'rss_update_frequency', '1' ); ?></sy:updateFrequency>
			<?php do_action('rss2_head'); ?>
			<?php while( have_posts()) : the_post(); ?>
			<item>
				<title><?php the_title_rss() ?></title>
				<link><?php the_permalink_rss() ?></link>
				<comments><?php comments_link_feed(); ?></comments>
				<pubDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_post_time('Y-m-d H:i:s', true), false); ?></pubDate>
				<dc:creator><?php the_author() ?></dc:creator>
				<?php the_category_rss('rss2') ?>

				<guid isPermaLink="false"><?php the_guid(); ?></guid>
		<?php if (get_option('rss_use_excerpt')) : ?>
				<description><![CDATA[<?php the_excerpt_rss() ?>]]></description>
		<?php else : ?>
				<description><![CDATA[<?php the_excerpt_rss() ?>]]></description>
			<?php if ( strlen( $post->post_content ) > 0 ) : ?>
				<content:encoded><![CDATA[<?php the_content_feed('rss2') ?>]]></content:encoded>
			<?php else : ?>
				<content:encoded><![CDATA[<?php the_excerpt_rss() ?>]]></content:encoded>
			<?php endif; ?>
		<?php endif; ?>
				<wfw:commentRss><?php echo esc_url( get_post_comments_feed_link(null, 'rss2') ); ?></wfw:commentRss>
				<slash:comments><?php echo get_comments_number(); ?></slash:comments>
		<?php rss_enclosure(); ?>
			<?php do_action('rss2_item'); ?>
			</item>
			<?php endwhile; ?>
		</channel>
		</rss>
		<?php
	}

}

$globaltabfeed = new globaltabfeed();

?>