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
    add_action( 'wp_enqueue_scripts', array( $this, 'assets' ) );
    add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
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
      ),

      'display_function' => array( $this, 'display_select' ),
      'edit_function'    => array( $this, 'edit_select' ),
      'sidebar'          => 'template_fields',
    );

    ninja_forms_register_field( 'connected_select', $args );
  }


  public function assets(){
    wp_register_script( 'ntz-ninja', plugins_url( 'assets/javascripts/ntz-ninja.js', __FILE__ ), array('jquery'), '1.0', true );
    wp_enqueue_script( 'ntz-ninja' );

  }

  public function admin_assets( $hook ){
    if( $hook == 'toplevel_page_ninja-forms' ){
      wp_register_script( 'ntz-ninja-admin', plugins_url( '/assets/javascripts/ntz-ninja-admin.js', __FILE__ ), array('jquery'), '1.0', true );
      wp_enqueue_script( 'ntz-ninja-admin' );
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


  public function display_select( $field_id, $data ){
    global $wpdb, $ninja_forms_fields;

    $field_row   = ninja_forms_get_field_by_id( $field_id );
    $field_class = ninja_forms_get_field_class( $field_id );
    $type        = $field_row['type'];
    $type_name   = $ninja_forms_fields[$type]['name'];
    $label_pos   = isset( $data['label_pos'] ) ? $data['label_pos'] : 'left';
    $label       = isset( $data['label'] ) ? $data['label'] : $type_name;

    $fetch_url  = $data['ntz_remote_url'];
    $fetch_url  = str_ireplace( '{home}', '', $fetch_url );
    $connect_to = $data['connect_to'];
    $disabled   = "";

    if($label_pos == 'inside'){
      $initial_value = sprintf( '<option value="" data-default_option="1">%s</option>', $label );
      $initial_value .= sprintf( '<option value="">%s</option>', $label );
      $initial_value .= sprintf( '<option value="">%s</option>', $label );
      $initial_value .= sprintf( '<option value="">%s</option>', $label );
      $initial_value .= sprintf( '<option value="">%s</option>', $label );
    }

    $field_class .= ' js-ntz-ninjaForms-connectedSelect';


    printf(
      '<select class="ninja_forms_field_%s" id="%s" data-fetch_url="%s" data-connected_to="%s"%s>%s</select>',
      $field_class,
      $field_id,
      $fetch_url,
      $connect_to,
      $disabled,
      $initial_value
    );
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
}

new ConnectedSelect();