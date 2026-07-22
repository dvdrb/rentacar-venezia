<?php



function wpa_90820() {

    wp_enqueue_style('ios-scroll-fix', get_stylesheet_directory_uri() .'/css/ios-scroll-fix.css', array(), '1.0' );

}



add_action('wp_enqueue_scripts', 'wpa_90820');



add_filter('style_loader_tag', 'sj_remove_type_attr', 10, 2);

add_filter('script_loader_tag', 'sj_remove_type_attr', 10, 2);

add_filter('wp_print_footer_scripts ', 'sj_remove_type_attr', 10, 2);

function sj_remove_type_attr($tag) {

    return preg_replace( "/type=['\"]text\/(javascript|css)['\"]/", '', $tag );

}

/* limit revision */

define('WP_POST_REVISIONS', 8);

/**

 * Disable the emoji's

 */

function disable_emojis() {

    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );

    remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );

    remove_action( 'wp_print_styles', 'print_emoji_styles' );

    remove_action( 'admin_print_styles', 'print_emoji_styles' );

    remove_filter( 'the_content_feed', 'wp_staticize_emoji' );

    remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );

    remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

    //  add_filter( 'tiny_mce_plugins', 'disable_emojis_tinymce' );

    //  add_filter( 'wp_resource_hints', 'disable_emojis_remove_dns_prefetch', 10, 2 );

}

add_action( 'init', 'disable_emojis' );



add_filter( 'auto_update_core', '__return_false' );

add_filter( 'auto_update_theme', '__return_false' );

add_filter( 'auto_update_plugin', '__return_false' );





remove_action('wp_head', 'rsd_link');

remove_action('wp_head', 'wp_generator');

remove_action('wp_head', 'feed_links', 2);

remove_action('wp_head', 'index_rel_link');

remove_action('wp_head', 'wlwmanifest_link');

remove_action('wp_head', 'feed_links_extra', 3);

remove_action('wp_head', 'start_post_rel_link', 10, 0);

remove_action('wp_head', 'parent_post_rel_link', 10, 0);

remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0);





remove_action('wp_head', 'wp_generator');

function wpbeginner_remove_version() {

    return '';

}

add_filter('the_generator', 'wpbeginner_remove_version');





function remove_x_pingback($headers) {

    unset($headers['X-Pingback']);

    return $headers;

}

add_filter('wp_headers', 'remove_x_pingback');



add_action( 'admin_bar_menu', 'remove_wp_logo', 999 );



function remove_wp_logo( $wp_admin_bar ) {

    $wp_admin_bar->remove_node( 'wp-logo' );

    $wp_admin_bar->remove_node( 'updates' );

}





/**

 * Movers functions and definitions

 *

 * @package Movers Lite

 */



add_action('phpmailer_init','send_smtp_email');



function send_smtp_email( $mail ) {

    // Define that we are sending with SMTP

    $mail->IsSMTP();

    //$mail->isMail(); - if we need mail class to send

    $mail->CharSet = 'UTF-8';

    //$mail->Host IsSMTP= 'spamfilter.starnet.md';//'smtp.gmail.com';

    $mail->Host = 'mail.rentacarvenezia.it';

    $mail->Port = '465';//'465';

    $mail->SMTPAuth = true;//true;

    $mail->Username = '[REDACTED_LEGACY_SMTP_USERNAME]';

    $mail->Password = '[REDACTED_LEGACY_SMTP_PASSWORD]';

    $mail->SMTPSecure = "ssl";

    $mail->IsHTML(true);

}

//

//if (isset($_GET['some'])) {

//    $headers = 'From: lanarent <no-reply@lanarent.com>' . "\r\n";

//    wp_mail( 'nina.malchic@gmail.com', 'subject', 'message', $headers );

//    exit();

//}

