<?php
/**
 * Function Name: Picatic Options
 *
 * Description: Authentication the Wordpress user with Picatic.com using oAuth
 */

 if ( ! defined( 'ABSPATH' ) )
  die( "Can't load this file directly" );

// add a admin menu
add_action( 'admin_menu', 'picatic_plugin_menu' );
function picatic_plugin_menu() {
  add_submenu_page( 'plugins.php', 'Picatic Options', 'Picatic Options', 'manage_options', 'picatic-options', 'picatic_options_page' );
}


// add a plugin authorizations section
add_action( 'admin_init', 'picatic_settings_authorization_init' );
function picatic_settings_authorization_init() {

  register_setting( 'picatic_settings_authorization', 'picatic_settings', 'picatic_settings_validate');

  // Register Sections
  add_settings_section(
    'picatic_authentication_section',
    __( 'Authentication', 'Picatic_Sell_Tickets_Widget_plugin' ),
    'picatic_settings_authentication_section_callback',
    'picatic_settings_authorization'
  );

  // Register Fields
  add_settings_field(
    'picatic_authentication_status',
    __( 'Status', 'Picatic_Sell_Tickets_Widget_plugin' ),
    'picatic_authentication_status_render',
    'picatic_settings_authorization',
    'picatic_authentication_section'
  );

}

// add a plugin cache settings section
add_action( 'admin_init', 'picatic_settings_cache_init' );
function picatic_settings_cache_init() {

  register_setting( 'picatic_settings_cache', 'picatic_settings_cache', 'save_picatic_cache_options');

  // Register Sections
  add_settings_section(
    'picatic_cache_section',
    __( 'Cache', 'Picatic_Sell_Tickets_Widget_plugin' ),
    'picatic_settings_cache_section_callback',
    'picatic_settings_cache'
  );

  // Register Fields

  add_settings_field(
    'picatic_cache',
    __( 'Cache', 'Picatic_Sell_Tickets_Widget_plugin' ),
    'picatic_cache_render',
    'picatic_settings_cache',
    'picatic_cache_section'
  );

  add_settings_field(
    'picatic_cache_duration',
    __( 'Cache Duration', 'Picatic_Sell_Tickets_Widget_plugin' ),
    'picatic_cache_duration_render',
    'picatic_settings_cache',
    'picatic_cache_section'
  );

}

function picatic_settings_authentication_section_callback() {
  echo __( 'Authenticate yourself with the Picatic plugin using an API key from Picatic.', 'Picatic_Sell_Tickets_Widget_plugin' );
}

function picatic_settings_cache_section_callback() {
  echo __( 'Caching will improve how quickly the Picatic widgets load.', 'Picatic_Sell_Tickets_Widget_plugin' );
}

function picatic_authentication_status_render() {
  $options = get_option( 'picatic_settings' );
  if( isset( $options['access_key'] ) && ( strlen(trim( $options['access_key'] )) > 0 ) ) {
  ?>

    <p><?php _e( 'Authenticated as', 'Picatic_Sell_Tickets_Widget_plugin' ); ?> <?php echo $options['user_name']; ?></p>
    <p>
      <?php submit_button( 'Remove' , 'primary' , 'submit-form', false); ?>
    </p>

    <?php // hidden fields ?>
    <input type="hidden" name="picatic_settings[auth_options]" value="0" id="auth-options"> <?php //for removing a access key from DB (wp_options table) ?>
    <input type="hidden" name="picatic_settings[user_id]" value='<?php echo $options['user_id']; ?>'><?php //for all events api function ?>
    <input type="hidden" name="picatic_settings[user_name]" value='<?php echo $options['user_name']; ?>'><?php  // for show in Options page ?>
  <?php
  } else {
  ?>
    <p>
      <input type="text" name="picatic_settings[access_key]" value="" placeholder="<?php _e( 'Enter your access key', 'Picatic_Sell_Tickets_Widget_plugin' ); ?>" />
    </p>
    <p><?php _e( 'Get an', 'Picatic_Sell_Tickets_Widget_plugin' ); ?> <a href="https://www.picatic.com/manage/users/applications/?utm_source=wordpress&utm_medium=integrations&utm_campaign=picatic%20for%20wordpress" target="_blank"><?php _e( 'API key', 'Picatic_Sell_Tickets_Widget_plugin' ); ?></a>.</p>

    <?php // hidden fields ?>
    <input type="hidden" name="picatic_settings[auth_options]" value="1" id="auth-options">
    <input type="hidden" name="picatic_settings[user_id]" value="">
    <input type="hidden" name="picatic_settings[user_name]" value="">
  <?php
  };
}

