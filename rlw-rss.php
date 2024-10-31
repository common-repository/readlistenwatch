<?php
$basepath = explode('wp-content', $_SERVER['SCRIPT_FILENAME']);
require($basepath[0] . '/wp-blog-header.php');

header('Content-Type: text/xml; charset=' . get_option('blog_charset'), true);

echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>';

if ( ! defined( 'THIS_PLUGIN_DIR' ) ) {
	$fullplugindir = explode('/', dirname(__FILE__));
	define( 'THIS_PLUGIN_DIR', $fullplugindir[sizeof($fullplugindir) - 1] );
}

// pull plugin widget data
$wid_name = 'widget_readlistenwatch-widget';
$wid_opts = get_option( $wid_name );
?>
<rss version="2.0"
 xmlns:content="http://purl.org/rss/1.0/modules/content/"
 xmlns:dc="http://purl.org/dc/elements/1.1/"
 xmlns:atom="http://www.w3.org/2005/Atom"
 xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
>
	<channel>
		<title><?php bloginfo_rss('name');?><?php if ($wid_opts[3]['rss_title']) { echo ': ' . localentity($wid_opts[3]['rss_title']); } ?></title>
		<atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+xml" />
		<link><?php bloginfo_rss('url') ?></link>
		<description><?php if ($wid_opts[3]['rss_desc']) { echo localentity($wid_opts[3]['rss_desc']); } ?></description>
		
		<language><?php echo get_option('rss_language'); ?></language>
		<sy:updatePeriod><?php echo apply_filters( 'rss_update_period', 'daily' ); ?></sy:updatePeriod>
		<sy:updateFrequency><?php echo apply_filters( 'rss_update_frequency', '1' ); ?></sy:updateFrequency>

<?php

// display rss image, if applicable
if ($wid_opts[3]['rss_image']) {
	echo "\t\t" . '<image>' . "\n";
	echo "\t\t " . '<title>'; bloginfo_rss('name');
	if ($wid_opts[3]['rss_title']) { echo ': ' . localentity($wid_opts[3]['rss_title']); }
	echo '</title>' . "\n";
	echo "\t\t " . '<url>' . $wid_opts[3]['rss_image'] . '</url>' . "\n";
	echo "\t\t " . '<link>'; bloginfo_rss('url'); echo '</link>' . "\n";
	echo "\t\t" . '</image>' . "\n\n";
}

output_rlw();

