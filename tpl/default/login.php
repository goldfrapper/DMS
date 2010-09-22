<?php
/**
 * Login screen
 */
if(!BOOTSTRAP) exit();

$settings->site_page_title = _('Login');
?>
<div id="login_screen">
<form method="POST" action="service.php">
	<div class="infomessage"><?php
	$e = DMS_System_Service::get('e');
	if($e!==null) {
		if($e=='invalid') echo _('Illegal login or password.');
	}
	?></div>
	<label><?php echo _('Login') ?></label><input type="text" name="login" />
	<label><?php echo _('Password') ?></label><input type="password" name="pass" />
	<input type="hidden" name="action" value="login" />
	<input type="submit" value="<?php echo _('Login') ?>" />
	<div><?php
	if($settings->profile_registration_active===true) { ?>
		<a href=""><?php echo _('Create an account!'); ?></a><?php
	} ?>
	<a href=""><?php echo _('Forgot your Password?'); ?></a>
	</div>
</form>
</div>