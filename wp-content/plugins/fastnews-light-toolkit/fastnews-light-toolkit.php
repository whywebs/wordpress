<?php
/*
Plugin Name: Fastnews Light Toolkit
Description: A specific plugin use in Fastnews Light Theme, included some custom widgets and shortcodes.
Version: 1.0.0
Author: Kopa Theme
Author URI: http://kopatheme.com
License: GNU General Public License v3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Revant Toolkit plugin, Copyright 2015 Kopatheme.com
Revant Toolkit is distributed under the terms of the GNU GPL

Requires at least: 4.1
Tested up to: 4.2.2
Text Domain: fastnews-light-toolkit
Domain Path: /languages/
*/

define('FLT_PATH', plugin_dir_path(__FILE__));
add_action('plugins_loaded', array('Fastnews_Light_Toolkit', 'plugins_loaded'));
add_action('after_setup_theme', array('Fastnews_Light_Toolkit', 'after_setup_theme'), 20 );

class Fastnews_Light_Toolkit {

	function __construct(){
		require FLT_PATH . 'inc/hook.php';
		require FLT_PATH . 'inc/widget.php';
	}

	public static function plugins_loaded(){
		load_plugin_textdomain('fastnews-light-toolkit', false, FLT_PATH . '/languages/');
	}

	public static function after_setup_theme(){
		if (!defined('KOPA_THEME_NAME') || !class_exists('Kopa_Framework'))
			return; 		
		else	
			new Fastnews_Light_Toolkit();
	}
}