<?php

function wpupper_log( $message ) {
    static $logger = null;

    if ( is_null( $logger ) ) {
        $logger = new WC_Logger();
    }

    $logger->add( 'wp-upper-import', $message );
}

function wh_deleteProduct($id, $force = FALSE)
{
    $product = wc_get_product($id);

    if(empty($product))
        return new WP_Error(999, sprintf(__('Нет %s упоминаний #%d', 'woocommerce'), 'product', $id));

    $name = $product->get_name();
    $sku  = $product->get_sku();

    // If we're forcing, then delete permanently.
    if ($force)
    {
        if ($product->is_type('variable'))
        {
            foreach ($product->get_children() as $child_id)
            {
                $child = wc_get_product($child_id);
                $child->delete(true);
            }
        }
        elseif ($product->is_type('grouped'))
        {
            foreach ($product->get_children() as $child_id)
            {
                $child = wc_get_product($child_id);
                $child->set_parent_id(0);
                $child->save();
            }
        }

        $product->delete(true);
        $result = $product->get_id() > 0 ? false : true;
    }
    else
    {
        $product->delete();
        $result = 'trash' === $product->get_status();
    }

    if (!$result)
    {
        return new WP_Error(999, sprintf(__('Этот %s товар не может быть удален', 'woocommerce'), 'product'));
    }

    // Delete parent product transients.
    if ($parent_id = wp_get_post_parent_id($id))
    {
        wc_delete_product_transients($parent_id);
    }

    wpupper_log( sprintf( 'Product "%s" [ID: %s SKU: %s] was successfully deleted.', $name, $id, $sku ) );

    return true;
}

function Featured_Image( $image_url, $product_id, $product_title  )
{
    $upload_dir = wp_upload_dir();
    $image_data = file_get_contents($image_url);


    $product = new WC_product($product_id);
    $tmp_image_id = $product->get_image_id();

    if ($tmp_image_id > 0 )
        wp_delete_attachment($tmp_image_id);

    if ($image_data === FALSE)
        return false;


    $filename = basename($image_url);
    if(wp_mkdir_p($upload_dir['path']))     $file = $upload_dir['path'] . '/' . $filename;
    else                                    $file = $upload_dir['basedir'] . '/' . $filename;
    file_put_contents($file, $image_data);

    $wp_filetype = wp_check_filetype($filename, null );
    $attachment = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_title' => $product_title,
        'post_content' => '',
        'post_status' => 'inherit'
    );
    $attach_id = wp_insert_attachment( $attachment, $file, $product_id );
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
    $res1= wp_update_attachment_metadata( $attach_id, $attach_data );
    $res2= set_post_thumbnail( $product_id, $attach_id );
}

function products_cache(){

	$params = array(
				'post_type' => 'product',
				'posts_per_page' => -1
				);
	$special_query_results = get_transient( 'products-in-site' );

			if ( false === $special_query_results ) {
				$wc_query = new \WP_Query($params);
							$products_in_site = array();
							while ( $wc_query->have_posts() ) {
								$products_in_site[get_the_ID()] = get_post_meta( get_the_ID(), '_sku', true );
								$wc_query->the_post();
							}

				set_transient( 'products-in-site', $products_in_site, 5 * HOUR_IN_SECONDS );
				wp_reset_postdata();

				return $products_in_site;
				//var_dump($products_in_site); die;
			} else {
				//var_dump($special_query_results); die;
				return $special_query_results;
			}

}

function products_none_cache(){

global $wpdb;

	$special_query_results = get_transient( 'products-none-site' );

			if ( false === $special_query_results ) {
				$newtable = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}none_products", ARRAY_A);
							$products_none_site = array();
						foreach($newtable as  $value)  {
								$products_none_site[$value['product_code']] = $value['name'];
							}

				set_transient( 'products-none-site', $products_none_site, 5 * HOUR_IN_SECONDS );
				wp_reset_postdata();

				return $products_none_site;
				//var_dump($products_in_site); die;
			} else {
				//var_dump($special_query_results); die;
				return $special_query_results;
			}

}


function product_category($product_code /*, $product_name=array()*/) {
	    $cat_name = '';
	   /* if(!empty(key($product_name))){
			$terms = get_the_terms( key($product_name), 'product_cat' );
        foreach ($terms as $key => $term) {
            $product_cat[$key] = $term->name;

        }
		return $cat_name = implode("; ", $product_cat);*/
		//} else {
			$terms = get_the_terms( $product_code, 'product_cat' );

		if (!empty($terms)) {
        foreach ($terms as $key => $term) {
            $product_cat[$key] = $term->name;

        }
		return $cat_name = implode("; ", $product_cat);
		} elseif($terms) {

		}
		//}

}
