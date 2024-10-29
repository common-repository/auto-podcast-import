
jQuery(document).on('submit','.aupi_add',function(e){
    e.preventDefault();
    var f=jQuery(this);
    f.find('.aupi_ajaxing').html(f.find('.aupi_ajaxing').data('ajaxing'));
    jQuery.ajax({
         type : 'POST' ,
         url : ajaxurl   ,
                 
         dataType : 'json' ,
         data: f.serialize(),
             
         success : function(r, status, jqFObj) { 
             f.find('.ric_err').html(r.message);
            
             f.find('.aupi_ajaxing').html(r.message);

             if(!r.error){
                window.location =r.url;
             }
             
             setTimeout(function(){
                f.find('.aupi_ajaxing').html('');
             },6000);
                   
         } ,
         error : function(param1, param2) {

            f.find('.aupi_ajaxing').html(f.find('.aupi_ajaxing').data('error'));
           } ,
         timeout : 30000
     });
     
 });

jQuery(document).on('submit','.aupi_settings',function(e){
    e.preventDefault();
    var f=jQuery(this);
    f.find('.aupi_ajaxing').html(f.find('.aupi_ajaxing').data('ajaxing'));
    jQuery.ajax({
         type : 'POST' ,
         url : ajaxurl   ,
                 
         dataType : 'json' ,
         data: f.serialize(),
             
         success : function(r, status, jqFObj) { 
             f.find('.ric_err').html(r.message);
            
             f.find('.aupi_ajaxing').html(r.message);
 
             
             setTimeout(function(){
                f.find('.aupi_ajaxing').html('');
             },6000);
                   
         } ,
         error : function(param1, param2) {

            f.find('.aupi_ajaxing').html(f.find('.aupi_ajaxing').data('error'));
           } ,
         timeout : 30000
     });
     
 });

jQuery(document).on('click','.aupi_delete_feed',function(e){
    e.preventDefault();
    var f=jQuery(this);
    var trans=f.data('confirm');
    var nonce=f.data('nonce');
    var id=f.data('id');

    if(!confirm(trans)){
        return ;
    }

    f.parent().find('.aupi_loading').addClass('active');
 
    jQuery.ajax({
         type : 'POST' ,
         url : ajaxurl   ,
                 
         dataType : 'json' ,
         data: {
            'action' : 'aupi_delete_feed',
            'id' :id,
            'aupi_nonce' : nonce,
         },
             
         success : function(r, status, jqFObj) { 
            
             if(!r.error){
                f.parent().find('.aupi_loading').removeClass('active');

                window.location =r.url;
             }
            
                   
         } ,
         error : function(param1, param2) {
            f.parent().find('.aupi_loading').removeClass('active');
           } ,
         timeout : 30000
     });
     
 });

jQuery(document).on('click','.aupi_run_feed',function(e){
    e.preventDefault();
    var f=jQuery(this);
    var nonce=f.data('nonce');
    var id=f.data('id');

    f.parent().find('.aupi_loading').addClass('active');
    jQuery.ajax({
         type : 'POST' ,
         url : ajaxurl   ,
                 
         dataType : 'json' ,
         data: {
            'action' : 'aupi_run_feed',
            'id' :id,
            'aupi_nonce' : nonce,
         },
             
         success : function(r, status, jqFObj) { 
            
             if(!r.error){
                window.location =r.url;
             }
             f.parent().find('.aupi_loading').removeClass('active');
                   
         } ,
         error : function(param1, param2) {
            f.parent().find('.aupi_loading').removeClass('active');
           } ,
         timeout : 30000
     });
     
 });