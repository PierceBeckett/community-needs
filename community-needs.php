<?php
/*
Plugin Name: Community Needs
Plugin URI:
Description: Post disaster community needs database
Version: 2.21.09.29.A
Author URI: piercebeckett.net
Licencse: GPL3
License URI:  https://www.gnu.org/licenses/gpl-3.0.html
*/

if ( ! defined( 'WPINC' ) ) {
   die;
}

register_activation_hook( __FILE__, 'communityneeds_install' );

register_uninstall_hook( __FILE__, 'communityneeds_uninstall' );

function communityneeds_install () {

	 // setup db tables
	 global $wpdb;
	 require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	 $table_name = $wpdb->prefix . 'communityneeds_users';

	 $sql = "CREATE TABLE $table_name (
	   id mediumint(9) NOT NULL AUTO_INCREMENT,
	   name tinytext DEFAULT '' NOT NULL,
	   email varchar(128) DEFAULT '' NOT NULL,
	   telefone varchar(20) DEFAULT '' NOT NULL,
	   webaddress varchar(256) DEFAULT '' NOT NULL,
	   info text DEFAULT '' NOT NULL,
	   location_id mediumint(9),
	   password varchar(128) NOT NULL,
	   lastlogin datetime,
	   language varchar(2) DEFAULT '' NOT NULL,
	   PRIMARY KEY  (id),
	   KEY location_id (location_id)
	 );";
	 dbDelta( $sql );

	 // alter mods
	 $sql = "ALTER TABLE $table_name ADD UNIQUE (email);";
	 $wpdb->query($sql);

	 $table_name = $wpdb->prefix . 'communityneeds_useritems';
	 $sql = "CREATE TABLE $table_name (
	   id mediumint(9) NOT NULL AUTO_INCREMENT,
	   receiver_id mediumint(9) NOT NULL,
	   category_id mediumint(9) NOT NULL,
	   description text NOT NULL,
	   quantity mediumint(9) DEFAULT '1',
	   PRIMARY KEY  (id),
	   KEY receiver_id (receiver_id),
	   KEY category_id (category_id)
	 );";
	 dbDelta( $sql );

	 // alter mods
	 $sql = "ALTER TABLE $table_name ADD status ENUM('Required','Pending','Received','Removed')";
	 $wpdb->query($sql);


	 $table_name = $wpdb->prefix . 'communityneeds_locations';
	 $sql = "CREATE TABLE $table_name (
	   id mediumint(9) NOT NULL AUTO_INCREMENT,
	   name tinytext DEFAULT '' NOT NULL,
	   region_id mediumint(9),
	   PRIMARY KEY  (id),
	   KEY region_id (region_id)
	 );";
	 dbDelta( $sql );

	 $table_name = $wpdb->prefix . 'communityneeds_regions';
	 $sql = "CREATE TABLE $table_name (
	   id mediumint(9) NOT NULL AUTO_INCREMENT,
	   name tinytext DEFAULT '' NOT NULL,
	   PRIMARY KEY  (id)
	 );";
	 dbDelta( $sql );

	 $table_name = $wpdb->prefix . 'communityneeds_categories';
	 $sql = "CREATE TABLE $table_name (
	   id mediumint(9) NOT NULL AUTO_INCREMENT,
	   group_id mediumint(9) NOT NULL,
	   name tinytext DEFAULT '' NOT NULL,
	   PRIMARY KEY  (id),
	   KEY group_id (group_id)
	 );";
	 dbDelta( $sql );

	 $table_name = $wpdb->prefix . 'communityneeds_categorygroups';
	 $sql = "CREATE TABLE $table_name (
	   id mediumint(9) NOT NULL AUTO_INCREMENT,
	   name tinytext DEFAULT '' NOT NULL,
	   PRIMARY KEY  (id)
	 );";
	 dbDelta( $sql );

	 $table_name = $wpdb->prefix . 'communityneeds_searches';
	 $sql = "CREATE TABLE $table_name (
	   id mediumint(9) NOT NULL AUTO_INCREMENT,
	   keywords tinytext DEFAULT '' NOT NULL,
	   PRIMARY KEY  (id)
	 );";
	 dbDelta( $sql );


	 //add_option('communityneeds_xxx', 'option-value');

}

function communityneeds_uninstall () {

	 // remove options
	 //delete_option('communityneeds_xxx');

	 // remove db tables
	 global $wpdb;
	 //$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}communityneeds-xxx");

}

add_action('init','initialise');

function initialise() {

	 wp_enqueue_script('jquery');


}

function wpb_confirm_leaving_js() { 
 
     wp_enqueue_script( 'Confirm Leaving', plugins_url( 'js/confirm-leaving.js', __FILE__ ), array('jquery'), '1.0.0', true );
}
add_action('wp_enqueue_scripts', 'wpb_confirm_leaving_js'); 


