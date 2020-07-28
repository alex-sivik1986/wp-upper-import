<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1); 
$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
require_once( $parse_uri[0] . 'wp-load.php' );

include_once __DIR__ . '/function_plugin.php';

foreach (glob("*.xml") as $key => $filename) { 
	
unlink($filename); 
} 
$newfile = 'data-'.date("d.m.y").'.xml';
set_time_limit(240);

$path = 'https://www.dptrade.lt/xml.php?user=******&pass=********&printers=1';
//$path = 'https://odavprint.preview.ee/wp-content/plugins/wp-upper-import/test-03.04.20.xml';
$fp = fopen ('./a.xml', 'w+');
$ch = curl_init($path);// or any url you can pass which gives you the xml file
curl_setopt($ch, CURLOPT_TIMEOUT, 240);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
curl_setopt($ch, CURLOPT_FILE, $fp);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_exec($ch);
curl_close($ch);
fclose($fp);
rename('a.xml',$newfile);


$xml = simplexml_load_file($newfile, 'SimpleXMLElement', LIBXML_NOCDATA);	

$result = $xml->xpath("/products/product");  
 global $wpdb;
        $newtable = $wpdb->get_results("TRUNCATE TABLE {$wpdb->prefix}tags_products");

foreach($result as $key => $value)  { 
 
if(!empty($value->printers)) {
	//echo $value->product_code;
	//echo $value->name1;
	$json = json_encode($value->printers); 
   // $printers = json_decode($json,TRUE);
	
	$table_name = $wpdb->prefix . 'tags_products';
	$wpdb->insert($table_name, array(
        'product_code' => $value->product_code,
        'name' => $value->name1,
		'tags' => $json
    ));
}

}

delete_transient('products-in-site');
products_cache();

				
delete_transient('products-none-site');
products_none_cache();
			

?>