<?php
/**
 * The Sidebar containing the main widget areas.
 *
 * @package Movers Lite
 */
?>
<div id="sidebar">
    <div class="mini_select">
        <form action="<?=get_the_permalink(122)?>" onsubmit="return validateForm2(this)" method="post">
            <div class="value_field">
                <label><?=_e('Pick Up')?></label>
                <select name="where_to">
                                <option value="Airport-Venice-Marco-Polo">Airport Venice Marco Polo</option>
                                <option value="Treviso-Airport-Arrivals">Treviso Airport Arrivals</option>
                                <option value="Treviso-Office">Treviso Office</option>
                </select>
            </div>
            <div class="value_field">
                <label><?=_e('Drop-off')?></label>
                <select name="where_off">
                                <option value="Airport-Venice-Marco-Polo">Airport Venice Marco Polo</option>
                                <option value="Treviso-Airport-Arrivals">Treviso Airport Arrivals</option>
                                <option value="Treviso-Office">Treviso Office</option>
                </select>
            </div>
            <div class="clear"></div>
            <div class="value_field fl_left lf_p">
                <label><?=_e('from')?></label>
                <div class="date_cal"><input id="from_date_sidebar" name="from_date_sidebar" value="<?=(isset($_POST['from_date_sidebar']) ? $_POST['from_date_sidebar'] : date('d/m/Y') )?>" type="text" readonly="readonly"></div>
                <select name="from_hour">
                    <?
                    //date('d/m/Y', time()+86400)
                    $time_default_from = (isset($_POST['from_hour']) ? $_POST['from_hour'] : '10.00');
                    for($i = 0; $i<=23; $i++)
                        for($j = 0; $j<=45; $j+=15)
                        {
                            if($j==0)
                                $time = $i.'.00';
                            else
                                $time = $i.'.'.$j;
                            if($time == $time_default_from)
                                echo'<option selected="selected">'.$time.'</option>';
                            else
                                echo"<option>".$time."</option>";
                        }
                    ?></select>
            </div>
            <div class="value_field fl_left ri_p">
                <label><?=_e('to')?></label>
                <div class="date_cal"><input type="text" id="to_datepicker_sidebar" name="to_date" value="<?=(isset($_POST['to_date']) ? $_POST['to_date'] : date('d/m/Y', time()+604800))?>" readonly="readonly"></div>
                <select name="to_hour">
                    <? //604800
                    $time_default = (isset($_POST['to_hour']) ? $_POST['to_hour'] : '10.00');
                    for($i = 0; $i<=23; $i++)
                        for($j = 0; $j<=45; $j+=15)
                        {
                            if($j==0)
                                $time = $i.'.00';
                            else
                                $time = $i.'.'.$j;
                            if($time == $time_default)
                                echo'<option selected="selected">'.$time.'</option>';
                            else
                                echo"<option>".$time."</option>";
                        }
                    ?></select>
            </div>
            <div class="clear"></div>
            <input type="submit" id="book" class="search_button" value="<?=_e('Search car')?>"/>
        </form>
    </div>

    <?php if ( ! dynamic_sidebar( 'sidebar-1' ) ) : ?>
        <aside id="archives" class="widget">
            <h3 class="widget-title"><?php esc_attr_e( 'Archives', 'movers-lite' ); ?></h3>
            <ul>
                <?php wp_get_archives( array( 'type' => 'monthly' ) ); ?>
            </ul>
        </aside>
        <aside id="meta" class="widget">
            <h3 class="widget-title"><?php esc_attr_e( 'Meta', 'movers-lite' ); ?></h3>
            <ul>
                <?php wp_register(); ?>
                <li><?php wp_loginout(); ?></li>
                <?php wp_meta(); ?>
            </ul>
        </aside>
    <?php endif; // end sidebar widget area ?>
	
</div><!-- sidebar -->