if ( ! function_exists( 'movers_lite_setup' ) ) :

    function movers_lite_setup() {



        if ( ! isset( $content_width ) )

            $content_width = 640; /* pixels */



        load_theme_textdomain( 'movers-lite', get_template_directory() . '/languages' );

        add_theme_support( 'automatic-feed-links' );

        add_theme_support( 'post-thumbnails' );

        add_theme_support( 'title-tag' );

        add_theme_support( 'custom-logo', array(

            'height'      => 240,

            'width'       => 240,

            'flex-height' => true,

        ) );

        add_image_size('homepage-thumb',400,250,true);

        register_nav_menus( array(

            'primary' => __( 'Primary Menu', 'movers-lite' ),

        ) );

        add_theme_support( 'custom-background', array(

            'default-color' => 'f1f1f1'

        ) );

        add_editor_style( array( 'editor-style.css', movers_lite_font_url() ) );

    }

endif; // movers_lite_setup

add_action( 'after_setup_theme', 'movers_lite_setup' );





function movers_lite_widgets_init() {

    register_sidebar( array(

        'name'          => __( 'Blog Sidebar', 'movers-lite' ),

        'description'   => __( 'Appears on blog page sidebar', 'movers-lite' ),

        'id'            => 'sidebar-1',

        'before_widget' => '<aside id="%1$s" class="widget %2$s">',

        'after_widget'  => '</aside>',

        'before_title'  => '<h3 class="widget-title">',

        'after_title'   => '</h3>',

    ) );



}

add_action( 'widgets_init', 'movers_lite_widgets_init' );



add_action( 'widgets_init', 'register_lang' );

function register_lang(){

    register_sidebar( array(

        'name'          => __( 'Header Sidebar', 'movers-lite' ),

        'description'   => __( 'For language', 'movers-lite' ),

        'id'            => 'sidebar-2',

        'before_widget' => '<aside id="%1$s" class="widget %2$s">',

        'after_widget'  => '</aside>',

        'before_title'  => '',

        'after_title'   => '',

    ) );

}





add_action( 'widgets_init', 'register_footer' );

function register_footer(){

    register_sidebar( array(

        'name'          => __( 'Footer Sidebar', 'movers-lite' ),

        'id'            => "sidebar-3",

        'description'   => '',

        'class'         => '',

        'before_widget' => '<aside id="%1$s" class="widget %2$s">',

        'after_widget'  => '</aside>',

        'before_title'  => '<span class="widget-title">',

        'after_title'   => '</span>',

    ) );

}





function movers_lite_font_url(){

    $font_url = '';



    /* Translators: If there are any character that are

    * not supported by Montserrat, translate this to off, do not

    * translate into your own language.

    */

    $mont = _x('on', 'Montserrat font:on or off','movers-lite');





    if('off' !== $mont){

        $font_family = array();



        if('off' !== $mont){

            $font_family[] = 'Montserrat:400,600,700';

        }



        $query_args = array(

            'family'	=> urlencode(implode('|',$font_family)),

        );



        $font_url = add_query_arg($query_args,'https://fonts.googleapis.com/css');

    }



    return $font_url;

}



