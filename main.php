<?php
namespace AUPI;

defined( 'ABSPATH' ) || exit;


/**
 * Main class
 *
 * @since 1.0.0
 */
class AUPIPlugin{

    //on activate actions
    public static function activate(){

        //sanitisation
        $data=[];
        $data['optimize_xml'] ='yes';


        \update_option(AUPI_SETTINGS_KEY,$data,false);

        
    }
    //on deactivate actions
    public static function deactivate(){
 
    }
    //on uninstall actions
    public static function uninstall(){

        
        \delete_option(AUPI_SETTINGS_KEY);
    }


    public static function init(){
 
        //some required validations
        if ( ! version_compare( PHP_VERSION, AUPI_MIN_PHP, '>=' ) ) {
            \add_action( 'admin_notices',function(){
                self::fail('php');
            });
            
        } elseif ( ! version_compare( get_bloginfo( 'version' ), AUPI_MIN_WP, '>=' ) ) {
            \add_action( 'admin_notices',function(){
                self::fail('wp');
            });
        } else {


        

            // Activation and deactivation hook.
            register_activation_hook(AUPI_FILE, 'AUPI\AUPIPlugin::activate');
            register_deactivation_hook( AUPI_FILE, 'AUPI\AUPIPlugin::deactivate');
            register_uninstall_hook( AUPI_FILE,'AUPI\AUPIPlugin::uninstall');

  
            //load the plugin
            \add_action( 'plugins_loaded', function(){
                self::loaded(); 
            } );

     

        }
    }
 

    //error messages
    public static function fail($error) {
        if($error=='php'){
            $message = sprintf( esc_html__( 'Auto podcast import requires minimum PHP version %s.', 'aupi' ), AUPI_MIN_PHP );
        }elseif($error=='wp'){
            $message = sprintf( esc_html__( 'Auto podcast import requires minimum WordPress version %s+.', 'aupi' ), AUPI_MIN_WP );
        }
        $html_message = \sprintf( '<div class="error">%s</div>', wpautop( $message ) );
        echo wp_kses_post($html_message);
    }


    /**
    * Plugin init
    *
    * @since 1.0.0
    * @param none
    * @return null
    */
    public static function loaded() {

        //* Localization Code */
        \load_plugin_textdomain(
            'aupi',
            false,
            \dirname(plugin_basename( __FILE__ ))  . '/languages'
        );

 

        //register feed post type
        require AUPI_DIR.'inc/post_type.php';

        //feed 
        require AUPI_DIR.'inc/feed.php';
         

        //cron 
        require AUPI_DIR.'inc/cron.php';

        //filters 
        require AUPI_DIR.'inc/filters.php';
         



        //admin
        if(\is_admin()){

            //admin menu
            require AUPI_DIR.'inc/admin_menu.php';

            //admin scripts
            \add_action('admin_enqueue_scripts', function(){
 
                \wp_register_style( AUPI_SLUG.'-admin',aupi_get_assets().'css/admin.css',AUPI_VER);
                \wp_enqueue_style(AUPI_SLUG.'-admin');
   
                \wp_enqueue_script(AUPI_SLUG.'-js',aupi_get_assets().'js/admin.js',[],AUPI_VER,true);
            });
        }
               
    }  


 
}

 