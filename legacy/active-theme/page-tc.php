<?php

/*
    Template Name: TC - Popup
*/

get_header(); ?>
    <div class="main-container">
        <div class="content-area">
            <div class="middle-align content_sidebar">
                <div class="site-main" id="sitemain">
                    <?php while ( have_posts() ) : the_post(); ?>
                        <?php get_template_part( 'content', 'page' ); ?>

                        <?php

                        $location = get_field('coordinates');
                        $location_2 = get_field('coordinates_2');
                        if( !empty($location) ):
                            ?>
                            <script src="https://maps.googleapis.com/maps/api/js"></script>

                            <script>
                                google.maps.event.addDomListener(window, 'load', init);
                                function init() {
                                    var mapOptions = {
                                        zoom:12,
                                        center: new google.maps.LatLng(<?=$location?>),
                                    };
                                    var mapElement = document.getElementById('map');
                                    var map = new google.maps.Map(mapElement, mapOptions);
                                    var image = '<?php echo esc_url( get_template_directory_uri() ); ?>/images/pin.png';
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
                            </script>
                            <div id="map" style="height: 480px"></div>
                            <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAX93ToVPpdQVpsbjr_N0ww8b_1j0xLOWg&callback=initMap"
                                    async defer></script>
                        <?php endif; ?>

                    <?php endwhile; // end of the loop. ?>

                </div>
                <?php get_sidebar(); ?>
                <div class="clear"></div>
            </div>
        </div>
    </div>

<?php get_footer(); ?>