function submit_receiver_form() {
    	global $wpdb;


	if (!isset($_SESSION['email'])) {
    	  echo '<form action="./#cn-edit" method="post">';
	  echo '<p>';
    	  echo 'Email • Email *<br />';
    	  echo '<input type="email" name="cn-email" required="required" />';
    	  echo '</p>';
	  echo '<p>';
    	  echo 'Palavra-passe • Password *<br />';
    	  echo '<input type="password" name="cn-password" required="required" />';
    	  echo '</p>';
    	  echo '<p><button type="submit" name="cn-action" value="Login">Login</button> <button type="submit" name="cn-action" value="Password Reminder" formnovalidate >Lembrete de palavra-passe • Password reminder</button></p>';
	  echo '</form>';

	  echo '<hr />';
	} else {
		$user = $wpdb->get_row("SELECT * FROM wp_communityneeds_users WHERE email = '" . $_SESSION['email'] . "';");
	}

    	echo '<form action="./#cn-edit" method="post" class="checkclose">';
    
	echo '<p>';
    	echo 'Nome • Name *<br />';
    	echo '<input type="text" name="cn-name" required="required" value="' . (isset($user) ? $user->name : '') . '"/>';
    	echo '</p>';
  
	if (!isset($_SESSION['email'])) {
		echo '<p>';
    		echo 'Email • Email *<br />';
    		echo '<input type="email" name="cn-email" required="required" />';
    		echo '</p>';
	}
    
	echo '<p>';
    	echo 'Web • Web<br />';
    	echo '<input type="url" name="cn-webaddress" value="' . ( isset($user) ? $user->webaddress : '') . '" placeholder="http://yourwebaddress.com" />';
    	echo '</p>';
    	echo '<p>';

	echo '<p>';
    	echo 'Telefone • Telephone<br />';
    	echo '<input type="text" name="cn-telefone" pattern="[0-9+ ]+" value="' . ( isset($user) ? $user->telefone : '') . '"/>';
    	echo '</p>';

	echo '<p>';
    	echo 'Info • Info<br />';
    	echo '<textarea name="cn-info" rows="' . ( isset($user) ? count(explode("\n", $user->info))+1 : '4') . '">' . ( isset($user) ? stripslashes($user->info) : '') . '</textarea>';
    	echo '</p>';
    
	echo '<p>Local • Location *<br />';
    	echo '<select rows="10" cols="35" name="cn-locationid" required="required">';
	echo '<option value=""></option>';

    	// write out all location options for each region
    	$reg_table_name = $wpdb->prefix . 'communityneeds_regions';
    	$regions_sql = "SELECT id, name FROM $reg_table_name ORDER BY name;";
    	$regions = $wpdb->get_results( $regions_sql );
    	$loc_table_name = $wpdb->prefix . 'communityneeds_locations';
    	foreach ( $regions as $region ) {
      		echo '<optgroup label="' . $region->name . '">';
      		$locations_sql = 'SELECT id, name FROM ' . $loc_table_name . ' WHERE region_id = ' . $region->id . ' ORDER BY name;';
      		$locations = $wpdb->get_results( $locations_sql );
      		foreach ( $locations as $location ) {
			if (isset($user) && $user->location_id==$location->id) {
			   echo '<option value="' . $location->id . '" selected="selected">' . $location->name . '</option>';
			} else {
      	      		   echo '<option value="' . $location->id . '">' . $location->name . '</option>';
			}
      		}
      	      	echo '</optgroup>';
	}

    	echo '</select>';
    	echo '</p>';

    	echo '<p style="visibility: hidden; height: 0px;">Lingua • Language *<br />';
	echo '<input type="radio" name="cn-language" id="cn-langpt" value="pt" ' . (!isset($user)?'checked':'') .(isset($user)&&$user->language=="pt"?'checked':'') . ' style="display: inline;" /><label for="cn-langpt" style="display: inline;">Português </label>';
	echo '<input type="radio" name="cn-language" id="cn-langen" value="en" ' . (isset($user)&&$user->language=="en"?'checked':'') . ' style="display: inline;" /><label for="cn-langen" style="display: inline;">English</label></p>';



  if (isset($user)) {
	echo '<p id="itemlist">';
    	echo 'Necessidades • Items Needed<br />';

	if (isset($user) ) {
	   $item_table_name = $wpdb->prefix . 'communityneeds_useritems';
	   $cat_table_name = $wpdb->prefix . 'communityneeds_categories';

	   $items_sql = 'SELECT description, quantity, status, c.name as category_name, c.id as category_id
        	       FROM ' . $item_table_name . ' i
            	       INNER JOIN ' . $cat_table_name. ' c ON i.category_id = c.id
            	       WHERE receiver_id = ' . $user->id . ';';

            $items = $wpdb->get_results( $items_sql );
	} else {
	    $items = array((object)[
	    	   'description'	=> '',
		   'quantity'		=> '1',
		   'category_name'	=> '',
		   'category_id'	=> ''
		   ]);
	}
        foreach ( $items as $item ) {
		echo make_cat_row($item);
	}
    	echo '</p><p><input type="button" onclick="addItemRow();return false;" value="+" /></p>';

	echo '<script type="text/javascript">';
	echo 'function addItemRow() {';
	echo '  jQuery( function ($) { $("#itemlist").append(`' . make_cat_row(null) . '`); });';
	echo '};';
	echo '</script>';
   }
	if (isset($user)) {
	  echo '<p><input type="hidden" name="cn-userid" value="' . $user->id . '" />';
	  echo '<p><button type="submit" name="cn-action" value="Update" >Atualizar • Update</button></p>';
	} else {
    	  echo '<p><button type="submit" name="cn-action" value="Register">Registo • Register</button></p>';
	}
    	echo '</form>';
}

function make_cat_row($item) {
	        global $wpdb;

		if (!isset($item)) {
		  $item = (object)[
	    	   'description'	=> '',
		   'quantity'		=> '1',
		   'category_name'	=> '',
		   'category_id'	=> '',
		   'status'		=> 'Required'
		   ];
		}

		$cat_row = '<select name="cn-itemcategoryid[]" required="required" style="width: 74%;">';
		$cat_row .= '<option value="">Categoria • Category</option>';
    
		$cat_table_name = $wpdb->prefix . 'communityneeds_categories';
		$catgroup_table_name = $wpdb->prefix . 'communityneeds_categorygroups';

    		$catgroups_sql = "SELECT id, name FROM $catgroup_table_name ORDER BY name";
		$catgroups = $wpdb->get_results($catgroups_sql);
		foreach ($catgroups as $group) {
    			$cats_sql = "SELECT id, name FROM $cat_table_name WHERE group_id = " . $group->id . " ORDER BY name";
			$cat_row .= '<optgroup label="' . $group->name . '">';
    			$categories = $wpdb->get_results( $cats_sql);
    			foreach ( $categories as $category ) {
				if ($item->category_id===$category->id) {
      				   $cat_row.= '<option value="' . $category->id . '" selected="selected">' . $category->name . '</option>';
				} else {
				  $cat_row.= '<option value="' . $category->id . '">' . $category->name . '</option>';
				}
    			}
			$cat_row .= '</optgroup>';
		}
    		$cat_row .= '</select> ';

		if (isset($_SESSION['email'])) {
		   $cat_row .= '<select name="cn-itemstatus[]" style="width: 24%;" >';
		   $cat_row .= '<option value="Required" ' . ( ($item->status==="Required")?'selected="selected"':'') . ' >Requerido • Required</option>';
		   $cat_row .= '<option value="Pending" ' . ( ($item->status==="Pending")?'selected="selected"':'') . ' >Pendente • Pending</option>';
		   $cat_row .= '<option value="Received" ' . ( ($item->status==="Received")?'selected="selected"':'') . ' >Recebido • Received</option>';
		   $cat_row .= '<option value="Removed" ' . ( ($item->status==="Removed")?'selected="selected"':'') . ' >Removido • Removed</option>';
		   $cat_row .= '</select>';
		}

    		$cat_row .= '<input type="text" name="cn-itemdescription[]" required="required" style="display: inline; width: 86%;" placeholder="descrição • description" value="' . $item->description .'" /> ';
    		$cat_row .= '<input type="text" name="cn-itemquantity[]"  maxlength="4" style="display: inline; width: 12%;" required="required" value="' . $item->quantity . '" /><br />';
    	
		return $cat_row;
}

