<?php
namespace AUPI;

defined( 'ABSPATH' ) || exit;

 
/**
 * post type
 *
 * @since 1.0.0
 */
PostTypes::init();
class PostTypes{

    private static $instance = null;

    public static function init(){
        if ( null == self::$instance ) {
                    self::$instance = new self;
            }
            return self::$instance;
    }




    public function __construct(){



  

        $labels = array(
            'name'                  => _x( 'Podcasts', 'Post type general name', 'aupi' ),
            'singular_name'         => _x( 'Podcast', 'Post type singular name', 'aupi' ),
            'menu_name'             => _x( 'Podcasts', 'Admin Menu text', 'aupi' ),
            'name_admin_bar'        => _x( 'Podcast', 'Add New on Toolbar', 'aupi' ),
            'add_new'               => __( 'Add New', 'aupi' ),
            'add_new_item'          => __( 'Add New Podcast', 'aupi' ),
            'new_item'              => __( 'New Podcast', 'aupi' ),
            'edit_item'             => __( 'Edit Podcast', 'aupi' ),
            'view_item'             => __( 'View Podcast', 'aupi' ),
            'all_items'             => __( 'All Podcasts', 'aupi' ),
            'search_items'          => __( 'Search Podcast', 'aupi' ),
            'parent_item_colon'     => __( 'Parent Podcast:', 'aupi' ),
            'not_found'             => __( 'No Podcasts found.', 'aupi' ),
            'not_found_in_trash'    => __( 'No Podcasts found in Trash.', 'aupi' ),
          );
 

        $args = array(
            'labels'             => $labels,
            'description'        => '',
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => false,
            'show_in_menu'       => false,
            'query_var'          => false,
            'rewrite'            => false,
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'exclude_from_search'       => true,
            'show_in_rest'       => false,
            'menu_position'      => 100,
            'taxonomies'      => [],
            'supports'           => array( 'title')
        );

        register_post_type( 'aupi_poscast' , $args );




    }






}

 