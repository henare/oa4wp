<?php
/*
Plugin Name: OpenAustralia.org for Wordpress
Plugin URI: http://github.com/henare/oa4wp/
Description: Displays your MP's most recent speeches from OpenAustralia.org on your blog. Adapted from the TheyWorkForYou plugin by Philip John: http://philipjohn.co.uk/category/plugins/theyworkforyou/
Author: Henare Degan
Version: 0.1
Author URI: http://www.henaredegan.com/

Future features list;
 * Custom date format
 
*/
/*  Copyright 2009  Philip John Ltd  (email : talkto@philipjohn.co.uk)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the Affero General Public License as published
    by the Affero Inc; either version 2 of the License, or (at your
    option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the Affero General Public License
    along with this program; if not, see http://www.affero.org/oagpl.html
*/

// The settings page for OL
function twfy_settings(){
	
	// The form has been submitted, so do the dirty work
	if ($_POST['twfy_hidden'] == "Y"){
		// Log the ID number of the MP
		$twfy_person_id = trim(htmlentities($_POST['twfy_person_id']));
		$twfy_title = trim($_POST['twfy_title']);
                $twfy_name = trim(htmlentities($_POST['twfy_name']));
		$twfy_desc = trim(htmlentities($_POST['twfy_desc']));
		$twfy_date = trim(htmlentities($_POST['twfy_date']));
		$twfy_limit = trim(htmlentities($_POST['twfy_limit']));
		$twfy_link = trim(htmlentities($_POST['twfy_link']));
        $twfy_options = array(
            'person_id' => $twfy_person_id,
            'name' => $twfy_name,
            'title' => $twfy_title,
            'desc' => $twfy_desc,
            'date' => $twfy_date,
            'limit' => $twfy_limit,
            'link' => $twfy_link
        );
		update_option('twfy_recent_activity_widget', $twfy_options);
        
		echo '<div class="updated"><p><strong>'. __('Options saved.' ) .'</strong></p></div>';
	}
	
	// Load the councils XML for choosing the right authority
	$xml = simplexml_load_file('http://www.openaustralia.org/api/getRepresentatives?key=EM8hqpERATyUCL4DvWFZbNjA&output=xml');
	
	// Retrieve the currently selected council, if there is one.
	$twfy_options = get_option('twfy_recent_activity_widget');
    
    // re-organise MPs into an array for sorting on name
    $MPs = array();
    foreach ($xml->match as $MP){
        $MPid = (string )$MP->person_id;
        $MPname = (string )$MP->name;
        $MPs[$MPid] = $MPname;
    }
    asort($MPs); // actually sort it.
	
	?>
	<div class="wrap">
		<h2><?php _e('OpenAustralia Settings'); ?></h2>
		<form name="twfy_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">  
			<input type="hidden" name="twfy_hidden" value="Y">  
			<h3><?php _e('Choose your MP'); ?></h3>
			<p>
				<select name="twfy_person_id" id="twfy_person_id">
					<?php
						foreach ($MPs as $MP_id => $MP_name){
							echo '<option value="'.$MP_id.'"';
							if ($twfy_options['person_id'] == $MP_id){
								echo ' selected="selected"';
							}
							echo '>'.$MP_name.'</option>'."\n";
						}
					?>
				</select>
			</p>
			
            <fieldset>
                <h3>Recent Activity Widget - Options</h3>
                <p>
                    <label for="twfy_title">Widget title: </label>
                    <input type="text" id="twfy_title" name="twfy_title" value="<?php echo stripslashes($twfy_options['title']);?>" />
                </p>
                <p>
                    Show MP name?<br/>
                    <label for="twfy_name_yes"><input type="radio" id="twfy_name_yes" name="twfy_name" value="1" <?php if ($twfy_options['name']==1){echo 'checked="checked" ';} ?>/> Yes</label><br/>
                    <label for="twfy_name_no"><input type="radio" id="twfy_name_no" name="twfy_name" value="0" <?php if ($twfy_options['name']==0){echo 'checked="checked" ';} ?>/> No</label>
                </p>
                <p>
                    Show description?<br/>
                    <label for="twfy_desc_yes"><input type="radio" id="twfy_desc_yes" name="twfy_desc" value="1" <?php if ($twfy_options['desc']==1){echo 'checked="checked" ';} ?>/> Yes</label><br/>
                    <label for="twfy_desc_no"><input type="radio" id="twfy_desc_no" name="twfy_desc" value="0" <?php if ($twfy_options['desc']==0){echo 'checked="checked" ';} ?>/> No</label>
                </p>
                <p>
                    Show date?<br/>
                    <label for="twfy_date_yes"><input type="radio" id="twfy_date_yes" name="twfy_date" value="1" <?php if ($twfy_options['date']==1){echo 'checked="checked" ';} ?>/> Yes</label><br/>
                    <label for="twfy_date_no"><input type="radio" id="twfy_date_no" name="twfy_date" value="0" <?php if ($twfy_options['date']==0){echo 'checked="checked" ';} ?>/> No</label>
                </p>
                <p>
                    <label for="twfy_limit">How many items should be shown?: </label>
                    <input type="text" id="twfy_limit" name="twfy_limit" value="<?php echo $twfy_options['limit'];?>" />
                </p>
                <p>
                    Show link to MP on OpenAustralia.org?<br/>
                    <label for="twfy_link_yes"><input type="radio" id="twfy_link_yes" name="twfy_link" value="1" <?php if ($twfy_options['link']==1){echo 'checked="checked" ';} ?>/> Yes</label><br/>
                    <label for="twfy_link_no"><input type="radio" id="twfy_link_no" name="twfy_link" value="0" <?php if ($twfy_options['link']==0){echo 'checked="checked" ';} ?>/> No</label>
                </p>
            </fieldset>
			<p class="submit">  
				<input type="submit" name="Submit" value="<?php _e('Update Options') ?>" />
			</p>  
		</form>
	</div>
	<?php
}