function output_rlw() {

	// ratings array
	$ratings = array(1 => "Hated it", 2 => "Didn't Like It", 3 => "Liked It", 4 => "Really Liked It", 5 => "Loved It");
	
	// pull plugin data
	$opt_name = 'readlistenwatch';
	$opt_val = get_option( $opt_name );
	
	global $wid_opts;
	
	$amznId = $wid_opts[3]['amznId'];
	$fullId = '';
	if ($amznId)
		$fullId = '?tag=' . $amznId;

	// loop through items

	for ($i = 0; $i < count($opt_val); $i++) {
		$type = $opt_val[ $i ][ 'type' ];
		$user = localentity($opt_val[ $i ][ 'user' ]);
		$asin = $opt_val[ $i ][ 'asin' ];
		$auth = localentity($opt_val[ $i ][ 'auth' ]);
		$title = localentity($opt_val[ $i ][ 'title' ]);
		$date = $opt_val[ $i ][ 'date' ];
		$rating = $opt_val[ $i ][ 'rating' ];

		if ($type && $title) {
			$link = '';
			if ($asin) {
				// build amazon link with associate id if applicable
				$link = 'http://www.amazon.com/dp/' . $asin . '/' . $fullId;
	
				// build amazon image link
				// if image can't be found, provide our own 'broken' image
				$image = 'http://ec1.images-amazon.com/images/P/' . $asin . '.01._SCMZZZZZZZ_.jpg';
					$rsize = remote_filesize($image);
					if ($rsize < 1000) {
						$image = WP_PLUGIN_URL . '/' . THIS_PLUGIN_DIR . "/images/rlw404.gif";
					}
			}

			// build main description block
			$tease = '';
			$desc = '';
				$desc .= '<p>';
				if ($asin)
					$desc .= '<img src="' . $image . '" align="right" hspace="2" border="1">';
				$tease .= '"' . $title;
				$desc .= '<strong><em>' . $title . '</em></strong>';
				// only include author/artist for reading/listening
				if ($type != 'w') {
					$tease .= '," by ' . $auth . '.';
					$desc .= '<br />' . "\n" . $auth;
				} else {
					$tease .= '"';
				}
				$desc .= '</p>' . "\n";
				// include ratings where applicable
				if ($rating) {
					$desc .= '<p>';
					if ($user) {
						$tease .= ' ' . $user . ' rated this ';
						$desc .= $user . ' rated this: ';
					}
					$tease .= $rating . ' out of 5 stars (' . $ratings[$rating] . ').';
					$desc .= '<img src="' . WP_PLUGIN_URL . '/' . THIS_PLUGIN_DIR . '/images/stars_2_' 
						. $rating . '0.gif" width="92" height="15" border="0" alt="' . $rating 
						. ' out of 5 stars (' . $ratings[$rating] . ')" title="' . $rating 
						. ' out of 5 stars (' . $ratings[$rating] . ')" />';
					$desc .= '</p>' . "\n";
				}
				if ($asin)
					$desc .= '<p><a href="' . $link . '">"' . $title . '" on Amazon.com</a></p>' . "\n";

			// create rss xml
			$itemContent = "\t\t" . '<item>' . "\n";
			$itemContent .= "\t\t" . ' <title>';
				if ($user)
					$itemContent .= 'What is ' . $user;
				else
					$itemContent .= $wid_opts[3]['title'];

				if ($type == 'r')
					$itemContent .= ' reading';
				elseif ($type == 'l')
					$itemContent .= ' listening to';
				else
					$itemContent .= ' watching';
				$itemContent .= '?</title>' . "\n";
			if ($asin) {
				$itemContent .= "\t\t" . ' <link>' . $link . '</link>' . "\n";
				$itemContent .= "\t\t" . ' <guid isPermaLink="false">' . $link . '</guid>' . "\n";
			} else {
				$itemContent .= "\t\t" . ' <link>' . get_bloginfo_rss('url') . '</link>' . "\n";
				$itemContent .= "\t\t" . ' <guid isPermaLink="false">' . get_bloginfo_rss('url') . '</guid>' . "\n";
			}
			$itemContent .= "\t\t" . ' <description><![CDATA[' . $tease . ']]></description>' . "\n";
			$itemContent .= "\t\t" . ' <content:encoded><![CDATA[' . $desc . ']]></content:encoded>' . "\n";
			if ($user)
				$itemContent .= "\t\t" . ' <dc:creator>' . $user . '</dc:creator>' . "\n";
			$itemContent .= "\t\t" . ' <pubDate>' . $date . '</pubDate>' . "\n";
			$itemContent .= "\t\t" . '</item>' . "\n\n";

			// push item onto array for later sorting
			$itemArray[sizeof($itemArray)][0] = strtotime($date, time());
			$itemArray[sizeof($itemArray) - 1][1] = $itemContent;
		}
	}

	// sort array based on 1st dimension field (date)
	// (typically rss feeds contain content in descending date order)
	$itemArray = msort($itemArray,0,false);
	for ($i = 0; $i < sizeof($itemArray); $i ++) {
		// output
		echo $itemArray[$i][1];
	}
}

// function to sort multi-dim array
function msort($array, $id = "id", $sort_ascending = true) {
	$temp_array = array();
	while(count($array) > 0) {
		$lowest_id = 0;
		$index = 0;
		foreach ($array as $item) {
			if (isset($item[$id])) {
				if ($array[$lowest_id][$id]) {
					if ($item[$id] < $array[$lowest_id][$id]) {
						$lowest_id = $index;
					}
				}
			}
			$index++;
		}
		$temp_array[] = $array[$lowest_id];
		$array = array_merge(array_slice($array, 0,$lowest_id), array_slice($array, $lowest_id + 1));
	}
	if ($sort_ascending) {
		return $temp_array;
	} else {
		return array_reverse($temp_array);
	}
}

// function to check remote filesize
function remote_filesize($url, $user = "", $pw = "") {
	ob_start();
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_NOBODY, 1);
	
	if(!empty($user) && !empty($pw)) {
		$headers = array('Authorization: Basic ' .  base64_encode("$user:$pw"));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	}
	
	$ok = curl_exec($ch);
	curl_close($ch);
	$head = ob_get_contents();
	ob_end_clean();
	
	$regex = '/Content-Length:\s([0-9].+?)\s/';
	$count = preg_match($regex, $head, $matches);
	
	return isset($matches[1]) ? $matches[1] : "unknown";
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

?>
	</channel>
</rss>
