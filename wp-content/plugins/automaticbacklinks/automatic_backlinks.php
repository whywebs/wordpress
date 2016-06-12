<?php
/*
Plugin Name: Automatic Backlinks
Plugin URI: http://www.automaticbacklinks.com
Description: Displays links from the Automatic Backlinks link exchange network and earns you backlinks from other members web sites automatically. To get started: 1) Click the "Activate" link to the left of this description, 2) Log in to your Automatic Backlinks account and click "Link Display Codes" and copy your account code from the bottom of the page 3) Under your Wordpress settings click "Automatic Backlinks" and save your account code.
Version: 2.2.1
Author: Automatic Backlinks
Author URI: http://www.automaticbacklinks.com
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

error_reporting( 1 );

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Widget
 */
if ( ! class_exists( 'AbWidget' ) ) {

	class AbWidget extends WP_Widget {

		/** constructor */
		function AbWidget() {
			parent::__construct( false, $name = 'Automatic Backlinks', array( "description" => "Displays links from the Automatic Backlinks Network" ) );
		}

		/** @see WP_Widget::update */
		function update( $new_instance, $old_instance ) {
			return $new_instance;
		}

		/** @see WP_Widget::form */
		function form( $instance ) {
			$title = esc_attr( $instance['title'] );
			?>
			<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title: (optional)' ); ?>
					<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></label>
			</p>
			<p>More settings can be found in the Dashboard of
				<a href="http://www.automaticbacklinks.com" target="_blank">Automatic Backlinks</a></p>
			<?php
		}

		/**
		 * Output the content of the widget
		 *
		 * @param <type> $args
		 * @param <type> $instance
		 */
		function widget( $args, $instance ) {
			extract( $args );
			?>
			<?php echo $before_widget; ?>
			<?php echo $before_title . $instance['title'] . $after_title; ?>
			<?php
			//Call Automatic Backlinks custom code
			init_ab();
			?>
			<?php echo $after_widget; ?>
			<?php
		}

	} // class AbWidget
}

// register AbWidget widget
add_action( 'widgets_init', create_function( '', 'return register_widget("AbWidget");' ) );

/**
 * Settings page
 */

//Init settings
add_action( 'admin_init', 'automaticbacklinks_settings_init' );
function automaticbacklinks_settings_init() {

	//Register setting sections and fields
	//All settings are saved in one array called automaticbacklinks_options
	register_setting( 'automaticbacklinks-settings-group', 'automaticbacklinks_options' );
	add_settings_section( 'automaticbacklinks-settings-section-general', 'General Settings', 'automaticbacklinks_settings_section_general', 'automaticbacklinks-settings-page' );
	add_settings_section( 'automaticbacklinks-settings-section-cache', 'Cache Settings', 'automaticbacklinks_settings_section_cache', 'automaticbacklinks-settings-page' );
	add_settings_field( 'automaticbacklinks-settings-field-account_code', 'Account Code', 'automaticbacklinks_settings_field_account_code', 'automaticbacklinks-settings-page', 'automaticbacklinks-settings-section-general' );
	add_settings_field( 'automaticbacklinks-settings-field-cache_folder', 'Cache Folder', 'automaticbacklinks_settings_field_cache_folder', 'automaticbacklinks-settings-page', 'automaticbacklinks-settings-section-cache' );
	add_settings_field( 'automaticbacklinks-settings-field-cache_time', 'Cache time (hours)', 'automaticbacklinks_settings_field_cache_time', 'automaticbacklinks-settings-page', 'automaticbacklinks-settings-section-cache' );

	//Set defaults
	$account_code = "483e6d0df8cc18c57327f368c987c436";
	if ( strlen( $account_code ) == 11 ) {
		$account_code = '';
	}
	$options = get_option( 'automaticbacklinks_options' );
	if ( empty( $options['account_code'] ) ) {
		$options['account_code'] = $account_code;
	}
	if ( empty( $options['cache_folder'] ) ) {
		$options['cache_folder'] = 'automaticbacklinks_cache';
	}
	if ( empty( $options['cache_time'] ) ) {
		$options['cache_time'] = '24';
	}
	update_option( 'automaticbacklinks_options', $options );

}

//Show notice until the user enters their account code
add_action( 'admin_notices', 'automaticbacklinks_warning' );
function automaticbacklinks_warning() {

	$options = get_option( 'automaticbacklinks_options' );
	if ( empty( $options['account_code'] ) ) {
		echo "<div id='automaticbacklinks-warning' class='updated fade'><p><strong>Automatic Backlinks is almost ready</strong>. Please <a href='options-general.php?page=automaticbacklinks-settings-page'>enter your account code</a> to complete the installation.</strong></p></div>";
	}

}

