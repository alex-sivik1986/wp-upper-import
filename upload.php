<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
/*
 *
 wp_insert_term
(
$value,
$tag_type, // its ‘product_tag’
array
( ‘description’ => $textsyn,)
);


$terms = get_terms( 'product_tag' );
$term_array = array();
if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
    foreach ( $terms as $term ) {
        $term_array[] = $term->name;
    }
}
if(isset($_COOKIE['dev'])) { var_dump($term_array); }

if(in_array('black',$term_array)) {
 echo 'black exists';
} else {
echo 'not exists';
}
 */
$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
require_once( $parse_uri[0] . 'wp-load.php' );

echo "<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css'>";
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
 foreach (glob("*.xml") as $key => $filename) { 
	
	//if(unlink($filename)); 
} 
$url_file = $_POST['url'];
$newfile = 'data-'.date("d.m.y").'.xml';

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
echo '<div class="reload"><img src="https://preloaders.net/preloaders/287/Filling%20broken%20ring.gif"></div>';	

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
 echo '<div class="success">Товар успешно загружен на сайт!</div>';
 echo '<div class="success-none">Товар будет убран со списка!</div>';
 }
   
/*******Вывод категорий	*************/

	echo '<a id="in_site" href="/wp-admin/admin.php?page=wp-upper-import&action=insite_upload" style="margin:15px auto" class="button button-primary"> Показать загруженные товары</a>';
	echo '<a id="in_site" href="/wp-admin/admin.php?page=wp-upper-import&action=none_upload" style="margin:15px auto; float:right" class="button button-primary button-wpml button-lg"> Показать список ненужных товаров</a>';
	echo '<h3>Tooted</h3>';
	
	echo '<table class="form-table" role="presentation"><tbody>	<tr>
		<th scope="row"><label for="blogname">Наценка для выбранных товаров в %(число)</label></th>
		<td>
		<input type="text" class="regular-text" name="price_discount" value=""></td></tr></tbody>
		</table>';
	echo '<table class="widefat fixed striped posts">';
	echo '<tr><td><input type="checkbox" data-id="d1" id="all-check1" class="all" /><label for="all-check1">Отметить колонку</label></td>';
	echo '<td><input type="checkbox" data-id="d2" id="all-check2" class="all" /><label for="all-check2">Отметить колонку</label></td></tr>';
	echo '</table>';
    echo '<table class="widefat fixed striped posts">';
    $td = 1;
	

$result = $xml->xpath("/products/product"); 	 
foreach($result as $key => $value)  {     
       
//if (!preg_grep('~'.$value->product_code.'~', products_cache()) and !preg_grep('~'.$value->name1.'~', products_cache())) {
	$product_code = array_search($value->product_code,products_cache());  
 if (empty($product_code)) { 	 
		   $currentRecord += 1;
if($currentRecord > ($startPage * $perPage) && $currentRecord < ($startPage * $perPage + $perPage)){
$url='http://dptrade.lt/xml.php?user=******&pass=*******&action=product&search='.$value->product_code.'&lang=ET';

$price = simplexml_load_file($url, 'SimpleXMLElement', LIBXML_NOCDATA);
$one_product = (array)$price->product;

	    if (($td%2==0)){$m=2;} else { $m=1; }
		if (($td%2==1))  echo '<tr>';   
	  echo '<td>'; 
		echo "<input class='one' data-id='d{$m}' id='{$value->product_code}' name='product[]'  value='{$value->product_code}' type='checkbox'><label for='{$value->product_code}'>$value->name1 </label>";    echo '<label><strong>price</strong>: '.$one_product['price'].$value->currency.'</label> <strong>Tootekood</strong>: '.$value->product_code;  
		 echo '</td>';
	  if (($td%2==0))  echo '</tr>';
	  ++$td;
 }

	} 
							
}
		echo '</table>';
		echo '<div class="error-product">Отметьте товар для загрузки</div>';
		echo '<button type="submit" style="margin:15px auto" id="import" class="button button-primary"> Загрузить товары</button><i class="reload-import fa fa-refresh fa-spin" style="font-size:24px"></i>';
		
		echo '<button type="submit" style="margin:15px auto; float:right" id="none" class="button button-primary button-wpml button-lg"> Добавить в список не нужных</button><i class="reload-import-none fa fa-refresh fa-spin" style="font-size:24px"></i>';
		echo '<div class="error-product-none">Отметьте товар для переноса в список ненужных</div>';
