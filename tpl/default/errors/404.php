<?php $prev = (isset($_SERVER['HTTP_REFERER']))? $_SERVER['HTTP_REFERER'] : $settings->site_url; ?>
<h1><?php echo _('Sorry, File Not Found') ?></h1>
<a href="<?php echo $prev ?>">Return to previous page</a>