function picatic_cache_render() {
  $options = get_option( 'picatic_settings_cache' );
  if ( !isset($options['cache']) ) { $options['cache'] = '1'; }
  ?>
  <p>
    <label>
      <input type="radio" id="picatic_cache_on" name="picatic_settings_cache[cache]" value="1" <?php checked( $options['cache'], 1); ?> />
      <?php _e( 'On', 'Picatic_Sell_Tickets_Widget_plugin' ); ?>
    </label>
    <br>
    <label>
      <input type="radio" id="picatic_cache_off" name="picatic_settings_cache[cache]" value="0" <?php checked( $options['cache'], 0); ?> />
      <?php _e( 'Off', 'Picatic_Sell_Tickets_Widget_plugin' ); ?>
    </label>
  </p>
  <?php
}

function picatic_cache_duration_render() {
  $options = get_option( 'picatic_settings_cache' );
  if ( empty($options['cache_duration']) ) { $options['cache_duration'] = '3600'; }
  ?>
  <p>
    <input type="text" name="picatic_settings_cache[cache_duration]" value="<?php echo $options['cache_duration']; ?>" placeholder="<?php _e( 'seconds', 'Picatic_Sell_Tickets_Widget_plugin' ); ?>"/>
  </p>
  <?php
}

// show a form
function picatic_options_page( $active_tab = '') {
  ?>
  <div class="wrap">
    <h2><?php _e( 'Picatic Options', 'picatic' ); ?></h2>
    <?php
    if ( isset( $_GET[ 'tab' ] ) ) {
      $active_tab = $_GET['tab'];
    } else if ( $active_tab == 'cache_options' ) {
      $active_tab = 'cache_options';
    } else {
      $active_tab = 'authorization_options';
    }
    ?>

    <h2 class="nav-tab-wrapper">
      <a href="?page=picatic-options&tab=authorization_options" class="nav-tab <?php echo $active_tab == 'authorization_options' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Authentication', 'picatic' ); ?></a>
      <a href="?page=picatic-options&tab=cache_options" class="nav-tab <?php echo $active_tab == 'cache_options' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Cache', 'picatic' ); ?></a>
    </h2><!-- /.nav-tab-wrapper -->

    <form action="options.php" method="POST">
      <?php
      if ( $active_tab == 'cache_options' ) {
        settings_fields( 'picatic_settings_cache' );
        do_settings_sections( 'picatic_settings_cache' );
      } else {
        settings_fields( 'picatic_settings_authorization' );
        do_settings_sections( 'picatic_settings_authorization' );
      }
      submit_button();
      ?>
    </form>

  </div><!-- /.wrap -->

  <?php
}


function picatic_settings_validate( $input ) {
  $output = get_option( 'picatic_settings' );

  //NOTE: $input['access_key'] is always 0 string length.
  if ( isset( $input['access_key'] ) && $input['auth_options'] == '1' ) {

    // call authentication API
    $picaticInstance = PicaticAPI::instance();
    $picaticInstance->setApiKey( $input['access_key'] );
    try {
      $user = $picaticInstance->factory()->modelCreate('User')->find('me');
      $result_user = $user->getValues();
    } catch (Exception $e) {
    }


    if ( isset( $result_user['id'] ) ) {

      // Save a access key to DB
      $output['access_key'] = $input['access_key'];
      $output['auth_options'] = $input['auth_options'];
      $output['user_id'] = $result_user['id'];
      $output['user_name'] = $result_user['first_name'];


      add_settings_error( 'picatic_settings', 'valid-key', 'Successfully Authenticated.' , 'updated' );

    } else {

      add_settings_error( 'picatic_settings', 'invalid-key', 'Authentication Failed.' , 'error' );

    }
  } else if ( $input['auth_options'] == '0' ) {
    $output['access_key'] = '';  // Clear
    $output['auth_options'] = $input['auth_options'];
    $output['user_id'] = ''; // Clear
    $output['user_name'] = ''; // Clear

    add_settings_error( 'picatic_settings', 'remove-auth', 'Removed Authentication Successfully.' , 'updated' );
  }

  return $output;
}

function save_picatic_cache_options( $data ) {
  $message = null;
  $type = null;

  if ( null != $data && null != $data['cache_duration'] ) {
    if ( false === get_option('picatic_settings_cache') ) {
      $output = $data;
      $type = 'updated';
      $message = __( 'Successfully saved', 'Picatic_Sell_Tickets_Widget_plugin' );
    } else {
      $output = $data;
      $type = 'updated';
      $message = __( 'Successfully updated', 'Picatic_Sell_Tickets_Widget_plugin' );
    }
  } else {
    $output = get_option('picatic_settings_cache');
    $type = 'error';
    $message = __( 'Duration can not be empty', 'Picatic_Sell_Tickets_Widget_plugin' );
  }

  add_settings_error(
    'picatic_settings_cache',
    esc_attr( 'settings_updated' ),
    $message,
    $type
  );

  return $output;

}

function picatic_notices_action() {
  // displays all messages registered to 'picatic_settings' slug
  settings_errors( 'picatic_settings' );
  settings_errors( 'picatic_settings_cache' );
}
add_action( 'admin_notices', 'picatic_notices_action' );
?>