echo '</form>';	

// pagination:
echo '<div class="pagination">';
        for ($i = 0, $m = 1; $i <= ($currentRecord / $perPage); $m++, $i++) {
			if($startPage == $i) {
				if($i == 0) {
					
			echo("<a style='background-color:#ddd' href='?page=wp-upper-import'>1</a>");            		
				} else {
            echo("<a style='background-color:#ddd' href='?page=wp-upper-import&paged=".$i."'>".$m."</a>");
				}
			} else {
				if($i == 0) {
			echo("<a href='?page=wp-upper-import'>1</a>");             		
				} else {
            echo("<a href='?page=wp-upper-import&paged=".$i."'>".$m."</a>");
				}
			}
        }
echo "</div>";  
	}	


?>
<script>

setTimeout(function(){
    jQuery(".all").on("change", function() {
        var groupId = jQuery(this).data('id');
        jQuery('.one[data-id="' + groupId + '"]').prop("checked", this.checked);
    });

    jQuery(".one").on("change", function() {
        var groupId = jQuery(this).data('id');
        var allChecked = jQuery('.one[data-id="' + groupId + '"]:not(:checked)').length == 0;
        jQuery('.all[data-id="' + groupId + '"]').prop("checked", allChecked);
    });
	
} , 500);



jQuery('#none').on('click', function(e){
	e.preventDefault();
	 var  product_import = new Array();
	 jQuery('input[name=\'product[]\']:checked').each(function(){
       product_import.push(jQuery(this).val());
    });
	jQuery.ajax({
		url: '<?php echo UPPER_IMPORT_URL; ?>none_products.php',
		type: 'post',
		data: {product_import:product_import},
		dataType: 'json',
		crossDomain: true,
		beforeSend: function() {
		
			jQuery('.reload').css('display','block'); 
			jQuery('.reload-import-none').css('display','inline-block'); 
		},
		
		success: function(json) {
		    
		    jQuery('.error-cat').css('display','none');
			jQuery('.error-product-none').css('display','none');
			jQuery('.reload').css('display','none'); 
			jQuery('.reload-import-none').css('display','none'); 
			if(json['error_product']) {
				jQuery('.error-product').css('display','block');	
			}
			if(json['success']) {
			   jQuery('html, body').animate({ scrollTop: 200 }, 'slow');
			   jQuery('input.one').each(function(){
					if(jQuery(this).is(':checked')){
				//	console.log(jQuery(this).attr('class'))
				//	jQuery(this).remove()
					jQuery(this).replaceWith('<b>Добавлен в список не нужных</b> ')
				}
				})
		      jQuery('.success-none').css('display','block');			 
			}
		
		}
	})
	
	
	
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
	price_discount = jQuery('input[name=\'price_discount\']').val();
	jQuery.ajax({
		url: '<?php echo UPPER_IMPORT_URL; ?>import_products.php',
		type: 'post',
		data: {product_import:product_import, category_import:category_import, pagination:pagination, price_discount:price_discount},
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
				jQuery('html, body').animate({ scrollTop: 0 }, 'slow');
				jQuery('.error-cat').css('display','block');
			}
			if(json['success']) {
			   jQuery('html, body').animate({ scrollTop: 250 }, 'slow');
			   
			   jQuery('input.one').each(function(){
					if(jQuery(this).is(':checked')){
					//console.log(jQuery(this).attr('class'))
				//	jQuery(this).remove()
				jQuery(this).replaceWith('<b>Загружен на сайт</b> ');
				}
				})
			   
		      jQuery('.success').css('display','block');			 
			}
		
		}
	}) 
	})
</script> 