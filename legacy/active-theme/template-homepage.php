<?php
/**
Template name: Home page

 */
get_header(); ?>
    <div class="main-container">
    <div class="content-area">
        <div class="middle-align content_sidebar">
            <div class="site-main" id="sitemain">
                <?php while ( have_posts() ) : the_post(); ?>
                    <div class="top_text"><? the_field( "text_header" );?></div>
                    <div class="content_text"><? the_content()?></div>
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
                            the_post(); $i++; ?>
                            <div class="one_car">
                                <div class="border-car">
                                    <div class="blog-post-thumb">
                                            <span class="icon_photo"></span>
                                            <? the_post_thumbnail('homepage-thumb') ?>
                                    </div>
                                    <h4><? the_title()?></h4>
                                    <div class="prices">
                                        <div class="pr_1"><?=get_post_meta(get_the_ID(), 'price_1_days_1', true)?>-<?=get_post_meta(get_the_ID(), 'price_1_days_2', true)?><?=_e('days')?> - <span class="lavel-evro"><b>&euro;<?=get_post_meta(get_the_ID(), 'price', true)?></b></span>/<?=_e('day')?></div>
                                        <div class="pr_1"><?=get_post_meta(get_the_ID(), 'price_2_days_1', true)?>-<?=get_post_meta(get_the_ID(), 'price_2_days_2', true)?><?=_e('days')?> - <span class="lavel-evro">&euro;<?=get_post_meta(get_the_ID(), 'price2', true)?></span>/<?=_e('day')?></div>
                                        <div class="pr_1"><?=get_post_meta(get_the_ID(), 'price_3_days_1', true)?>-<?=get_post_meta(get_the_ID(), 'price_3_days_2', true)?><?=_e('days')?> - <span class="lavel-evro">&euro;<?=get_post_meta(get_the_ID(), 'price3', true)?></span>/<?=_e('day')?></div>
                                        <div class="pr_1"><?=(get_post_meta(get_the_ID(), 'price_3_days_2', true)+1)?>+ <?=_e('days')?> - <span class="lavel-evro">&euro;<?=get_post_meta(get_the_ID(), 'price4', true)?></span>/<?=_e('day')?></div>
                                    </div><div class="clear"></div>
                                    <div class="aditional_info">
                                        <div class="lb_1 ln_1"><?=_e('Gearbox')?>: <?
                                            if(get_post_meta(get_the_ID(), 'gearbox', true) == 'Manual') {
                                                echo  __('Manual');
                                            } else {
                                                echo  __('Automatic');
                                            }
                                            ?></div>
                                        <div class="lb_1 ln_2"><?=_e('Air conditioning')?>: <?=(get_post_meta(get_the_ID(), 'air_conditioning', true) == 0 ? _e('no') : _e('yes'))?></div>
                                        <div class="lb_1 ln_3"><?=_e('Max passagers')?>: <?=get_post_meta(get_the_ID(), 'max_passagers', true)?></div>
                                        <div class="lb_1 ln_4"><?=_e('Doors')?>: <?=get_post_meta(get_the_ID(), 'doors', true)?></div>
                                    </div><div class="clear"></div>
                                    <a href="<?php the_permalink();?>#<?php the_ID(); ?>" data-fancybox-type="iframe" class="various btn_reserv" id="<?php the_ID(); ?>" data-class="<?php echo get_post_type( get_the_ID() ); ?>"><?=_e('Reservation')?></a>

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
        <div class="bottom_section">
            <?php while ( have_posts() ) : the_post(); ?>

                <div class="content-area title_h2">
                        <h2><?=_e('Find us here')?></h2>
                </div>
<style>
.videoWrapper {
margin-bottom: 10px;
}

.videoWrapper iframe {
width: 100%;
height: 400px;
}
</style>
                '<div class="videoWrapper">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2788.812651150502!2d12.201332826942553!3d45.65458397071483!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47793795130b81ff%3A0xa0c0e4422990028f!2sRent%20a%20car%20Treviso%20Airport!5e0!3m2!1sen!2s!4v1709818446323!5m2!1sen!2s" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
                <?php
/*
                $location = get_field('coordinates');
                $location_2 = get_field('coordinates_2');
                if( !empty($location) ):
                    ?>
                <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAX93ToVPpdQVpsbjr_N0ww8b_1j0xLOWg&callback=init"
                        async defer></script>
                <script >
                    function init() {
                        google.maps.event.addDomListener(window, 'load', init);

                        function init() {
                            var mapOptions = {
                                zoom: 12,
                                center: new google.maps.LatLng(<?=$location?>),
                            };

                            var mapElement = document.getElementById('map');
                            var map = new google.maps.Map(mapElement, mapOptions);
                            var image = '<?php echo esc_url(get_template_directory_uri()); ?>/images/pin.png';
                            var marker = new google.maps.Marker({
                                position: new google.maps.LatLng(<?=$location?>),
                                map: map,
                                icon: image
                            });
                            var marker = new google.maps.Marker({
                                position: new google.maps.LatLng(<?=$location_2?>),
                                map: map,
                                icon: image
                            });


                        }
                    }
                </script>
                <div id="map" style="height: 480px"></div>

                <?php endif; 
*/
                ?>
                <div class="section_b">
                    <div class="content-area">
                        <? the_field( "text_seo" );?>
                    </div>
                </div>

            <?php endwhile; // end of the loop. ?>


        </div>
    </div>
<?php get_footer(); ?>