function movers_lite_scripts() {

    //wp_enqueue_script( 'jquery-ui-core' );

    wp_enqueue_script( 'jquery-ui-datepicker' );

    // wp_enqueue_script( 'jquery-ui', get_template_directory_uri() . '/css/jquery-ui.min.js', array('jquery') );

    wp_enqueue_style( 'font', 'https://fonts.googleapis.com/css?family=Roboto:400,500,700&amp;subset=cyrillic', array() );

    wp_enqueue_style( 'lite-basic-style', get_stylesheet_uri() );

    wp_enqueue_style( 'lite-editor-style', get_template_directory_uri().'/editor-style.css' );

    wp_enqueue_style( 'lite-responsive-style', get_template_directory_uri().'/css/theme-responsive.css' );

    wp_enqueue_style( 'nivo-style', get_template_directory_uri().'/css/nivo-slider.css');

    wp_enqueue_style( 'font-awesome-style', get_template_directory_uri().'/css/font-awesome.css' );

    wp_enqueue_style( 'jquery-ui', get_template_directory_uri().'/css/jquery-ui.min.css' );

    wp_enqueue_style( 'structure-ui', get_template_directory_uri().'/css/jquery-ui.structure.min.css' );

    wp_enqueue_style( 'theme-ui', get_template_directory_uri().'/css/jquery-ui.theme.css' );

    wp_enqueue_style( 'fancybox-css', get_template_directory_uri().'/fancybox/jquery.fancybox.css' );

    //wp_enqueue_style( 'slider-css', get_template_directory_uri().'/slider/flexslider.css' );

    //   wp_enqueue_style( 'jquery-ui', get_template_directory_uri().'/css/jquery-ui.min.css' );

    //wp_enqueue_script( 'jquery-nivo-slider-js', get_template_directory_uri() . '/js/jquery.nivo.slider.js', array('jquery') );

    // wp_enqueue_script( 'modernizr', get_template_directory_uri() . '/slider/modernizr.js', array('jquery') );

    //  wp_enqueue_script( 'slider', get_template_directory_uri() . '/slider/jquery.fancybox.pack.js', array('jquery'), '', true );

    wp_enqueue_script( 'fancybox', get_template_directory_uri() . '/fancybox/jquery.fancybox.pack.js', array('jquery'), '', true );

    wp_enqueue_script( 'auto-script', get_template_directory_uri() . '/js/custom.js?v='.time(), array('jquery'), '', true );

    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {

        wp_enqueue_script( 'comment-reply' );

    }

}

add_action( 'wp_enqueue_scripts', 'movers_lite_scripts' );



/**

 * Use front-page.php when Front page displays is set to a static page.

 *

 *

 * @param string $template front-page.php.

 *

 * @return string The template to be used: blank if is_home() is true (defaults to index.php), else $template.

 */

//function movers_lite_front_page_template( $template ) {

//	return is_home() ? '' : $template;

//}

//add_filter( 'frontpage_template',  'movers_lite_front_page_template' );





/**

 * Implement the Custom Header feature.

 */

require get_template_directory() . '/inc/custom-header.php';



/**

 * Custom template tags for this theme.

 */

require get_template_directory() . '/inc/template-tags.php';



/**

 * Custom functions that act independently of the theme templates.

 */

require get_template_directory() . '/inc/extras.php';



/**

 * Customizer additions.

 */

require get_template_directory() . '/inc/customizer.php';



/*

 * Load customize pro

 */

require_once( trailingslashit( get_template_directory() ) . 'customize-pro/class-customize.php' );







/*WIPPO*/

add_action( 'init', 'cars_init' );

function cars_init() {

    $labels = array(

        'name'               => _x( 'Cars', 'post type general name', 'your-plugin-textdomain' ),

        'singular_name'      => _x( 'Car', 'post type singular name', 'your-plugin-textdomain' ),

        'menu_name'          => _x( 'Cars', 'admin menu', 'your-plugin-textdomain' ),

        'name_admin_bar'     => _x( 'Cars', 'add new on admin bar', 'your-plugin-textdomain' ),

    );



    $args = array(

        'labels'             => $labels,

        'description'        => __( 'Description.', 'your-plugin-textdomain' ),

        'public'             => true,

        'publicly_queryable' => true,

//        'show_ui'            => true,

        'show_in_menu'       => true,

        'query_var'          => true,

//        //   'rewrite'            => array( 'slug' => 'book' ), 'editor',

//        'capability_type'    => 'post',

//        'has_archive'        => true,

//        'hierarchical'       => false,

        'supports'           => array( 'title',  'author', 'thumbnail', ),

        'menu_icon'          => 'dashicons-sos',

        'menu_position'      =>  10

    );



    register_post_type( 'cars', $args );

}



class new_general_gps {

