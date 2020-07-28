<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
require_once( $parse_uri[0] . 'wp-load.php' );
echo "<link rel='stylesheet' id='import_files'  href='/wp-content/plugins/wp-upper-import/styles-import.css' type='text/css' media='all' />";
global $wpdb;
include_once __DIR__ . '/function_plugin.php';


if(empty($_POST['search'])) {
	echo '<div style="background-color:red;padding:3px;font-size:16px; width: fit-content;">Введите ID товара или его код</div>';
	exit;
} else {
	
$table_name = $wpdb->prefix . 'none_products';
$search_product = $_POST['search'];
$none_product = $wpdb->get_results( "SELECT * FROM $table_name Where product_code LIKE '%$search_product%' " );

if(empty($none_product)) {
$url='http://dptrade.lt/xml.php?user=*******&pass=******&action=product&search='.$_POST['search'].'&lang=ET';		
$search = simplexml_load_file($url, 'SimpleXMLElement', LIBXML_NOCDATA);	

$url='http://dptrade.lt/xml.php?user=*******&pass=*******&action=product&id='.$_POST['search'].'&lang=ET';
$product_id = simplexml_load_file($url, 'SimpleXMLElement', LIBXML_NOCDATA);
}

}


echo '<form id="import_search" method="post">';
echo '<div class="reload"><img src="http://preloaders.net/preloaders/287/Filling%20broken%20ring.gif"></div>';	
/*******Вывод категорий	*************/
$product_categories = get_terms( array(
	'hide_empty'  => 0,  
	'taxonomy'    => 'product_cat') );
$count = count($product_categories);
 if ( $count > 0 ){

  echo '<div class="tabs-panel">';
  echo '<h3>Tootekategooriad</h3>';
  echo '<ul id="brandchecklist" class="categorychecklist form-no-clear">';
 
 foreach ( $product_categories as $product_category ) {
	  echo "<li id='category-$product_category->term_id' class='wpseo-term-unchecked'><label class='selectit'><input name='category[]' value='{$product_category->slug}' type='checkbox'>$product_category->name</label></li>";  
}
echo '</ul>'; echo '</div>';
 }
   echo '<button type="submit" id="in_file" style="margin:15px auto" class="button button-primary"> Вернутся к товарам со списка</button>';
/*******Вывод категорий	*************/

echo '<table class="form-table" role="presentation"><tbody>	<tr>
		<th scope="row"><label for="blogname">Наценка для выбранных товаров в %(число)</label></th>
		<td>
		<input type="text" class="regular-text" name="price_discount" value=""></td></tr></tbody>
		</table>';
	echo '<h3>Tooted</h3>';	
    echo '<table class="widefat fixed striped posts">';
    $td = 1;
if(!empty($product_id)) {
	foreach($product_id->product as $key => $value) { 
//$product_name = preg_grep('~'.$value->name1.'~', products_cache());
//$product_code = preg_grep('~'.$value->product_code.'~', products_cache());   
$product_code = array_search($value->product_code,products_cache());
if (!empty($product_code)) { 
	   if (($td%2==1))  echo '<tr>';
     
	  echo '<td>'; 
  echo "<input name='product[]' checked value='{$value->product_code}' type='checkbox'>$value->name1";
echo '<b>Tootekategooriad:</b> '.product_category($product_code);  
     echo "<br>";
	 echo '</td>';
	   echo '</tr> ';	   
	   } else {
        	   
		if (($td%2==1))  echo '<tr>';
     
	  echo '<td>'; 
		echo "<input id='{$value->product_code}' name='product[]'  value='{$value->product_code}' type='checkbox'><label for='{$value->product_code}'>$value->name1</label>";    
		 echo '</td>';
	  if (($td%2==0))  echo '</tr>';
	   }
		
		++$td;
        }
}
if(!empty($search)) {
	
	foreach($search->product as $key => $value) {   
//$product_name = preg_grep('~'.$value->name1.'~', products_cache());
//$product_code = preg_grep('~'.$value->product_code.'~', products_cache()); 
$product_code = array_search($value->product_code,products_cache());
if (!empty($product_code)) {

	   if (($td%2==1))  echo '<tr>';
     
	  echo '<td>'; 
  echo "<input name='product[]' checked value='{$value->product_code}' type='checkbox'>$value->name1 "; 
     echo '<b>Tootekategooriad:</b> '.product_category($product_code);
     echo "<br>";
	 echo '</td>';
	   echo '</tr> ';	   
	   } else {
        	   
		if (($td%2==1))  echo '<tr>';
     
	  echo '<td>'; 
		echo "<input id='{$value->product_code}' name='product[]'  value='{$value->product_code}' type='checkbox'><label for='{$value->product_code}'>$value->name1</label>";    
		 echo '</td>';
	  if (($td%2==0))  echo '</tr>';
	   }
		
		++$td;
        }
}


