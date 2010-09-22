<?php
/**
 * Top navigation and/or menu
 */
?>
<div id="top_plane">
	<strong><?php echo $settings->site_main_title ?></strong>
	
	<div id="profile_box">
		<span><?php echo _('Welcome, ').ucfirst($session->user->fname); ?></span>
		<a href="service.php?action=logout">Logout</a>
<!-- 		<a href="?p=edit_profile">Edit Profile</a> -->
	</div>
</div>