<?php
$apost = $_GET["type"];
preg_replace('/_/', 'html', preg_replace('/%/', 'html', htmlentities(mysqli_real_escape_string($apost))));
?><?php if($apost == 'iframe') { ?>
<?php get_header('single'); ?>
    <? if (have_posts()) : while (have_posts()) : the_post();

        $night = 0;
        if(($_GET['to_hour_list'] <= '8.00' && $_GET['to_hour_list'] >= '0.00' ) || ($_GET['to_hour_list'] >= '20.00' && $_GET['to_hour_list'] <= '23.45') ) {
            $night = 25;
        } else {
            $night = 0;
        }
        $night_from = 0;
        if(($_GET['from_hour_list'] <= '8.00' && $_GET['from_hour_list'] >= '0.00' ) || ($_GET['from_hour_list'] >= '20.00' && $_GET['from_hour_list'] <= '23.45') ) {
            $night_from = 25;
        } else {
            $night_from = 0;
        }

        $totalprice = '';
        $diff_days = '';
        $pricePerDay = '';
        if(isset($_GET['from_hour_list'])) {
//print_r( $_GET);
            $date = explode('/', $_GET['from_date']);
            $date_to = explode('/', $_GET['to_date']);
            $from_date_sidebar =  $date[2].'/'.$date[1].'/'.$date[0];
            $to_date = $date_to[2].'/'.$date_to[1].'/'.$date_to[0];
            $datetime1 = new DateTime($from_date_sidebar);
            $datetime2 = new DateTime($to_date);
            $interval = $datetime1->diff($datetime2);
            $diff_days = $interval->format('%a');

            $f_hm = explode('.', $_GET['from_hour_list']);
            $t_hm = explode('.', $_GET['to_hour_list']);

            if ($f_hm[0] < $t_hm[0] || ($f_hm[0] == $t_hm[0] && $f_hm[1] < $t_hm[1])) {
                $diff_days = $diff_days + 1;
            }

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


        }
    ?>
        <style>body{float: left}</style>
        <div class="popup-car">
            <div class="col-2">
                <div class="cont_left">
                    <h2><?=_e('BOOK YOUR CAR TODAY!')?></h2>
                    <h3><? the_title()?></h3><span class="info_car_icon"><i class="fa fa-question"></i><div class="content_info">
                        <?
                        if(get_locale() == 'ru_RU') {
                             echo   get_post_meta(23,'text_field_car', true);
                        } elseif(get_locale() == 'en_US') {
                            echo   get_post_meta(20,'text_field_car', true);
                        }
                      ?></div></span>
                    <div id="slider" class="flexslider">
                        <ul class="slides">
                            <li><? the_post_thumbnail('homepage-thumb') ?></li>
                            <?php if( have_rows('gallery') ): ?>
                                <?php while( have_rows('gallery') ): the_row();
                                  //  $image = get_sub_field('image');
                                    $image = wp_get_attachment_image_src(get_sub_field('image'), 'homepage-thumb');

                                    ?>
                                    <li><img src="<?php echo $image[0]; ?>" alt="" /></li>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <div id="carousel" class="flexslider">
                        <ul class="slides">
                            <li><? the_post_thumbnail('homepage-thumb') ?></li>
                            <?php if( have_rows('gallery') ): ?>
                                <?php while( have_rows('gallery') ): the_row();
                                   // $image = get_sub_field('image');  $image['url'];
                                    $image = wp_get_attachment_image_src(get_sub_field('image'), 'homepage-thumb');
                                    ?>
                                    <li><img src="<?php echo $image[0]; ?>" alt="" /></li>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </ul>
                    </div>

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
                </div>
            </div>
            <div class="col-2">
                <div class="mini_select">
                    <form method="post" id="ajax_form">
<!--                        onsubmit="return validateForm2(this)"-->
                        <div class="value_field fl_left lf_p l_box_2">
                            <input type="hidden" name="price_1_days_1" value="<?=get_post_meta(get_the_ID(), 'price_1_days_1', true)?>" />
                            <input type="hidden" name="price_1_days_2" value="<?=get_post_meta(get_the_ID(), 'price_1_days_2', true)?>" />
                            <input type="hidden" name="price_2_days_1" value="<?=get_post_meta(get_the_ID(), 'price_2_days_1', true)?>" />
                            <input type="hidden" name="price_2_days_2" value="<?=get_post_meta(get_the_ID(), 'price_2_days_2', true)?>" />
                            <input type="hidden" name="price_3_days_1" value="<?=get_post_meta(get_the_ID(), 'price_3_days_1', true)?>" />
                            <input type="hidden" name="price_3_days_2" value="<?=get_post_meta(get_the_ID(), 'price_3_days_2', true)?>" />

                            <input type="hidden" name="price_1" value="<?=get_post_meta(get_the_ID(), 'price', true)?>" />
                            <input type="hidden" name="price_2" value="<?=get_post_meta(get_the_ID(), 'price2', true)?>" />
                            <input type="hidden" name="price_3" value="<?=get_post_meta(get_the_ID(), 'price3', true)?>" />
                            <input type="hidden" name="price_4" value="<?=get_post_meta(get_the_ID(), 'price4', true)?>" />
                            <label><?=_e('from')?></label>
                            <div class="from date_cal" bm="bm">
                               <input id="from_datepicker_list" name="from_date" value="<?=(isset($_GET['from_date']) ? $_GET['from_date'] : date('d/m/Y', mktime(0, 0, 0, date("m") , date("d"), date("Y"))))?>" bb="bb"  type="text" readonly="readonly">
                            </div>
                            <select name="from_hour_list">
                                <? //+1
                                $time_default_from = (isset($_GET['from_hour_list']) ? $_GET['from_hour_list'] : '10.00');

                                for($i = 0; $i<=23; $i++)
                                    for($j = 0; $j<=45; $j+=15)
                                    {
                                        if($j==0)
                                            $time = $i.'.00';
                                        else
                                            $time = $i.'.'.$j;
                                        if($time ==$time_default_from) // _GET
                                            echo'<option selected="selected">'.$time.'</option>';
                                        else
                                            echo"<option>".$time."</option>";
                                    }
                                ?></select>
                        </div>
                        <div class="value_field fl_left ri_p l_box_2">
                            <label><?=_e('to')?></label>
                            <?php $hour = 60*60; $im = time()+$hour*24*8;?>
                            <div class="to date_cal" bm="mb">
                                <input type="text" id="to_datepicker_list" name="to_date" value="<?=(isset($_GET['to_date']) ? $_GET['to_date'] : date('d/m/Y', time()+604800))?>" readonly="readonly">
                            </div>
                            <select name="to_hour_list">
                                <? //604800
                                $time_default = (isset($_GET['to_hour_list']) ? $_GET['to_hour_list'] : '10.00');
                                for($i = 0; $i<=23; $i++)
                                    for($j = 0; $j<=45; $j+=15)
                                    {
                                        if($j==0)
                                            $time = $i.'.00';
                                        else
                                            $time = $i.'.'.$j;
                                        if($time == $time_default)  // _GET
                                            echo'<option selected="selected">'.$time.'</option>';
                                        else
                                            echo"<option>".$time."</option>";
                                    }
                                ?></select>
                        </div>
                        <div class="clear"></div>
                        <div class="value_field fl_left lf_p">
                            <input type="text" name="last_name"  placeholder="<?=_e('Last Name')?> *" required>
                        </div>
                        <div class="value_field fl_left ri_p">
                            <input type="text" name="first_name" placeholder="<?=_e('First Name')?> *" required>
                        </div>
                        <div class="clear"></div>
                        <div class="value_field fl_left lf_p">
                            <input type="tel" name="phone"  placeholder="<?=_e('Phone')?> *" required>
                        </div>
                        <div class="value_field fl_left ri_p">
                            <input type="email" name="email" placeholder="<?=_e('E-mail')?> *" required>
                        </div>
                        <div class="clear"></div>
                        <div class="value_field fl_left lf_p">
                            <label><?=_e('Pick Up')?> *</label>
                            <select name="where_to">
                                <option value="Airport-Venice-Marco-Polo">Airport Venice Marco Polo</option>
                                <option value="Treviso-Airport-Arrivals">Treviso Airport Arrivals</option>
                                <option value="Treviso-Office">Treviso Office</option>
                            </select>
                        </div>
                        <div class="value_field fl_left">
                            <label><?=_e('Drop-off')?> *</label>
                            <select name="where_off">
                                <option value="Airport-Venice-Marco-Polo">Airport Venice Marco Polo</option>
                                <option value="Treviso-Airport-Arrivals">Treviso Airport Arrivals</option>
                                <option value="Treviso-Office">Treviso Office</option>
                            </select>
                        </div>
                        <div class="value_field">
                            <label><?=_e('Insurance:')?> *</label>
                            <select class="asig" name="asig" required> <!-- onchange="calcPrice(this,<?=$pricePerDay;?>)" -->
                                <option data-price-asig="0" value=""><?=_e('Select')?></option>
                                <?
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
                                ?>
                                <option value="rca" data-price-asig="<?=get_option('asig')?>"><?=$asig?> - <?=get_option('asig')?> &euro;/<?=_e('day')?></option>
                                <option value="casco" data-price-asig=<?=get_option('asig_two')?>><?=$asig2?> - <?=get_option('asig_two')?> &euro;/<?=_e('day')?></option>
                                <option value="cdw" data-price-asig="<?=get_option('asig_three')?>"><?=$asig3?> - <?=get_option('asig_three')?> &euro;/<?=_e('day')?></option>
                            </select>
                        </div>
                        <div class="clear"></div>
                        <div class="value_field">
                            <label class="red-color"><?=_e('Add extra:')?></label>
                        </div>
                        <div class="value_field fl_left lf_p">
                            <div class="checkbox-input"><label><input type="checkbox" name="gps_check" value="GPS" style="display: none;"><span class="checkbox"><span><i class="fa fa-check"></i></span></span>  <?=_e('GPS')?> (+<?=get_option('extra_gps')?>€/<?=_e('per day')?>) </label></div>
                            <div class="checkbox-input"><label><input type="checkbox" name="child_check" value="Child seat" style="display: none;"><span class="checkbox"><span><i class="fa fa-check"></i></span></span>  <?=_e('Child seat')?> (+<?=get_option('child_check')?>€/<?=_e('per day')?>) </label></div>
                        </div>
                         <div class="value_field fl_left">
                            <div class="checkbox-input"><label><input type="checkbox" name="2driver_check" value="Two drivers" style="display: none;"><span class="checkbox"><span><i class="fa fa-check"></i></span></span>  <?=_e('Two drivers')?> (+<?=get_option('2driver_check')?>€/<?=_e('per day')?>) </label></div>
                            <div class="checkbox-input"><label><input type="checkbox" name="sim_internet" value="Sim with internet" style="display: none;"><span class="checkbox"><span><i class="fa fa-check"></i></span></span>  <?=_e('Sim with internet')?> (+<?=get_option('sim_internet')?>€/<?=_e('per day')?>) </label></div>
                        </div>
                        <div class="clear"></div>
                        <div class="value_field">
                            <label><?=_e('Price')?></label>
                            <div class="price_box l_box_2_root">
                                <div class="line_1">
                                    <span><?=_e('Days')?></span>
                                    <span>&euro; <?=_e('per day')?></span>
                                    <span><?=_e('Night time')?></span>
                                    <span><?=_e('Total cost')?></span>
                                </div>
                                <div class="line_1">
                                    <span id="total_days"><?=(empty($diff_days)  ? '7' : $diff_days)?></span>
                                    <span id="price_per_day"><?=(empty($pricePerDay) ? get_post_meta(get_the_ID(), 'price2', true): $pricePerDay )?> &euro;</span>
                                    <span id="night_time"><?  echo $night+$night_from;   ?> &euro;</span>
                                    <span id="total_price"><?=(empty($totalprice)  ?  7*get_post_meta(get_the_ID(), 'price2', true)  : $totalprice)?>  &euro;</span>
                                </div>
                            </div>
                            <div class="clear"></div>
                        </div>
                        <div class="value_field right-text">
                            <div class="radio-input"><label><input type="radio" name="terms" id="terms" value="yes" style="display: none;"><span class="checkbox"><span><i class="fa fa-check"></i></span></span><a><?=_e('Accept Terms and Conditions')?></a></label></div>
                        </div>

                        <input type="hidden" name="night_time" value="<? echo $night+$night_from; ?>"/>
                        <input type="hidden" name="car_price" value="<?=(empty($totalprice)  ?  7*get_post_meta(get_the_ID(), 'price2', true)  : $totalprice)?>"/>
                        <input type="hidden" name="car_price_per_day" value="<?=(empty($pricePerDay) ? get_post_meta(get_the_ID(), 'price2', true): $pricePerDay )?>"/>
                        <input type="hidden" name="total_days" value="<?=(empty($diff_days)  ? '7' : $diff_days)?>"/>
                        <input type="hidden" name="car_model" value="<?=get_the_title()?>"/>
                        <input type="hidden" name="id_post" value="<?=get_the_ID()?>"/>

                        <input type="hidden" name="action" value="send_order">
                        <input type="submit" id="book" class="search_button" value="<?=_e('Confirm')?>" name="search"/>


                    </form>
                </div>

            </div>
            <div class="clear"></div>
        </div>
    <?php endwhile; endif; ?>
<script type="text/javascript">
    $ = jQuery;
    $(window).load(function(){
        $('#carousel').flexslider({
            animation: "slide",
            controlNav: false,
            animationLoop: false,
            slideshow: false,
            itemWidth: 138,
            itemMargin: 5,
            asNavFor: '#slider'
        });

        $('#slider').flexslider({
            animation: "slide",
            controlNav: false,
            animationLoop: false,
            slideshow: false,
            sync: "#carousel",
            start: function(slider){
                $('body').removeClass('loading');
            }
        });
    });

    $(document).ready(function() {

        $('#ajax_form').submit(function (e) {
            if(!$('#terms').is(':checked'))
            {
                alert("<?=_e('Do you accept terms and conditions?')?>");
                e.preventDefault();
                return false;
            }
            else  return true;
        })



        $('.checkbox-input input').change(function() {
            dateDifference($('[name="from_date"]'));
        });


//        $('.l_box_2_root').each(function () {
//            var startDays = $(this).find('#total_days').text();
//            startDays = parseInt(startDays);
//            var startTotalSum = $(this).find('#price_per_day').text();
//            startTotalSum = parseInt(startTotalSum);
//            $(this).find('#total_price').text((startDays * startTotalSum) + ' €');
//        });

        $('.asig').change(function() {
            dateDifference($('[name="from_date"]'));
        });

            /*  DATE DEFF   */

//        $('#from_datepicker_list').datepicker({
//                showOn: 'both',
//                buttonImageOnly: true,
//                buttonImage: '/wp-content/images/calendar.png',
//                firstDay: 1,
//                maxDate: '+2Y',
//                dateFormat:"dd/mm/yy",
//                minDate:0, // 1
//                onSelect: function(dateText, inst) {
//
//                    var minDate = $(this).datepicker('getDate', '+2d');
//                    minDate.setDate(minDate.getDate() + 2);
//
//                    var minDate = $(this).datepicker('getDate', '+1d');
//                    minDate.setDate(minDate.getDate() + 1);
//
//                    $("#to_datepicker_list").datepicker('option', 'minDate', new Date(minDate));
//
//                   // $(".to input").datepicker('option', 'minDate', $(this).datepicker( "getDate" ));
//
//                    if( $(this).datepicker('getDate') >= $('.to input').datepicker('getDate') )
//                    {
//                        var nextDayDate = $(this).datepicker('getDate', '+1d');
//                        nextDayDate.setDate(nextDayDate.getDate() + 1);
//                        $('.to input').datepicker("setDate",nextDayDate);
//                    }
//                    //dateText comes in as MM/DD/YY
//                    var datePieces = dateText.split('/');
//                    var month = datePieces[0];
//                    var day = datePieces[1];
//                    var year = datePieces[2];
//                    $('select#arrmonth').val(month);
//                    $('select#arrday').val(day);
//                    $('select#arryear').val(year);
//                    dateDifference($('#from_datepicker_list'));
//
//                }
//            });
//
//
//        $('#to_datepicker_list').datepicker({
//            showOn: 'both',
//            buttonImageOnly: true,
//            dateFormat:"dd/mm/yy",
//            buttonImage: '/wp-content/images/calendar.png',
//            firstDay: 1,
//            maxDate: '+2Y',
//            defaultDate: "+1w",
//            minDate: "+1d", // 2
//            onSelect: function(dateText, inst) {
//                //dateText comes in as MM/DD/YY
//
//                var datePieces = dateText.split('/');
//                var month = datePieces[0];
//                var day = datePieces[1];
//                var year = datePieces[2];
//                //define select option values for
//                //corresponding element
//                $('select#depmonth').val(month);
//                $('select#depday').val(day);
//                $('select#depyear').val(year);
//                //var iq= $(this);
//                //var iq = $(this).find('.to')
//                dateDifference($('#to_datepicker_list'));
//            }
//        });
            /*  DATA*/
        $('#from_datepicker_list').datepicker({
            showOn: 'both',
            buttonImageOnly: true,
            buttonImage: '/wp-content/images/calendar.png',
            firstDay: 1,
            maxDate: '+2Y',
            dateFormat:"dd/mm/yy",
            minDate:0,
            onSelect: function(dateText, inst) {

                var minDate = $(this).datepicker('getDate', '+2d');
                minDate.setDate(minDate.getDate() + 2);

                $("#to_datepicker_list").datepicker('option', 'minDate', new Date(minDate));

                // $(".to input").datepicker('option', 'minDate', $(this).datepicker( "getDate" ));

                if( $(this).datepicker('getDate') >= $('.to input').datepicker('getDate') )
                {
                    var nextDayDate = $(this).datepicker('getDate', '+1d');
                    nextDayDate.setDate(nextDayDate.getDate() + 1);
                    $('.to input').datepicker("setDate",nextDayDate);
                }
                //dateText comes in as MM/DD/YY
                var datePieces = dateText.split('/');
                var month = datePieces[0];
                var day = datePieces[1];
                var year = datePieces[2];
                //define select option values for
                //corresponding element
                $('select#arrmonth').val(month);
                $('select#arrday').val(day);
                $('select#arryear').val(year);
                //var iq = $(this).find('.from')
                dateDifference($('#from_datepicker_list'));

            }
        });


        $('#to_datepicker_list').datepicker({
            showOn: 'both',
            buttonImageOnly: true,
            dateFormat:"dd/mm/yy",
            buttonImage: '/wp-content/images/calendar.png',
            firstDay: 1,
            maxDate: '+2Y',
            defaultDate: "+1w",
            minDate: "+2d",
            onSelect: function(dateText, inst) {
                //dateText comes in as MM/DD/YY

                var datePieces = dateText.split('/');
                var month = datePieces[0];
                var day = datePieces[1];
                var year = datePieces[2];
                //define select option values for
                //corresponding element
                $('select#depmonth').val(month);
                $('select#depday').val(day);
                $('select#depyear').val(year);
                //var iq= $(this);
                //var iq = $(this).find('.to')
                dateDifference($('#to_datepicker_list'));
            }
        });

        $('select[name="from_hour_list"], select[name="to_hour_list"]').change(function() {
            dateDifference($('[name="from_date"]'));
        });


            function dateDifference(iq) {
                var price_1_days_1 = $('input[name="price_1_days_1"]').val(),
                    price_1_days_2 = $('input[name="price_1_days_2"]').val(),
                    price_2_days_1 = $('input[name="price_2_days_1"]').val(),
                    price_2_days_2 = $('input[name="price_2_days_2"]').val(),
                    price_3_days_1 = $('input[name="price_3_days_1"]').val(),
                    price_3_days_2 = $('input[name="price_3_days_2"]').val();

                var check1 = $('.checkbox-input').find('input[name="gps_check"]');
                var check2 = $('.checkbox-input').find('input[name="child_check"]');
                var check3 = $('.checkbox-input').find('input[name="2driver_check"]');
                var check31 = $('.checkbox-input').find('input[name="sim_internet"]');
                var hidden_input_p = $('input[name="car_price"]');
                var car_price_per_day = $('input[name="car_price_per_day"]');
                var total_days_mail = $('input[name="total_days"]');
                var checkbox = 0;
                if(check1.is(':checked')){
                    checkbox += <?=get_option('extra_gps')?>;
                }
                if(check2.is(':checked')){
                    checkbox +=  <?=get_option('child_check')?>;
                }
                if(check3.is(':checked')){
                    checkbox +=  <?=get_option('2driver_check')?>;
                }
                if(check31.is(':checked')){
                    checkbox +=  <?=get_option('sim_internet')?>;
                }

              //  console.log(checkbox);

              //  checkbox += parseFloat($('.asig').find('option:selected').attr('data-price-asig'));

                var price_asigurare = parseFloat($('.asig').find('option:selected').attr('data-price-asig'));
             //   console.log(checkbox);

                var from_hour = $('[name="from_hour_list"]').val().split('.');
                from_hour[0] = parseInt(from_hour[0]);
                var to_hour = $('[name="to_hour_list"]').val().split('.');
                to_hour[0] = parseInt(to_hour[0]);

                var night_to = 0,
                    to_h = $('[name="to_hour_list"]').val();
                if((to_h <= 8.00 && to_h >= 0.00 ) || (to_h >= 20.00 && to_h <= 23.45) ) {
                    night_to = 25;//20 time
                } else {
                    night_to = 0;
                }
              //  console.log('to: ' + night_to);
                var night_from = 0,
                    from_h = $('[name="from_hour_list"]').val();
                if((from_h <= 8.00 && from_h >= 0.00 ) || (from_h >= 20.00 && from_h <= 23.45) ) {
                    night_from = 25; //20 time
                } else {
                    night_from = 0;
                }
                var night_time = parseInt(night_from+night_to);
                //console.log('from: ' +night_from);
                $('#night_time').text( night_time+ ' €');
                $('[name="night_time"]').val( night_time );
                if(iq.attr('bb') == "bb"){
                    var to = $('.to').children('input').datepicker("getDate");
                    var from = iq.datepicker("getDate");
                    //var diff = 7;
                    var diff = Math.floor((to-from) / 1000 / 60 / 60 / 24);
                    var days = $('#total_days');
                    var price_to_day = $('#price_per_day');
                    var sold = $('#total_price');
                    if (to_hour[0] > from_hour[0]) {
                        diff++;
                    }

                    days.html("").html(diff);
                    days = parseInt(days.text());

                    //.parents('.l_block2').parents('.lightbox').parents('.www').prevAll('.book_now2').children('table').html();
                    var attrID = $('.ajax_form').parent().parent().children().attr('id');
                    attrID = '#'+attrID;
                    var priceDays = $('input[href="'+attrID+'"]').prevAll('table');
                    var res_p_price;
                    if (days <= price_1_days_2)
                    {

                        var p_price =  $('input[name="price_1"]').val();
                        p_price = parseInt(p_price);
                    //    var asig_price = price_asigurare;
                        res_p_price = p_price + checkbox + price_asigurare ;
                        price_to_day.text(res_p_price + ' €');
                        var total_p_p = res_p_price * days + night_time;
                        sold.text(total_p_p + ' €');

                        hidden_input_p.val(total_p_p);
                        car_price_per_day.val(res_p_price);
                        total_days_mail.val(days);

                    }
                    else if (days >= price_2_days_1 && days <= price_2_days_2)
                    {
                        var p_price =  $('input[name="price_2"]').val();
                        p_price = parseInt(p_price);
                        res_p_price = p_price + checkbox + price_asigurare;
                        price_to_day.text(res_p_price + ' €');
                        var total_p_p = res_p_price * days + night_time;
                        sold.text(total_p_p + ' €');
                        hidden_input_p.val(total_p_p);
                        car_price_per_day.val(res_p_price);
                        total_days_mail.val(days);
                    }
                    else if (days >= price_3_days_1 && days <= price_3_days_2)
                    {
                        var p_price =  $('input[name="price_3"]').val();
                        p_price = parseInt(p_price);
                        res_p_price = p_price + checkbox + price_asigurare;
                        price_to_day.text(res_p_price + ' €');
                        var total_p_p = res_p_price * days + night_time;
                        sold.text(total_p_p + ' €');
                        hidden_input_p.val(total_p_p);
                        car_price_per_day.val(res_p_price);
                        total_days_mail.val(days);
                    }
                    else
                    {
                        var p_price =  $('input[name="price_4"]').val();
                        p_price = parseInt(p_price);
                        res_p_price = p_price + checkbox + price_asigurare;
                        price_to_day.text(res_p_price + ' €');
                        var total_p_p = res_p_price * days + night_time;
                        sold.text(total_p_p + ' €');
                        hidden_input_p.val(total_p_p);
                        car_price_per_day.val(res_p_price);
                        total_days_mail.val(days);
                    }
                }
                else{
                    //var diff = (to.datepicker("getDate") - from.datepicker("getDate")) / 1000 / 60 / 60 / 24;
                    var from = $('.from').children('input').datepicker("getDate");
                    var to = iq.datepicker("getDate");
                    //var diff = 7;
                    var diff = Math.floor((to-from) / 1000 / 60 / 60 / 24);
                    var days = $('#total_days');
                    var price_to_day = $('#price_per_day');
                    var sold = $('#total_price');
                    if (to_hour[0] > from_hour[0]) {
                        diff++;
                    }
                    days.html("").html(diff);
                    days = parseInt(days.text());

                    //.parents('.l_block2').parents('.lightbox').parents('.www').prevAll('.book_now2').children('table').html();
                    var attrID = iq.parents('.ajax_form').parent().parent().children().attr('id');
                    attrID = '#'+attrID;
                    var priceDays = $('input[href="'+attrID+'"]').prevAll('table');

                    if (days <= price_1_days_2)
                    {
                        var p_price =  $('input[name="price_1"]').val();
                        p_price = parseInt(p_price);
                        res_p_price = p_price + checkbox + price_asigurare;
                        price_to_day.text(res_p_price + ' €');
                        var total_p_p = res_p_price * days + night_time;
                        sold.text(total_p_p + ' €');
                        hidden_input_p.val(total_p_p);
                        car_price_per_day.val(res_p_price);
                        total_days_mail.val(days);
                    }
                    else if (days >= price_2_days_1 && days <= price_2_days_2)
                    {
                        var p_price =  $('input[name="price_2"]').val();
                        p_price = parseInt(p_price);
                        res_p_price = p_price + checkbox + price_asigurare;
                        price_to_day.text(res_p_price + ' €');
                        var total_p_p = res_p_price * days + night_time ;
                        sold.text(total_p_p + ' €');
                        hidden_input_p.val(total_p_p);
                        car_price_per_day.val(res_p_price);
                        total_days_mail.val(days);
                    }
                    else if (days >= price_3_days_1 && days <= price_3_days_2)
                    {
                        var p_price =  $('input[name="price_3"]').val();
                        p_price = parseInt(p_price);
                        res_p_price = p_price + checkbox + price_asigurare;
                        price_to_day.text(res_p_price + ' €');
                        var total_p_p = res_p_price * days + night_time;
                        sold.text(total_p_p + ' €');
                        hidden_input_p.val(total_p_p);
                        car_price_per_day.val(res_p_price);
                        total_days_mail.val(days);
                    }
                    else
                    {
                        var p_price =  $('input[name="price_4"]').val();
                        p_price = parseInt(p_price);
                        res_p_price = p_price + checkbox + price_asigurare;
                        price_to_day.text(res_p_price+ ' €');
                        var total_p_p = res_p_price * days + night_time;
                        sold.text(total_p_p + ' €');
                        hidden_input_p.val(total_p_p);
                        car_price_per_day.val(res_p_price);
                        total_days_mail.val(days);
                    }
                }
            }

});

</script>
</body>
</html>
<?php }  else { ?>
    <?php get_header(); ?>
    <div class="main-container">
        <div class="content-area">
            <div class="middle-align content_sidebar">
                <div class="site-main" id="sitemain">
                    <?php while (have_posts()) : the_post(); ?>
                        <?php get_template_part('content', 'single'); ?>
                        <?php // the_post_navigation( 'nav-below' ); ?>
                    <?php endwhile; // end of the loop. ?>
                    <?php

                    $related = get_posts(array('category__in' => wp_get_post_categories($post->ID), 'numberposts' => 3, 'post__not_in' => array($post->ID)));
                    if ($related) ?>
                    <div class="related-post">
                        <h3><?= _e('Related articles') ?></h3>
                        <div class="editor-grid-post">
                            <?
                            foreach ($related as $post) {
                                setup_postdata($post); ?>
                                <div class="ediotr-item">
                                    <? if (has_post_thumbnail()) { ?>
                                        <div class="blog-post-thumb">
                                            <a href="<?php the_permalink() ?>" rel="bookmark"
                                               title="<?php the_title(); ?>" class="thumb_link">
                                                <? the_post_thumbnail('medium'); ?>
                                            </a>
                                        </div>
                                    <? } ?>
                                    <h4><a href="<?php the_permalink() ?>" rel="bookmark"
                                           title="<?php the_title(); ?>"><?php the_title(); ?></a></h4>
                                    <div class="excerpt_short"><?php echo mb_substr(strip_tags(get_the_excerpt()), 0, 110); ?>
                                        <?= strlen(get_the_excerpt()) >= 110 ? '...' : '' ?>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <?
                    wp_reset_postdata(); ?>
                </div>
                <?php get_sidebar(); ?>
                <div class="clear"></div>
            </div>
        </div>
    </div>
    <?php get_footer();
} ?>

