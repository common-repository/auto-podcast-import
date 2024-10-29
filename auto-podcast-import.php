<?php
/**
 * Plugin Name:       Auto podcast import
 * Plugin URI:        https://wordpress.org/plugins/auto-podcast-import/
 * Description:       Import your podcast feed, automatically from any supported podcast provider.
 * Version:           1.0.4
 * Requires at least: 6.1.0
 * Requires PHP:      7.4
 * Author:            R2K TEAM
 * Author URI:        https://www.r2k.co.il/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       aupi
 * Domain Path:       /languages
 */
defined( 'ABSPATH' ) || exit;
 
define('AUPI_VER','1.0.4');
define('AUPI_SLUG','aupi');

define('AUPI_MIN_PHP','7.4');
define('AUPI_MIN_WP','6.1.0');
 
 
if(!defined('AUPI_SETTINGS_KEY')){
    define('AUPI_SETTINGS_KEY','aupi_gen_options');
}
 
define('AUPI_URL',plugin_dir_url( __FILE__ ));
define('AUPI_ASSETS_URL',AUPI_URL.'assets/');
if ( ! defined( 'AUPI_FILE' ) ) {
	define( 'AUPI_FILE', __FILE__ );
}
if ( ! defined( 'AUPI_DIR' ) ) {
	define( 'AUPI_DIR', dirname(__FILE__).'/' );
}
 
 
 
//load main pro file
require AUPI_DIR.'functions.php';
require AUPI_DIR.'main.php';
AUPI\AUPIPlugin::init();