function list_receiver_items() {

	 global $wpdb;
	 $keywords	= ( isset( $_REQUEST["cn-keywords"] ) ? $_REQUEST["cn-keywords"] : '' ) ;
	 $categoryid 	= isset($_REQUEST["cn-categoryid"]) ? $_REQUEST["cn-categoryid"]: 0;
	 $locationid 	= isset($_REQUEST["cn-locationid"]) ? $_REQUEST["cn-locationid"]: 0;
	 $action	= (isset($_REQUEST["cn-action"]) ? $_REQUEST["cn-action"] : "" );
	 $listtype	= (isset($_REQUEST["cn-listtype"]) ? $_REQUEST["cn-listtype"] : "Category" );
	 $matchtype	= (isset($_REQUEST["cn-matchtype"]) ? $_REQUEST["cn-matchtype"] : "Any" );
	 $start_lim	= (isset($_REQUEST["cn-startlim"]) ? $_REQUEST["cn-startlim"] : "0" );
	 $page_lim	= (isset($_REQUEST["cn-pagelim"]) ? $_REQUEST["cn-pagelim"] : "20" );
	 $action	= (isset($_REQUEST["cn-action"]) ? $_REQUEST["cn-action"] : "" );

	 if ( $action=="Prev" ) {
	     $start_lim -= $page_lim;
	     if ($start_lim<1) {$start_lim=0;}; 
	 } elseif ( $action=="Next" ) {
	     $start_lim += $page_lim;
	 }
	 $end_lim	= $start_lim + $page_lim;
	 $current_page  = ($start_lim==0) ? 1 : round($start_lim / $page_lim)+1;

	 $item_table_name = $wpdb->prefix . 'communityneeds_useritems';
	 $user_table_name = $wpdb->prefix . 'communityneeds_users';
	 $loc_table_name = $wpdb->prefix . 'communityneeds_locations';
	 $reg_table_name = $wpdb->prefix . 'communityneeds_regions';
	 $cat_table_name = $wpdb->prefix . 'communityneeds_categories';
	 $catgroup_table_name = $wpdb->prefix . 'communityneeds_categorygroups';


	 // complete actions afore display!
         switch ($action) {
                case "Email":
		     $to = $_REQUEST["cn-email-to"];
		     $from = $_REQUEST["cn-email-from"];
		     $name = $_REQUEST["cn-email-name"];
		     $message = $_REQUEST["cn-email-message"];
		     $emailbody = '<h2>Response to your list in the Community Needs Database</h2>
		     You have received the following message from <br />
		     ' . $name . ' (' . $from . ')<br />
		     <br />
		     ' . $message . '<br />
		     <br />
		     Good luck!<br />
		     <br />
		     <br />
		     <h2>Resposta à sua lista na Base de Dados de Necessidades Comunitárias</h2>
		     <br />
		     Recebeu essa mensagem de<br />
		     ' . $name . ' (' . $from . ')<br />
		     <br />
                     ' . $message . '<br />
		     <br />
		     Boa sorte!<br />';


		     $headers = array(
		       'Reply-To: ' . $name . '<' . $from . '>',
		     );
		     add_filter( 'wp_mail_content_type', 'cn_set_content_type' );
            	     wp_mail($to, "Community Needs Submission", $emailbody, $headers);
            	     remove_filter( 'wp_mail_content_type','cn_set_content_type' );

		     echo '<p><strong>A sua mensagem foi enviado com sucesso • Your message has been sent successfully</strong></p>';

                     break;
	 }

	 $where_clause = "WHERE u.lastlogin AND i.status IN ('Required', 'Pending', 'Received') ";
	 $order_by = ($listtype=="Person") ? 'ORDER BY u.lastlogin DESC, g.name, c.name ' : 'ORDER BY g.name, c.name, u.lastlogin ';

	 $limit_by = "LIMIT $start_lim, $page_lim";

	 if ( $keywords ) {

	    // store the search terms
	    $search_sql ='';
	    $words = explode(" ", str_replace(")","",str_replace("(","",$keywords)));
	    foreach ($words as $word) {
	    	    $word = sanitize_text_field($word);
	    	    $search_sql = "INSERT INTO wp_communityneeds_searches SET keywords = '$word';";
	    	    $wpdb->query($search_sql);
	    }


	    $andbits = array();
	    $leftovers = $keywords;
	    preg_match_all( "/\([\p{L} \\\'\"\/\-\%0-9]+\)/ui", $keywords, $bracketbits);
	    foreach ($bracketbits as $bracketbit) {
	    	    foreach ($bracketbit as $subbit) {
			    array_push($andbits, str_replace(")","",str_replace("(","",$subbit)));
			    $leftovers = str_replace($subbit, "", $leftovers);
		    }
	    }
	    $leftovers = str_replace("  ", " ", $leftovers);
	    $leftoversA = explode(" ", trim($leftovers));
	    foreach ($leftoversA as $leftover) {
		    array_push($andbits, str_replace(")","",str_replace("(","",$leftover)));
	    }
	    
	    foreach ($andbits as $andbit) {
	    	  $andbit = trim($andbit);
	    	  if ($andbit=="") {continue;};
		  echo '<!-- andbit = '.$andbit.' -->';
		  $orbits = explode(" ", str_replace(')','',str_replace('(','',$andbit)));
		  $where_clause .= "\n AND \n(";
	 	  foreach ($orbits as $orbit) {
	     	 	  $where_clause .= " (description LIKE '%" . $orbit . "%' OR ";
	     	  	  $where_clause .= "u.name LIKE '%" . $orbit . "%' OR ";
	     	  	  $where_clause .= "email LIKE '%" . $orbit . "%' OR ";
	     	  	  $where_clause .= "l.name LIKE '%" . $orbit . "%' OR ";
	     	  	  $where_clause .= "r.name LIKE '%" . $orbit . "%' OR ";
	     	  	  $where_clause .= "g.name LIKE '%" . $orbit . "%' OR ";
	     	  	  $where_clause .= "c.name LIKE '%" . $orbit . "%' ) ";
	     		  $where_clause .= "  OR ";
	   	   }
		   // remove trailing OR
		   $where_clause = substr_replace($where_clause, '', (strlen($where_clause)-3) );
	     	   $where_clause .= ") ";
	     }
	 }

	 if ($categoryid) {
	   $where_clause .= "AND c.id IN (" . $categoryid . ") ";	
	 }

	 if ($locationid) {
	   $where_clause .= "AND l.id IN (" . $locationid . ") ";
	 }


	 $items_sql = 'SELECT SQL_CALC_FOUND_ROWS i.id, description, quantity, status, u.name as user_name, email, webaddress, telefone, info,
	 	    l.name as location_name, r.name as region_name, 
	 	    c.name as category_name, g.name as group_name
	   FROM ' . $item_table_name . ' i 
	   INNER JOIN ' . $user_table_name. ' u ON i.receiver_id = u.id 
	   INNER JOIN ' . $loc_table_name. ' l ON u.location_id = l.id 
	   INNER JOIN ' . $reg_table_name. ' r ON l.region_id = r.id 
	   INNER JOIN ' . $cat_table_name. ' c ON i.category_id = c.id 
	   INNER JOIN ' . $catgroup_table_name. ' g ON g.id = c.group_id '
	   . $where_clause . $order_by . $limit_by;

	   echo '<!-- ' . $items_sql . ' -->';

      	 $items = $wpdb->get_results( $items_sql );
	 $item_count = $wpdb->get_var('SELECT FOUND_ROWS()');
	 if ( $page_lim > $item_count) {$start_lim=0;};
	 $page_count = ceil($item_count / $page_lim);
	 
    	 echo '<a name="cn-list" style="position: relative; top: -150px;">&nbsp;</a>';	 
	 echo '<h4 style="padding:4px;">Pesquisa • Search</h4>';
    	 echo '<form action="./#cn-list" method="post">';

	   echo '<button type="submit" value="cn-search" style="width: 40px; float: right; position: relative; top : 36px;"><svg id="cn-icon-search" class="icon icon-search" viewBox="0 0 30 32">
	   <path class="path1" d="M20.571 14.857q0-3.304-2.348-5.652t-5.652-2.348-5.652 2.348-2.348 5.652 2.348 5.652 5.652 2.348 5.652-2.348 2.348-5.652zM29.714 29.714q0 0.929-0.679 1.607t-1.607 0.679q-0.964 0-1.607-0.679l-6.125-6.107q-3.196 2.214-7.125 2.214-2.554 0-4.884-0.991t-4.018-2.679-2.679-4.018-0.991-4.884 0.991-4.884 2.679-4.018 4.018-2.679 4.884-0.991 4.884 0.991 4.018 2.679 2.679 4.018 0.991 4.884q0 3.929-2.214 7.125l6.125 6.125q0.661 0.661 0.661 1.607z"></path>
	   </svg></button>';

	 echo '<input type="text" name="cn-keywords" onchange="submit();" value="' . stripslashes($keywords) . '" placeholder="Palavras-chave de pesquisa • Search keywords" style="display: inline;" /></p>';


/*
	   echo 'Corresponder • Matching <select name="cn-matchtype" onchange="submit();" >';
	   echo '  <option value="Any" ' . ($matchtype=='Any'?'selected':'') . '>Qualquer • Any</option>';
	   echo '  <option value="All" ' . ($matchtype=='All'?'selected':'') . '>Todos • All</option>';
	   echo '</select> ';

	   echo '<hr />';


	  echo '<select name="cn-locationid" >';
	  echo '<option value="">todos locais • all locations</option>';

          // write out all location options for each region
          $regions_sql = "SELECT id, name FROM $reg_table_name ORDER BY name;";
          $regions = $wpdb->get_results( $regions_sql );
          foreach ( $regions as $region ) {
                echo '<optgroup label="' . $region->name . '">';
                $locations_sql = 'SELECT id, name FROM ' . $loc_table_name . ' WHERE region_id = ' . $region->id . ' ORDER BY name;';
                $locations = $wpdb->get_results( $locations_sql );
		foreach ( $locations as $location ) {
                        if ($locationid==$location->id) {
                           echo '<option value="' . $location->id . '" selected="selected">' . $location->name . '</option>';
                        } else {
                           echo '<option value="' . $location->id . '">' . $location->name . '</option>';
                        }
		}
           	echo '</optgroup>';
      	   }
	   echo '</select>';

	   echo '<select name="cn-categoryid" style="width: 70%;">';
           echo '<option value="">todas categorias • all categories</option>';

           $catgroups_sql = "SELECT id, name FROM $catgroup_table_name ORDER BY name";
           $catgroups = $wpdb->get_results($catgroups_sql);
           foreach ($catgroups as $group) {
                   $cats_sql = "SELECT id, name FROM $cat_table_name WHERE group_id = " . $group->id . " ORDER BY name";
                   echo '<optgroup label="' . $group->name . '">';
                   $categories = $wpdb->get_results( $cats_sql);
                   foreach ( $categories as $category ) {
                           if ($categoryid===$category->id) {
                              echo '<option value="' . $category->id . '" selected="selected">' . $category->name . '</option>';
                           } else {
                              echo '<option value="' . $category->id . '">' . $category->name . '</option>';
                           }
                   }
                   echo '</optgroup>';
           }
           echo '</select>';
*/


	   echo '<p>Mostra pela • List results by ';
	   echo '  <select name="cn-listtype" onchange="submit();">';
	   echo '    <option value="Person" ' .  ( ($listtype==="Person")?'selected':'') . '>Pessoa • Person</option>';
	   echo '    <option value="Category" ' . ( ($listtype==="Category")?'selected':'') . ' >Categoria • Category</option>';
	   echo '</select> ';

	   echo '<span style="float: right;">Per page <select name="cn-pagelim" onchange="submit();">';
	   echo '  <option value="10" ' . ($page_lim==10?'selected':'') . '>10</option>';
	   echo '  <option value="20" ' . ($page_lim==20?'selected':'') . '>20</option>';
	   echo '  <option value="50" ' . ($page_lim==50?'selected':'') . '>50</option>';
	   echo '  <option value="100" ' . ($page_lim==100?'selected':'') . '>100</option>';
	   echo '</select><span>';

	   echo '</p>';

	   echo '<input type="hidden" name="cn-startlim" value="' . $start_lim . '" />';
	   if ($start_lim>0) {
	   	   echo '<button type="submit" name="cn-action" id="cn-prev-page" value="Prev" style="float: left;">&lt;</button> ';
	   }
	   if ($page_count>1) {
	     echo '<p align="center">Pagina • Page ' . $current_page . ' / ' . $page_count . ' ';
	   }
	   if ($item_count>$end_lim) {
	      	   echo '<button type="submit" name="cn-action" id="cn-next-page" value="Next" style="float: right;">&gt;</button> ';
	   }

	   echo '</p>';

	   echo '</form>';

	 if ($listtype=="Person") {

	 	 echo '<table>';
/*		 echo '<tr style="background-color: lightgray;">';
		 echo '<th>Local • Location</th>';
         	 echo '<th>Quem • Whom</th>';
	 	 echo '<th style="width: 160px;">Contato • Contact</th>';
	 	 echo '</tr>';
	 	 echo '<tr>';
         	 echo '<th>Descrição • Description</th>';
         	 echo '<th>Quantidade • Quantity</th>';
	 	 echo '</tr>';
*/
		 $current_user = "";
		 $current_group = "";
	 	 foreach ( $items as $item ) {
		 	 if ( $current_user != $item->user_name) {
         	    	    echo '<tr style="background-color: lightgray;">';
         	    	    echo '<td colspan="2" id="contact-' . $item->id . '" ><a href="./?cn-listtype=Person&amp;cn-keywords=' . $item->user_name . ' ' . $item->location_name . '#cn-list" >' . $item->user_name . '</a>';
			    echo '<span style="float:right;"><a href="https://www.google.com/maps/search/' . $item->location_name . ', ' . $item->region_name . '" target="_blank">' . $item->location_name . ', ' . $item->region_name . '</a></span></td>';
		    	    echo '<td nowrap align="right"><button onclick="showMailForm(\'' . $item->user_name . '\', \'' . $item->email . '\'); return false;" title="email" style="display: inline;">e</button> ';
		    	    if ($item->webaddress) {
			       echo '<a target="_blank" href="' . $item->webaddress . '"><button title="web" onclick="" style="display: inline;">w</button></a> ';
		    	    }
		    	    if ($item->telefone) {
		       	       echo '<button id="tbutton-' . $item->id . '" onclick="showNumber(\'' . $item->id .'\', \'' . $item->telefone . '\'); return false;" title="telefone" style="display: inline;">t</button> ';
		    	    }
			    if ($item->info) {
			        echo '<button onClick="showInfo(\'' . $item->user_name . '\', `' . $item->info . '`); return false;" title="info">i</button> ';
			    }
		    	    echo '</td></tr>';
		    	    $current_user = $item->user_name;
		 	 }
			 
			 if ( $current_group != $item->group_name . $item->category_name) {
                            echo '<tr style="background-color: #eeeeee;"><td colspan="3"><strong>' . $item->group_name . '</strong> <span style="float: right; font-weight: 600;">' . $item->category_name . '</span></td></tr>';
                            $current_group = $item->group_name . $item->category_name;
                         }

	 	 	 if ($item->status=="Pending") {
		   	   echo '<tr style="background-color: #fff4d3;" >';
		 	 } elseif ($item->status=="Received") {
			   echo '<tr style="background-color: #d6f5d6;" >';
			 } else {
		   	   echo '<tr>';
		 	 }

         	 	 echo '<td valign="top">' . $item->description . '</td>'; 
			 echo '<td valign="top">' . $item->quantity . '</td>';

		 	 $pt_status = "Requerido";
		 	 if ($item->status=="Pending") {$pt_status="Pendente";};
		 	 if ($item->status=="Received") {$pt_status="Recibido";};
		 	 echo '<td valign="top">' . $pt_status . ' • ' . $item->status . '</td>';
		 	 echo '</tr>';
      	 	 }

	 	 echo '</table>';
	} elseif ($listtype=="Category") {
	  	 // display by the item cateogries

		 echo '<table>';
/*                 echo '<tr style="background-color: lightgray;">';
                 echo '<th>Item • Item info</th>';
                 echo '<th>Local • Location</th>';
                 echo '<th style="width: 120px;">Contato • Contact</th>';
                 echo '</tr>';
*/
                 $current_group = "";
                 foreach ( $items as $item ) {
                         if ( $current_group != $item->group_name . $item->category_name) {
                            echo '<tr style="background-color: lightgray;"><td colspan="3"><strong>' . $item->group_name . '</strong> <span style="float: right; font-weight: 600;">' . $item->category_name . '</span></td></tr>';
                            $current_group = $item->group_name . $item->category_name;
                         }

			 if ($item->status=="Pending") {
		   	   echo '<tr style="background-color: #fff4d3;" >';
		 	 } elseif ($item->status=="Received") {
			   echo '<tr style="background-color: #d6f5d6;" >';
		 	 } else {
		   	   echo '<tr>';
		 	 }

                         echo '<td valign="top"><span style="float: right;">' . $item->quantity . '</span>' . $item->description . '</td>';
			 echo '<td valign="top" nowrap>' . $item->region_name . '</td>';
			 echo '<td valign="top"><input type="button" name="cn-showcontactrow" value="+" style="float: right;" onclick="showContactline(\'' . $item->id . '\', this);"/></td>';
                         echo '</tr>';
                         
			 echo '<tr style="display: none;" id="cn-contactline-' . $item->id . '">';
			 echo '<td id="contact-' . $item->id . '" ><a href="./?cn-listtype=Person&amp;cn-keywords=' . $item->user_name . ' ' . $item->location_name . '#cn-list" >' . $item->user_name . '</a> - <a href="https://www.google.com/maps/search/' . $item->location_name . ', ' . $item->region_name . '" target="_blank">' . $item->location_name . ', ' . $item->region_name . '</a></td>';
                         echo '<td colspan="2" nowrap align="right"><button onclick="showMailForm(\'' . $item->user_name . '\', \'' . $item->email . '\'); return false;" title="email" style="display: inline;">e</button> ';
                         if ($item->webaddress) {
                            echo '<a target="_blank" href="' . $item->webaddress . '"><button title="web" onclick="" style="display: inline;">w</button></a> ';
                         }
                         if ($item->telefone) {
                            echo '<button id="tbutton-' . $item->id . '" onclick="showNumber(\'' . $item->id .'\', \'' . $item->telefone . '\'); return false;" title="telefone" style="displa\
y: inline;">t</button> ';
                         }
			 if ($item->info) {
			    echo '<button onClick="showInfo(\'' . $item->user_name . '\', `' . esc_textarea($item->info) . '`); return false;" title="info">i</button> ';
			 }
                         echo '</td></tr>';

                 }

                 echo '</table>';
	}


	// show the page info at end too
	echo '<form><p>';
	if ($start_lim>0) {
	   echo '<button type="button" name="cn-action" value="Prev" onclick="jQuery( function($) {$(\'#cn-prev-page\').trigger(\'click\');});" style="float: left;">&lt;</button> ';
	}
	if ($page_count>1) {
	   echo '<p align="center">Pagina • Page ' . $current_page . ' / ' . $page_count . ' ';
	}
	if ($item_count>$end_lim) {
	   echo '<button type="button" name="cn-action" value="Next" onclick="jQuery( function($) {$(\'#cn-next-page\').trigger(\'click\');});" style="float: right;">&gt;</button> ';
	}
   	echo '</p></form>';


	echo '<script type="text/javascript">';
	echo 'function showNumber(elemid, telnumber) {';
	echo '  jQuery( function ($) { ';
	echo '  $("#contact-" + elemid).append(" <a href=\'tel:"+ telnumber + "\' ><span><strong>t: " + telnumber + "</strong></span></a>");';
	echo '  $("#tbutton-" + elemid).hide();';
	echo ' });';
	echo '};';

	echo 'function showContactline(elemid, caller) {';
	echo '  jQuery( function ($) { ';
	echo '  $("#cn-contactline-" + elemid).toggle();';
	echo '  if ($(caller).val()=="-") { $(caller).val("+"); } else { $(caller).val("-"); };';
	echo ' });';
	echo '};';

	echo 'function showMailForm(name, emailaddress) {';
	echo '  jQuery( function ($) { ';
	echo '  $("#cn-email-to").val(emailaddress); $("#cn-email-name").text(name); $("#cn-email-address-form").dialog(\'open\');';
	echo ' });';
	echo '};';

	echo 'function showInfo(name, info) {';
	echo '  jQuery( function ($) { ';
	echo '  $("#cn-info-name").text(name); $("#cn-info").val(info); $("#cn-infobox").dialog(\'open\');';
	echo ' });';
	echo '};';
	echo '</script>';

	wp_enqueue_script( 'jquery-ui-dialog' ); // jquery and jquery-ui should be dependencies, didn't check though...
	wp_enqueue_style( 'wp-jquery-ui-dialog' );

	echo '<script>
	jQuery(function ($) {
  // initalise the dialogs
  $(\'#cn-email-address-form\').dialog({
    title: \'Send Email\',
    dialogClass: \'wp-dialog\',
    autoOpen: false,
    draggable: false,
    width: "75%",
    modal: true,
    resizable: false,
    closeOnEscape: true,
    position: {
      my: "center",
      at: "center",
      of: window
    },
    open: function () {
      // close dialog by clicking the overlay behind it
      $(\'.ui-widget-overlay\').bind(\'click\', function(){
        $(\'#cn-email-form\').dialog(\'close\');
      })
    },
    create: function () {
      // style fix for WordPress admin
      $(\'.ui-dialog-titlebar-close\').addClass(\'ui-button\');
    },
  });
  $(\'#cn-infobox\').dialog({
    title: \'Info\',
    dialogClass: \'wp-dialog\',
    autoOpen: false,
    draggable: false,
    width: "75%",
    modal: true,
    resizable: false,
    closeOnEscape: true,
    position: {
      my: "center",
      at: "center",
      of: window
    },
    open: function () {
      // close dialog by clicking the overlay behind it
      $(\'.ui-widget-overlay\').bind(\'click\', function(){
        $(\'#cn-infobox\').dialog(\'close\');
      })
    },
    create: function () {
      // style fix for WordPress admin
      $(\'.ui-dialog-titlebar-close\').addClass(\'ui-button\');
    },
  });
  });
  </script>';

	echo '<div id="cn-email-address-form" class="hidden">';
	echo '<p><form action="./#cn-list" method="post">';
	echo '  <p>Para • To - <span id="cn-email-name"></span></p>';
	echo '  <p>Nome • Name <input type="text" name="cn-email-name" required="required"/></p>';
	echo '  <p>Email • Email <input type="email" name="cn-email-from" required="required"/></p>';
	echo '  <p>Mensagem • Message <textarea name="cn-email-message" rows="4" required="required"></textarea></p>';
	echo '  <input type="hidden" name="cn-email-to" id="cn-email-to"/>';
	echo '  <input type="hidden" name="cn-keywords" value="' . $keywords . '" />';
	echo '  <input type="submit" name="cn-action" value="Email" />';
	echo '</form>';
	echo '</div>';

	echo '<div id="cn-infobox" class="hidden">';
	echo '  <p id="cn-info-name"></p>';
	echo '  <textarea id="cn-info" rows="20" readonly="readonly"></textarea>';
	echo '</div>';

}

