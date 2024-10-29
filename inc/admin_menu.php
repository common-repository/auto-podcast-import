<?php
namespace AUPI;

defined( 'ABSPATH' ) || exit;

 

/**
 * admin menu class
 *
 * @since 1.0.0
 */
AdminMenu::init();
class AdminMenu{

    private static $instance = null;

    public static function init(){
        if ( null == self::$instance ) {
                    self::$instance = new self;
            }
            return self::$instance;
    }




    public function __construct(){


          //admin option
          add_action('admin_menu', function(){
                add_submenu_page( 
                    'tools.php',
                    __( 'Auto podcast import', 'aupi' ),
                    __( 'Auto podcast import', 'aupi' ),
                    'manage_options',
                    'aupi_dashboard',
                
                    '\AUPI\AdminMenu::admin_option',
                    100
                );

            }); 



        
            //save feed form ajax
            \add_action ( 'wp_ajax_aupi_update_feed',function(){

              

                //security
                if (  ! isset( $_POST['aupi_nonce'] ) || !wp_verify_nonce( sanitize_text_field( wp_unslash ( $_POST['aupi_nonce'] ) ) , 'aupi_nonce' ) ) {
                    echo \wp_json_encode(['error'=>true,'message'=>esc_html__('Invailed request','aupi'),'html'=>'']);
                    die();
                } 

                //required fields
                $reqFields=['feed_title','feed_url','post_type','post_status','post_author','force_update_posts'];
                $isAllIn=true;
                foreach($reqFields as $reqField){
                    if(  empty($_POST[$reqField]) ){
                        $isAllIn=false;
                    }
                }
                if(!$isAllIn){
                    echo \wp_json_encode(['error'=>true,'message'=>esc_html__('Invailed request','aupi'),'html'=>'']);
                    die();
                }


                //get allowed cron intervals
                $allowedRecurrence=Cron::$allowedIntervals;


                //sanitisation
                $data=[];
                $data['insert_audio_player'] = !empty($_POST['force_update_posts']) && $_POST['force_update_posts'] =='yes' ? 'yes': 'no';
                $data['replace_thumbnail'] = !empty($_POST['replace_thumbnail']) && $_POST['replace_thumbnail'] =='yes' ? 'yes': 'no';
                $data['force_update_posts'] = !empty($_POST['force_update_posts']) && $_POST['force_update_posts'] =='yes' ? 'yes': 'no';
                 
                $data['feed_title'] = \wp_kses_post($_POST['feed_title']);
                $data['feed_url'] = \wp_kses_post($_POST['feed_url']);
                $data['post_type'] = \wp_kses_post($_POST['post_type']);
                $data['post_status'] = \wp_kses_post($_POST['post_status']);
                $data['recurrence'] = \wp_kses_post($_POST['recurrence']);
                $data['post_author'] = \absint($_POST['post_author']);
                $data['id'] =!empty($_POST['id']) ?  \absint($_POST['id']) : false;
               

                //if this is update ajax, verify post is exist
                if($data['id'] && !get_post($data['id'])){
                    echo \wp_json_encode(['error'=>true,'message'=>esc_html__('Invailed feed post','aupi'),'html'=>'']);
                    die();  
                }
                
                //check url scheme
                if(strpos($data['feed_url'],'https://')===false){
                    echo \wp_json_encode(['error'=>true,'message'=>esc_html__('Invailed Url','aupi'),'html'=>'']);
                    die();   
                }

                //check if choosed post type still registed
                $post_types=\aupi_get_post_types();
                if(!in_array($data['post_type'],$post_types)){
                    echo \wp_json_encode(['error'=>true,'message'=>esc_html__('Unknown post type','aupi'),'html'=>'']);
                    die();   
                }

                //check if choosed recurrence allowed
                if(!in_array($data['recurrence'],$allowedRecurrence)){
                    echo \wp_json_encode(['error'=>true,'message'=>esc_html__('Unknown recurrence slug','aupi'),'html'=>'']);
                    die();   
                }

                // check if user still exist
                $author_obj = \get_user_by('id',  $data['post_author']);
                if(!$author_obj){
                    echo \wp_json_encode(['error'=>true,'message'=>esc_html__('Unknown author','aupi'),'html'=>'']);
                    die();   
                }



                //in cacse of update, then not create new post
                if($data['id']){
                    $pnum= $data['id'];
                }else{
                    $args=[];
                    $args['post_status']='publish';
                    $args['post_type']='aupi_poscast';
                    $args['post_title']=$data['feed_title']; 
                    $pnum = \wp_insert_post($args);
                }
                  
                if($pnum>0){

 

                    foreach($data as $k=>$v){
                        update_post_meta($pnum,$k,$v);
                    }


                             
                    echo \wp_json_encode(['error'=>false,'message'=>esc_html__('Saved.','aupi'),'url'=>admin_url('tools.php?page=aupi_dashboard&tab=edit&id='.(\absint($pnum)))]);
                    die();

                }else{
                    echo \wp_json_encode(['error'=>true,'message'=>esc_html__('Invailed request','aupi'),'html'=>'']);
                    die();
                }
  
                


            });
        
            //save feed form ajax
            \add_action ( 'wp_ajax_aupi_update_settings',function(){

     
                //security
                if (  ! isset( $_POST['aupi_nonce'] ) || !wp_verify_nonce( sanitize_text_field( wp_unslash ( $_POST['aupi_nonce'] ) ) , 'aupi_nonce' ) ) {
                    echo \wp_json_encode(['error'=>true,'message'=>esc_html__('Invailed request','aupi'),'html'=>'']);
                    die();
                } 

            
 

                //sanitisation
                $data=[];
                $data['optimize_xml'] =!empty($_POST['optimize_xml']) && $_POST['optimize_xml']=='yes' ? 'yes': 'no';
   

                \update_option(AUPI_SETTINGS_KEY,$data,false);

                             
                    echo \wp_json_encode(['error'=>false,'message'=>esc_html__('Saved.','aupi')]);
                    die();

            


            });

        
            //delete feed ajax
            \add_action ( 'wp_ajax_aupi_delete_feed',function(){

      
                //securti
                if (  ! isset( $_POST['aupi_nonce'] ) || !wp_verify_nonce( sanitize_text_field( wp_unslash ( $_POST['aupi_nonce'] ) ) , 'aupi_nonce' ) ) {
                    echo \wp_json_encode(['error'=>true,'message'=>esc_html__('Invailed request','aupi'),'html'=>'']);
                    die();
                } 

             
              
                if( empty($_POST['id']) ){
                    echo \wp_json_encode(['error'=>true,'message'=>esc_html__('Invailed request','aupi'),'html'=>'']);
                    die();
                }

                $id = absint($_POST['id']);

                //verify post 
                if(!get_post($id)){
                    echo \wp_json_encode(['error'=>true,'message'=>esc_html__('Invailed feed post','aupi'),'html'=>'']);
                    die();  
                }
 
              
               
 
                wp_delete_post($id,true);


                            
                echo \wp_json_encode(['error'=>false,'message'=>esc_html__('Deleted.','aupi'),'url'=>admin_url('tools.php?page=aupi_dashboard')]);
                die();

            
  
                


            });

        
            //run feed ajax
            \add_action ( 'wp_ajax_aupi_run_feed',function(){

      
                //securti
                if (  ! isset( $_POST['aupi_nonce'] ) || !wp_verify_nonce( sanitize_text_field( wp_unslash ( $_POST['aupi_nonce'] ) ) , 'aupi_nonce' ) ) {
                    echo \wp_json_encode(['error'=>true,'message'=>esc_html__('Invailed request','aupi'),'html'=>'']);
                    die();
                } 

             
              
                if( empty($_POST['id']) ){
                    echo \wp_json_encode(['error'=>true,'message'=>esc_html__('Invailed request','aupi'),'html'=>'']);
                    die();
                }

                $id = \absint($_POST['id']);


                $feed = \aupi_get_feed_by_id($id);
                //verify post 
                if(!$feed){
                    echo \wp_json_encode(['error'=>true,'message'=>esc_html__('Invailed feed post','aupi'),'html'=>'']);
                    die();  
                }
 
              

                $f = new Feed($feed);
               $pr = $f->doProccess();
 
                //wp_delete_post($id,true);


                            
                echo \wp_json_encode(['error'=>false,'message'=>esc_html__('Imported.','aupi'),'url'=>admin_url('tools.php?page=aupi_dashboard')]);
                die();

            
  
                


            });


    }


    
    //admin html menu
    public static function admin_option(){
    

        //check user role
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }



