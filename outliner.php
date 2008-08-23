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

	echo $sql;
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

	echo $sql;
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

function outline() {
	echo 'outline!';
}

// Checks to see if plugin needs to be installed/updated in the DB
add_action( 'init', 'outliner_init' );
?>