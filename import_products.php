<?php

set_time_limit( 60 * 5 );
ini_set( 'error_reporting', E_ALL );
ini_set( 'display_errors', 1 );
ini_set( 'display_startup_errors', 1 );
ini_set( 'max_execution_time', 60 * 5 );

$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
require_once( $parse_uri[0] . 'wp-load.php' );
include_once __DIR__ . '/function_plugin.php';

$json = array();
header( 'Content-Type: application/json', true );

if ( empty( $_POST['product_import'] ) ) {
	$json['error_product'] = 'Выберите товар';
	echo $json = json_encode( $json );
	exit;
}

if ( empty( $_POST['category_import'] ) ) {
	$json['error_category'] = 'Выберите категорию';
	echo $json = json_encode( $json );
	exit;
}
//if(isset($_COOKIE['dev'])) { var_dump($_POST['product_import']); die; }
if ( ! empty( $_POST['product_import'] ) && ! empty( $_POST['category_import'] ) ) {
	foreach( $_POST['product_import'] as $product ) {
		$url = 'https://dptrade.lt/xml.php?user=*******&pass=******&action=product&search=' . $product . '&lang=ET';
		$xml = simplexml_load_file( $url, 'SimpleXMLElement', LIBXML_NOCDATA );

		$one_product = (array) $xml->product;
        global $wpdb;
		
		if ( isset( $_POST['product_none'] ) && $_POST['product_none'] == 1 ) {

			$wpdb->delete( $wpdb->prefix.'none_products', array( 'product_code' => $product ) );
		}
		

		$product_id = $wpdb->get_var( $wpdb->prepare( "
		SELECT posts.ID
		FROM $wpdb->posts AS posts
		LEFT JOIN $wpdb->postmeta AS postmeta ON ( posts.ID = postmeta.post_id )
		WHERE posts.post_type IN ( 'product', 'product_variation' )
		AND postmeta.meta_key = '_sku' AND postmeta.meta_value = '%s'
		LIMIT 1
		", $one_product['product_code'] ) );

		if(!empty($product_id)) { 
			wh_deleteProduct($product_id, TRUE);
			
		}
        if(empty($one_product['description'])) { 
		$description = $one_product['text']; 
			} else {  
		$description = $one_product['description']; 
		}
		$post_id = wp_insert_post( array(
			'post_title'   => $one_product['name1'],
			'post_content' => $description,
			'post_status'  => 'publish',
			'post_type'    => 'product',
		) );

		if ( ! empty( $_POST['price_discount'] ) ) {
			$price = $one_product['price'] * ( $_POST['price_discount'] / 100 ) + $one_product['price'];
		} else {
			$price = $one_product['price'];
		}

		$price = $price * 1.2;  // Налог

		add_post_meta( $post_id, 'markup_price', $_POST['price_discount'] );

		$term = get_term_by( 'name', $one_product['manufacturer'], 'brand' );

		if ( $term == false ) {
			$terms = wp_set_object_terms( $post_id, array($one_product['manufacturer']), 'brand' );
		}
		//var_dump($one_product['manufacturer']); die;
        ////////////Tags
		
	    $code = $one_product['product_code'];
		$newtable = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}tags_products WHERE product_code = '{$code}'", ARRAY_A);
		if(!empty($newtable)) {
			$tags = json_decode($newtable[0]['tags'],TRUE);
			$tag_product = array();
				foreach($tags['printer'] as $key => $tag) 
				{   
					$tag_product[$key] = $tag["printer_title"];

				}
			
			wp_set_object_terms( $post_id, $tag_product, 'product_tag' );
			//////////////////
			
			if(empty($description)) {
				$my_post = array();
				$my_post['ID'] = $post_id;
				$my_post['post_content'] = implode(", ", $tag_product);
				wp_update_post( wp_slash($my_post) );
			}
		
		}
		wp_set_object_terms( $post_id, $_POST['category_import'], 'product_cat' );
        
		Featured_Image( $one_product['foto'], $post_id, $one_product['name1'] );

		wp_set_object_terms( $post_id, 'simple', 'product_type' );
		update_post_meta( $post_id, '_visibility', 'visible' );
		update_post_meta( $post_id, '_stock_status', 'instock' );
		update_post_meta( $post_id, 'total_sales', '0' );
		update_post_meta( $post_id, '_downloadable', 'no' );
		update_post_meta( $post_id, '_virtual', 'no' );
		update_post_meta( $post_id, '_regular_price', $price );
		update_post_meta( $post_id, '_sale_price', '' );
		update_post_meta( $post_id, '_purchase_note', '' );
		update_post_meta( $post_id, '_featured', 'no' );
		update_post_meta( $post_id, '_weight', '' );
		update_post_meta( $post_id, '_length', '' );
		update_post_meta( $post_id, '_width', '' );
		update_post_meta( $post_id, '_height', '' );
		update_post_meta( $post_id, '_sku', $one_product['product_code'] );
		update_post_meta( $post_id, '_product_attributes', array() );
		update_post_meta( $post_id, '_sale_price_dates_from', '' );
		update_post_meta( $post_id, '_sale_price_dates_to', '' );
		update_post_meta( $post_id, '_price', $price );
		update_post_meta( $post_id, '_sold_individually', '' );
		update_post_meta( $post_id, '_manage_stock', 'no' );
		update_post_meta( $post_id, '_backorders', 'no' );
		update_post_meta( $post_id, '_stock', '' );

		wc_simplbooks()->queue()->add( 'product', $post_id );
	}

	delete_transient('products-in-site');

	$json['success']    = 'Товар успешно добавлен!';
	$json['pagination'] = isset( $_POST['pagination'] ) ? $_POST['pagination'] : '';
	echo $json = json_encode( $json );
}