    function new_general_gps( ) {

        add_filter( 'admin_init' , array( &$this , 'register_fields' ) );

    }

    function register_fields() {

        register_setting( 'general', 'extra_gps', 'esc_attr' );

        add_settings_field('extra_gps', '<h3>Extra price per day</h3><label for="extra_gps">'.__('Extra price: GPS (euro)' , 'mail' ).'</label>' , array(&$this, 'fields_html') , 'general' );

    }

    function fields_html() {

        $value = get_option( 'extra_gps', '' );

        echo '<input type="text" id="extra_gps" name="extra_gps" value="' . $value . '" />';

    }

}

$new_general_gps = new new_general_gps();



class new_general_childseat {

    function new_general_childseat( ) {

        add_filter( 'admin_init' , array( &$this , 'register_fields' ) );

    }

    function register_fields() {

        register_setting( 'general', 'child_check', 'esc_attr' );

        add_settings_field('child_check', '<label for="child_check">'.__('Extra price: Child seat (euro)' , 'mail' ).'</label>' , array(&$this, 'fields_html') , 'general' );

    }

    function fields_html() {

        $value = get_option( 'child_check', '' );

        echo '<input type="text" id="child_check" name="child_check" value="' . $value . '" />';

    }

}

$new_general_childseat = new new_general_childseat();



class new_general_drivers {

    function new_general_drivers( ) {

        add_filter( 'admin_init' , array( &$this , 'register_fields' ) );

    }

    function register_fields() {

        register_setting( 'general', '2driver_check', 'esc_attr' );

        add_settings_field('2driver_check', '<label for="2driver_check">'.__('Extra price: Two drivers (euro)' , 'mail' ).'</label>' , array(&$this, 'fields_html') , 'general' );

    }

    function fields_html() {

        $value = get_option( '2driver_check', '' );

        echo '<input type="text" id="2driver_check" name="2driver_check" value="' . $value . '" />';

    }

}

$new_general_drivers = new new_general_drivers();



class new_general_simtravel{

    function new_general_simtravel( ) {

        add_filter( 'admin_init' , array( &$this , 'register_fields' ) );

    }

    function register_fields() {

        register_setting( 'general', 'sim_internet', 'esc_attr' );

        add_settings_field('sim_internet', '<label for="sim_internet">'.__('Extra price: Sim with internet (euro)' , 'mail' ).'</label>' , array(&$this, 'fields_html') , 'general' );

    }

    function fields_html() {

        $value = get_option( 'sim_internet', '' );

        echo '<input type="text" id="sim_internet" name="sim_internet" value="' . $value . '" />';

    }

}

$new_general_dsimtravel = new new_general_simtravel();



class new_general_asig{

    function new_general_asig( ) {

        add_filter( 'admin_init' , array( &$this , 'register_fields' ) );

    }

    function register_fields() {

        register_setting( 'general', 'asig', 'esc_attr' );

        add_settings_field('asig', '<label for="asig">'.__('Insurance 1 (euro/per day)' , 'mail' ).'</label>' , array(&$this, 'fields_html') , 'general' );

    }

    function fields_html() {

        $value = get_option( 'asig', '' );

        echo '<input type="text" id="asig" name="asig" value="' . $value . '" />';

    }

}

$new_general_asig = new new_general_asig();



class new_general_asig_text{

    function new_general_asig_text( ) {

        add_filter( 'admin_init' , array( &$this , 'register_fields' ) );

    }

    function register_fields() {

        register_setting( 'general', 'asig_text', 'esc_attr' );

        add_settings_field('asig_text', '<label for="asig_text">'.__('Insurance 1: Name en' , 'mail' ).'</label>' , array(&$this, 'fields_html') , 'general' );

    }

    function fields_html() {

        $value = get_option( 'asig_text', '' );

        echo '<input type="text" id="asig_text" name="asig_text" value="' . $value . '" />';

    }

}

