<?php
/*
Plugin Name: Lists Outliner
Description: Genric free-form outlined lists plugin. adds a "List" option in the write pane for creating lists and a "Lists" option in the manage pane for managing lists. Lists can be displayed with widgets or template tags. 
Version: 0.1
Author: Ran Yaniv Hartstein
Author URI: http://ranh.co.il/
*/

/*  Copyright 2008 Ran Yaniv Hartstein (email : ran at ranh co il)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

$outliner_db_version = "12";


function outliner_db($outlines,$items) {
	
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	$sql = "CREATE TABLE " . $outlines . " (
		list_id SERIAL,
		list_title varchar(200) NOT NULL,
		list_name varchar(200) NOT NULL,
		list_template text,
		UNIQUE KEY (list_id)
	);";

	if ($debug) { echo $sql; }
	dbDelta($sql);

	$sql = "CREATE TABLE " . $items . " (
		item_id SERIAL,
		item_list bigint(20) NOT NULL,
		item_title text,
		item_URL varchar(255),
		item_order int(11) NOT NULL,
		item_parent_id bigint(20),
		UNIQUE KEY (item_id) 
	);";

	if ($debug) { echo $sql; }
	dbDelta($sql);
	
}

function outliner_init() {
	
	global $outliner_db_version;
	global $wpdb;
	$outlines_table = $wpdb->prefix . "outlines";
	$items_table = $wpdb->prefix . "outline_items";
	
	if ( ($wpdb->get_var("show tables like '$outlines_table'") != $outlines_table) || ($wpdb->get_var("show tables like '$items_table'") != $items_table) ) {
		
		outliner_db($outlines_table,$items_table);

		add_option("outliner_db_version", $outliner_db_version);
		
	} else {
		
		$installed_ver = get_option( "outliner_db_version" );
		
		if( $installed_ver != $outliner_db_version ) {

			outliner_db($outlines_table,$items_table);

			update_option( "outliner_db_version", $outliner_db_version );

		}
	}
	
}

function outline($list) {
	
	global $wpdb;
	
	$outlines_table = $wpdb->prefix . "outlines";
	$items_table = $wpdb->prefix . "outline_items";
	
	$sql = 'SELECT item_title FROM '.$items_table.' WHERE item_list="'.$list.'"';
	
	$items = $wpdb->get_results($sql);
	
	if (!empty($items)) {
		foreach ($items as $item) {
			echo $item->item_title . '<br />';
		}
	}
}

function outliner_admin() {
	
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	global $wpdb;
	$outlines_table = $wpdb->prefix . "outlines";
	$items_table = $wpdb->prefix . "outline_items";
	
	if ( $_POST['action'] = 'outliner_update' ) {
	
		if (isset($_POST["save"]) && trim($_POST["save"])!=='') {

			// See if want to save changes or add a row
			foreach ($_POST['items'] as $field => $items) { 

				$sql = $sql . 'UPDATE '.$items_table.' SET item_title="'.$items['title'].'",item_URL="'.$items['URL'].'" WHERE item_id="'.$items['id'].'";';
				
			}


			dbDelta($sql);

		} elseif (isset($_POST["add"]) && trim($_POST["add"])!=='') {
			
			$sql = 'INSERT INTO '.$items_table.' (item_list) VALUES ("1")';
			
			$wpdb->query($sql);
			
		} else {
			
		}

	}

//the page itself
?>

<div class="wrap">
	
	<form name="outliner_options" method="post" 
		action="<?php echo $_SERVER[PHP_SELF]; ?>?page=outliner.php">

		<input type="hidden" name="action" value="outliner_update" />

    	<h2>Lists Outliner</h2>
		
		<table class="form-table">
			<tbody>
			
			<?php $sql = 'SELECT item_id,item_title,item_URL FROM '.$items_table.' WHERE item_list="1"';

			$items = $wpdb->get_results($sql);

			if (!empty($items)) {
				foreach ($items as $item) { 
					
					$id = $item->item_id;
					$title = $item->item_title;
					$URL = $item->item_URL; ?>

			<tr valign="top">
				<th scope="row">
					<label 
					for="items[<?php echo $id; ?>][title]"><?php 
					echo $id; ?></label>
					<input type="hidden"
					id="items[<?php echo $id; ?>][id]"
					name="items[<?php echo $id; ?>][id]"
					value="<?php echo $id; ?>" /">
				</th>
				<td>
					<input type="text"
					id="items[<?php echo $id; ?>][title]"
					name="items[<?php echo $id; ?>][title]"
					value="<?php echo $title; ?>" />
				</td>
				<td>
					<input type="text" 
					id="items[<?php echo $id; ?>][URL]"
					name="items[<?php echo $id; ?>][URL]"
					value="<?php echo $URL; ?>" />
				</td>
			</tr>

				<?php }
			} ?>
			
			<tr valign="top">
				<td colspan="3">
					<input name="add" id="add" type="submit" value="Add row">
				</td>
			</tr>	
			
			</tbody>
		</table>

		<p class="submit"><input name="save" id="save" type="submit" value="Save"></p>
   
	</form> 

<?php }

// Add admin pages
function outliner_add_pages() {
    add_options_page('Outliner','Outliner', 8, basename(__FILE__), 'outliner_admin');
}

add_action('admin_menu', 'outliner_add_pages');


// Checks to see if plugin needs to be installed/updated in the DB
add_action( 'init', 'outliner_init' );

?>