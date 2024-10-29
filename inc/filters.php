<?php

defined( 'ABSPATH' ) || exit;

//incase not uploaded image to field
add_filter( 'post_thumbnail_id', 'aupi_post_thumbnail_id', 10, 3);
function aupi_post_thumbnail_id( $thumbnail_id, $post ) {
    $imagePodcast= get_post_meta($post->ID, 'aupi_image_url', true );
    if(!empty($imagePodcast)){
        return 1;
    }
    return $thumbnail_id;
}



add_filter( 'post_thumbnail_html', 'aupi_post_thumbnail_html', 10, 5);
function aupi_post_thumbnail_html(  $html, $post_id, $post_thumbnail_id, $size, $attr ) {
    $imagePodcast= get_post_meta($post_id, 'aupi_image_url', true );

    if(empty($imagePodcast)){
        return $html;
    }

    $alt= get_post_meta($post_id, 'aupi_image_title', true );


	$html  = '<img ';
    $attr=[];
    $attr['src'] = $imagePodcast ;
    $attr['class'] = 'attachment-aupi_podcast size-full wp-post-image';
    $attr['alt'] = !empty($alt) ?  $alt : '';

    $attr = array_map( 'esc_attr', $attr );
    
    

    foreach ( $attr as $name => $value ) {
        $html .= " $name=" . '"' . $value . '"';
    }

    $html .= ' />';


	return $html;
}