function store_receiver() {

       // sanitize form values
       $name             = sanitize_text_field( $_REQUEST["cn-name"] );
       $email            = sanitize_email( $_REQUEST["cn-email"] );
       $webaddress       = $_REQUEST["cn-webaddress"];
       $telefone         = sanitize_text_field( $_REQUEST["cn-telefone"] );
       $info         	 = sanitize_textarea_field( $_REQUEST["cn-info"] );
       $locationid       = $_REQUEST["cn-locationid"];
//       $itemdescriptions = $_REQUEST["cn-itemdescription"];
//       $itemquantities   = $_REQUEST["cn-itemquantity"];
//       $itemcategories   = $_REQUEST["cn-itemcategoryid"] ;
       $password 	 = random_password();

       $user_array = array(
         'name'        => $name,
	 'email'       => $email,
	 'webaddress'  => $webaddress,
	 'telefone'    => $telefone,
	 'info'	       => $info,
	 'location_id' => $locationid,
	 'password'    => $password
       );


       global $wpdb;
       $user_table_name = $wpdb->prefix . 'communityneeds_users';
       $item_table_name = $wpdb->prefix . 'communityneeds_useritems';
       $cat_table_name = $wpdb->prefix . 'communityneeds_categories';

       // check not already registered
       $found = $wpdb->get_var("SELECT COUNT(id) FROM $user_table_name WHERE email = '$email'");

       if ($found) {
         echo '<p>Email already in use, please login or register with new email</p>';
       } elseif ( $wpdb->insert( $user_table_name, $user_array ) ) {
            echo '<div>';
            echo '<p>Obrigado pelo submissão, boa sorte! • Thanks for your submission, good luck!</p>';
	    echo '<p>Please confirm your submission via email received</p>';
            echo '</div>';

       	    $userid = $wpdb->insert_id;
/*
	    $itemcount = count($itemdescriptions);
	    for ($x = 0; $x < $itemcount; $x++) {
      	    	$item_array = array(
              	  'receiver_id' => $userid,
	      	  'category_id' => $itemcategories[$x],
              	  'description' => sanitize_text_field($itemdescriptions[$x]),
	      	  'quantity'    => $itemquantities[$x],
		  'status'	=>'Required'
       	    	);
       	    	$wpdb->insert( $item_table_name, $item_array );
	    }
*/
	    // inform them of what they submitted via email
	    $message = "<h2>Community Needs Submissions</h2>";
	    $message .= "<p>Hello " . $name . ",</p>";
	    $message .= "<p>Your submission for the following items was successfull:</p>";
/*	    
	    $message .= "<table>";
	    $items_sql = 'SELECT description, quantity, c.name as category_name
	    FROM ' . $item_table_name . ' i 
	    INNER JOIN ' . $cat_table_name. ' c ON i.category_id = c.id 
	    WHERE receiver_id = ' . $userid . ';';

      	    $items = $wpdb->get_results( $items_sql );
	    foreach ( $items as $item ) {
	    	    $message .= '<tr>';
         	    $message .= '<td>' . $item->category_name . ' - ' . $item->description . '</td>';
         	    $message .= '<td>' . $item->quantity . '</td>';
		    $message .= '</tr>';
	    }
	    $message .= '</table>';
*/
	    $message .= '<p>Your password has been set as <br /><strong>' . $password . '</strong></p>';
	    $message .= '<p>You can <a href="' . esc_url( $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] )  . '">login</a> to modify your submission.</p>';

	    $message .= "<p>Regards,<br/>The team at Post-fire Community Needs Database</p>";
	    
	    $message .= '<h2>Registo de Necessidades Comunitárias</h2>
	    <br />
	    Olá ' . $name . ',<br />
	    <br />
	    A sua palavra-chave (password) é:<br /><strong>' . $password . '</strong><br />
	    <br />
	    Para alterar os seus registos faça <a href="' . esc_url( $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] )  . '">login</a><br />
	    <br />
	    Cumprimentos,<br />
	    A equipa de BASE DE DADOS DE NECESSIDADES PÓS-INCÊNDIOS<br/>';

	    add_filter( 'wp_mail_content_type', 'cn_set_content_type' );
	    wp_mail($email, "Post-fire Community Needs Database submission confirmation", $message);
	    remove_filter( 'wp_mail_content_type','cn_set_content_type' );

       } else {
            echo 'An unexpected error occurred, unable to submit registration to db.';
       }


}

