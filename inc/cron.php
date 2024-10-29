<?php
namespace AUPI;

defined( 'ABSPATH' ) || exit;

 
/**
 * cron job
 *
 * @since 1.0.0
 */
Cron::init();
class Cron{

    private static $instance = null;

    private $optimize = false;

    public static $allowedIntervals =['hourly','daily','weekly','monthly'];

    public static function init(){
        if ( null == self::$instance ) {
                    self::$instance = new self;
            }
            return self::$instance;
    }







    public function __construct(){
        $vals=\aupi_get_settings(); 
        $this->optimize =!empty($vals['optimize_xml']) && $vals['optimize_xml'];
  
        //create crons
        foreach(self::$allowedIntervals as $interval){
                $cronName=  'aupi_cron_'.$interval; 
                if (! \wp_next_scheduled ($cronName )) {
                    \wp_schedule_event(time()+1, $interval, $cronName);
                }
                \add_action($cronName,[ $this,'doRun_'.$interval] );
         }

      
            
       
        if($this->optimize){

            //inner cron for optimize query
            if (! \wp_next_scheduled ('aupi_fetch_feeds' )) {
                \wp_schedule_event(time()+1, 'hourly', 'aupi_fetch_feeds');
            }
            \add_action('aupi_fetch_feeds',[ $this, 'aupi_fetch_feeds_cb' ]);

        }else{
            \wp_clear_scheduled_hook( 'aupi_fetch_feeds' );
        }

 
   
    }


    //fetch xml data hourly
    public function aupi_fetch_feeds_cb(){
        $feeds = \aupi_get_feeds();
        if(empty($feeds)){
            return ; 
        }
        foreach($feeds as $feed){
            $f = new Feed($feed);
            $f->fetch();
            \sleep(5);
        }   
    }

 


    //run wordpress insertion
    public  function doRun($recurrence){

 
        $meta=[];
        $meta[]=[
            'key'=>'recurrence',
            'value'=>$recurrence
        ];
        $feeds = \aupi_get_feeds($meta);
 
        if(empty($feeds)){
            return ; 
        }
 
        foreach($feeds as $feed){

              
            $f = new Feed($feed);
            $f->doProccess();

            sleep(5);

        
        }

 
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

 