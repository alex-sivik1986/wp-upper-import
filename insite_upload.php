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
	$newfile = 'data-'.date("d.m.y").'.xml';
/******Pagination*******/

if(isset($_POST['url']) && !empty($_POST['url'])) {
	$url_file = $_POST['url'];
	if (!copy($url_file, $newfile)) {
    echo "не удалось скопировать $newfile...\n";
    } 
    
}

	
if(is_file($newfile)) {
	echo 'Товары загружены с файла '.$newfile;
	echo "<br>";
	$xml = simplexml_load_file($newfile, 'SimpleXMLElement', LIBXML_NOCDATA);
	
$result = $xml->xpath("/products/product"); 

 foreach ($result as $key=>$el) { 
 $json = json_encode($el->product_code); 
 $test = json_decode($json,TRUE); 

	 if ( array_key_exists($test[0], products_none_cache())) {  
		 $domRef = dom_import_simplexml($el); 
		 $domRef->parentNode->removeChild($domRef); 
		 $dom = new DOMDocument('1.0'); 
		 $dom->preserveWhiteSpace = false; 
		 $dom->formatOutput = true; 
		
		 } 
	 } 
if((isset($_POST['url']) && !empty($_POST['url'])) || array_key_exists($test[0], products_none_cache())) {
	 $dom->loadXML($xml->asXML()); 
	 $dom->save($newfile); 
}
	 
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
 echo '<div class="success">Товар успешно обновлен!</div>';
 }
   
/*******Вывод категорий	*************/

	echo '<a id="in_file" href="/wp-admin/admin.php?page=wp-upper-import" style="margin:15px auto" class="button button-primary"> Вернутся к товарам со списка</a>';
    echo '<h3>Товары которые уже на сайте</h3>';
echo '<table class="form-table" role="presentation"><tbody>	<tr>
		<th scope="row"><label for="blogname">Наценка для выбранных товаров в %(число)</label></th>
		<td>
		<input type="text" class="regular-text" name="price_discount" value=""></td></tr></tbody>
		</table>';	
	echo '<table class="widefat fixed striped posts">';
		echo '<tr><td><input type="checkbox" data-col="d2" id="all-check1" class="all" /><label for="all-check1">Отметить колонку</label></td>';
		echo '<td><input type="checkbox" data-col="d1" id="all-check2" class="all" /><label for="all-check2">Отметить колонку</label></td></tr>';
	echo '</table>';
    echo '<table class="widefat fixed striped posts">';
    $td = 1;


$result = $xml->xpath("/products/product"); 

foreach($result as $key => $value)  {     

//$product_name = preg_grep('~'.$value->name1.'~', products_cache());
//$product_code = preg_grep('~'.$value->product_code.'~', products_cache()); 
  
 //if (!empty($product_code) or !empty($product_name)) { 
$product_code = array_search($value->product_code,products_cache());
 if ($product_code!=false) { 
$currentRecord += 1;
	if($currentRecord > ($startPage * $perPage) && $currentRecord < ($startPage * $perPage + $perPage)){
		 if (($td%2==0)){$m=1;} else { $m=2; }
		if (($td%2==1))  echo '<tr>';     
	  echo '<td>'; 
	  //  $id = !(empty($product_name))?key($product_name):key($value->product_code);
	    $price = get_post_meta( $product_code, '_regular_price', true );
		echo "<input class='one' data-col='d{$m}' data-id='{$product_code}' name='product[]'  value='{$value->product_code}:{$product_code}' type='checkbox'><label for='{$value->product_code}'>$value->name1</label> ";   echo '<label><strong>price</strong>: '.$price.$value->currency.'</label> <strong>Tootekood</strong>: '.$value->product_code;  
echo ' <b>Tootekategooriad:</b> '.product_category($product_code);		
		 echo '</td>';
	  if (($td%2==0))  echo '</tr>';
	  ++$td;
 }
	}	
 				
}
		echo '</table>';
		echo '<div class="error-product">Отметьте товар для обновления</div>';
		echo '<button type="submit" style="margin:15px auto" id="update" class="button button-primary"> Обновить товары</button>';
echo '</form>';	


// pagination:
echo '<div class="pagination">';
        for ($i = 0, $m = 1; $i <= ($currentRecord / $perPage); $m++, $i++) {
			if($startPage == $i) {
				if($i == 0) {
					
			echo("<a style='background-color:#ddd' href='?page=wp-upper-import&action=insite_upload'>1</a>");            		
				} else {
            echo("<a style='background-color:#ddd' href='?page=wp-upper-import&action=insite_upload&paged=".$i."'>".$m."</a>");
				}
			} else {
				if($i == 0) {
			echo("<a href='?page=wp-upper-import&action=insite_upload'>1</a>");             		
				} else {
            echo("<a href='?page=wp-upper-import&action=insite_upload&paged=".$i."'>".$m."</a>");
				}
			}
        }
echo "</div>";  
	}	


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
	jQuery('#update').on('click', function(e){
	e.preventDefault();
	pagination = <?=($startPage&&$startPage>0)?$startPage:0?>;
  var  product_update = new Array();
	 jQuery('input[name=\'product[]\']:checked').each(function(){
       product_update.push(jQuery(this).val());
	  // product_update.push(jQuery(this).attr("data-pid"));
    });
	//console.log(product_import)
  var  category_import = new Array();
	 jQuery('input[name=\'category[]\']:checked').each(function(){
       category_import.push(jQuery(this).val());
    });
	price_discount = jQuery('input[name=\'price_discount\']').val();
	jQuery.ajax({
		url: '<?php echo UPPER_IMPORT_URL; ?>update_products.php',
		type: 'post',
		data: {product_update:product_update, category_import:category_import,price_discount:price_discount},
		dataType: 'json',
		crossDomain: true,
		beforeSend: function() {
			jQuery('#update').text('Загрузка, ...');
			jQuery('.reload').css('display','block'); 
		},
		
		success: function(json) {
		    jQuery('#update').text('Обновить товары');
		    jQuery('.error-cat').css('display','none');
			jQuery('.error-product').css('display','none');
			jQuery('.reload').css('display','none'); 
			if(json['error_product']) {
				jQuery('.error-product').css('display','block');	
			}
			if(json['error_category']) {
				jQuery('html, body').animate({ scrollTop: 0 }, 'slow');
				jQuery('.error-cat').css('display','block');
			}
			if(json['success']) {
			   jQuery('html, body').animate({ scrollTop: 0 }, 'slow');
		      jQuery('.success').css('display','block');			 
			}
		
		}
	}) 
	})
</script> 