<?php
/**
Template name: Success page

 */
get_header('success'); ?>
<div class="success">
    <?php while ( have_posts() ) : the_post(); ?>
        <h1><? the_title() ?></h1>
        <? the_content()?>
    <?php endwhile; // end of the loop. ?>
    <table class="table" cellpadding="0" cellspacing="0">
        <tbody>
            <tr>
                <td><?=_e('Car')?></td>
                <td><b><?=get_the_title($_GET['id'])?></b></td>
            </tr>
            <tr>
                <td><?=_e('Pick Up')?></td>
                <td><b><?=$_GET['where_to']?></b></td>
            </tr>
            <tr>
                <td><?=_e('Drop-off')?></td>
                <td><b><?=$_GET['where_off']?></b></td>
            </tr>
            <tr>
                <td><?=_e('from')?></td>
                <td><b><?=$_GET['from']?></b></td>
            </tr>
            <tr>
                <td><?=_e('to')?></td>
                <td><b><?=$_GET['to']?></b></td>
            </tr>
            <tr>
                <td><?=_e('Insurance')?></td>
                <td><b>
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

                        if($_GET['asig']== 'rca') {
                           echo $asig;
                        }elseif($_GET['asig']== 'casco'){
                            echo $asig2;
                        }elseif($_GET['asig']== 'cdw'){
                            echo $asig3;
                        } elseif($_GET['asig']== 0){
                           echo '-';
                        }
                        ?>

                    </b></td>
            </tr>
            <tr>
                <td><?=_e('Extra')?></td>
                <td><ul><?  if(!empty($_GET['gps_check'])) {
                            echo '<li>'.__('GPS').'</li>';
                        }
                        if(!empty($_GET['child_check'])) {
                            echo '<li>'.__('Child seat').'</li>';
                        }
                        if(!empty($_GET['2driver_check'])) {
                            echo '<li>'.__('Two drivers').'</li>';
                        }
                        if(!empty($_GET['sim_internet'])) {
                            echo '<li>'.__('Sim with internet').'</li>';
                        } ?></ul></td>
            </tr>
        </tbody>
    </table>
    <div class="value_field">
        <div class="price_box l_box_2_root">
            <div class="line_1">
                <span><?=_e('Days')?></span>
                <span>&euro; <?=_e('per day')?></span>
                <span><?=_e('Night time')?></span>
                <span><?=_e('Total cost')?></span>
            </div>
            <div class="line_1">
                <span id="total_days"><?=$_GET['total_day']?></span>
                <span id="price_per_day"><?=$_GET['car_price_per_day']?> &euro;</span>
                <span id="night_time"><?=$_GET['night_time']?> &euro;</span>
                <span id="total_price"><?=$_GET['total_price']?>  &euro;</span>
            </div>
        </div>
        <div class="clear"></div>
    </div>
    <? $src = wp_get_attachment_image_src( get_post_thumbnail_id($_GET['id']), 'large', '' ); ?>
    <img src="<?=$src[0]?>" alt=""/>
</div>
<? //print_r($_GET) ?>
<style>
    img{ border: 2px solid rgb(33, 162, 26); }
    body { margin: 0;
        font-family: sans-serif;
        font-size: 14px;
        /*background: -moz-radial-gradient(center, ellipse cover, rgba(254,255,255,1) 0%, rgba(254,255,255,0.76) 24%, rgba(210,235,249,0.5) 50%, rgba(254,255,255,0.77) 77%, rgba(254,255,255,1) 100%);*/
        /*background: -webkit-radial-gradient(center, ellipse cover, rgba(254,255,255,1) 0%,rgba(254,255,255,0.76) 24%,rgba(210,235,249,0.5) 50%,rgba(254,255,255,0.77) 77%,rgba(254,255,255,1) 100%);*/
        /*background: radial-gradient(ellipse at center, rgba(254,255,255,1) 0%,rgba(254,255,255,0.76) 24%,rgba(210,235,249,0.5) 50%,rgba(254,255,255,0.77) 77%,rgba(254,255,255,1) 100%);*/
        /*filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#feffff', endColorstr='#feffff',GradientType=1 );*/
    }
    h1 {    color: rgb(68, 68, 68);
        text-align: center;
        font-size: 24px; }
    P { text-align: center}
    img { max-width: 100%; display: block; margin: auto}
    .price_box { margin-bottom: 15px; margin-top: 5px;
        box-sizing: border-box;}
    .price_box .line_1:first-child { color: #888; border-bottom: 1px solid #fff; }
    .price_box .line_1:first-child > span { font-size: 16px }
    .price_box, .line_1 {
        float: left;
        width: 100%;
        text-align: center;
    }
    .value_field a { color: #008AFF; text-decoration: underline; font-size: 14px; letter-spacing: 0.5px;}
    .line_1 > span {
        float: left;
        width: 25%;
        background: #F2F2F2;
        padding: 0px 7px;
        height: 36px;
        line-height: 36px;
        box-sizing: border-box;
    }
    .price_box .line_1:first-child > span:last-child { font-size: 13px}
    .line_1 > span:last-child { color: #21A21A; font-size: 22px; font-weight: bold; }
    .success { width: 50%; margin: auto}
    .table {
        width: 100%;
        border-bottom: 1px solid rgb(226, 226, 226);
        margin-bottom: 5px;
    }
    tr:nth-child(2n) {
        background: rgba(33, 162, 26, 0.15); /* Цвет фона */
    }
    .table tr td {
        font-weight: bold; padding: 5px 15px;width: 50%; font-size: 13px; border-top: 1px solid rgb(226, 226, 226); border-left: 1px solid rgb(226, 226, 226); }
    .table tr  td:first-child{
        /*background: rgb(242, 242, 242);*/
        text-transform: capitalize;
        /*color: rgb(136, 136, 136);*/
        border-right: 1px solid rgb(226, 226, 226);

    }
    .table tr  td:last-child { border-right: 1px solid rgb(226, 226, 226); border-left: 0; }

    @media(max-width:800px) { .success { width: 100%}}
    @media (max-width: 380px) {
        .line_1 > span:last-child { font-size: 16px; }
        .price_box .line_1:first-child > span {
            font-size: 12px;
        }
        h1 { font-size: 20px; margin-bottom: 0;
            margin-top: 0;}
        p{    margin: 2px 0 10px;}
        .table tr td:last-child {    padding: 5px 10px;}
    }
</style>
</div>
</body>
</html>

