<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width">
    <?php wp_head(); ?>
    <link rel='stylesheet'  href='<?=get_template_directory_uri()?>/slider/flexslider.css' type='text/css' media='all' />
    <script type='text/javascript' src='<?=get_template_directory_uri()?>/slider/modernizr.js'></script>
    <script defer type='text/javascript' src='<?=get_template_directory_uri()?>/slider/jquery.flexslider-min.js'></script>
    <script type='text/javascript' src='<?=get_template_directory_uri()?>/slider/jquery.mousewheel.js'></script>
    <script type='text/javascript' src='<?=get_template_directory_uri()?>/slider/jquery.easing.js'></script>
    <script type='text/javascript' src='/wp-includes/js/jquery/ui/core.min.js?ver=1.11.4'></script>
    <script type='text/javascript' src='/wp-includes/js/jquery/ui/datepicker.min.js?ver=1.11.4'></script>
</head>
<body class="loading">