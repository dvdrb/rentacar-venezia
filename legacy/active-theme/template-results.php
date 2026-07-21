<?php
/**
Template name: Results page

 */
get_header();
//print_r($_POST);
$date = explode('/', $_POST['from_date_sidebar']);
$date_to = explode('/', $_POST['to_date']);
$from_date_sidebar =  $date[2].'/'.$date[1].'/'.$date[0];
$to_date = $date_to[2].'/'.$date_to[1].'/'.$date_to[0];
$datetime1 = new DateTime($from_date_sidebar);
$datetime2 = new DateTime($to_date);
$interval = $datetime1->diff($datetime2);
$diff_days = $interval->format('%a');

$f_hm = explode('.', $_POST['from_hour']);
$t_hm = explode('.', $_POST['to_hour']);

if ($f_hm[0] < $t_hm[0] || ($f_hm[0] == $t_hm[0] && $f_hm[1] < $t_hm[1])) {
    $diff_days = $diff_days + 1;
}
$night = 0;
if(($_POST['to_hour'] <= '8.00' && $_POST['to_hour'] >= '0.00' ) || ($_POST['to_hour'] >= '20.00' && $_POST['to_hour'] <= '23.45') ) {
    $night = 20;
} else {
    $night = 0;
}
$night_from = 0;
if(($_POST['from_hour'] <= '8.00' && $_POST['from_hour'] >= '0.00' ) || ($_POST['from_hour'] >= '20.00' && $_POST['from_hour'] <= '23.45') ) {
    $night_from = 20;
} else {
    $night_from = 0;
}

?>
    <div class="main-container">
        <div class="content-area">
            <div class="middle-align content_sidebar">
                <div class="site-main" id="sitemain">
                    <?php while ( have_posts() ) : the_post(); ?>
                        <h1 class="entry-title"><? the_title()?></h1>
                        <div class="entry-content"><? the_content()?></div>
                        <b>Days: </b><?=$diff_days?>
                    <?php endwhile; // end of the loop. ?>
                    <div class="clear"></div>
                    <div class="row">
                        <div class="section_cars">
                            <?php
                            $args = array(
                                'posts_per_page' => -1,
                                'post_type' => 'cars'
                            );
                            query_posts( $args );
                            if( have_posts() ){
                                $i=0; while( have_posts() ){
                                    the_post(); $i++;

                                    if ($diff_days <= get_post_meta(get_the_ID(), 'price_1_days_2', true)) {
                                        $pricePerDay = get_post_meta(get_the_ID(), 'price', true);
                                    } elseif ($diff_days >= get_post_meta(get_the_ID(), 'price_2_days_1', true) && $diff_days <= get_post_meta(get_the_ID(), 'price_2_days_2', true)) {
                                        $pricePerDay = get_post_meta(get_the_ID(), 'price2', true);
                                    } elseif ($diff_days >= get_post_meta(get_the_ID(), 'price_3_days_1', true) && $diff_days <= get_post_meta(get_the_ID(), 'price_3_days_2', true)) {
                                        $pricePerDay = get_post_meta(get_the_ID(), 'price3', true);
                                    } else {
                                        $pricePerDay = get_post_meta(get_the_ID(), 'price4', true);
                                    }

                                    $totalprice = ($pricePerDay*$diff_days)+$night_from+$night;

                                 ?><div class="one_car">
                                    <div class="border-car">
                                        <div class="blog-post-thumb">
                                            <span class="icon_photo"></span>
                                            <? the_post_thumbnail('homepage-thumb') ?>
                                        </div>
                                        <h4><? the_title()?></h4>
                                        <div class="prices">
                                            <div class="pr_1"><?=_e('per day')?>:</div>
                                            <div class="pr_1"><span class="lavel-evro">&euro; <?=$pricePerDay?></span></div>
                                            <div class="pr_1"><?=_e('Total cost')?>:</div>
                                            <div class="pr_1"> <span class="lavel-evro">&euro; <?=$totalprice?></span></div>
                                        </div><div class="clear"></div>
                                        <div class="aditional_info">
                                            <div class="lb_1 ln_1"><?=_e('Gearbox')?>: <?  if(get_post_meta(get_the_ID(), 'gearbox', true) == 'Manual') {
                                                echo  __('Manual');
                                                } else {
                                                echo  __('Automatic');
                                                }
                                                ?></div>
                                            <div class="lb_1 ln_2"><?=_e('Air conditioning')?>: <?=(get_post_meta(get_the_ID(), 'air_conditioning', true) == 0 ? _e('no') : _e('yes'))?></div>
                                            <div class="lb_1 ln_3"><?=_e('Max passagers')?>: <?=get_post_meta(get_the_ID(), 'max_passagers', true)?></div>
                                            <div class="lb_1 ln_4"><?=_e('Doors')?>: <?=get_post_meta(get_the_ID(), 'doors', true)?></div>
                                        </div><div class="clear"></div>
                                        <a href="<?php the_permalink();?>?from_date=<?=$_POST['from_date_sidebar']?>&to_date=<?=$_POST['to_date']?>&from_hour_list=<?=$_POST['from_hour']?>&to_hour_list=<?=$_POST['to_hour']?>#<?php the_ID();?>" data-fancybox-type="iframe" class="various btn_reserv" id="<?php the_ID(); ?>" data-class="<?php echo get_post_type( get_the_ID() ); ?>"><?=_e('Order Now')?></a>

                                        </div>
                                    </div>

                                    <?=($i%3==0 ? '<div class="visible_md"></div>' : '')?>
                                    <?=($i%2==0 ? '<div class="visible_sm"></div>' : '')?>
                                <? }
                                wp_reset_query();
                            } else {

                            }
                            ?>
                        </div>
                    </div>
                    <div class="clear"></div>
                </div>
                <?php get_sidebar(); ?>
                <div class="clear"></div>
            </div>
        </div>
    </div>

<?php get_footer(); ?>