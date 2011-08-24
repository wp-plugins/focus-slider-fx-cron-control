<?php
$xml = simplexml_load_file("settings.xml");

$photos_count = $xml->children()->photos_count;
$path = $xml->children()->path;
define('DB_NAME',  $xml->children()->db_name);
define('DB_USER',  $xml->children()->db_user);
define('DB_PASSWORD', $xml->children()->db_pass);
define('DB_HOST', $xml->children()->db_location);

$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
if (!$link) { die("Could not connect: " . mysql_error()."\n"); }
echo "Connected successfully\n";
if (mysql_select_db(DB_NAME)) { echo DB_NAME." SELECTED\n"; }

$get_list_sql="select post_date,guid, post_name,post_title from wp_posts where post_mime_type='image/jpeg' ORDER BY  post_date DESC limit $photos_count;";
$get_list_sql = mysql_query($get_list_sql);

$xml=new DomDocument('1.0','utf-8');
$images = $xml->appendChild($xml->createElement('images'));

$i=0;
while($get_list = mysql_fetch_array($get_list_sql)) {
$photo = $images->appendChild($xml->createElement('photo'));
$photo->setAttribute('image',$get_list['guid']);
$i++;
}
$xml->formatOutput = true;
$out = html_entity_decode($xml->saveXML());
$fp = "$path";
$ttest = fopen($fp,"w");
fputs ($ttest,$out);
fclose($ttest);
mysql_close($link);
?>
