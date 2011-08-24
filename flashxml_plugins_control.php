<?php
/*
Plugin Name: FlashXML plugins CONTROL
Plugin URI: http://tarnazar.pp.ua/wp_plugins
Description: Control panel for FlashXML plugins.
Version: 0.1.1
Author: Samael
Author URI: http://www.tarnazar.pp.ua/wp_plugins
License: GPL2
*/
ini_set('display_errors',1);
$global_vars = array(
'plugin_dir_path'=>realpath(dirname(__FILE__)),
);

function manual_update () {
global $wpdb;
global $global_vars;
$xml = simplexml_load_file($global_vars['plugin_dir_path']."/settings.xml");
    $photos_count = $xml->children()->photos_count;
    $path = $xml->children()->path;

$get_list_sql = $wpdb->get_results("select post_date,guid, post_name,post_title from $wpdb->posts where post_mime_type='image/jpeg' ORDER BY  post_date DESC limit $photos_count;",'ARRAY_A');

$xml=new DomDocument('1.0','utf-8');
$images = $xml->appendChild($xml->createElement('images'));

foreach ($get_list_sql as $key => $val) {
   $photo = $images->appendChild($xml->createElement('photo'));
    $photo->setAttribute('image',$val['guid']);
}

$xml->formatOutput = true;
$out = html_entity_decode($xml->saveXML());
$fp = "$path";
$ttest = fopen($fp,"w");
fputs ($ttest,$out);
fclose($ttest);
}


function save_database_settings ($db_user,$db_pass,$db_name,$db_location) {
global $global_vars;
$xml =  simplexml_load_file($global_vars['plugin_dir_path']."/settings.xml");
   foreach ($xml->xpath('//settings') as $item) {
    $item->db_user = $db_user;
    $item->db_pass = $db_pass;
    $item->db_name = $db_name;
    $item->db_location = $db_location;
      if ($xml->asXML($global_vars['plugin_dir_path']."/settings.xml")) {
        return true;
      } else {
        return false;
      }
    }
}

function save_settings_file ($photos_count,$fp) {
global $global_vars;
$xml =  simplexml_load_file($global_vars['plugin_dir_path']."/settings.xml");
foreach ($xml->xpath('//settings') as $item) {
    $item->photos_count = $photos_count;
    $item->path = $fp;
    if ($xml->asXML($global_vars['plugin_dir_path']."/settings.xml")) {
    return true;
    } else 
    {
    return false;
    }
}
}

function load_settings_file () {
global $global_vars;
$xml = simplexml_load_file($global_vars['plugin_dir_path']."/settings.xml");

$photos_count = $xml->children()->photos_count;
$fp = $xml->children()->path;
$db_user = $xml->children()->db_user;
$db_pass = $xml->children()->db_pass;
$db_name = $xml->children()->db_name;
$db_location = $xml->children()->db_location;
return array("photos_count"=>$photos_count,"fp"=>$fp,"db_user"=>$db_user,"db_pass"=>$db_pass,"db_name"=>$db_name,"db_location"=>$db_location);
}

function admin_control() {
        add_options_page('FlashXML plugins Control', 'FlashXML plugins Control', 'manage_options', 'fsfx_cc', 'fsfx_cc_options');
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
if (isset($_GET['do_manual'])) {
manual_update();
echo "<br>Updated!";
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
<form method="post" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']) ?>&do_manual">
<?php wp_nonce_field('update-options'); ?>
<p class="submit">
<input type="submit" name="manual" class="button-primary" value="Update Manualy" />
<input type="hidden" name="action" value="<?php echo$_GET['action']?>" />
</p>



</form>

</div>
<?php 

?>
<?php
}

add_action('admin_menu', 'admin_control');
?>