// Add the settings page
function twfy_actions(){
	add_options_page('OpenAustralia Settings', 'OpenAustralia', 5, 'OpenAustralia', 'twfy_settings');
}


// Recent activity widget
function twfy_recent_activity_widget($args){
	extract($args); // Prep to display widget code
    
    $twfy_options = get_option('twfy_recent_activity_widget');
	
	echo $before_widget;
	echo $before_title.stripslashes($twfy_options['title']).$after_title;
	twfy_recent_activity_widget_contents();
	echo $after_widget;
}

// Recent activity DASHBOARD widget
function twfy_recent_activity_dbwidget(){
    twfy_recent_activity_widget_contents();
}

// Contents for recent activity widgets
function twfy_recent_activity_widget_contents(){
	$twfy_options = get_option('twfy_recent_activity_widget'); // The council we're displaying
    
    if ($twfy_person_id !== FALSE){ // Not if the ID isn't set.
        $xml = simplexml_load_file("http://www.openaustralia.org/api/getHansard?key=EM8hqpERATyUCL4DvWFZbNjA&output=xml&person=".$twfy_options['person_id']); // Load XML
        
        // Get some information about the MP
        $MPname = (string )$xml->rows->match->speaker->first_name.' '.$xml->rows->match->speaker->last_name;
        $MPconstituency = (string )$xml->rows->match->speaker->constituency;
        $MPurl = (string )$xml->rows->match->speaker->url;
        
        // Display the MP name and constituency
        if ($twfy_options['name']==1){ echo '<a href="http://www.openaustralia.org'.$MPurl.'">'.$MPname.', MP - Member for '.$MPconstituency.'</a>'; }

        echo "\n<ul>\n";
        $i = 0; //counter for number of meetings
        foreach ($xml->rows->match as $match){
            if ($i>=$twfy_options['limit']) { break; } // don't list more than 5 meetings
            $date = strtotime($match->hdate);
            echo '<li><b>';
            if ($twfy_options['date']==1){ echo date('j M', $date).': '; }
            echo '<a href="http://www.openaustralia.org'.$match->listurl.'">'.$match->parent->body.'</a></b>';
            if ($twfy_options['desc']==1){ echo '<br/>'.$match->body; }
            echo '</li>'."\n";
            $i++; //increment the counter
        }
        echo "</ul>\n";
        
        if ($twfy_options['link']==1){
            // Link back to the MPs page on TWFY^WOpenAustralia
            echo '<p>More from <a href="http://www.openaustralia.org'.$MPurl.'">OpenAustralia.org</a></p>';
        }
    }
    else {
        echo "<p>Sorry, no MP has been selected. Please select an MP from the settings page.";
    }
}



// Dashboard widgets init function 
function twfy_add_dashboard_widgets(){
	wp_add_dashboard_widget('twfy-recent-activity-widget', 'My MPs Recent Activity', 'twfy_recent_activity_dbwidget');
}

// Initialising function
function twfy_init(){
	register_sidebar_widget(__('My MPs Recent Activity'), 'twfy_recent_activity_widget');
    $twfy_default_options = array(
        'person_id'=>'10552',
        'title'=>'My MPs recent activity',
        'name'=>1,
        'desc'=>1,
        'date'=>1,
        'limit'=>5,
        'link'=>1
    );
    add_option('twfy_recent_activity_widget', $twfy_default_options);
}

add_action("plugins_loaded", "twfy_init");
add_action('admin_menu', 'twfy_actions');
add_action('wp_dashboard_setup', 'twfy_add_dashboard_widgets');

?>
