<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
require_once( $parse_uri[0] . 'wp-load.php' );
include_once __DIR__ . '/function_plugin.php';
set_time_limit(180);
$json = array();
header( 'Content-Type: application/json', true );

if ( empty( $_POST['product_update'] ) ) {
	$json['error_product'] = 'Выберите товар';
	echo $json = json_encode($json);
	exit;
}

if ( empty( $_POST['category_import'] ) ) {
	$json['error_category'] = 'Выберите категорию';
	echo $json = json_encode($json);
	exit;
}

if ( !empty( $_POST['product_update'] ) && !empty( $_POST['category_import'] ) ) {
	$test = array();
	foreach( $_POST['product_update'] as $product ) {
		$result = explode( ':', $product );
		$product_id = $result[1];
		$product_code =  $result[0];
		$url = 'https://dptrade.lt/xml.php?user=******&pass=******&action=product&search=' . $product_code . '&lang=ET';

		$xml = simplexml_load_file( $url, 'SimpleXMLElement', LIBXML_NOCDATA );

		$one_product = (array) $xml->product;

		//if ( $one_product['available'] == 0 ) {
			
		//} else { 
			if ( !empty( $_POST['price_discount'] ) ) {
				$price = $one_product['price'] * ( $_POST['price_discount'] / 100 ) + $one_product['price'];
			} 

			update_post_meta( $product_id, 'markup_price', $_POST['price_discount'] );

			$price = $price * 1.2; // Налог
			$term = get_term_by( 'name', $one_product['manufacturer'], 'brand' );

			if($term == false) {
				$terms = wp_set_object_terms( $product_id, array($one_product['manufacturer']), 'brand' );
			}
			
			////////////Tags
		
			$code = $one_product['product_code'];
			$newtable = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}tags_products WHERE product_code = '{$code}'", ARRAY_A);
			
			if(empty($one_product['description'])) { 
			$description = $one_product['text']; 
				} else {  
			$description = $one_product['description']; 
			}
			
			if(!empty($newtable)) { 
				$tags = json_decode($newtable[0]['tags'],TRUE);
				$tag_product = array();
					foreach($tags['printer'] as $key => $tag) 
					{   
						$tag_product[$key] = $tag["printer_title"];

					}
				
				wp_set_object_terms( $product_id, $tag_product, 'product_tag' );
				//////////////////
				
				
				if(empty($description)) {
					$my_post = array();
					$my_post['ID'] = $product_id;
					$my_post['post_content'] = implode(", ", $tag_product);
					wp_update_post( wp_slash($my_post) );
				} else {
					$my_post = array();
					$my_post['ID'] = $product_id;
					$my_post['post_content'] = $description;
					wp_update_post( wp_slash($my_post) );
				}
			
			}
            
			wp_set_object_terms( $product_id, $_POST['category_import'], 'product_cat' );

			Featured_Image($one_product['foto'], $product_id, $one_product['name1'] );
		
			update_post_meta( $product_id, '_price', $price );
			update_post_meta( $product_id, '_regular_price', $price );
             
			wc_simplbooks()->queue()->add( 'product', $product_id );
		//}
	}

	delete_transient( 'products-in-site' );
	$json['success'] = 'Товар успешно обновлен!';
	echo $json = json_encode( $json );
}