function edit_receiver() {

       // sanitize form values
       $name             = sanitize_text_field( $_REQUEST["cn-name"] );
       $email            = $_SESSION['email'];
       $webaddress	 = $_REQUEST["cn-webaddress"];
       $telefone         = sanitize_text_field( $_REQUEST["cn-telefone"] );
       $info         	 = sanitize_textarea_field( $_REQUEST["cn-info"] );
//       $language	 = $_REQUEST["cn-language"];
       $language	 = "pt";
       $locationid       = $_REQUEST["cn-locationid"];
       $itemdescriptions = $_REQUEST["cn-itemdescription"];
       $itemquantities   = $_REQUEST["cn-itemquantity"];
       $itemcategories   = $_REQUEST["cn-itemcategoryid"];
       $itemstatus   	 = $_REQUEST["cn-itemstatus"];

       $user_array = array(
         'name'        => $name,
	 'webaddress'  => $webaddress,
	 'telefone'    => $telefone,
	 'info'	       => $info,
	 'location_id' => $locationid,
	 'language'    => $language
       );


       global $wpdb;
       $user_table_name = $wpdb->prefix . 'communityneeds_users';
       $item_table_name = $wpdb->prefix . 'communityneeds_useritems';
       $user_id = $wpdb->get_var("SELECT id FROM $user_table_name WHERE email = '".$email."'");

       if ( $wpdb->update( $user_table_name, $user_array , array('id' => $user_id, 'email' => $email)) !== false ) {
            echo '<div>';
            echo '<p>Obrigado pelo submissão, boa sorte! • Thanks for your submission, good luck!</p>';
            echo '</div>';

	    // remove existing
	    $wpdb->query("DELETE FROM $item_table_name WHERE receiver_id = ".$user_id.";");
	    // then add anew
	    $itemcount = count($itemdescriptions);
	    for ($x = 0; $x < $itemcount; $x++) {
	    	if ($itemquantities[$x]>0) {  // we delete the 0 quantity items
		      	    $item_array = array(
              			  'receiver_id' => $user_id,
	      			  'category_id' => $itemcategories[$x],
              			  'description' => sanitize_text_field($itemdescriptions[$x]),
	      			  'quantity'    => $itemquantities[$x],
				  'status'	=> $itemstatus[$x]
       	    		    );
       	    		    $wpdb->insert( $item_table_name, $item_array );
		}
	    }

       } else {
            echo 'Erro aconteceu, tento de nova. • An error occurred, unable to submit modification to db.';
       }


}