        $page_type=!empty($_GET['page']) ? wp_kses_post($_GET['page']) :false;
        if(!$page_type || $page_type!='aupi_dashboard'){
                return false;
        }
        $screen = \get_current_screen(); 
        if ( $screen->parent_file != 'tools.php' )
                return;


        $baseUrl = \admin_url('tools.php?page=aupi_dashboard');
                



        $currentTab = !empty($_GET['tab']) ? \esc_html(sanitize_text_field($_GET['tab'])) : 'list';

       

        $id = !empty($_GET['id']) ? \absint($_GET['id']) : false;

 
        echo '<div class="aupi_container">';
            echo '<div class="aupi_tabs">';
                echo '<a class="'.($currentTab == 'list' ? 'current' : '').'" href="'.esc_attr($baseUrl).'&tab=list">'.esc_html__( 'Feeds list', 'aupi' ).'</a>';
                echo '<a class="'.($currentTab == 'add' ? 'current' : '').'" href="'.esc_attr($baseUrl).'&tab=add">'.esc_html__( 'Add feed', 'aupi' ).'</a>';
                echo '<a class="'.($currentTab == 'settings' ? 'current' : '').'" href="'.esc_attr($baseUrl).'&tab=settings">'.esc_html__( 'Settings', 'aupi' ).'</a>';

