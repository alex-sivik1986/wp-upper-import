<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
require_once( $parse_uri[0] . 'wp-load.php' );
echo "<link rel='stylesheet' id='import_files'  href='/wp-content/plugins/wp-upper-import/styles-import.css' type='text/css' media='all' />";

include_once __DIR__ . '/function_plugin.php';

/******Pagination*******/
$startPage = 0;
if(isset($_POST['pagination'])) {
	$startPage = $_POST['pagination'];		
}	
    $perPage = 120;
    $currentRecord = 0;	
/******Pagination*******/
 global $wpdb;
        $newtable = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}none_products", ARRAY_A);


	echo "<br>";

echo '<form id="form_products" method="post">';
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
  echo '</ul>';

  echo '</div>';
  echo '<div class="error-cat">Отметьте категории для загрузки в них товара</div>';
  echo '<div class="success">Товар успешно добавлен!</div>';
 }
   
/*******Вывод категорий	*************/

	echo '<a id="in_file" href="/wp-admin/admin.php?page=wp-upper-import" style="margin:15px auto" class="button button-primary"> Вернутся к товарам со списка</a>';
    echo '<h3>Товары в списке не нужных</h3>';
echo '<table class="form-table" role="presentation"><tbody>	<tr>
		<th scope="row"><label for="blogname">Наценка для выбранных товаров в %(число)</label></th>
		<td>
		<input type="text" class="regular-text" name="price_discount" value=""></td></tr></tbody>
		</table>';	
	echo '<table class="widefat fixed striped posts">';
		echo '<tr><td><input type="checkbox" data-col="d1" id="all-check1" class="all" /><label for="all-check1">Отметить колонку</label></td>';
		echo '<td><input type="checkbox" data-col="d2" id="all-check2" class="all" /><label for="all-check2">Отметить колонку</label></td></tr>';
	echo '</table>';
    echo '<table class="widefat fixed striped posts">';
    $td = 1;
	
foreach($newtable as  $value)  {     
  
$currentRecord += 1;

if($currentRecord > ($startPage * $perPage) && $currentRecord < ($startPage * $perPage + $perPage)){
$url='http://dptrade.lt/xml.php?user=********&pass=******&action=product&search='.$value["product_code"].'&lang=ET';
$price = simplexml_load_file($url, 'SimpleXMLElement', LIBXML_NOCDATA);
$one_product = (array)$price->product;

	if (($td%2==0)){$m=2;} else { $m=1; }
	if (($td%2==1))  echo '<tr>';   
	  echo '<td>'; 
	//echo "<input id='{$value['product_code']}'>"; 
	  echo "<input class='one' data-col='d{$m}' id='{$value['product_code']}' name='product[]'  value='{$value['product_code']}' type='checkbox'><label for='{$value['product_code']}'>{$value['name']} </label>"; echo '<label><strong>price</strong>: '.$one_product['price'].$one_product['currency'].'</label> <strong>Tootekood</strong>: '.$value['product_code'];  
	  echo '</td>';
	if (($td%2==0))  echo '</tr>';
	  ++$td;
 }
 				
}
		echo '</table>';
		echo '<div class="error-product">Отметьте товар для загрузки на сайт</div>';
		echo '<button type="submit" style="margin:15px auto" id="import" class="button button-primary"> Добавить товары на сайт</button>';
echo '</form>';	


// pagination:
echo '<div class="pagination">';
        for ($i = 0, $m = 1; $i <= ($currentRecord / $perPage); $m++, $i++) {
			if($startPage == $i) {
				if($i == 0) {
					
			echo("<a style='background-color:#ddd' href='?page=wp-upper-import&action=none_upload'>1</a>");            		
				} else {
            echo("<a style='background-color:#ddd' href='?page=wp-upper-import&action=none_upload&paged=".$i."'>".$m."</a>");
				}
			} else {
				if($i == 0) {
			echo("<a href='?page=wp-upper-import&action=none_upload'>1</a>");             		
				} else {
            echo("<a href='?page=wp-upper-import&action=none_upload&paged=".$i."'>".$m."</a>");
				}
			}
        }
echo "</div>";  
		


?>
<script>
jQuery(document).ready(function() { 
    jQuery(".all").on("change", function() {
        var groupId = jQuery(this).data('col');
        jQuery('.one[data-col="' + groupId + '"]').prop("checked", this.checked);
    });

    jQuery(".one").on("change", function() {
        var groupId = jQuery(this).data('col');
        var allChecked = jQuery('.one[data-col="' + groupId + '"]:not(:checked)').length == 0;
        jQuery('.all[data-col="' + groupId + '"]').prop("checked", allChecked);
    });
});

jQuery('#import').on('click', function(e){
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
	product_none = 1;
	price_discount = jQuery('input[name=\'price_discount\']').val();
	jQuery.ajax({
		url: '<?php echo UPPER_IMPORT_URL; ?>import_products.php',
		type: 'post',
		data: {product_import:product_import, category_import:category_import, pagination:pagination, price_discount:price_discount, product_none:product_none},
		dataType: 'json',
		crossDomain: true,
		beforeSend: function() {
			jQuery('#import').text('Загрузка, ...');
			jQuery('.reload').css('display','block'); 
			jQuery('.reload-import').css('display','inline-block'); 
		},
		
		success: function(json) {
		    jQuery('#import').text('Загрузить');
		    jQuery('.error-cat').css('display','none');
			jQuery('.error-product').css('display','none');
			jQuery('.reload').css('display','none'); 
			jQuery('.reload-import').css('display','none'); 
			if(json['error_product']) {
				jQuery('.error-product').css('display','block');	
			}
			if(json['error_category']) {
				jQuery('html, body').animate({ scrollTop: 200 }, 'slow');
				jQuery('.error-cat').css('display','block');
			}
			if(json['success']) {
			   jQuery('html, body').animate({ scrollTop: 200 }, 'slow');
		      jQuery('.success').css('display','block');			 
			}
		
		}
	}) 
	})
</script> 