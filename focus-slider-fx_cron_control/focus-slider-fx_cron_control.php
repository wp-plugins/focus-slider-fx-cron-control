<?php
/*
Plugin Name: Focus Slider FX CRON CONTROL
Plugin URI: http://tarnazar.pp.ua
Description: Control panel for automatic add images to Focus Slider FX. 
Version: 0.1
Author: Samael
Author URI: http://www.tarnazar.pp.ua
License: GPL2
*/

/* start global parameters */
function save_database_settings ($db_user,$db_pass,$db_name,$db_location) {
$xml =  simplexml_load_file(WP_PLUGIN_DIR."/focus-slider-fx_cron_control/settings.xml");
foreach ($xml->xpath('//settings') as $item) {
    $item->db_user = $db_user;
    $item->db_pass = $db_pass;
    $item->db_name = $db_name;
    $item->db_location = $db_location;
 if ($xml->asXML(WP_PLUGIN_DIR."/focus-slider-fx_cron_control/settings.xml")) {
     return true;
         } else
             {
                 return false;
                     }
                     }

}
function save_settings_file ($photos_count,$fp) {
$xml =  simplexml_load_file(WP_PLUGIN_DIR."/focus-slider-fx_cron_control/settings.xml");
foreach ($xml->xpath('//settings') as $item) {
    $item->photos_count = $photos_count;
    $item->path = $fp;
    if ($xml->asXML(WP_PLUGIN_DIR."/focus-slider-fx_cron_control/settings.xml")) {
    return true;
    } else 
    {
    return false;
    }
}
}

function load_settings_file () {
$xml = simplexml_load_file(WP_PLUGIN_DIR."/focus-slider-fx_cron_control/settings.xml");

$photos_count = $xml->children()->photos_count;
$fp = $xml->children()->path;
$db_user = $xml->children()->db_user;
$db_pass = $xml->children()->db_pass;
$db_name = $xml->children()->db_name;
$db_location = $xml->children()->db_location;
return array("photos_count"=>$photos_count,"fp"=>$fp,"db_user"=>$db_user,"db_pass"=>$db_pass,"db_name"=>$db_name,"db_location"=>$db_location);
}

function admin_control() {
        add_options_page('Focus Slider FX cron control', 'Focus Slider FX cron control', 'manage_options', 'fsfx_cc', 'fsfx_cc_options');
            }

function fsfx_cc_options () {
global $wpdb;
$lsf = load_settings_file();
if ($_GET['settings-updated']) {
save_settings_file (get_option('photos_count'),get_option('path'));
}
if (($lsf['db_user']==$wpdb->dbuser) and ($lsf['db_pass']==$wpdb->dbpassword) and ($lsf['db_name']==$wpdb->dbname) and ($lsf['db_location']==$wpdb->dbhost)) {
echo "Database Settings is Ok!";
} else {
if (save_database_settings($wpdb->dbuser,$wpdb->dbpassword,$wpdb->dbname,$wpdb->dbhost)) {
echo "Database configuration was wrong. But now its Ok";
}
}
?>
<div class="wrap">
<h2>Focus Slider FX CRON CONTROL</h2>
<!-- <form method="post">-->
<form method="post" action="options.php">
<?php wp_nonce_field('update-options'); ?>
        <pre>
        The Focus Slider FX cron control is plug-in for wordpress, which generates XML file for FlashXML Focus Slider FX plug-in. This file containes pathes to latest loaded pictures on the site.
        Plugin inscribes database's adjustments to the script automatically. 
        You might change the path to the file from which Focus Slider FX takes necessary adjustments.
        The number of pictures displaying on the page is also variable. 
        Focus Slider FX plug-in doesn't came with this package. It should be installed separately.
        The Cron Job is writing manually as it is * * * * * cd /path/to/wp-plugin-dir/t-slider-fx_cron_control; /path/to//php gen_flashxml.php 1>/dev/null 2/dev/null.
        
        </pre> 
        Last photos count: <input type="text" name="photos_count" value="<?php echo $lsf["photos_count"];?>" size=2><p>
        DEFAULT PATH: <b> <?php echo WP_CONTENT_DIR?>/flashxml/focus-slider-fx/images.xml</b><br>
        Path To images.xml: <input type="text" name="path" value="<?php echo $lsf["fp"]; ?>" size=100><br>
        <input type="hidden" name="action" value="update" />
        <input type="hidden" name="page_options" value="photos_count,path" />

        <p class="submit">
          <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
        </p>

</form>
</div>
<?php 

?>
<?php
}

function regenerate_now() {
exec ('php gen_flashxml.php');
}


add_action('admin_menu', 'admin_control');
?>
