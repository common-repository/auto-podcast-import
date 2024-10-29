<?php
namespace AUPI;

defined( 'ABSPATH' ) || exit;

 
 
 
class Feed{

    private $id =false;
    private $feed =false;
    private $optimize = false;

    
    public function __construct($feed){

        $vals=\aupi_get_settings(); 
        $this->optimize =!empty($vals['optimize_xml']) && $vals['optimize_xml'];
        $this->feed =$feed;
        $this->id =$feed['id'];
 
    }


    public function fetch(){
        //parse xml
        $xml = $this->parseXml($this->feed['feed_url']);
        \update_post_meta($this->feed['id'],'last_run',time());
        if(!$xml || empty($xml)){
            \update_post_meta($this->feed['id'],'last_feed_error','cannot_parse_remote_xml');
            return false;
        }

        \update_post_meta($this->feed['id'],'last_feed_error','xml_parsed');

        \update_post_meta($this->feed['id'],'last_feed_xml_data',$xml);

    }


    //render remote xml
    public  function parseXml($url){
        $items=[];
        $response = \wp_remote_get( $url );
        if(!empty($response)){
            $body     = \wp_remote_retrieve_body($response);
            if(!empty($body)){
                $xml  = \simplexml_load_string($body);
                $xml = (array)$xml; 

                 
                    

                 
                if( !empty( $xml['channel'] ) ) {

                    //fetch additional data
                    $moreData=[];
                    $moreData['image']=[];
                    foreach($xml['channel'] as $k=>$item ){

                        
 


                        switch($k){
                            case 'image':

                           
                           
                                if(!empty($item->url)){
                                    $moreData['image']['url'] = \wp_kses_post( $item->url) ;
                                    $moreData['image']['title'] = \wp_kses_post($item->title) ;
                                 }
                                 
                            break;
                        }
                    }
                
 
                    foreach($xml['channel'] as $k=>$item ){

                      


                        if($k=='item'){
                            $r=[];
                            $r['title']  = \wp_kses_post((string)$item->title);
                            $r['description']  = \wp_kses_post((string)$item->description);
                            $r['content']  = \wp_kses_post((string)$item->content);
                            $r['summary']  = \wp_kses_post((string)$item->summary);
                            $r['subtitle']  = \wp_kses_post((string)$item->subtitle);
                            $r['guid']  = \wp_kses_post((string)$item->guid);
                            $r['link']  = (string)$item->link;
                            //image data
                            $r['image']  = !empty($moreData['image']) ? $moreData['image'] : [];
                            
                            $r['pubDate']  = (string)$item->pubDate;
                            $url = (array)$item->enclosure ;
                            $r['url']  = !empty($url['@attributes']['url']) ? $url['@attributes']['url'] : '';
                            $items[]=$r;
                        }
                    } 
                }
            }
        }
        return $items;
    }



