<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width">
<link rel="profile" href="http://gmpg.org/xfn/11">
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
    <link rel="shortcut icon" href="<?php echo esc_url( get_template_directory_uri() ); ?>/images/favicon.png" type="image/png">
<?php wp_head(); ?>
   <!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-K7M7PGJ');</script>
<!-- End Google Tag Manager -->
</head>
<body <?php body_class(); ?>>

<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-K7M7PGJ"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
<div id="fb-root"></div>
<script>(function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = 'https://connect.facebook.net/<?=get_locale()?>/sdk.js#xfbml=1&version=v2.12&appId=1629183950429870&autoLogAppEvents=1';
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));</script>
<?php if(get_theme_mod('top-txt') && get_theme_mod('time-txt') != '') { ?>
<div class="header-top">
  <div class="container">
     <div class="left">
     	<?php if(get_theme_mod('top-txt') != '') { ?>
     		<span><?php echo esc_html(get_theme_mod('top-txt')); ?></span>
        <?php } ?>
     </div>
     <div class="right">
     		<?php if(get_theme_mod('time-txt') != '') { ?>
     			<span class="hours"><i class="fa fa-clock-o"></i><?php echo esc_html(get_theme_mod('time-txt')); ?></span>
            <?php } ?>
     </div>
     <div class="clear"></div>
  </div>
</div><!--end header-top--> 
<?php } ?>
<div class="header">
	<div class="header-inner">
      <div class="logo">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" title="<?php bloginfo('name'); ?>"><img src="<?php echo esc_url( get_template_directory_uri() ); ?>/images/logo.jpg"  alt="<?php bloginfo('name'); ?>"/></a>
    </div><!-- .logo -->                 
    <div class="header_right">
        <div class="right-box last">
            <? dynamic_sidebar( 'sidebar-2' ) ?>
        </div>
    	<div class="right-box last ">
        	<?php if(get_theme_mod('address-txt') && (get_theme_mod('street-txt') != '')) { ?>       	
            	<i class="fa fa-map-marker"></i>  
             <?php } ?>          
            <div class="bx-text">
            	 <?php if(get_theme_mod('address-txt') != '') { ?>
            		<h5><?php echo esc_html(get_theme_mod('address-txt')); ?></h5>
                <?php } ?>
                <?php if(get_theme_mod('street-txt') != '') { ?>
            	<span class="box_street"><?php echo esc_html(get_theme_mod('street-txt')); ?></span>
                <?php } ?>
            </div><!-- bx-text --><div class="clear"></div>
        </div><!-- right-box --> 
    	<div class="right-box phone_box">
        	<?php if(get_theme_mod('phone-txt') ) { ?>
            	<i class="fa fa-phone"></i>   
                <?php } ?>        
            <div class="bx-text">
            	<?php if(get_theme_mod('phone-txt') != '') { ?>
                    <h5><a href="<?php echo 'tel:'.get_theme_mod('phone-txt');?>"><?php echo esc_html(get_theme_mod('phone-txt')); ?></a></h5>
                <span class="social_links">
                    <a href="<?php echo 'viber://chat?number='.get_theme_mod('phone-txt'); ?>" rel="nofollow" class="viber"></a>
                    <a href="<?php echo 'https://wa.me/'.get_theme_mod('phone-txt'); ?>" rel="nofollow" target="_blank" class="whatsapp"></a>
                </span>
                <?php } ?>
            </div><!-- bx-text --><div class="clear"></div>
        </div><!-- right-box -->


    </div><!--header_right-->
 <div class="clear"></div>
</div><!-- .header-inner-->
</div><!-- .header -->

<div id="navigation">
	<div class="container">
    	<div class="toggle">
            <a class="toggleMenu" href="#">
                <?php esc_attr_e('Menu','movers-lite'); ?>                
            </a>
    	</div><!-- toggle -->    
    <div class="sitenav">                   
   	 	<?php wp_nav_menu( array('theme_location' => 'primary') ); ?>   
    </div><!--.sitenav -->
    <div class="clear"></div>
    </div><!-- container -->    
</div><!-- navigation -->