<?php
defined( 'ABSPATH' ) || exit;


/**
 * get plugin settings (public)
 *  
 * @since 1.0.0
 * 
 * @return array
 */
function aupi_get_settings(){
    $opt = get_option(AUPI_SETTINGS_KEY);
    $vals=[];
    $vals['optimize_xml']=!empty($opt['optimize_xml']) && $opt['optimize_xml']=='yes' ;

    return $vals;
}

/**
 * get assets url
 *  
 * @since 1.0.2
 * 
 * @return string
 */
function aupi_get_assets($path=null){
    return AUPI_ASSETS_URL.$path;
}
 


/**
 * get registered post type (public)
 *  
 * @since 1.0.0
 * 
 * @return array
 */
function aupi_get_post_types(){
    $args = array(
        'public'   => true
    );
    $post_types = get_post_types( $args );
    $ret=[];
    if(!empty($post_types)){
        foreach ($post_types as $post_type){
            if($post_type=='attachment'){
                continue;
            }
            $ret[]=$post_type;
        }
    }
    return $ret;
}
 

 

/**
 * get registered feed
 * 
 * @since 1.0.0
 * 
 * @param array $meta_query {
 *     Optional. An array of meta_query.
 * }
 * 
 * @return array
 */
function aupi_get_feeds($meta=[]){
    
    $ret=[];

    $args=[];
    $args['numberposts']=50;
    $args['post_type']='aupi_poscast';
    if(!empty($meta)){
        $args['meta_query']=$meta;
    }
    $feeds = get_posts($args); 

    $dateTimeFormat = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );


    if(!empty($feeds)){
        foreach($feeds as $feed){
            $lr = get_post_meta($feed->ID,'last_run',true);
            if(!empty($lr)){
                $lr =wp_date($dateTimeFormat, $lr);
            }else{
                $lr = __('Waiting for run','aupi');
            }

            $r=[];
            $r['id'] = $feed->ID;
            $r['feed_title'] =  $feed->post_title;
            $r['post_type'] = get_post_meta($feed->ID,'post_type',true);
            $r['force_update_posts'] = get_post_meta($feed->ID,'force_update_posts',true);
            $r['insert_audio_player'] = get_post_meta($feed->ID,'insert_audio_player',true)=='yes' ? 'yes' : 'no';
            $r['post_status'] = get_post_meta($feed->ID,'post_status',true);
            $r['post_author'] = get_post_meta($feed->ID,'post_author',true);
            $r['feed_url'] = get_post_meta($feed->ID,'feed_url',true);
            $r['last_feed_error'] = get_post_meta($feed->ID,'last_feed_error',true);
            $r['recurrence'] = get_post_meta($feed->ID,'recurrence',true);

         
            $r['replace_thumbnail'] =  get_post_meta($feed->ID,'replace_thumbnail',true)=='yes' ? 'yes' : 'no';

            $r['last_run'] = $lr;
            $ret[]=$r;
        }
    
    }
 

    return $ret;
}


/**
 * get feed by id
 * 
 * @since 1.0.2
 * 
 * @param array $meta_query {
 *     Optional. An array of meta_query.
 * }
 * 
 * @return array
 */
function aupi_get_feed_by_id($id=false){
    if(!$id){
        return false;
    }


    $feed = get_post(absint($id)); 
    if(!$feed){
        return false;
    }

    $dateTimeFormat = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );


    $lr = get_post_meta($feed->ID,'last_run',true);
    if(!empty($lr)){
        $lr =wp_date($dateTimeFormat, $lr);
    }else{
        $lr = __('Waiting for run','aupi');
    }

    $r=[];
    $r['id'] = $feed->ID;
    $r['feed_title'] =  $feed->post_title;
    $r['post_type'] = get_post_meta($feed->ID,'post_type',true);
    $r['force_update_posts'] = get_post_meta($feed->ID,'force_update_posts',true);
    $r['insert_audio_player'] =  get_post_meta($feed->ID,'insert_audio_player',true)=='yes' ? 'yes' : 'no';
    $r['post_status'] = get_post_meta($feed->ID,'post_status',true);
    $r['post_author'] = get_post_meta($feed->ID,'post_author',true);
    $r['feed_url'] = get_post_meta($feed->ID,'feed_url',true);
    $r['last_feed_error'] = get_post_meta($feed->ID,'last_feed_error',true);
    $r['recurrence'] = get_post_meta($feed->ID,'recurrence',true);
    
 
    $r['replace_thumbnail'] =  get_post_meta($feed->ID,'replace_thumbnail',true)=='yes' ? 'yes' : 'no';


    $r['last_run'] = $lr;
 

    return $r;
}