//Register the method that holds the code for our settings link
add_action( 'admin_menu', 'automaticbacklinks_plugin_menu' );

//Adds the settings page to the plugin menu 
function automaticbacklinks_plugin_menu() {
	add_options_page( 'Settings', 'Automatic Backlinks', 'manage_options', 'automaticbacklinks-settings-page', 'automaticbacklinks_settings' );
}

//Code for settings page
function automaticbacklinks_settings() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	?>
	<div class="wrap">
		<?php screen_icon(); ?>
		<h2>Automatic Backlinks Settings</h2>

		<form method="post" action="options.php">
			<?php settings_fields( 'automaticbacklinks-settings-group' ); ?>
			<?php do_settings_sections( 'automaticbacklinks-settings-page' ); ?>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

//Section intros
function automaticbacklinks_settings_section_cache() {
	echo "Do not change the default caching settings unless you know what you are doing";
}

function automaticbacklinks_settings_section_general() {
	echo "You can find your account code at the bottom of the 'Hosting Code' page after <a href='http://www.automaticbacklinks.com/' target='_blank'>logging into Automatic Backlinks</a>";
}

//Fields: account_code
function automaticbacklinks_settings_field_account_code() {
	$options = get_option( 'automaticbacklinks_options' );
	echo "<input id='automaticbacklinks-settings-field-account_code' name='automaticbacklinks_options[account_code]' size='40' type='text' value='{$options['account_code']}' />";
}

//Fields: cache_folder
function automaticbacklinks_settings_field_cache_folder() {
	$options = get_option( 'automaticbacklinks_options' );
	echo "<input id='automaticbacklinks-settings-field-cache_folder' name='automaticbacklinks_options[cache_folder]' size='80' type='text' value='{$options['cache_folder']}' />";
}

//Fields: cache_time
function automaticbacklinks_settings_field_cache_time() {
	$options = get_option( 'automaticbacklinks_options' );
	?>
	<select id='automaticbacklinks-settings-field-cache_time' name='automaticbacklinks_options[cache_time]'>
		<option value="24" <?php echo ( $options['cache_time'] == '24' ) ? 'selected="selected"' : ''; ?>>24 hours</option>
		<option value="48" <?php echo ( $options['cache_time'] == '48' ) ? 'selected="selected"' : ''; ?>>48 hours (2 days)</option>
		<option value="96" <?php echo ( $options['cache_time'] == '96' ) ? 'selected="selected"' : ''; ?>>96 hours (4 days)</option>
		<option value="168" <?php echo ( $options['cache_time'] == '168' ) ? 'selected="selected"' : ''; ?>>168 hours (One week)</option>
	</select>
	<?php
	//echo "<input id='automaticbacklinks-settings-field-cache_time' name='automaticbacklinks_options[cache_time]' size='40' type='text' value='{$options['cache_time']}' />";
}

// Add settings link on plugin page
function automaticbacklinks_settings_link( $links ) {
	$settings_link = '<a href="options-general.php?page=automaticbacklinks-settings-page">Settings</a>';
	$links[]       = $settings_link;

	return $links;
}

$plugin = plugin_basename( __FILE__ );
add_filter( "plugin_action_links_$plugin", 'automaticbacklinks_settings_link' );

/**
 * Clear Wordpress caches when clearing AB cache manually
 */
if ( ! empty( $_GET['ab_cc'] ) ) {

	//Clearing super cache if it exists
	if ( ! empty( $GLOBALS["super_cache_enabled"] ) ) {
		wp_cache_clear_cache();
	}

}

/**
 * Automatic Backlinks Main function that calls all other ABC functions
 */
