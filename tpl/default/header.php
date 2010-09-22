<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><?php echo (isset($settings->site_page_title))? $settings->site_page_title : $_SERVER['SERVER_NAME']; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<style type="text/css" media="print, screen and (min-width: 481px)">
	/*<![CDATA[*/
	@import url("public/css/advanced.css");
	/*]]>*/
	</style>
	<link href="public/css/minimum.css" rel="stylesheet" type="text/css" media="handheld, only screen and (max-device-width: 480px)" />
	<meta name="viewport" content="width=device-width" />
	<link rel="stylesheet" href="public/css/print.css" type="text/css" media="print" />
	<link rel="shortcut icon" href="public/img/favicon.ico" type="image/x-icon" />
	
	<script src="public/js/jquery-1.4.2.min.js"></script>
	<script src="public/js/validate.jquery.js"></script>
	<script src="public/js/json2.js"></script>
	<script src="public/js/functions.js"></script>
	<?php
	if($settings->site_include_jqueryui) { ?>
		<script src="public/js/jquery-ui-1.8.5.custom.min.js"></script>
		<link rel="stylesheet" href="public/css/jquery-ui-1.8.5.custom.css" type="text/css" media="all" /><?php
	} ?>
</head>
<body>