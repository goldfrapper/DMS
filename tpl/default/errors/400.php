<?php $prev = (isset($_SERVER['HTTP_REFERER']))? $_SERVER['HTTP_REFERER'] : $settings->site_url; ?>

<h1><?php echo _('400 Bad Request') ?></h1>
<a href="<?php echo $prev ?>">Return to previous page</a>