function init_ab() {

	error_reporting( 1 );

	//Settings
	$options           = get_option( 'automaticbacklinks_options' );
	$abCacheFolderName = $options['cache_folder'];
	$abAccountCode     = $options['account_code'];
	$abCacheHours      = $options['cache_time'];

	if ( empty( $abAccountCode ) ) {
		echo "Please enter your Automatic Backlinks account code in the plugin settings page";

		return false;
	}

	/**
	 * Do not change anything below
	 * Do not change anything below
	 * Do not change anything below
	 * Do not change anything below
	 * Do not change anything below
	 */

	$v     = "2.3";
	$s     = "wp28";
	$abMsg = array();
	if ( trim( $_GET['ab_debug'] ) == $abAccountCode ) {
		$debug = true;
		echo "<li />Version: " . $v;
		echo "<li />System: " . $s;
		unset( $_GET['ab_debug'] );
	}

	//Show phpinfo if debug is on and phpinfo is requested
	if ( $debug && $_GET['phpinfo'] ) {

		?>
		<style type="text/css">
			#phpinfo_div {
				position   : fixed;
				bottom     : 0;
				left       : 0;
				height     : 300px;
				width      : 100%;
				overflow   : scroll;
				background : #F0F0F0;
				color      : #000;
				border-top : 2px solid;
				z-index    : 999;
			}
		</style>
		<div id="phpinfo_div">
			<?php echo phpinfo(); ?>
		</div>
		<?php

	}

	//Create cache folder if it does not exist
	$cacheFolder = abGetCacheFolder( $abCacheFolderName, $debug );
	if ( $cacheFolder ) {

		//Current URL
		$page = abGetPageUrl( $debug );
		if ( abIsValidUrl( $page, $debug ) ) {

			$cacheFileName = $cacheFolder . "/" . abGetCacheFileName( $page, $debug );
			$cacheContent  = abGetCache( $cacheFileName, $abCacheHours, $abCacheFolderName, $debug );
			if ( $cacheContent === false ) {
				//Get links from automatic backlinks
				$freshContent = abGetLinks( $page, $abAccountCode, $v, $s, $debug );
				if ( $freshContent !== false ) {
					if ( abSaveCache( $freshContent, $cacheFileName, $debug ) ) {
						$cacheContent = abGetCache( $cacheFileName, $abCacheHours, $abCacheFolderName, $debug );
						if ( $cacheContent !== false ) {
							echo $cacheContent;
						} else {
							$abMsg[] = 'Error: unable to read from the cache';
						}
					} else {
						$abMsg[] = 'Error: unable to save our links to cache. Please make sure that the folder ' . $abCacheFolderName . ' located in the folder ' . $_SERVER['DOCUMENT_ROOT'] . ' and is writable';
					}
				} else {
					$abMsg[] = 'Error: unable to get links from server. Please make sure that your site supports either file_get_contents() or the cURL library.';
				}
			} else {
				//Display the cached content
				echo $cacheContent;
			}

		} else {
			$abMsg[] = 'Error: your site reports that it is located on the following URL: ' . $page . ' - This is not a valid URL and we can not display links on this page. This is probably due to an incorrect setting of the $_SERVER variable.';
		}

	} else {
		$abMsg[] = 'Error: Unable to create or read from your link cache folder. Please try to create a folder by the name "' . $abCacheFolderName . '" directly in the root of your site and make it writable';
	}

	foreach ( $abMsg as $error ) {
		echo $error . "<br />";
	}

}

/**
 * Automatic Backlinks Helper functions
 */

function abSaveCache( $content, $file, $debug = false ) {

	//Prepend a timestamp to the content
	$content = time() . "|" . $content;

	echo ( $debug ) ? "<li />Saving Cache: " . $content : "";

	$fh = fopen( $file, 'w' );
	if ( $fh !== false ) {
		if ( ! fwrite( $fh, $content ) ) {
			echo ( $debug ) ? "<li />Error Saving Cache!" : "";

			return false;
		}
	} else {
		echo ( $debug ) ? "<li />Error opening cache file for writing!" : "";

		return false;
	}
	if ( ! fclose( $fh ) ) {
		echo ( $debug ) ? "<li />Error closing file handle!" : "";

		return false;
	}

	if ( ! file_exists( $file ) ) {
		echo ( $debug ) ? "<li />Error could not create cache file!" : "";

		return false;
	} else {
		echo ( $debug ) ? "<li />Cache file created successfully" : "";

		return true;
	}

}

//Deletes any cache file that is from before Today (Max 500)
function abClearOldCache($cacheFolderName, $cacheHours, $debug=false) {
    
    $today = date('Ymd');
    $cacheFolder = abGetCacheFolder($cacheFolderName);
    
    if (is_dir($cacheFolder)) {
        
        $allCacheFiles = glob($cacheFolder.'/*.cache');
        $todaysCacheFiles = glob($cacheFolder.'/'.$today.'*.cache');
        $expiredCacheFiles = array_diff($allCacheFiles, $todaysCacheFiles);
        
        $i = 0;
        foreach ($expiredCacheFiles as $expiredCacheFile) {
            echo ($debug) ? "<li />Deleting expired cache file: ".$expiredCacheFile : "";
            abRemoveCacheFile($expiredCacheFile, $debug);
            
            // Limit to max 500
            $i++;
            if ($i >= 500) {
                break;
            }
        }
    }
}


