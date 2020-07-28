<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
set_time_limit(60 * 5);
ini_set('max_execution_time', 60 * 5);

$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
require_once( $parse_uri[0] . 'wp-load.php' );
include_once __DIR__ . '/function_plugin.php';

$json = array();
header("Content-Type: application/json", true);

if(empty($_POST['product_import'])) {
	
	$json['error_product'] = 'Выберите товар';
	echo  $json = json_encode($json);
	exit;
}


if(!empty($_POST['product_import'])) { 
	
global $wpdb;
	
	foreach($_POST['product_import'] as $product) {
$url='http://dptrade.lt/xml.php?user=******&pass=********&action=product&search='.$product.'&lang=ET';

$xml = simplexml_load_file($url, 'SimpleXMLElement', LIBXML_NOCDATA);
	
$one_product = (array)$xml->product;

  $table_name = $wpdb->prefix . 'none_products'; // do not forget about tables prefix

    $wpdb->insert($table_name, array(
        'product_code' => $one_product['product_code'],
        'name' => $one_product['name1']
    ));
	
				
	}
	delete_transient('products-none-site');
	$json['success'] = 'Товар успешно добвлен!';
	echo  $json = json_encode($json);
}