            echo '</div>';



            echo '<div id="aupi_list" class="aupi_tab_content">';


 


                if($id && !get_post($id)){
                    echo '<h1>'.esc_html__( 'Unkown feed post', 'aupi' ).'</h1>';
                }else{
                    switch($currentTab){
                        case 'add':
                        case 'edit':
                            self::addTab($id);
                        break;
                          case 'list':
                            self::list();
                        break;
                          case 'settings':
                            self::settings();
                        break;
                    }
                }
           
                


            echo '</div>';


        echo '</div>';

    }
  

    //feed list
    public static function list(){

    
        
        $feeds = \aupi_get_feeds();
        if(empty($feeds)){

            echo '<h2>'.esc_html__( 'No feeds found, please add some to start', 'aupi' ).'.</h2>';
        }else
        if(!empty($feeds)){




            echo '<table class="wp-list-table widefat fixed striped table-view-list pages">';
                echo '<thead>';
                    echo '<tr>';
                        echo '<td>'.esc_html__( 'ID', 'aupi' ).'</td>';
                        echo '<td>'.esc_html__( 'Feed title', 'aupi' ).'</td>';
                        echo '<td>'.esc_html__( 'Post type', 'aupi' ).'</td>';
                        echo '<td>'.esc_html__( 'Recurrence', 'aupi' ).'</td>';
                        echo '<td>'.esc_html__( 'Last run', 'aupi' ).'</td>';
                        echo '<td>'.esc_html__( 'Last error', 'aupi' ).'</td>';
                        echo '<td>'.esc_html__( 'Run now', 'aupi' ).'</td>';
                        echo '<td>'.esc_html__( 'Edit', 'aupi' ).'</td>';
                        echo '<td>'.esc_html__( 'Delete', 'aupi' ).'</td>';
                     echo '</tr>';
                echo '</thead>';
                echo '<tbody>';

                $nonce = wp_create_nonce('aupi_nonce');

                foreach($feeds as $feed){
                    $id = \absint($feed['id']);
                    echo '<tr>';
                        echo '<td>'.esc_html($id).'</td>';
                        echo '<td>'.esc_html($feed['feed_title']).'</td>';
                        echo '<td>'.esc_html($feed['post_type']).'</td>';
                        echo '<td>'.esc_html($feed['recurrence']).'</td>';
                        echo '<td>'.esc_html($feed['last_run'] ? $feed['last_run'] : 'NA').'</td>';
                        echo '<td>'.esc_html($feed['last_feed_error']).'</td>';
                        echo '<td><a  data-nonce="'.$nonce.'"  data-id="'.esc_attr($id).'" class="aupi_run_feed button button-primary" href="#">'.esc_html__( 'Run now', 'aupi' ).'</a><img class="aupi_loading" src="'.esc_attr(aupi_get_assets('images/loader.gif')).'" alt="" /></td>';
                        echo '<td><a class="button button-primary" href="'.admin_url('tools.php?page=aupi_dashboard&tab=edit&id='.$id).'">'.esc_html__( 'Edit', 'aupi' ).'</a></td>';
                        echo '<td><a data-id="'.esc_attr($id).'" data-confirm="'.esc_attr__( 'Are you sure to delete?', 'aupi' ).'" class="button aupi_delete_feed" data-nonce="'.$nonce.'" href="#">'.esc_html__( 'Delete', 'aupi' ).'</a><img class="aupi_loading" src="'.esc_attr(aupi_get_assets('images/loader.gif')).'" alt="" /></td>';
                    echo '</tr>';
                }
                     
                echo '</tbody>';

            echo '</table>';

        }

    }



    //add or edit feed 
    public static function addTab($id = false){

        $vals=[];
        $vals['feed_title']='';
        $vals['feed_url']='';
        $vals['post_type']='';
        $vals['post_status']='';
        $vals['recurrence']='';
        $vals['post_author']='';
        $vals['force_update_posts']='no';
        $vals['insert_audio_player']='no';
        $vals['replace_thumbnail']='no';


        if($id){
            $vals['feed_title']=\get_post_meta($id,'feed_title',true);
            $vals['feed_url']=\get_post_meta($id,'feed_url',true);
            $vals['post_type']=\get_post_meta($id,'post_type',true);
            $vals['post_status']=\get_post_meta($id,'post_status',true);
            $vals['recurrence']=\get_post_meta($id,'recurrence',true);
            $vals['post_author']=\get_post_meta($id,'post_author',true);

            $vals['force_update_posts']=\get_post_meta($id,'force_update_posts',true);
            $vals['insert_audio_player']= \get_post_meta($id,'insert_audio_player',true)=='yes' ? 'yes' : 'no';
            $vals['replace_thumbnail']=  \get_post_meta($id,'replace_thumbnail',true)=='yes' ? 'yes' : 'no';
        }

    
    
        $ret=\aupi_get_post_types();

        if(!$id){
            echo '<h1>'.esc_html__( 'Add new feed', 'aupi' ).'</h1>';
        }else{
            echo '<h1>'.esc_html__( 'Edit feed', 'aupi' ).'</h1>';
        }

        echo '<form class="aupi_add">';
                wp_nonce_field('aupi_nonce','aupi_nonce');
            
                echo '<input   type="hidden" name="action" value="aupi_update_feed" />';
                if($id){
                    echo '<input   type="hidden" name="id" value="'.(\absint($id)).'" />';
                }
                 

                echo '<table class="form-table" role="presentation">';
                    echo '<tbody>';
                        echo '<tr>';
                            echo '<th>';
                                echo '<label for="feed_title">'.esc_html__( 'Feed title', 'aupi' ).':</label>';
                            echo '</th>';
                            echo '<td>';
                                echo '<input required type="text" id="feed_title" name="feed_title" value="'.esc_attr($vals['feed_title']).'">';
                            echo '</td>';
                        echo '</tr>';
                        echo '<tr>';
                            echo '<th>';
                                echo '<label for="feed_url">'.esc_html__( 'Feed url', 'aupi' ).':</label>';
                            echo '</th>';
                            echo '<td>';
                                echo '<input required type="text" id="feed_url" name="feed_url" value="'.esc_attr($vals['feed_url']).'">';
                            echo '</td>';
                        echo '</tr>';
                        echo '<tr>';
                            echo '<th>';
                                echo '<label for="post_type">'.esc_html__( 'Post type', 'aupi' ).':</label>';
                            echo '</th>';
                            echo '<td>';
                                echo '<select id="post_type" name="post_type">';
                                    foreach($ret as $r){
                                        echo '<option '.($r==$vals['post_type'] ? 'selected' : '').' value="'.esc_attr($r).'">'.esc_html($r).'</option>';
                                    } 
                                echo '</select>';
                            echo '</td>';
                        echo '</tr>';
                        echo '<tr>';
                            echo '<th>';
                                echo '<label for="post_status">'.esc_html__( 'Post status', 'aupi' ).':</label>';
                            echo '</th>';
                            echo '<td>';
                                echo '<select id="post_status" name="post_status">';
                                    echo '<option '.('publish'==$vals['post_status'] ? 'selected' : '').' value="publish">'.esc_html__( 'Publish', 'aupi' ).'</option>';
                                    echo '<option '.('draft'==$vals['post_status'] ? 'selected' : '').' value="draft">'.esc_html__( 'Draft', 'aupi' ).'</option>';
                                echo '</select>';
                            echo '</td>';
                        echo '</tr>';
                        echo '<tr>';
                            echo '<th>';
                                echo '<label for="recurrence">'.esc_html__( 'Recurrence', 'aupi' ).':</label>';
                            echo '</th>';
                            echo '<td>';
                                echo '<select id="recurrence" name="recurrence">';
                                    echo '<option '.('hourly'==$vals['recurrence'] ? 'selected' : '').' value="hourly">'.esc_html__( 'Hourly', 'aupi' ).'</option>';
                                    echo '<option '.('daily'==$vals['recurrence'] ? 'selected' : '').' value="daily">'.esc_html__( 'Daily', 'aupi' ).'</option>';
                                    echo '<option '.('weekly'==$vals['recurrence'] ? 'selected' : '').' value="weekly">'.esc_html__( 'Weekly', 'aupi' ).'</option>';
                                    echo '<option '.('monthly'==$vals['recurrence'] ? 'selected' : '').' value="monthly">'.esc_html__( 'Monthly', 'aupi' ).'</option>';
                                echo '</select>';
                            echo '</td>';
                        echo '</tr>';


                        $blogusers = get_users( array( 'fields' => array( 'display_name' ,'ID') ,  'role__in' => array( 'author', 'subscriber','administrator','editor' ,'contributor') ) );
                        if(!empty($blogusers)){
                            echo '<tr>';
                                echo '<th>';
                                    echo '<label for="post_author">'.esc_html__( 'Post author', 'aupi' ).':</label>';
                                echo '</th>';
                                echo '<td>';
                                echo '<select id="post_author" name="post_author">';
                                foreach($blogusers as $bloguser){
                                    echo '<option '.($bloguser->ID==$vals['post_author'] ? 'selected' : '').' value="'.esc_attr($bloguser->ID).'">'.esc_html($bloguser->display_name) .'</option>';
                                }
                                echo '</select>';
                                echo '</td>';
                            echo '</tr>';
                        }
 



                        echo '<tr>';
                            echo '<th>';
                                echo '<label for="force_update_posts">'.esc_html__( 'Force update existing posts', 'aupi' ).':</label>';
                            echo '</th>';
                            echo '<td>';
                                echo '<select id="force_update_posts" name="force_update_posts">';
                                    echo '<option '.('yes'==$vals['force_update_posts'] ? 'selected' : '').' value="yes">'.esc_html__( 'Yes', 'aupi' ).'</option>';
                                    echo '<option '.('no'==$vals['force_update_posts'] ? 'selected' : '').' value="no">'.esc_html__( 'No', 'aupi' ).'</option>';
                                echo '</select>';
                            echo '</td>';
                        echo '</tr>';


                        echo '<tr>';
                            echo '<th>';
                                echo '<label for="insert_audio_player">'.esc_html__( 'Insert audio player in post content', 'aupi' ).':</label>';
                            echo '</th>';
                            echo '<td>';
                                echo '<input '.( $vals['insert_audio_player']=='yes' ? 'checked' : '') .' type="checkbox" id="insert_audio_player" name="insert_audio_player" value="yes">';
                            echo '</td>';
                        echo '</tr>';


                        echo '<tr>';
                            echo '<th>';
                                echo '<label for="replace_thumbnail">'.esc_html__( 'Replace default wordpress post thumbnail', 'aupi' ).':</label>';
                            echo '</th>';
                            echo '<td>';
                                echo '<input '.( $vals['replace_thumbnail']=='yes' ? 'checked' : '') .' type="checkbox" id="replace_thumbnail" name="replace_thumbnail" value="yes">';
                            echo '</td>';
                        echo '</tr>';


                    echo '</tbody>';
 
                echo '</table>';


           
             

                echo '<div data-error="'.esc_attr__( 'Error, please try later.', 'aupi' ).'"  data-saved="'.esc_attr__( 'Saved.', 'aupi' ).'" data-ajaxing="'.esc_attr__( 'Saving settings, please wait!', 'aupi' ).'" class="aupi_ajaxing"></div>';


                echo '<button class="button button-primary button-large">'.esc_html__( 'Save', 'aupi' ).'</button>';

        echo '</form>';


    }

    //add or edit feed 
    public static function settings(){

         
        $vals=aupi_get_settings();
 

        echo '<h1>'.esc_html__( 'Settings', 'aupi' ).'</h1>';

        echo '<form class="aupi_settings">';
                wp_nonce_field('aupi_nonce','aupi_nonce');
            
                echo '<input   type="hidden" name="action" value="aupi_update_settings" />';
                

                echo '<table class="form-table" role="presentation">';
                    echo '<tbody>';
                        echo '<tr>';
                            echo '<th>';
                                echo '<label for="optimize_xml">'.esc_html__( 'Optimze XML render', 'aupi' ).':</label>';
                                
                            echo '</th>';
                            echo '<td>';
                                echo '<input '.( $vals['optimize_xml'] ? 'checked' : '') .' type="checkbox" id="optimize_xml" name="optimize_xml" value="yes">';
                                echo '<p class="description">'.esc_html__( 'By default, load remote XML and update WordPress posts done in the same action, this option will separate them', 'aupi' ).'.</p>';
                            echo '</td>';
                        echo '</tr>';
                         


                    echo '</tbody>';
 
                echo '</table>';


           
             

                echo '<div data-error="'.esc_attr__( 'Error, please try later.', 'aupi' ).'"  data-saved="'.esc_attr__( 'Saved.', 'aupi' ).'" data-ajaxing="'.esc_attr__( 'Saving settings, please wait!', 'aupi' ).'" class="aupi_ajaxing"></div>';


                echo '<button class="button button-primary button-large">'.esc_html__( 'Save', 'aupi' ).'</button>';

        echo '</form>';


    }





}

 