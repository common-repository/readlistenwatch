<?php
/*
Plugin Name: Read/Listen/Watch
Plugin URI: http://scott.teamahearn.com/read-listen-watch/
Description: Display what I'm reading, listening to, and watching; with links to Amazon.
Version: 2.1.2
Author: Scott A'Hearn
Author URI: http://scott.teamahearn.com/
License: GPL2
*/

/*	Copyright 2010  Scott A'Hearn  (email : sahearn at gmail.com)
	
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as 
	published by the Free Software Foundation.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program; if not, If not, see <http://www.gnu.org/licenses/>.
*/

/* init menu and widget */
add_action('admin_menu', 'rlw_plugin_menu');
add_action('widgets_init', 'load_rlw_widget' );

if ( ! defined( 'THIS_PLUGIN_DIR' ) ) {
	$fullplugindir = explode('/', dirname(__FILE__));
	define( 'THIS_PLUGIN_DIR', $fullplugindir[sizeof($fullplugindir) - 1] );
}

add_action('wp_head', 'getRLWHeadTags');

/* add menu */
function rlw_plugin_menu() {
	// level 6 allows editors and higher to manange RLW content, but NOT widget settings
	add_options_page('Read Listen Watch Options', 'Read/Listen/Watch ', 6, 'rlw-plugin-settings', 'rlw_plugin_options');
}

/* register widget */
function load_rlw_widget() {
	register_widget( 'RLW_Widget' );
}

/* include rss in head */
function getRLWHeadTags() {
		// css for sidebar widget ?>
		<!-- read/listen/watch -->
		<link rel="stylesheet" type="text/css" href="<?php echo WP_PLUGIN_URL . '/' . THIS_PLUGIN_DIR; ?>/read-listen-watch.css" />

		<?php
}

/* diff of multidim arrays */
function array_diff_no_cast(&$ar1, &$ar2) {
	$diff = Array();
	foreach ($ar1 as $key => $val1) {
		if (array_search($val1, $ar2) === false) {
			$diff[] = $val1;
		}
	}
	return $diff;
}