    //run wordpress insertion
    public  function doProccess(){

 

    
        //force_update_posts
        if($this->optimize){
            $xml =  \get_post_meta($this->feed['id'],'last_feed_xml_data',true);
        }
        
 
        if(empty($xml)){

            
            $xml = $this->parseXml($this->feed['feed_url']);

            \update_post_meta($this->feed['id'],'last_run',time());
            if(empty($xml)){
                \update_post_meta($this->feed['id'],'last_feed_error','cannot_parse_remote_xml');
                return false;
            }

            
            \update_post_meta($this->feed['id'],'last_feed_error','xml_parsed');

            \update_post_meta($this->feed['id'],'last_feed_xml_data',$xml);
        }
    
 
        if(empty($xml)){
            return false;
        }
 


 
        foreach($xml as $x){

            $guid= !empty($x['guid']) ? wp_kses_post($x['guid']) : false;
            if(!$guid){
                \update_post_meta($this->feed['id'],'last_feed_error','missing_guid');
                continue;
            }

            
            $args=[];
            $args['fields'] ='ids';
            $args['post_status'] ='any';
            $args['numberposts'] = 1;
            $args['post_type'] = $this->feed['post_type'];
            $args['meta_query'] =[
                [
                    'key'=>'aupi_guid',
                    'value'=>$guid
                ]
            ];

            $post = \get_posts($args);

            
    

            $metaToSave= [];
            $metaToSave['aupi_feed_id'] =$this->feed['id']; 
            $metaToSave['aupi_summary'] = !empty($x['summary']) ? wp_kses_post($x['summary']) : ''; 
            $metaToSave['aupi_subtitle'] = !empty($x['subtitle']) ? wp_kses_post($x['subtitle']) : ''; 
            $metaToSave['aupi_guid'] = $guid; 
            $metaToSave['aupi_subtitle'] = !empty($x['subtitle']) ? wp_kses_post($x['subtitle']) : ''; 
            $metaToSave['aupi_link'] = !empty($x['link']) ? wp_kses_post($x['link']) : ''; 
            $metaToSave['aupi_audio'] = !empty($x['url']) ? wp_kses_post($x['url']) : ''; 
            $metaToSave['aupi_date'] = !empty($x['pubDate']) ? wp_kses_post($x['pubDate']) : ''; 
             
             
            $metaToSave['aupi_image_url'] ='';
            $metaToSave['aupi_image_title'] ='';

            //insert_audio_player
            $postContent= !empty($x['content']) ? wp_kses_post($x['content']) : ''; 
            if(empty($postContent) && !empty($x['description'])){
                $postContent=\wp_kses_post($x['description']); 
            }
            if($this->feed['insert_audio_player']=='yes'){
                $postContent= '[audio src="'.$metaToSave['aupi_audio'].'"][/audio]'.$postContent;
            }

          
            if($this->feed['replace_thumbnail']=='yes'){
                $metaToSave['aupi_image_url'] = !empty($x['image']) && $x['image']['url'] ? wp_kses_post($x['image']['url']) : ''; 
                $metaToSave['aupi_image_title'] = !empty($x['image']) && $x['image']['title'] ? wp_kses_post($x['image']['title']) : ''; 
            } 
           
 
            $updateMeta=true;

            if(empty($post)){
                $args=[];
                $args['post_author']= $this->feed['post_author'];
                $args['post_status']= $this->feed['post_status'];
                $args['post_type']= $this->feed['post_type'];
                $args['post_title']= !empty($x['title']) ? strip_tags($x['title']) : ''; 
                $args['post_content']=$postContent;


            
                $pnum = \wp_insert_post($args);
                
                
            }else{

                $pnum = $post[0];

            
                if($this->feed['force_update_posts']=='yes'){

                    $args=[];
                    $args['ID']= $pnum;
                    $args['post_author']= $this->feed['post_author'];
                    $args['post_status']= $this->feed['post_status'];
                    $args['post_type']= $this->feed['post_type'];
                    $args['post_title']= !empty($x['title']) ? wp_kses_post($x['title']) : ''; 
                    $args['post_content']=$postContent;
                    if(empty($args['post_content']) && !empty($x['description'])){
                        $args['post_content']=wp_kses_post($x['description']); 
                    }
                    \wp_update_post($args);

                }else{
                    $updateMeta=false;
                }
            

            }
        

            if($pnum && $updateMeta){

                foreach($metaToSave as $k=>$v){
                    \update_post_meta($pnum,$k,$v);
                }
          

            }

            
            
            
        }


        \update_post_meta($this->feed['id'],'last_run',time());



        return true;
            
 
    }



    //crons types
    public  function doRun_hourly(){
        $this->doRun('hourly');
    }
    public  function doRun_daily(){
        $this->doRun('daily');
    }
    public  function doRun_weekly(){
        $this->doRun('weekly');
    }
    public  function doRun_monthly(){
        $this->doRun('monthly');
    }

 
}

 