if(!empty($none_product)) {
    
	foreach($none_product as  $value) { 
    $url='http://dptrade.lt/xml.php?user=******&pass=*******&action=product&search='.$value->product_code.'&lang=ET';		
$search = simplexml_load_file($url, 'SimpleXMLElement', LIBXML_NOCDATA);

	
foreach($search->product as $key => $value) {   

if (($td%2==1))  echo '<tr>';
     
	  echo '<td>'; 
	  echo '<input type="hidden" class="regular-text" name="product_none" value="1">';
  echo "<input name='product[]' value='{$value->product_code}' type='checkbox'>$value->name1 <label><strong>price</strong>: ".$value->price.$value->currency; 
     echo '<b> В СПИСКЕ НЕ НУЖНЫХ</b>';
     echo "<br>";
	 echo '</td>';
	   echo '</tr> ';	   
	    
		if (($td%2==0))  echo '</tr>';
		++$td;
        }
}
}

		echo '</table>';
		if(empty($search) && empty($product_id)) {
			echo '<h2 style="font-size:16; font-weight:bold;">Ничего не найдено по указанным параметрам</h2>';
		}
		echo '<br><button type="submit" id="upload_search" class="button button-primary"> Загрузить товар</button>';
echo '</form>';	
  	?>
<script>
	jQuery('#upload_search').on('click', function(e){
	e.preventDefault();
	pagination = <?=($startPage&&$startPage>0)?$startPage:0?>;
  var  product_import = new Array();
	 jQuery('input[name=\'product[]\']:checked').each(function(){
       product_import.push(jQuery(this).val());
    });
  var  category_import = new Array();
	 jQuery('input[name=\'category[]\']:checked').each(function(){
       category_import.push(jQuery(this).val());
    });
	
	price_discount = jQuery('input[name=\'price_discount\']').val();
	product_none = jQuery('input[name=\'product_none\']').val();
	jQuery.ajax({
		url: '<?php echo UPPER_IMPORT_URL; ?>import_products.php',
		type: 'post',
		data: {product_import:product_import, category_import:category_import, pagination:pagination, price_discount:price_discount,product_none:product_none},
		dataType: 'json',
		crossDomain: true,
		beforeSend: function() {
			jQuery('#upload_search').text('Загрузка, 5 сек...');
			jQuery('.reload').css('display','block'); 
		},
		
		success: function(json) {
		
			jQuery('#upload_search').text('Загрузить');
			jQuery('.reload').css('display','none'); 
			if(json['error_product']) {
				
				jQuery('.widefat.fixed.striped.posts').after('<div style="background-color:red;padding:3px;font-size:16px; width: fit-content;">Отметьте товар для загрузки</div>');
			}
			if(json['error_category']) {
				
				jQuery('#brandchecklist').after('<div style="background-color:red;padding:3px;font-size:16px; width: fit-content;">Отметьте категории для загрузки в них товара</div>');
			}
			if(json['success']) {
				
		    
			 jQuery('#brandchecklist').after('<div style="background-color:green;padding:3px;font-size:16px; width: fit-content;">Загрузка произошла успешно!</div>');			 
			}
		
		}
	}) 
	})
</script>