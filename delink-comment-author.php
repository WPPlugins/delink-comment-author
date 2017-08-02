<?php

// Delink Comment Author
//
// Copyright (c) 2007-2009 Alex King
// http://alexking.org/projects/wordpress
//
// Released under the GPL license
// http://www.opensource.org/licenses/gpl-license.php
//
// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
// *****************************************************************

/*
Plugin Name: Delink Comment Author
Plugin URI: http://alexking.org/projects/wordpress
Description: Adds a link to comment e-mails for you to remove the URL the commentor left. Useful if you want to ditch the comment URL but keep the comment.
Author: Alex King
Author URI: http://alexking.org
Version: 1.4
*/ 

if (!function_exists('is_admin_page')) {
	function is_admin_page() {
		if (function_exists('is_admin')) {
			return is_admin();
		}
		if (function_exists('check_admin_referer')) {
			return true;
		}
		else {
			return false;
		}
	}
}

function akdca_admin_head() {
	global $wp_version;
	if (isset($wp_version) && version_compare($wp_version, '2.7', '>=')) {
		print("
<script type=\"text/javascript\">
jQuery(function($) {
	$('#the-comment-list tr[id^=comment]').each(function() {
		var id = $(this).attr('id').replace('comment-', '');
		$(this).find('div.row-actions').append(' | <a href=\"".get_bloginfo('wpurl')."/wp-admin/index.php?ak_action=delink_comment_author&comment_id=' + id + '\">Delink Comment</a>');
	});
});
</script>
		");
	}
	else if (isset($wp_version) && version_compare($wp_version, '2.5', '>=')) {
		print("
<script type=\"text/javascript\">
jQuery(function($) {
	$('#the-comment-list tr[id^=comment]').each(function() {
		var id = $(this).attr('id').replace('comment-', '');
		$(this).children('td.action-links').append(' | <a href=\"".get_bloginfo('wpurl')."/wp-admin/index.php?ak_action=delink_comment_author&comment_id=' + id + '\">Delink Comment Author</a>');
	});
});
</script>
		");
	}
	else {
		print("
<script type=\"text/javascript\">
jQuery(function($) {
	$('#the-comment-list li[id^=comment]').each(function() {
		var id = $(this).attr('id').replace('comment-', '');
		$(this).children('p').eq(0).append('&nbsp;| <a href=\"".get_bloginfo('wpurl')."/wp-admin/index.php?ak_action=delink_comment_author&comment_id=' + id + '\">Delink Comment Author</a>');
	});
});
</script>
		");
	}
}

if (is_admin_page()) {
	wp_enqueue_script('jquery');
}
add_action('admin_head', 'akdca_admin_head');

function akdca_request_handler() {
	if (!empty($_GET['ak_action'])) {
		switch($_GET['ak_action']) {
			case 'delink_comment_author':
				if (!empty($_GET['comment_id'])) {
					global $wpdb;
					$comment_id = intval($_GET['comment_id']);
					$comment_post_id = $wpdb->get_var("
						SELECT comment_post_ID
						FROM $wpdb->comments
						WHERE comment_ID = '$comment_id'
					");
					if (current_user_can('edit_post', $comment_post_id)) {
						$wpdb->query("
							UPDATE $wpdb->comments
							SET comment_author_url = ''
							WHERE comment_ID = '$comment_id'
						");
						header('Location: '.get_bloginfo('wpurl').'/wp-admin/edit-comments.php');
						die();
					}
				}
				break;
		}
	}
}
add_action('init', 'akdca_request_handler');

function akdca_email($text, $comment_id) {
	return $text .= "\r\n".'Delink Comment Author: '.get_bloginfo('wpurl').'/wp-admin/index.php?ak_action=delink_comment_author&comment_id='.$comment_id."\r\n";
}
add_filter('comment_notification_text', 'akdca_email', 10, 2);

?>