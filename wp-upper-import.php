<?php
/*
 * Plugin Name: Upper-import
 * Author: Sivik
 * Description: Плагин импорта по ссылке http://www.dptrade.lt
 
 */
 
define('UPPER_IMPORT_DIR', plugin_dir_path(__FILE__));
define('UPPER_IMPORT_URL', plugin_dir_url(__FILE__));


	
register_activation_hook(__FILE__, 'upper_import_activation');
register_deactivation_hook(__FILE__, 'upper_import_deactivation');



function upper_import_activation() {
     upper_import_load();
	 create_plugin_tables();
    	
}
 
function upper_import_deactivation() {
    // при деактивации
}

add_action( 'admin_menu', 'upper_import_load' );
function upper_import_load(){
	 $page_title = 'Import';
	 $menu_title = 'Import products';
	 $capability ='manage_options';
	 $menu_slug = 'wp-upper-import';
	 $function = 'import_admin_page';
	 $icon_url = 'dashicons-upload';
	 $position = 10;
	
     add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );

    /*if(is_admin()) // подключаем файлы администратора, только если он авторизован
        require_once(UPPER_IMPORT_DIR.'includes/admin.php');
 
    require_once(UPPER_IMPORT_DIR.'includes/core.php');*/
		
}

function create_plugin_tables()
{
	global $wpdb;
	$table_name = $wpdb->prefix . 'none_products';
	$sql = "CREATE TABLE $table_name (
			id int(11) NOT NULL AUTO_INCREMENT,
			product_code varchar(255) DEFAULT NULL,
			name varchar(255) DEFAULT NULL,
			UNIQUE KEY id (id)
	);";
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
	
	$table_name_tags = $wpdb->prefix . 'tags_products';
	
	$sql_tags = "CREATE TABLE $table_name_tags (
			id int(11) NOT NULL AUTO_INCREMENT,
			product_code varchar(255) DEFAULT NULL,
			name varchar(255) DEFAULT NULL,
			tags text DEFAULT NULL,
			UNIQUE KEY id (id)
	);";
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql_tags );
	
}	
	 
function import_admin_page() {

?>
<script>
<?php  if(isset($_GET['action']) && $_GET['action'] == 'insite_upload') {  ?>

pagination = <?=(isset($_GET['paged']))?$_GET['paged']:0?>;

let product_in_site = 1;
jQuery.ajax({
	url: '<?php echo UPPER_IMPORT_URL; ?>insite_upload.php',
	type: "post",
	data:  {pagination:pagination, product_in_site:product_in_site},
	crossDomain: true,
	success: function(data){
		
	  jQuery('.upproducts').html(data);
	},
			
});

<?php }  elseif(isset($_GET['action']) && $_GET['action'] == 'none_upload') {  ?>

pagination = <?=(isset($_GET['paged']))?$_GET['paged']:0?>;

let product_in_site = 1;
jQuery.ajax({
	url: '<?php echo UPPER_IMPORT_URL; ?>none_upload.php',
	type: "post",
	data:  {pagination:pagination, product_in_site:product_in_site},
	crossDomain: true,
	success: function(data){
		
	  jQuery('.upproducts').html(data);
	},
			
});

<?php } else { ?>
<?php if(isset($_GET['paged']) && $_GET['paged'] > 0) { ?>
pagination = <?=$_GET['paged']?>;

jQuery.ajax({
	url: '<?php echo UPPER_IMPORT_URL; ?>upload.php',
	type: "post",
	data:  {pagination:pagination},
	crossDomain: true,
	success: function(data){
		
	  jQuery('.upproducts').html(data);
	},
			
});	
<?php } else { ?>	
jQuery(document).ready(function() {
	jQuery('.upproducts').load('<?php echo UPPER_IMPORT_URL; ?>upload.php');
})
<?	} }	?>
</script>	

<link rel='stylesheet' id='import_files'  href='/wp-content/plugins/wp-upper-import/styles-import.css' type='text/css' media='all' />
	<div class="wrap">
		<h2><?php echo get_admin_page_title() ?></h2>
<form id="upload_form" method="POST">
		<table class="form-table" role="presentation">
		<tbody>
		<tr>
		<th scope="row"><label for="blogname">Введите ссылку</label></th>
		<td>
		<input type="text" class="regular-text" name="import_file" value>
		
		<button id="upload-file" class="button button-primary" type="submit">Загрузить</button>
		<div class="dateproducts"></div>
		</td>
		</tr>
		</tbody>
		</table>		
			
</form>

<form id="search_form" method="POST">
		<table class="form-table" role="presentation">
		<tbody>
		<tr>
		<th scope="row"><label for="blogname">Поиск по коду или ID товара на сайте http://dptrade.lt</label></th>
		<td>
		<input type="text" class="regular-text" name="search_product" value>
		
		<button id="search-product" class="button button-primary" type="submit">Поиск</button>
		<div class="dateproducts"></div>
		</td>
		</tr>
		</tbody>
		</table>		
			
</form>
		<div class="upproducts"></div>
		<img id="paint"  src="https://preloaders.net/preloaders/287/Filling%20broken%20ring.gif">
		<div class="reload"><img src="https://preloaders.net/preloaders/287/Filling%20broken%20ring.gif"></div>	
	</div>	
		
<script>
jQuery(document).ready(function () {
    setTimeout(function(){
  document.getElementById("paint").style.display = "none"; 
 }, 7000);
});
jQuery('#search-product').on('click', function(e){
	e.preventDefault();
	var search_product = jQuery("input[name='search_product']").val();
	
	pagination = <?=($_GET['paged']&&$_GET['paged']>0)?$_GET['paged']:0?>;

	jQuery.ajax({
		url: '<?php echo UPPER_IMPORT_URL; ?>search.php',
		type: 'post',
		data: {search:search_product, pagination:pagination},
		crossDomain: true,
		beforeSend: function() {
			jQuery('.reload').css('display','block'); 
            
			jQuery('#search-product').text('Поиск ...');
		},
		
		success: function(data) {
			jQuery('.reload').css('display','none'); 
			 jQuery('#search-product').text('Поиск');
		     jQuery('.upproducts').html(data);
		
		}
	})
	
	
})


jQuery('#upload-file').on('click', function(e){
	e.preventDefault();
	var upload_url = jQuery("input[name='import_file']").val();
	
	pagination = <?=($_GET['paged']&&$_GET['paged']>0)?$_GET['paged']:0?>;

	jQuery.ajax({
		url: '<?php echo UPPER_IMPORT_URL; ?>upload.php',
		type: 'post',
		data: {url:upload_url, pagination:pagination},
		crossDomain: true,
		beforeSend: function() {
			jQuery('.reload').css('display','block'); 
            
			jQuery('#upload-file').text('Загрузка, 10 сек...');
		},
		
		success: function(data) {
			 jQuery('.reload').css('display','none'); 
			 jQuery('#upload-file').text('Загрузить');
		     jQuery('.upproducts').html(data);
			 jQuery('input[name="import_file"]').val('');
		
		}
	})
	
	
})	


</script>

<?php
	
}