//Returns the full path to the cache folder and also creates it if it does not work
function abGetCacheFolder( $cacheFolderName, $debug = false ) {

	if ( isset( $_SERVER['DOCUMENT_ROOT'] ) ) {
		$docRoot = rtrim( $_SERVER['DOCUMENT_ROOT'], "/" ); //Remove any trailing slashes
	} else if ( isset( $_SERVER['PATH_TRANSLATED'] ) ) {
		$docRoot = rtrim( substr( $_SERVER['PATH_TRANSLATED'], 0, 0 - strlen( $_SERVER['PHP_SELF'] ) ), '\\' );
		$docRoot = str_replace( '\\\\', '/', $docRoot );
	} else {
		echo ( $debug ) ? "<li />Error: Could not construct cache path" : "";
	}
	$cacheFolder = $docRoot . "/" . $cacheFolderName;

	echo ( $debug ) ? "<li />Cache folder is: " . $cacheFolder : "";

	if ( ! file_exists( $cacheFolder ) ) {
		echo ( $debug ) ? "<li />Cache folder does not exist: " . $cacheFolder : "";
		if ( ! @mkdir( $cacheFolder, 0777 ) ) {
			echo ( $debug ) ? "<li />Error - could not create cache folder: " . $cacheFolder : "";

			return false;
		} else {
			echo ( $debug ) ? "<li />Successfully created cache folder" : "";
			//Also make an empty default html file
			$blankFile = $cacheFolder . "/index.html";
			if ( ! file_exists( $blankFile ) ) {
				$newFile = @fopen( $blankFile, "w" );
				@fclose( $newFile );
			}
		}
	}

	return $cacheFolder;

}

//Url validation
function abIsValidUrl( $url, $debug = false ) {

	$urlBits = @parse_url( $url );
	if ( $urlBits['scheme'] != "http" && $urlBits['scheme'] != "https" ) {
		echo ( $debug ) ? "<li />Error! URL does not start with http: " . $url : "";

		return false;
	} else if ( strlen( $urlBits['host'] ) < 4 || strpos( $urlBits['host'], "." ) === false ) {
		echo ( $debug ) ? "<li />Error! URL is incorrect: " . $url : "";

		return false;
	}

	return true;
}

//Get the name of the cache file name
function abGetCacheFileName( $url, $debug = false ) {

	$cacheFileName = date('Ymd').md5( $url ) . ".cache";
	echo ( $debug ) ? "<li />Cache file name for URL: " . $url . " is " . $cacheFileName : "";

	return $cacheFileName;

}

//Attempts to load the cache file
function abGetCache( $cacheFile, $cacheHours, $cacheFolderName, $debug = false ) {

	//If the url is called with ab_cc=1 then discard the cache file
	if ( isset( $_GET['ab_cc'] ) && $_GET['ab_cc'] == "1" ) {
		echo ( $debug ) ? "<li />Clear cache invoked!" : "";
		abRemoveCacheFile( $cacheFile );
		unset( $_GET['ab_cc'] );

		return false;
	}

	if ( ! file_exists( $cacheFile ) ) {
		echo ( $debug ) ? "<li />Error! Cache file does not exist! " . $cacheFile : "";

		return false;
	}

	$cache_contents = @file_get_contents( $cacheFile );

	if ( $cache_contents === false ) {
		echo ( $debug ) ? "<li />Error: Cache file is completely empty!" : "";

		return false;
	} else {
		echo ( $debug ) ? "<li />Cache file contents" . $cache_contents : "";

		//Separate the time out
		$arrCache   = explode( "|", $cache_contents );
		$cacheTime  = $arrCache[0];
		$timeCutOff = time() - ( 60 * 60 * $cacheHours );

		//Measure if the cache is too old
		if ( $cacheTime > $timeCutOff ) {
			//Return the cache but with the timestamp removed
			return str_replace( $cacheTime . "|", "", $cache_contents );
		} else {
			//echo "cacheTime ($cacheTime) <= timeCutOff ($timeCutOff)";
			abRemoveCacheFile( $cacheFile, $debug );
			abClearOldCache( $cacheFolderName, $cacheHours, $debug ); //Also remove other old cache files
			return false;
		}
	}

}

