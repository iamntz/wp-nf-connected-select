<?php
/*
Plugin Name: Ninja Forms Connected Select Fields
Plugin URI: N/A
Description: Adds ability to create connected select fields to a ninja form
Author: Ionuț Staicu
Version: 1.0
Author URI: http://iamntz.com
*/

namespace ninjaforms;

class ConnectedSelect {
  protected $existing_forms = array();
  function __construct() {
    add_action( 'init', array( $this, 'init' ) );
    add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

    add_action( 'wp_ajax_ntz_ninja_get_connected', array( $this, 'get_options_tags' ) );
    add_action( 'wp_ajax_nopriv_ntz_ninja_get_connected', array( $this, 'get_options_tags' ) );
  }


  public function init(){
    if( !function_exists( 'ninja_forms_register_field' ) ){ return; }

    $args = array(
      'name' => 'Connected Select',
      'edit_options' => array(
        array(
          'type'          => 'text',
          'name'          => 'ntz_remote_url',
          'label'         => 'Remote URL to get values from (use <code>{home}</code> for site URL)',
          'class'         => 'widefat',
        ),

        array(
          'type'          => 'text',
          'label'         => "Connected With (debug)",
          'name'          => 'connect_to',
          'default_value' => 0
        ),

        array(
          'type'          => 'text',
          'label'         => 'Extra Parameters to send',
          'name'          => 'extra_param',
          'class'         => 'widefat'
        ),
      ),

      'display_function' => array( $this, 'display_select' ),
      'edit_function'    => array( $this, 'edit_select' ),
      'sidebar'          => 'template_fields',
    );

    ninja_forms_register_field( 'connected_select', $args );
  }

  protected function register_assets(){
    wp_register_script( 'ntz-ninja-admin', plugins_url( '/assets/javascripts/ntz-ninja-admin.js', __FILE__ ), array('jquery'), '1.0', true );
    wp_register_script( 'ntz-ninja', plugins_url( 'assets/javascripts/ntz-ninja.js', __FILE__ ), array('jquery'), '1.0', true );

    wp_localize_script( 'ntz-ninja', 'ntz_ninja', array(
      'nonce' => wp_create_nonce( "ntz-ninja-connected-select" )
    ) );
  }

  public function enqueue_assets(){
    $this->register_assets();
    wp_enqueue_script( 'ntz-ninja' );
  }

  public function enqueue_admin_assets( $hook ){
    $this->register_assets();
    if( $hook == 'toplevel_page_ninja-forms' ){
      wp_enqueue_script( 'ntz-ninja-admin' );
    }
    if( $hook == 'forms_page_ninja-forms-subs' ){
      wp_enqueue_script( 'ntz-ninja' );
    }
  }


  public function edit_select( $field_id, $data ){
    ?>
    <div class="description description-wide select">
      <span class="field-option">
        <label>Connected With:</label><br>
        <select class="code widefat js-ntz-ninjaForms-connectedSelect">
          <option>Select Connect</option>
          <?php echo $this->get_siblings_connects( $field_id ) ?>
        </select>
      </span>
      <p><small>If you have Select1 and Select2 and you want to change Select2 content based on Select1 option,
        you need to add a remote URL to Select1 and select <code>Connect With</code> on Select2</small></p>
      </div>
    <?php
  }


  public function get_siblings_connects( $field_id ){
    $current_form = ninja_forms_get_form_by_field_id( $field_id );
    $form_fields = ninja_forms_get_fields_by_form_id( $current_form['id'] );
    $options = array();
    foreach( $form_fields as $key => $form_field ){
      if( $form_field['type'] == 'connected_select' && $form_field['id'] != $field_id ){
        $options[] = sprintf( '<option value="%s">%s</option>',
          $form_field['id'],
          $form_field['data']['label']
        );
      }
    }

    return join( $options, "\n" );
  }



  /* AJAX Helper
  ***************************/

  public function get_options_tags(){
    check_ajax_referer( 'ntz-ninja-connected-select', 'nonce' );
    $selects = apply_filters( 'ntz/ninja-forms/connected-select/select_data', '' );
    echo $selects;
    die();
  }



  /* Frontend Fields
  ***************************/

  public function display_select( $field_id, $data ){

    $field_row   = ninja_forms_get_field_by_id( $field_id );
    $type        = $field_row['type'];

    $label_pos   = isset( $data['label_pos'] ) ? $data['label_pos'] : 'left';
    $label       = isset( $data['label'] ) ? $data['label'] : '';

    if($label_pos == 'inside'){
      $initial_value = sprintf( '<option value="-null-" data-default_option="1">%s</option>', $label );
    }

    $field_class = ninja_forms_get_field_class( $field_id );
    $field_class .= ' js-ntz-ninjaForms-connectedSelect';

    $otherAttributes = array();
    $otherAttributes[] = $this->get_fetch_url( $field_id, $data );
    $otherAttributes[] = $this->get_connected_to( $field_id, $data );
    $otherAttributes[] = $this->get_selected_value( $field_id, $data );
    $otherAttributes[] = sprintf( 'data-extra_param="%s"', $data['extra_param'] );

    printf(
      '<select class="ninja_forms_field_%1$s" id="%2$s" name="ninja_forms_field_%2$s"%3$s>%4$s</select>',
      $field_class,
      $field_id,
      join( $otherAttributes, ' ' ),
      $initial_value
    );
  }


  protected function get_selected_value( $field_id, $data ){
    if( isset( $data['default_value'] ) )
    return sprintf( 'data-value="%s"', $data['default_value'] );
  }


  protected function get_connected_to( $field_id, $data ){
    $connect_to = $data['connect_to'];
    $disabled   = "";

    if( !empty( $connect_to ) ){
      $connect_to = sprintf( 'data-connected_to="%s"', $connect_to );
    }

    return $connect_to;
  }


  protected function get_fetch_url( $field_id, $data ){
    $fetch_url  = $data['ntz_remote_url'];
    $fetch_url  = str_ireplace( '{home}', get_home_url(), $fetch_url );
    $fetch_url  = apply_filters( "ntz/ninja-forms/connected-select/fetch-url", $fetch_url );
    if ( !empty( $fetch_url ) ){
      $fetch_url = sprintf( 'data-fetch_url="%s"', $fetch_url );
    }

    return $fetch_url;
  }
}

new ConnectedSelect();