$new_general_asig_text = new new_general_asig_text();



class new_general_asig_text_ru{

    function new_general_asig_text_ru( ) {

        add_filter( 'admin_init' , array( &$this , 'register_fields' ) );

    }

    function register_fields() {

        register_setting( 'general', 'asig_text_ru', 'esc_attr' );

        add_settings_field('asig_text_ru', '<label for="asig_text_ru">'.__('Страховка 1: Название' , 'mail' ).'</label>' , array(&$this, 'fields_html') , 'general' );

    }

    function fields_html() {

        $value = get_option( 'asig_text_ru', '' );

        echo '<input type="text" id="asig_text_ru" name="asig_text_ru" value="' . $value . '" />';

    }

}

$new_general_asig_text_ru = new new_general_asig_text_ru();





class new_general_asig_one{

    function new_general_asig_one( ) {

        add_filter( 'admin_init' , array( &$this , 'register_fields' ) );

    }

    function register_fields() {

        register_setting( 'general', 'asig_two', 'esc_attr' );

        add_settings_field('asig_two', '<label for="asig_two">'.__('Insurance 2 (euro/per day)' , 'mail' ).'</label>' , array(&$this, 'fields_html') , 'general' );

    }

    function fields_html() {

        $value = get_option( 'asig_two', '' );

        echo '<input type="text" id="asig_two" name="asig_two" value="' . $value . '" />';

    }

}

$new_general_asig_one = new new_general_asig_one();



class new_general_asig_text2{

    function new_general_asig_text2( ) {

        add_filter( 'admin_init' , array( &$this , 'register_fields' ) );

    }

    function register_fields() {

        register_setting( 'general', 'asig_text2', 'esc_attr' );

        add_settings_field('asig_text2', '<label for="asig_text2">'.__('Insurance 2: Name' , 'mail' ).'</label>' , array(&$this, 'fields_html') , 'general' );

    }

    function fields_html() {

        $value = get_option( 'asig_text2', '' );

        echo '<input type="text" id="asig_text2" name="asig_text2" value="' . $value . '" />';

    }

}

$new_general_asig_text2 = new new_general_asig_text2();



class new_general_asig_text2_ru{

    function new_general_asig_text2_ru( ) {

        add_filter( 'admin_init' , array( &$this , 'register_fields' ) );

    }

    function register_fields() {

        register_setting( 'general', 'asig_text2_ru', 'esc_attr' );

        add_settings_field('asig_text2_ru', '<label for="asig_text2_ru">'.__('Страховка 2: Название' , 'mail' ).'</label>' , array(&$this, 'fields_html') , 'general' );

    }

    function fields_html() {

        $value = get_option( 'asig_text2_ru', '' );

        echo '<input type="text" id="asig_text2_ru" name="asig_text2_ru" value="' . $value . '" />';

    }

}

$new_general_asig_text2_ru = new new_general_asig_text2_ru();



class new_general_asig_three{

    function new_general_asig_three( ) {

        add_filter( 'admin_init' , array( &$this , 'register_fields' ) );

    }

    function register_fields() {

        register_setting( 'general', 'asig_three', 'esc_attr' );

        add_settings_field('asig_three', '<label for="asig_three">'.__('Insurance 3 (euro/per day)' , 'mail' ).'</label>' , array(&$this, 'fields_html') , 'general' );

    }

    function fields_html() {

        $value = get_option( 'asig_three', '' );

        echo '<input type="text" id="asig_three" name="asig_three" value="' . $value . '" />';

    }

}

$new_general_asig_three = new new_general_asig_three();



class new_general_asig_text3{

    function new_general_asig_text3( ) {

        add_filter( 'admin_init' , array( &$this , 'register_fields' ) );

    }

    function register_fields() {

        register_setting( 'general', 'asig_text3', 'esc_attr' );

        add_settings_field('asig_text3', '<label for="asig_text3">'.__('Insurance 3: Name' , 'mail' ).'</label>' , array(&$this, 'fields_html') , 'general' );

    }