/* admin menu options */
function rlw_plugin_options() {

	// pull plugin widget data
	$wid_name = 'widget_readlistenwatch-widget';
	$wid_opts = get_option( $wid_name );
	$show_rss = isset( $wid_opts[3]['show_rss'] ) ? $wid_opts[3]['show_rss'] : false;

	// ratings array
	$ratings = array(1 => "Hated it", 2 => "Didn't Like It", 3 => "Liked It", 4 => "Really Liked It", 5 => "Loved It");
	
	// variables for the field and option names 
	$opt_name = 'readlistenwatch';
	// read in existing option value from database
	$opt_val = get_option( $opt_name );

	// pull complete item count
	$hidden_item_count = $item_count = count($opt_val);
	if (strip_tags($_GET[ 'additem' ]) == 1) {
		// increment count if adding item
		$hidden_item_count++;
	}

	// see if the user has posted form
	// if so, this hidden field will be set to '1'
	if( strip_tags($_POST[ 'rlw_submit_hidden' ]) == '1' ) {

		// init deletion array
		$del_list = array();

		// loop through submitted form fields
		for ($i = 0; $i < $hidden_item_count; $i++) {
			// Read their posted value
			$opt_val[$i]['type'] = strip_tags($_POST[ 'type_' . $i ]);
			$opt_val[$i]['user'] = strip_tags(stripslashes($_POST[ 'user_' . $i ]));
			$opt_val[$i]['asin'] = strip_tags($_POST[ 'asin_' . $i ]);
			$opt_val[$i]['auth'] = strip_tags(stripslashes($_POST[ 'auth_' . $i ]));
			$opt_val[$i]['title'] = strip_tags(stripslashes($_POST[ 'title_' . $i ]));
			$opt_val[$i]['date'] = strip_tags($_POST[ 'date_' . $i ]);
			$opt_val[$i]['rating'] = strip_tags($_POST[ 'rating_' . $i ]);

			// if field was marked for deletion, save index
			if (isset($_POST[ 'del_' . $i ])) {
				$del_list[] = $opt_val[$i];
			}
		}
		$save_array = $opt_val;

		// if field was marked for deletion, delete at index
		if ($del_list) {
			$save_array = array_diff_no_cast($opt_val, $del_list);
			$hidden_item_count = $item_count = count($save_array);
		}

		// save form values in the database
		update_option( $opt_name, $save_array );

		// put an options updated message on the screen
		?>
		<div class="updated"><p><strong><?php _e('Options saved.', 'rlwentity' ); ?></strong></p></div>
		<?php
	}

	// display the options editing screen
	echo '<div class="wrap">' . "\n";
	
	// header
	echo "<h2>" . __( 'Read/Listen/Watch Options', 'rlwentity' ) . "</h2>\n";
	echo "<p>" . __( 'Fields marked with <strong>*</strong> are required.', 'rlwentity' ) . "</p>";
	echo "<p>" . __( 'If the optional "ASIN" field is left blank, a hyperlink to Amazon will not be created.', 'rlwentity' ) . "</p>";
	echo "<p>" . __( 'The "Date" field is required if you have opted to use the RSS feed feature. (see Widget options)', 'rlwentity' ) . "</p>";

	// options form
	?>
	
	<? // utility javascript ?>
	<script type="text/javascript" src="<?php echo WP_PLUGIN_URL . '/' . THIS_PLUGIN_DIR;?>/read-listen-watch.js"></script>
	<? // css for settings page ?>
	<style type="text/css">
	fieldset {
		border : 1px solid #c0c0c0;
		clear : both;
		font-weight : bold;
		margin-bottom : 2em;
		padding : 1em;
		width : 60%;
		border-radius : 5px;
		-moz-border-radius : 5px;
		-webkit-border-radius : 5px;
	}
	legend { padding: 0px 2px; }
	#readlistenwatch-form { margin-top : 2em; }
	.rlw_item_entry {
		clear : both;
		margin-bottom : 3em;
	}
	.rlw_line {
		clear : both;
		font-weight : normal;
	}
	.rlw_line LABEL { width : 10em; }
	.rlw_line_group {
		clear : both;
		padding-left : 2em;
	}
	.rlw_text_line { clear : both; }
	DIV.del_line LABEL { color : #d44; }
	</style>

	<form name="readlistenwatch-form" id="readlistenwatch-form" method="post" action="">
	<input type="hidden" name="rlw_submit_hidden" value="1">
	<input type="hidden" name="item_count" value="<?php echo $hidden_item_count; ?>">

	<? // first item - we must have at least one; display or not via widget ?>
	<fieldset>
		<legend>Item 1</legend>
		<div class="rlw_line">
			<label style="float:left;" for="user_0"><?php _e('User:', 'rlwentity'); ?></label>
			<input style="float:left;" type="text" size="40" id="user_0" name="user_0" value="<?php echo $opt_val[0][ 'user' ]; ?>" />
		</div>
		<div class="rlw_line_group">
			<div class="rlw_line">
				<label style="float:left;" for="type_0"><?php _e('Is:*', 'rlwentity'); ?></label>
				<select style="float:left;" id="type_0" name="type_0" onchange="rlwUpdateFields(0, this.value);">
					<option value="r"<?php if ($opt_val[0]['type'] == 'r') echo ' selected'; ?>>Reading</option>
					<option value="l"<?php if ($opt_val[0]['type'] == 'l') echo ' selected'; ?>>Listening To</option>
					<option value="w"<?php if ($opt_val[0]['type'] == 'w') echo ' selected'; ?>>Watching</option>
				</select>
			</div>
			<div class="rlw_line">
				<label style="float:left;" for="asin_0"><?php _e('ASIN:', 'rlwentity'); ?></label>
				<input style="float:left;" type="text" size="40" id="asin_0" name="asin_0" value="<?php echo $opt_val[0][ 'asin' ]; ?>" />
			</div>
			<div class="rlw_line">
				<label style="float:left;" for="auth_0" id="lbl_auth_0">
					<?php
					$isDisabled = '';
					if 		($opt_val[0]['type'] == 'r' || !$opt_val[0]) _e('Author:*', 'rlwentity');
					else if ($opt_val[0]['type'] == 'l') _e('Artist:*', 'rlwentity');
					else if ($opt_val[0]['type'] == 'w') {
						_e('--', 'rlwentity');
						$isDisabled = 'disabled';
					}
					?>
				</label>
				<input style="float:left;" type="text" size="40" id="auth_0" name="auth_0" value="<?php echo $opt_val[0][ 'auth' ]; ?>" <?=$isDisabled?> />
			</div>
			<div class="rlw_line">
				<label style="float:left;" for="title_0"><?php _e('Title:*', 'rlwentity'); ?></label>
				<input style="float:left;" type="text" size="40" id="title_0" name="title_0" value="<?php echo $opt_val[0][ 'title' ]; ?>" />
			</div>
			<div class="rlw_line">
				<label style="float:left;" for="date_0">
					<?php
					if ($show_rss) _e('Date:*', 'rlwentity');
					else _e('Date:', 'rlwentity');
					?>
				</label>
				<input style="float:left;" type="text" size="40" id="date_0" name="date_0" value="<?php echo $opt_val[0][ 'date' ]; ?>" /> 
				<span>[<a href="javascript:void(0);" onclick="document.getElementById('date_0').value='<?=gmdate("D, d M Y H:i:s +0000");?>'">set</a>]</span>
			</div>
			<div class="rlw_line">
				<label style="float:left;" for="rating_0"><?php _e('Rating:', 'rlwentity'); ?></label>
				<select style="float:left;" id="rating_0" name="rating_0">
					<option value=""></option>
					<?php for ($i = 1; $i < 6; $i++) {
					echo '<option value="' . $i . '"';
					if ($opt_val[0][ 'rating' ] == $i) { echo ' selected'; }
					echo '>' . $i . ' - ' . $ratings[$i] . '</option>'; } ?>
				</select>
			</div>
		</div>
	</fieldset>

	<?php
	// if there are more than 1 item, display
	if ($item_count > 1) {
		for ($i = 1; $i < $item_count; $i++) {
		?>
			<fieldset>
				<legend>Item <?php echo $i + 1; ?></legend>
				<div class="rlw_line">
					<label style="float:left;" for="user_<?=$i?>"><?php _e('User:', 'rlwentity'); ?></label>
					<input style="float:left;" type="text" size="40" id="user_<?=$i?>" name="user_<?=$i?>" value="<?php echo $opt_val[$i][ 'user' ]; ?>" />
				</div>
				<div class="rlw_line_group">
					<div class="rlw_line">
						<label style="float:left;" for="type_<?=$i?>"><?php _e('Is:*', 'rlwentity'); ?></label>
						<select style="float:left;" id="type_<?=$i?>" name="type_<?=$i?>" onchange="rlwUpdateFields(<?=$i?>, this.value);">
							<option value="r"<?php if ($opt_val[$i]['type'] == 'r') echo ' selected'; ?>>Reading</option>
							<option value="l"<?php if ($opt_val[$i]['type'] == 'l') echo ' selected'; ?>>Listening To</option>
							<option value="w"<?php if ($opt_val[$i]['type'] == 'w') echo ' selected'; ?>>Watching</option>
						</select>
					</div>
					<div class="rlw_line">
						<label style="float:left;" for="asin_<?=$i?>"><?php _e('ASIN:', 'rlwentity'); ?></label>
						<input style="float:left;" type="text" size="40" id="asin_<?=$i?>" name="asin_<?=$i?>" value="<?php echo $opt_val[$i][ 'asin' ]; ?>" />
					</div>
					<div class="rlw_line">
						<label style="float:left;" for="auth_<?=$i?>" id="lbl_auth_<?=$i?>">
							<?php
							$isDisabled = '';
							if 		($opt_val[$i]['type'] == 'r') _e('Author:*', 'rlwentity');
							else if ($opt_val[$i]['type'] == 'l') _e('Artist:*', 'rlwentity');
							else if ($opt_val[$i]['type'] == 'w') {
								_e('--', 'rlwentity');
								$isDisabled = 'disabled';
							}
							?>
						</label>
						<input style="float:left;" type="text" size="40" id="auth_<?=$i?>" name="auth_<?=$i?>" value="<?php echo $opt_val[$i][ 'auth' ]; ?>" <?=$isDisabled?> />
					</div>
					<div class="rlw_line">
						<label style="float:left;" for="title_<?=$i?>"><?php _e('Title:*', 'rlwentity'); ?></label>
						<input style="float:left;" type="text" size="40" id="title_<?=$i?>" name="title_<?=$i?>" value="<?php echo $opt_val[$i][ 'title' ]; ?>" />
					</div>
					<div class="rlw_line">
						<label style="float:left;" for="date_<?=$i?>">
							<?php
							if ($show_rss) _e('Date:*', 'rlwentity');
							else _e('Date:', 'rlwentity');
							?>
						</label>
						<input style="float:left;" type="text" size="40" id="date_<?=$i?>" name="date_<?=$i?>" value="<?php echo $opt_val[$i][ 'date' ]; ?>" /> 
						<span>[<a href="javascript:void(0);" onclick="document.getElementById('date_<?=$i?>').value='<?=gmdate("D, d M Y H:i:s +0000");?>'">set</a>]</span>
					</div>
					<div class="rlw_line del_line">
						<label style="float:left;" for="del_<?=$i?>"><?php _e('Delete:', 'rlwentity'); ?></label>
						<input style="float:left;" type="checkbox" id="del_<?=$i?>" name="del_<?=$i?>">
					</div>
					<div class="rlw_line">
						<label style="float:left;" for="rating_<?=$i?>"><?php _e('Rating:', 'rlwentity'); ?></label>
						<select style="float:left;" id="rating_<?=$i?>" name="rating_<?=$i?>">
							<option value=""></option>
							<?php for ($j = 1; $j < 6; $j++) {
							echo '<option value="' . $j . '"';
							if ($opt_val[$i][ 'rating' ] == $j) { echo ' selected'; }
							echo '>' . $j . ' - ' . $ratings[$j] . '</option>'; } ?>
						</select>
					</div>
				</div>
			</fieldset>
		<?php
		}
	}
    
	// if adding a new item, display empty form
	if (strip_tags($_GET[ 'additem' ]) == 1) {
	?>
		<a name="newitem"></a>
		<fieldset>
			<legend>New Item</legend>
			<div class="rlw_line">
				<label style="float:left;" for="user_<?=$item_count;?>"><?php _e('User:', 'rlwentity'); ?></label>
				<input style="float:left;" type="text" size="40" id="user_<?=$item_count;?>" name="user_<?=$item_count;?>" value="<?php echo $opt_val[$item_count][ 'user' ]; ?>" />
			</div>
			<div class="rlw_line_group">
				<div class="rlw_line">
					<label style="float:left;" for="type_<?=$item_count;?>"><?php _e('Is:*', 'rlwentity'); ?></label>
					<select style="float:left;" id="type_<?=$item_count;?>" name="type_<?=$item_count;?>" onchange="rlwUpdateFields(<?=$item_count;?>, this.value);">
						<option value="r"<?php if ($opt_val[$item_count]['type'] == 'r') echo ' selected'; ?>>Reading</option>
						<option value="l"<?php if ($opt_val[$item_count]['type'] == 'l') echo ' selected'; ?>>Listening To</option>
						<option value="w"<?php if ($opt_val[$item_count]['type'] == 'w') echo ' selected'; ?>>Watching</option>
					</select>
				</div>
				<div class="rlw_line">
					<label style="float:left;" for="asin_<?=$item_count;?>"><?php _e('ASIN:', 'rlwentity'); ?></label>
					<input style="float:left;" type="text" size="40" id="asin_<?=$item_count;?>" name="asin_<?=$item_count;?>" value="<?php echo $opt_val[$item_count][ 'asin' ]; ?>" />
				</div>
				<div class="rlw_line">
					<label style="float:left;" for="auth_<?=$item_count;?>" id="lbl_auth_<?=$item_count?>"><?php _e('Author:*', 'rlwentity'); ?></label>
					<input style="float:left;" type="text" size="40" id="auth_<?=$item_count;?>" name="auth_<?=$item_count;?>" value="<?php echo $opt_val[$item_count][ 'auth' ]; ?>" />
				</div>
				<div class="rlw_line">
					<label style="float:left;" for="title_<?=$item_count;?>"><?php _e('Title:*', 'rlwentity'); ?></label>
					<input style="float:left;" type="text" size="40" id="title_<?=$item_count;?>" name="title_<?=$item_count;?>" value="<?php echo $opt_val[$item_count][ 'title' ]; ?>" />
				</div>
				<div class="rlw_line">
					<label style="float:left;" for="date_<?=$item_count;?>">
						<?php
						if ($show_rss) _e('Date:*', 'rlwentity');
						else _e('Date:', 'rlwentity');
						?>
					</label>
					<input style="float:left;" type="text" size="40" id="date_<?=$item_count;?>" name="date_<?=$item_count;?>" value="<?php echo $opt_val[$item_count][ 'date' ]; ?>" /> 
					<span>[<a href="javascript:void(0);" onclick="document.getElementById('date_<?=$item_count;?>').value='<?=gmdate("D, d M Y H:i:s +0000");?>'">set</a>]</span>
				</div>
				<div class="rlw_line">
					<label style="float:left;" for="rating_<?=$item_count;?>"><?php _e('Rating:', 'rlwentity'); ?></label>
					<select style="float:left;" id="rating_<?=$item_count;?>" name="rating_<?=$item_count;?>">
						<option value=""></option>
						<?php for ($i = 1; $i < 6; $i++) {
						echo '<option value="' . $i . '"';
						if ($opt_val[$item_count][ 'rating' ] == $i) { echo ' selected'; }
						echo '>' . $i . ' - ' . $ratings[$i] . '</option>'; } ?>
					</select>
				</div>
			</div>
		</fieldset>

	<?php
	}
	?>

	<p class="rlw_text_line" id="add-item">
	<?php
	if (!strip_tags($_GET[ 'additem' ]) && strip_tags($_POST[ 'rlw_submit_hidden' ]) != '1') {
	?>
		<p><a href="<?php echo $_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['QUERY_STRING'] . '&amp;additem=1#newitem'; ?>">[+] Add another item</a></p>

		<p class="submit">
		<input type="submit" name="Submit" class="button-primary" value="<?php _e('Save Changes', 'rlwentity' ) ?>" />
		</p>
	<?php
	} else if (strip_tags($_POST[ 'rlw_submit_hidden' ]) == '1') {
	?>
		<p class="submit"><a href="<?php echo $_SERVER['SCRIPT_NAME'] . '?page=rlw-plugin-settings'; ?>">Go back to the settings page to maintain or add items.</a></p>
	<?php
	} else if (strip_tags($_GET[ 'additem' ])) {
	?>
		<p class="submit">
		<input type="submit" name="Submit" class="button-primary" value="<?php _e('Save Changes', 'rlwentity' ) ?>" /> 
		<input type="button" name="Cancel" class="button-secondary" value="<?php _e('Cancel', 'rlwentity' ) ?>" onclick="location.href='<?php echo $_SERVER['SCRIPT_NAME'] . '?page=rlw-plugin-settings'; ?>'" />
		</p>
	<?php
	}
	?>
	</p>

	</form>
	</div>

<?php
}

/* main widget class */
class RLW_Widget extends WP_Widget {

	/* setup */
	function RLW_Widget() {
		/* settings. */
		$widget_ops = array(
			'classname' => 'rlwentity',
			'description' => __('Display what you\'re reading, listening to, and watching; with links to Amazon.', 'rlwentity')
			);

		/* control settings. */
		$control_ops = array( 'width' => 300, 'id_base' => 'readlistenwatch-widget' );
		
		/* create the widget. */
		$this->WP_Widget( 'readlistenwatch-widget', __('Read/Listen/Watch', 'rlwentity'), $widget_ops, $control_ops );
	}

	/* display */
	function widget( $args, $instance ) {
		// ratings array
		$ratings = array(1 => "Hated it", 2 => "Didn't Like It", 3 => "Liked It", 4 => "Really Liked It", 5 => "Loved It");
		
		// pull widget options
		extract( $args );
		
		/* widget variables */
		$title = apply_filters('widget_title', $instance['title'] );
		$amznId = $instance['amznId'];
		$fullId = '';
		if ($amznId)
			$fullId = '?tag=' . $amznId;
		$new_win = isset( $instance['new_win'] ) ? $instance['new_win'] : false;
		$target = '';
		if ($new_win) { $target = 'target="_blank"'; }
		$show_rss = isset( $instance['show_rss'] ) ? $instance['show_rss'] : false;
		$rss_desc = $instance['rss_desc'];
		$show_icons = isset( $instance['show_media_icons'] ) ? $instance['show_media_icons'] : false;
		$icon_string = '';
		if ($show_icons) {
			$icon_string_r = 'background : url(' . "'" . WP_PLUGIN_URL . '/' . THIS_PLUGIN_DIR . "/images/icon_media_book.gif'" . ') top right no-repeat;';
			$icon_string_l = 'background : url(' . "'" . WP_PLUGIN_URL . '/' . THIS_PLUGIN_DIR . "/images/icon_media_headphones.gif'" . ') top right no-repeat;';
			$icon_string_w = 'background : url(' . "'" . WP_PLUGIN_URL . '/' . THIS_PLUGIN_DIR . "/images/icon_media_tv.gif'" . ') top right no-repeat;';
		}

		/* before widget (defined by themes) */
		echo $before_widget . "\n";

		/* display the widget title if one was input (before and after defined by themes) */
		echo "\t" . $before_title;
		echo $title;
		if ($show_rss) {
			echo '&nbsp;&nbsp; <a title="RSS feed: ' . $rss_desc . ' This is a separate feed from the blog." href="' . WP_PLUGIN_URL . '/' . THIS_PLUGIN_DIR . '/rlw-rss.php"><img align="bottom" style="margin-bottom:-3px;" src="' . WP_PLUGIN_URL . '/' . THIS_PLUGIN_DIR . '/images/rss-icon.gif" width="16" height="16" border="0" alt="RSS" /></a>';
		}
		echo $after_title . "\n";

		echo "\t" . '<ul id="readlistenwatch_ul">' . "\n";

		// pull plugin data
		$opt_name = 'readlistenwatch';
		$opt_val = get_option( $opt_name );

		// loop through items

		/* reading */
		for ($i = 0; $i < count($opt_val); $i++) {
			$type = $opt_val[ $i ][ 'type' ];
			if ($type == 'r') {
				$user = RLW_Widget::localentity($opt_val[ $i ][ 'user' ]);
				$asin = $opt_val[ $i ][ 'asin' ];
				$auth = RLW_Widget::localentity($opt_val[ $i ][ 'auth' ]);
				$title = RLW_Widget::localentity($opt_val[ $i ][ 'title' ]);
				$rating = $opt_val[ $i ][ 'rating' ];
    
				if ($title) {
					echo "\t\t" . '<li';
					if ($rating) {
						echo ' title="';
						if ($user) echo $user . ' rated this ';
						echo $rating . ' out of 5 stars (' . $ratings[$rating] . ')"';
					}
					echo '><strong style="color:#2b333c;">';
					if ($user) echo $user . ' ';
					echo 'reading:</strong>' . "\n";
					echo "\t\t\t" . '<ul class="media-item" style="' . $icon_string_r . '"><li>' . $auth . ':<br />';
					if ($asin)
						echo '<a ' . $target . ' href="http://www.amazon.com/dp/' . $asin . '/' . $fullId . '">' . $title . '</a>';
					else
						echo $title;
					echo '</li></ul>' . "\n";
					echo "\t\t" . '</li>' . "\n";
				}
			}
		}

		/* listening */
		for ($i = 0; $i < count($opt_val); $i++) {
			$type = $opt_val[ $i ][ 'type' ];
			if ($type == 'l') {
				$user = RLW_Widget::localentity($opt_val[ $i ][ 'user' ]);
				$asin = $opt_val[ $i ][ 'asin' ];
				$auth = RLW_Widget::localentity($opt_val[ $i ][ 'auth' ]);
				$title = RLW_Widget::localentity($opt_val[ $i ][ 'title' ]);
				$rating = $opt_val[ $i ][ 'rating' ];
			
				if ($title) {
					echo "\t\t" . '<li';
					if ($rating) {
						echo ' title="';
						if ($user) echo $user . ' rated this ';
						echo $rating . ' out of 5 stars (' . $ratings[$rating] . ')"';
					}
					echo '><strong style="color:#2b333c;">';
					if ($user) echo $user . ' ';
					echo 'listening to:</strong>' . "\n";
					echo "\t\t\t" . '<ul class="media-item" style="' . $icon_string_l . '"><li>' . $auth . ':<br />';
					if ($asin)
						echo '<a ' . $target . ' href="http://www.amazon.com/dp/' . $asin . '/' . $fullId . '">' . $title . '</a>';
					else
						echo $title;
					echo '</li></ul>' . "\n";
					echo "\t\t" . '</li>' . "\n";
				}
			}
		}

		/* watching */
		for ($i = 0; $i < count($opt_val); $i++) {
			$type = $opt_val[ $i ][ 'type' ];
			if ($type == 'w') {
				$user = RLW_Widget::localentity($opt_val[ $i ][ 'user' ]);
				$asin = $opt_val[ $i ][ 'asin' ];
				$auth = RLW_Widget::localentity($opt_val[ $i ][ 'auth' ]);
				$title = RLW_Widget::localentity($opt_val[ $i ][ 'title' ]);
				$rating = $opt_val[ $i ][ 'rating' ];
				
				if ($title) {
					echo "\t\t" . '<li';
					if ($rating) {
						echo ' title="';
						if ($user) echo $user . ' rated this ';
						echo $rating . ' out of 5 stars (' . $ratings[$rating] . ')"';
					}
					echo '><strong style="color:#2b333c;">';
					if ($user) echo $user . ' ';
					echo 'watching:</strong>' . "\n";
					echo "\t\t\t" . '<ul class="media-item" style="' . $icon_string_w . '"><li>';
					if ($asin)
						echo '<a ' . $target . ' href="http://www.amazon.com/dp/' . $asin . '/' . $fullId . '">' . $title . '</a>';
					else
						echo $title;
					echo '<br />&nbsp;</li></ul>' . "\n";
					echo "\t\t" . '</li>' . "\n";
				}
			}
		}

		echo "\t" . '</ul>' . "\n";
		
		/* after widget (defined by themes) */
		echo $after_widget;
	}

	/* update */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		
		// update widget options
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['amznId'] = strip_tags( $new_instance['amznId'] );
		$instance['new_win'] = $new_instance['new_win'];
		$instance['show_rss'] = $new_instance['show_rss'];
		$instance['show_media_icons'] = $new_instance['show_media_icons'];
		$instance['rss_title'] = strip_tags( $new_instance['rss_title'] );
		$instance['rss_desc'] = strip_tags( $new_instance['rss_desc'] );
		$instance['rss_image'] = strip_tags( $new_instance['rss_image'] );
		
		return $instance;
	}

	/* widget settings controls on the widget panel */
	function form( $instance ) {
		/* set defaults */
		$defaults = array(
			'title' => __('What is... ', 'rlwentity'),
			'amznId' => '',
			'new_win' => false,
			'show_rss' => true,
			'show_media_icons' => true,
			'rss_title' => __('Reading, Watching, &amp; Listening', 'rlwentity'),
			'rss_desc' => __('What are we reading, watching, and listening to?', 'rlwentity'),
			'rss_image' => ''
			);
		$instance = wp_parse_args( (array) $instance, $defaults );
		?>

		<? // utility javascript ?>
		<script type="text/javascript" src="<?php echo WP_PLUGIN_URL . '/' . THIS_PLUGIN_DIR;?>/read-listen-watch.js"></script>
		<? // css for widget config ?>
		<style type="text/css">
		#rss-extra { margin-left : 1em; }
		.rlw_widget_line { clear : both; }
		.rlw_widget_line INPUT { margin-bottom : 1em; }
		</style>

		<? // widget config form ?>
		<div class="rlw_widget_line">
			<label style="float:left;width:50%;" for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Heading:', 'rlwentity'); ?></label>
			<input style="float:left;width:45%;" type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" />
		</div>
		<div class="rlw_widget_line">
			<label style="float:left;width:50%;" for="<?php echo $this->get_field_id( 'amznId' ); ?>"><?php _e('Amazon Associate ID:', 'rlwentity'); ?></label>
			<input style="float:left;width:45%;" type="text" id="<?php echo $this->get_field_id( 'amznId' ); ?>" name="<?php echo $this->get_field_name( 'amznId' ); ?>" value="<?php echo $instance['amznId']; ?>" />
		</div>
		<div class="rlw_widget_line">
			<label style="float:left;width:60%;" for="<?php echo $this->get_field_id( 'new_win' ); ?>"><?php _e('Open links in new window?', 'rlwentity'); ?></label>
			<input style="float:left;" type="checkbox"<?php if($instance['new_win']) echo ' checked'; ?> id="<?php echo $this->get_field_id( 'new_win' ); ?>" name="<?php echo $this->get_field_name( 'new_win' ); ?>" /> 
		</div>
		<div class="rlw_widget_line">
			<label style="float:left;width:60%;" for="<?php echo $this->get_field_id( 'show_media_icons' ); ?>"><?php _e('Display Media Icons?', 'rlwentity'); ?></label>
			<input style="float:left;" type="checkbox"<?php if($instance['show_media_icons']) echo ' checked'; ?> id="<?php echo $this->get_field_id( 'show_media_icons' ); ?>" name="<?php echo $this->get_field_name( 'show_media_icons' ); ?>" /> 
		</div>
		<div class="rlw_widget_line">
			<label style="float:left;width:60%;" for="<?php echo $this->get_field_id( 'show_rss' ); ?>"><?php _e('Display RSS Icon?', 'rlwentity'); ?></label>
			<input style="float:left;" type="checkbox"<?php if($instance['show_rss']) echo ' checked'; ?> id="<?php echo $this->get_field_id( 'show_rss' ); ?>" name="<?php echo $this->get_field_name( 'show_rss' ); ?>" /> 
		</div>

		<div style="clear:both;">&nbsp;</div>

		<div id="rss-extra">
			If <em>'Display RSS Icon'</em> is checked:
			<div class="rlw_widget_line">
				<label style="float:left;width:40%;" for="<?php echo $this->get_field_id( 'rss_title' ); ?>"><?php _e('RSS Title:', 'rlwentity'); ?></label>
				<input style="float:left;width:55%;" type="text" id="<?php echo $this->get_field_id( 'rss_title' ); ?>" name="<?php echo $this->get_field_name( 'rss_title' ); ?>" value="<?php echo $instance['rss_title']; ?>" />
			</div>
			<div class="rlw_widget_line">
				<label style="float:left;width:40%;" for="<?php echo $this->get_field_id( 'rss_desc' ); ?>"><?php _e('RSS Description:', 'rlwentity'); ?></label>
				<input style="float:left;width:55%;" type="text" id="<?php echo $this->get_field_id( 'rss_desc' ); ?>" name="<?php echo $this->get_field_name( 'rss_desc' ); ?>" value="<?php echo $instance['rss_desc']; ?>" />
			</div>
			<div class="rlw_widget_line">
				<label style="float:left;width:40%;" for="<?php echo $this->get_field_id( 'rss_image' ); ?>"><?php _e('RSS Image URL:', 'rlwentity'); ?></label>
				<input style="float:left;width:55%;" type="text" id="<?php echo $this->get_field_id( 'rss_image' ); ?>" name="<?php echo $this->get_field_name( 'rss_image' ); ?>" value="<?php echo $instance['rss_image']; ?>" />
			</div>
		</div>

		<div style="clear:both;">&nbsp;</div>

	<?php
	}

	// global htmlentities
	function localentity($convert) {
		$style = ENT_COMPAT;
		$charset = get_option('blog_charset');
		$double = FALSE;
		// the double-encoding param was added in 5.2.3
		if (strnatcmp(PHP_VERSION,'5.2.3') >= 0) {
			$converted = htmlspecialchars($convert, $style, $charset, $double);
		} else {
			$converted = htmlspecialchars($convert, $style, $charset);
		}
		return $converted;
	}
}

// end
?>