function cn_set_content_type() {
	 return "text/html";
}

function cn_submit_shortcode() {
    
    ob_start();

    global $wpdb;


    $action = (isset($_REQUEST["cn-action"]) ? $_REQUEST["cn-action"] : "" );

    echo '<a name="cn-edit" style="position: relative; top: -150px;"><p></p></a>';

    // if the submit button is clicked, send the email
    if ( $action=="Register" ) {
       // make new registration
       store_receiver();
    } elseif ( $action=="Update" ) {
       // modify existing entry
       edit_receiver();
    } elseif ( $action=="Password Reminder") {
       // send email to user with info on password
       $email = $_REQUEST["cn-email"];
       $password = $wpdb->get_var("SELECT password FROM wp_communityneeds_users WHERE email = '" . $email . "';");
       if ($password) {
              $emailbody = "<h2>Password Reminder</h2>";
       	      $emailbody .= "<p>Your password is <strong>" . $password . "</strong></p>";
	      $emailbody .= "<p>Regards,<br />The team at Post-fire Community Needs</p>";
	      $emailbody .= '<h2>Lembrete da palavra-passe</h2>
	      A sua palavra-passe é: <strong>' . $password . '</strong><br />
	      <br />
	      Cumprimentos,<br />
	      A equipa de BASE DE DADOS DE NECESSIDADES PÓS-INCÊNDIOS';

	      add_filter( 'wp_mail_content_type', 'cn_set_content_type' );
       	      wp_mail($email, "Community Needs Submission", $emailbody);
       	      remove_filter( 'wp_mail_content_type','cn_set_content_type' );
	      echo '<p>Password reminder has been sent to your email';
	}

    } elseif ( $action=="Login" ) {
       // check login attempt
       $email = $_REQUEST["cn-email"];
       $password = $_REQUEST["cn-password"];
       $login = $wpdb->get_row("SELECT id, email FROM wp_communityneeds_users WHERE email = '" . $email . "' AND password='" . $password . "';");
       if ( $login !== null) {
           $_SESSION['email'] = $email;
	   $sql = "UPDATE wp_communityneeds_users SET lastlogin = NOW() WHERE id = " . $login->id;
	   $wpdb->query($sql);
       } else {
           echo '<p>Erro com login, tenta de novo • Login failed, please try again</p>';
       }
    } elseif ( $action=="Logout" ) {
      $_SESSION['email'] = null;
    }

    if ( session_id() && isset($_SESSION['email']) ) {
       // show users saved data
       $user = $wpdb->get_row("SELECT id, email, name FROM wp_communityneeds_users WHERE email = '" . $_SESSION['email'] . "';");
       echo '<form action="./#cn-edit" method="post"><h3>Logged in as ' . $user->email . ' <input style="display: inline; float: right;" type="submit" name="cn-action" value="Logout" /></h3></form>';
       $link = 'https://www.centralportugal.com/community-needs/para-doadores-for-donors/?cn-keywords=' . $user->name . '#cn-list';
       echo '<p>Personal list share link : <br/><a href="' . $link . '">' . $link . '</a></p>';
    }
    
    submit_receiver_form();

    return ob_get_clean();
}
add_shortcode( 'submit_receiver_form', 'cn_submit_shortcode' );

