<?php

// Adding a region/city connected selector

add_filter( 'ntz/ninja-forms/connected-select/select_data', 'ntz_ninja_connected_select_data' );

function ntz_ninja_connected_select_data( $data ){
  global $wpdb;
  $data = '';

  if( empty( $_REQUEST['parent_value'] ) ){
    $regions = $wpdb->get_results( "SELECT * FROM `regions` WHERE `country`='IT'" );
    foreach ( $regions as $key => $region ) {
      if( empty( $region->name ) ){ continue; }
      $data .= sprintf( '<option value="%s">%s</option>',
        esc_attr( $region->code ),
        $region->name
      );
    }
  }else {
    $regions = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `cities` WHERE `country`='IT' AND region='%s'", $_REQUEST['parent_value'] ) );
    foreach ( $regions as $key => $region ) {
      if( empty( $region->name ) ){ continue; }
      $data .= sprintf( '<option value="%s">%s</option>',
        esc_attr( $region->name ),
        $region->name
      );
    }
  }

  return $data;
}

add_filter( 'ntz/ninja-forms/connected-select/fetch-url', 'ntz_ninja_connected_select_url' );
function ntz_ninja_connected_select_url( $url ){
  if( $url ){
    return admin_url('admin-ajax.php');
  }
}