//Delete a cache file
function abRemoveCacheFile( $cacheFile, $debug = false ) {
	if ( ! @unlink( $cacheFile ) ) {
		echo ( $debug ) ? "<li />Error: Could not remove cache file: " . $cacheFile : "";

		return false;
	} else {
		echo ( $debug ) ? "<li />Successfully removed the cache file: " . $cacheFile : "";

		return true;
	}
}

//Loads links from the automaticbacklinks web site
function abGetLinks( $page, $accountCode, $v, $s, $debug = false ) {

	//Make the URL
	$url = "http://links.automaticbacklinks.com/links.php";
	$url = $url . "?a=" . $accountCode;
	$url = $url . "&v=" . $v;
	$url = $url . "&s=" . $s;
	$url = $url . "&page=" . urlencode( $page );

	echo ( $debug ) ? "<li />Making call to AB: " . $url : "";

	ini_set( 'default_socket_timeout', 10 );
	if ( intval( get_cfg_var( 'allow_url_fopen' ) ) && function_exists( 'file_get_contents' ) ) {
		echo ( $debug ) ? "<li />Using file_get_contents()" : "";
		$links = @file_get_contents( $url );
	} else if ( intval( get_cfg_var( 'allow_url_fopen' ) ) && function_exists( 'file' ) ) {
		echo ( $debug ) ? "<li />Using file()" : "";
		if ( $content = @file( $url ) ) {
			$links = @join( '', $content );
		}
	} else if ( function_exists( 'curl_init' ) ) {
		echo ( $debug ) ? "<li />Using cURL()" : "";
		$ch = curl_init( $url );
		curl_setopt( $ch, CURLOPT_HEADER, 0 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		$links = curl_exec( $ch );
		curl_close( $ch );
	} else {
		echo ( $debug ) ? "<li />Error: no method available to fetch links!" : "";

		return false;
	}

	return $links;

}

//remove ab_cc etc. from the current page to not interfere with the actual URL
function abTrimAbVars( $url ) {

	$url = str_replace( "?ab_cc=1", "", $url );
	$url = str_replace( "&ab_cc=1", "", $url );
	$url = str_replace( "?ab_debug=483e6d0df8cc18c57327f368c987c436", "", $url );
	$url = str_replace( "&ab_debug=483e6d0df8cc18c57327f368c987c436", "", $url );
	$url = str_replace( "&phpinfo=1", "", $url );

	return $url;

}

//Get page
function abGetPageUrl( $debug = false ) {

	$query    = "";
	$protocol = ( isset( $_SERVER['HTTPS'] ) && strtolower( $_SERVER['HTTPS'] ) != "off" ) ? "https://" : "http://";
	$host     = $_SERVER['HTTP_HOST'];

	if ( $_SERVER["REDIRECT_URL"] && $_SERVER["REDIRECT_URL"] != "/index.php" ) {
		//Redirect
		if ( isset( $_SERVER['REDIRECT_SCRIPT_URI'] ) ) {
			//Use URI - it is complete
			$page = $_SERVER['REDIRECT_SCRIPT_URI'];
		} else {
			//Use file and query
			$file = $_SERVER["REDIRECT_URL"];
			if ( isset( $_SERVER['REDIRECT_QUERY_STRING'] ) ) {
				$query = "?" . $_SERVER['REDIRECT_QUERY_STRING'];
			}
		}
	} else {
		//No redirect
		if ( isset( $_SERVER['REQUEST_URI'] ) ) {
			//Use URI
			if ( substr( $_SERVER['REQUEST_URI'], 0, 4 ) == "http" ) {
				//Request URI has host in it
				$page = $_SERVER['REQUEST_URI'];
			} else {
				//Request uri lacks host
				$page = $protocol . $host . $_SERVER['REQUEST_URI'];
			}
		} else if ( isset( $_SERVER['SCRIPT_URI'] ) ) {
			//Use URI - it is complete
			$page = $_SERVER['SCRIPT_URI'];
		} else {
			$file = $_SERVER['SCRIPT_NAME'];
			if ( isset( $_SERVER['QUERY_STRING'] ) ) {
				$query = "?" . $_SERVER['QUERY_STRING'];
			}
		}
	}
	if ( ! $page ) {
		$page = $protocol . $host . $file . $query;
	}

	$page = abTrimAbVars( $page );

	echo ( $debug ) ? "<li />This page is reported as: " . $page : "";

	return $page;

}

?>