function cn_list_items_shortcode() {
    ob_start();

    list_receiver_items();

    return ob_get_clean();
}
add_shortcode( 'receiver_list', 'cn_list_items_shortcode' );

function register_session() {
    if (!session_id())
        session_start();
}

add_action('init', 'register_session');


function wpb_sender_email( $original_email_address ) {
    return 'communityneeds@centralportugal.com';
}
 
// Function to change sender name
function wpb_sender_name( $original_email_from ) {
    return 'Central PT - Community Needs';
}
 
// Hooking up our functions to WordPress filters 
add_filter( 'wp_mail_from', 'wpb_sender_email' );
add_filter( 'wp_mail_from_name', 'wpb_sender_name' );


// ADMIN STUFF

add_action( 'admin_menu', 'cn_plugin_menu' );

function cn_plugin_menu() {
	 add_options_page( 
	   'Community Needs Setup',
	   'Community Needs',
	   'manage_options',
	   'cn-options.php',
	   'cn_admin_content'
	 );
}

function cn_admin_content() {

	 echo '<h2>Community Needs Setup</h2>';
	 echo '<hr />';

	 global $wpdb;

	 echo '<h3>Regions - Locations</h3>';

	 $action = (isset($_REQUEST["cn-action"]) ? $_REQUEST["cn-action"] : "" );
	 $regionid = (isset($_REQUEST["cn-regionid"]) ? $_REQUEST["cn-regionid"] : 0 );
	 $locationid = (isset($_REQUEST["cn-locationid"]) ? $_REQUEST["cn-locationid"] : 0 );
	 $regionname = (isset($_REQUEST["cn-region-name"]) ? $_REQUEST["cn-region-name"] : "" );
	 $locationname = (isset($_REQUEST["cn-location-name"]) ? $_REQUEST["cn-location-name"] : 0 );

         $reg_table_name = $wpdb->prefix . 'communityneeds_regions';
	 $loc_table_name = $wpdb->prefix . 'communityneeds_locations';

	 // complete actions afore display!
	 switch ($action) {
	 	case "Add Region":
		     $wpdb->insert($reg_table_name, array ('name' => $regionname) );
		     echo '<p>' . $regionname . ' added to Regions</p>';
		     break;
	 	case "Save Region":
		     $wpdb->update($reg_table_name, array ('name' => $regionname), array ('id' => $regionid) );
		     echo '<p>' . $regionname . ' modified in Regions</p>';
		     break;
	 	case "Save Location":
		     $wpdb->update($loc_table_name, array ('name' => $locationname), array ('id' => $locationid) );
		     echo '<p>' . $locationname . ' modified in Locations</p>';
		     break;
		case "Add Location to Region":
		     $wpdb->insert($loc_table_name, array ('name' => $locationname, 'region_id' => $regionid));
		     $regionname = $wpdb->get_var("SELECT name FROM $reg_table_name WHERE id = $regionid;");
		     echo '<p>' . $locationname . ' added to ' . $regionname . '</p>';
		     break;
		case "other":
		     // ?;
		     break;
		default:
		     break;
	 }


	 if ( !$regionid ) {
            $regions_sql = "SELECT id, name FROM $reg_table_name ORDER BY name;";
            $regions = $wpdb->get_results( $regions_sql );

    	    echo '<form method="post">';
            echo '<select name="cn-regionid">';
            foreach ( $regions as $region ) {
		echo '<option value="' . $region->id . '" >' . $region->name . '</option>';                
	    }
	    echo '</select> ';
	    echo '<input type="submit" value="Edit Region" name="cn-action" />';
	    echo '<input type="submit" value="Edit Locations" name="cn-action" /><br />';
	    echo '</form>';
	    echo '<form method="post">';
	    echo '<input type="text" required="required" name="cn-region-name" />';
	    echo '<input type="submit" value="Add Region" name="cn-action" />';
	    echo '</form>';
	 }

	 if ( $regionid ) {
	    $regionname = $wpdb->get_var("SELECT name FROM $reg_table_name WHERE id = $regionid;");
	    echo 'Editing Region : ' . $regionname;
	    
	    if ($action==="Edit Region") {
	            echo '<form method="post">';
	   	    echo '<input type="text" required="required" name="cn-region-name" value="' . $regionname . '" />';
	    	    echo '<input type="hidden" name="cn-regionid" value="' . $regionid . '" />';
	    	    echo '<input type="submit" value="Save Region" name="cn-action" />';
		    echo '</form>';
	    } elseif ($action==="Edit Location") {
	      	    $locationname = $wpdb->get_var("SELECT name FROM $loc_table_name WHERE id = $locationid;");
	            echo '<p>Editing Location : ' . $locationname . '</p>';
	            echo '<form method="post">';
	   	    echo '<input type="text" required="required" name="cn-location-name" value="' . $locationname . '" />';
	    	    echo '<input type="hidden" name="cn-locationid" value="' . $locationid . '" />';
	    	    echo '<input type="hidden" name="cn-regionid" value="' . $regionid . '" />';
	    	    echo '<input type="submit" value="Save Location" name="cn-action" />';
		    echo '</form>';
	    } else {
	    	    echo '<form method="post">';
	    	    echo '<select name="cn-locationid">';
	    	    $locations_sql = 'SELECT id, name FROM ' . $loc_table_name . ' WHERE region_id = ' . $regionid . ' ORDER BY name;';
       	    	    $locations = $wpdb->get_results( $locations_sql );
            	    foreach ( $locations as $location ) {
         	    	    echo '<option value="' . $location->id . '">' . $location->name . '</option>';
	    	    }
	    	    echo '</select> ';
	    	    echo '<input type="hidden" name="cn-regionid" value="' . $regionid . '" />';
	    	    echo '<input type="submit" value="Edit Location" name="cn-action" /><br />';
	    	    echo '</form>';
	    	    echo '<form method="post">';
	   	    echo '<input type="text" required="required" name="cn-location-name" />';
	    	    echo '<input type="hidden" name="cn-regionid" value="' . $regionid . '" />';
	    	    echo '<input type="submit" value="Add Location to Region" name="cn-action" />';
	    	    echo '</form>';
	    }
	    	    echo '<form method="post"><input type="submit" value="Cancel" /></form>';
	 }
	 echo '<hr />';


	 echo '<h3>Item Categories</h3>';

	 $action = (isset($_REQUEST["cn-action"]) ? $_REQUEST["cn-action"] : "" );
	 $groupid = (isset($_REQUEST["cn-groupid"]) ? $_REQUEST["cn-groupid"] : 0 );
	 $categoryid = (isset($_REQUEST["cn-categoryid"]) ? $_REQUEST["cn-categoryid"] : 0 );
	 $groupname = (isset($_REQUEST["cn-group-name"]) ? $_REQUEST["cn-group-name"] : "" );
	 $categoryname = (isset($_REQUEST["cn-category-name"]) ? $_REQUEST["cn-category-name"] : 0 );

         $group_table_name = $wpdb->prefix . 'communityneeds_categorygroups';
	 $cat_table_name = $wpdb->prefix . 'communityneeds_categories';

	 // complete actions afore display!
	 switch ($action) {
	 	case "Add Group":
		     $wpdb->insert($group_table_name, array ('name' => $groupname) );
		     echo '<p>' . $groupname . ' added to Groups</p>';
		     break;
	 	case "Save Group":
		     $wpdb->update($group_table_name, array ('name' => $groupname), array ('id' => $groupid) );
		     echo '<p>' . $groupname . ' modified in Groups</p>';
		     break;
	 	case "Save Category":
		     $wpdb->update($cat_table_name, array ('name' => $categoryname), array ('id' => $categoryid) );
		     echo '<p>' . $categoryname . ' modified in Categories</p>';
		     break;
		case "Add Category to Group":
		     $wpdb->insert($cat_table_name, array ('name' => $categoryname, 'group_id' => $groupid));
		     $groupname = $wpdb->get_var("SELECT name FROM $reg_table_name WHERE id = $groupid;");
		     echo '<p>' . $categoryname . ' added to ' . $groupname . '</p>';
		     break;
		default:
		     break;
	 }


	 if ( !$groupid ) {
            $groups_sql = "SELECT id, name FROM $group_table_name ORDER BY name;";
            $groups = $wpdb->get_results( $groups_sql );

    	    echo '<form method="post">';
            echo '<select name="cn-groupid">';
            foreach ( $groups as $group ) {
		echo '<option value="' . $group->id . '" >' . $group->name . '</option>';                
	    }
	    echo '</select> ';
	    echo '<input type="submit" value="Edit Group" name="cn-action" />';
	    echo '<input type="submit" value="Edit Categories" name="cn-action" /><br />';
	    echo '</form>';
	    echo '<form method="post">';
	    echo '<input type="text" required="required" name="cn-group-name" />';
	    echo '<input type="submit" value="Add Group" name="cn-action" />';
	    echo '</form>';
	 }

	 if ( $groupid ) {
	    $groupname = $wpdb->get_var("SELECT name FROM $group_table_name WHERE id = $groupid;");
	    echo 'Editing Group : ' . $groupname;
	    
	    if ($action==="Edit Group") {
	            echo '<form method="post">';
	   	    echo '<input type="text" required="required" name="cn-group-name" value="' . $groupname . '" />';
	    	    echo '<input type="hidden" name="cn-groupid" value="' . $groupid . '" />';
	    	    echo '<input type="submit" value="Save Group" name="cn-action" />';
		    echo '</form>';
	    } elseif ($action==="Edit Category") {
	      	    $categoryname = $wpdb->get_var("SELECT name FROM $cat_table_name WHERE id = $categoryid;");
	            echo '<p>Editing Category : ' . $categoryname . '</p>';
	            echo '<form method="post">';
	   	    echo '<input type="text" required="required" name="cn-category-name" value="' . $categoryname . '" />';
	    	    echo '<input type="hidden" name="cn-categoryid" value="' . $categoryid . '" />';
	    	    echo '<input type="hidden" name="cn-groupid" value="' . $groupid . '" />';
	    	    echo '<input type="submit" value="Save Category" name="cn-action" />';
		    echo '</form>';
	    } else {
	    	    echo '<form method="post">';
	    	    echo '<select name="cn-categoryid">';
	    	    $categorys_sql = 'SELECT id, name FROM ' . $cat_table_name . ' WHERE group_id = ' . $groupid . ' ORDER BY name;';
       	    	    $categorys = $wpdb->get_results( $categorys_sql );
            	    foreach ( $categorys as $category ) {
         	    	    echo '<option value="' . $category->id . '">' . $category->name . '</option>';
	    	    }
	    	    echo '</select> ';
	    	    echo '<input type="hidden" name="cn-groupid" value="' . $groupid . '" />';
	    	    echo '<input type="submit" value="Edit Category" name="cn-action" /><br />';
	    	    echo '</form>';
	    	    echo '<form method="post">';
	   	    echo '<input type="text" required="required" name="cn-category-name" />';
	    	    echo '<input type="hidden" name="cn-groupid" value="' . $groupid . '" />';
	    	    echo '<input type="submit" value="Add Category to Group" name="cn-action" />';
	    	    echo '</form>';
	    }
	    	    echo '<form method="post"><input type="submit" value="Cancel" /></form>';
	 }


	 echo '<hr />';

	 echo '<h3>Current users</h3>';

	 $user_table_name = $wpdb->prefix . 'communityneeds_users';
	 $item_table_name = $wpdb->prefix . 'communityneeds_useritems';

         $keywords = ( isset( $_REQUEST["cn-keywords"] ) ? $_REQUEST["cn-keywords"] : '' ) ;
	 $userid = (isset($_REQUEST["cn-userid"]) ? $_REQUEST["cn-userid"] : 0 );

	 switch ($action) {
		case "Delete user and items":
		     $wpdb->query("DELETE FROM " . $item_table_name . " WHERE receiver_id = '" . $userid . "';");
		     $wpdb->query("DELETE FROM " . $user_table_name . " WHERE id = '" . $userid . "';");
		     break;
	 }

	 echo '<form method="post" >';
	 echo '<p>Search <input type="text" name="cn-keywords" value="' . $keywords . '" /><input type="submit" value="filter" /></p>';
	 echo '</form>';

	 $users_sql = 'SELECT u.id, name, email, COUNT(i.id) as item_count FROM ' . $user_table_name . ' u inner join ' . $item_table_name . ' i on u.id = i.receiver_id 
	 	    WHERE name LIKE "%' . $keywords . '%" OR email LIKE "%' . $keywords . '%" 
		    GROUP BY u.id ORDER BY name;';

	 $users = $wpdb->get_results($users_sql);
	 echo '<table>';
	 echo '<tr><th>Name</th><th>Email</th><th>Item count</th><th></th></tr>';
	 foreach ($users as $user) {
	 	 echo '<tr>';
		 echo '<td>' . $user->name . '</td>';
		 echo '<td><a href="mailto:' . $user->email . '" >' . $user->email . '</a></td>';
		 echo '<td>' . $user->item_count . '</td>';
		 echo '<td><form method="post" ><input type="hidden" name="cn-userid" value="' . $user->id . '"/><input type="submit" name="cn-action" onclick="return confirm(\'Are you sure you wish to delete this data? It will not be restorable!\')" value="Delete user and items" /></form></td>';
	 	 echo '</tr>';
	 }
	 echo '</table>';
	 
	 echo '<hr />';

	 echo '<h3>Top Search Terms</h3>';

	 $searchs_sql = "SELECT keywords, count(id) as count FROM wp_communityneeds_searches GROUP BY keywords ORDER BY count DESC LIMIT 20";
	 $searchs = $wpdb->get_results($searchs_sql);
	 echo '<table>';
	 foreach ($searchs as $search) {
	   echo '<tr><td>' . $search->keywords . '</td><td>' . $search->count . '</td></tr>';
	 }

}

function random_password( $length = 8 ) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $password = substr( str_shuffle( $chars ), 0, $length );
    return $password;
}


?>