    function fields_html() {

        $value = get_option( 'asig_text3', '' );

        echo '<input type="text" id="asig_text3" name="asig_text3" value="' . $value . '" />';

    }

}

$new_general_asig_text3 = new new_general_asig_text3();



class new_general_asig_text3_ru{

    function new_general_asig_text3_ru( ) {

        add_filter( 'admin_init' , array( &$this , 'register_fields' ) );

    }

    function register_fields() {

        register_setting( 'general', 'asig_text3_ru', 'esc_attr' );

        add_settings_field('asig_text3_ru', '<label for="asig_text3_ru">'.__('Страховка 3: Название' , 'mail' ).'</label>' , array(&$this, 'fields_html') , 'general' );

    }

    function fields_html() {

        $value = get_option( 'asig_text3_ru', '' );

        echo '<input type="text" id="asig_text3_ru" name="asig_text3_ru" value="' . $value . '" />';

    }

}

$new_general_asig_text3_ru = new new_general_asig_text3_ru();





if (isset($_POST['action']) && $_POST['action'] == 'send_order') {



    $src = wp_get_attachment_image_src( get_post_thumbnail_id($_POST['id_post'], 'medium'), false, '' );

    $msg = '';

    $msg .= '<table width="550" cellpadding="0" cellspacing="0">';

    $msg .= '<tr><td>';

    $msg .= '<table width="550" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF" style="border:6px solid #f7f7f7;">';

    $msg .= '<tr><td height="140" align="center">';

    $msg .= '<h2>'.$_POST['last_name'].' '.$_POST['first_name'].'</h2><br><b>'.__('Thank you!This is only a request! Your reservation will be confirmed with a unique number in the next 24hours.').'</b></td></tr>';

    $msg .= '<tr><td>';

    $msg .= '<table width="524" align="center" style="border:1px solid #e7e7e7; border-bottom:none" cellpadding="0" cellspacing="0">';

    $msg .= '<tr><td align="center"><h1 style="padding:8px 0; margin:0; background-color:#f2f2f2"><font color="#209719" face="Arial, Helvetica, sans-serif" size="4">'.__('Your Reservation').':</h1></td></tr></table>';

    $msg .= '<table width="524" bgcolor="#f7f7f7" align="center" style="border:1px solid #e7e7e7; padding:10px 20px" cellpadding="0" cellspacing="0"><tr>';

    //  $msg .= '<td><img src="'.$info_list['car_photo'].'" width="218" height="146" /></td>';

    $msg .= '<td align="left"><ul>';

    $msg .= '<li>'.__('Car').': <b>'.$_POST['car_model'].'</b> '.__('or similar').'</li>';

    //  $msg .= '<li>Fly From: '.$info_list['fly_from'].'</li>';

    $msg .= '<li>'.__('Pick Up').':  '.$_POST['where_to'].'</li>';

    $msg .= '<li>'.__('Drop-off').':  '.$_POST['where_off'].'</li>';

    $msg .= '<li>'.__('from').': '.$_POST['from_date'].' - '.$_POST['from_hour_list'].'</li>';

    $msg .= '<li>'.__('to').': '.$_POST['to_date'].' - '.$_POST['to_hour_list'].'</li>';

    $msg .= '<li>'.__('Additional').': <ul>';



    if(isset($_POST['gps_check'])) {

        $msg .= '<li>'.__('GPS').'</li>';

    }

    if(isset($_POST['child_check'])) {

        $msg .= '<li>'.__('Child seat').'</li>';

    }

    if(isset($_POST['2driver_check'])) {

        $msg .= '<li>'.__('Two drivers').'</li>';

    }

    if(isset($_POST['sim_internet'])) {

        $msg .= '<li>'.__('Sim with internet').'</li>';

    }

    $msg .= '</ul></li>';



    $my_current_lang = apply_filters( 'wpml_current_language', NULL );

    if($my_current_lang == 'en') {

        $asig = get_option('asig_text');

        $asig2 = get_option('asig_text2');

        $asig3 = get_option('asig_text3');

    } else if($my_current_lang == 'ru') {

        $asig = get_option('asig_text_ru');

        $asig2 = get_option('asig_text2_ru');

        $asig3 = get_option('asig_text3_ru');

    }



    if($_POST['asig']== 'rca') {

        $msg .= '<li>'.__('Insurance').': '.$asig.'</li>';

    }elseif($_POST['asig']== 'casco'){

        $msg .= '<li>'.__('Insurance').': '.$asig2.'</li>';

    }elseif($_POST['asig']== 'cdw'){

        $msg .= '<li>'.__('Insurance').': '.$asig3.'</li>';

    } elseif($_POST['asig']== 0){

        echo '-';

    }



    $msg .= '<li>'.__('Phone').': '.$_POST['phone'].'</li>';

    $msg .= '<li>'.__('E-mail').': '.$_POST['email'].'</li>';

    $msg .= '<li style="color:#209719">'.__('Price').' '.__('per day').': '.$_POST['car_price_per_day'].' &euro;</li>';

    $msg .= '<li style="color:#209719">'.__('Days').': '.$_POST['total_days'].'</li>';

    $msg .= '<li style="color:#209719">'.__('Price').' '.__('Night time').': '.$_POST['night_time'].' &euro;</li>';

    $total_price = ($_POST['car_price_per_day']*$_POST['total_days'])+$_POST['night_time'];

    $msg .= '<li><h3 style="color:#209719">'.__('Total cost').': '.$total_price.' &euro;</h3></li>';

    $msg .= '</ul></td>';

    $msg .= '</tr><tr><td><img src="'.$src[0].'" alt="" width="250px"/></td></tr></table></td></tr>';

    $msg .= '<tr><td style="padding-left:20px; text-align: center"><br>

                    	<span style="font-size: 12px">Rent a Car Venezia +39 344 506 8823<br>

                        '.$_SERVER['HTTP_HOST'].'<br></span>

                        <span style="font-size: 12px">Airport, Viale Galileo Galilei, 30/1, 30173 Venice VE, Italy</span>

                        <br><br>

                    </td></tr>';

    $msg .= '</table></td></tr></table> ';





    function set_html_content_type() {

        return 'text/html';

    }

    add_filter( 'wp_mail_content_type', 'set_html_content_type' );



    $subject = __('New order')." - ".$_POST['car_model'];

    $email = $_POST['email'];

    $headers[] = 'From: Rent a car Venezia <info@rentacarvenezia.it>';

    // wp_mail(get_option('admin_email'), $subject, $msg, $headers);

    wp_mail('info@rentacarvenezia.it', $subject, $msg, $headers);

    wp_mail($email, $subject, $msg, $headers);



    remove_filter( 'wp_mail_content_type', 'set_html_content_type' );

    wp_redirect(  get_the_permalink(135).'/?total_day='.$_POST['total_days'].'&night_time='.$_POST['night_time'].'&car_price_per_day='.$_POST['car_price_per_day'].'&$total_price='.$total_price.'&car='.$_POST['car_model'].'&where_to='.$_POST['where_to'].'&where_off='.$_POST['where_off'].'&from='.$_POST['from_date'].' - '.$_POST['from_hour_list'].'&to='.$_POST['to_date'].' - '.$_POST['to_hour_list'].'&gps_check='.$_POST['gps_check'].'&child_check='.$_POST['child_check'].'&2driver_check='.$_POST['2driver_check'].'&sim_internet='.$_POST['sim_internet'].'&asig='.$_POST['asig'].'&id='.$_POST['id_post'] );

    //header("Location: //".$_SERVER['HTTP_HOST']."/?success");

    exit();





}


