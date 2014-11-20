<?php

/**
 * Plugin Name: Picatic
 * Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
 * Description: Integrate your Picatic event into your WordPress site
 * Version: 1.1.1
 * Stable tag: 1.1.1
 * Author: Picatic E-Ticket Inc.
 * Author URI: https://www.picatic.com/
 * License: MIT
 */

 /*  Copyright (c) 2014 Picatic E-Ticket Inc.

  Permission is hereby granted, free of charge, to any person obtaining a copy
  of this software and associated documentation files (the "Software"), to deal
  in the Software without restriction, including without limitation the rights
  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
  copies of the Software, and to permit persons to whom the Software is
  furnished to do so, subject to the following conditions:

  The above copyright notice and this permission notice shall be included in
  all copies or substantial portions of the Software.

  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
  THE SOFTWARE.
*/

if ( ! defined( 'ABSPATH' ) )
  die( "Can't load this file directly" );


/**
 * making API call with PHPlibs
 */
include("vendor/autoload.php"); // not work in localhost



/**
 * add new Picaltic Options menu under Plugins menu
 * add new custom options page
 */
include( 'picatic-options.php' );

require_once( plugin_dir_path( __FILE__ ) . 'picatic-lib.php' );



/**
 * Load Plugin JS/CSS for widgets
 */
function picatic_widget_CSS() {

  wp_register_style( 'picatic_sell_tickets_widget_CSS', plugins_url( 'css/pt-sell-tickets.css', __FILE__ ) );
  wp_enqueue_style( 'picatic_sell_tickets_widget_CSS' );

  wp_register_style( 'picatic_upcoming_events_widget_CSS', plugins_url( 'css/pt-upcoming-events.css', __FILE__ ) );
  wp_enqueue_style( 'picatic_upcoming_events_widget_CSS' );

}
add_action( 'wp_enqueue_scripts', 'picatic_widget_CSS', 99 );


/**
* Picatic Sell Tickets Widget at Administrator
*/
class Picatic_Sell_Tickets_Widget extends WP_Widget {

  // constructor
  function Picatic_Sell_Tickets_Widget() {
    parent::WP_Widget(false, $name = __('Picatic: Sell Tickets', 'Picatic_Sell_Tickets_Widget_plugin') );

    //get the access key from wp_options table.
    //it can be set from Picatic Options page or Widgets
    // 3 indexs are access_key, auth_options, user_id
    $this->access_key_check = get_option( 'picatic_settings' );
  }


  // widget form creation
  function form($instance) {


    if ( empty( $this->access_key_check['access_key'] ) )  // check the exist access key in DB
    {
      include( 'admin-wg-auth-form.php' ); // call authentication form

    } else {

      include( 'admin-wg-sell-form.php' ); // call sell tickets form
    }
  }


  // update widget
  function update($new_instance, $old_instance) {
    $instance = $old_instance;

    //-- Fields --//

    // for authentication update
    if ( strlen(strip_tags($new_instance['auth_access_key'])) > 0 ) {

      // check the authentication and add the new access key
      // call authentication API
      $picaticInstance = PicaticAPI::instance();
      $picaticInstance->setApiKey( $new_instance['auth_access_key'] );
      $user = $picaticInstance->factory()->modelCreate('User')->find('me');
      $result_user = $user->getValues();

      if ( isset( $result_user['id'] ) ) {
        update_option( 'picatic_settings' , array(
                'access_key' => strip_tags($new_instance['auth_access_key']) ,
                'auth_options' => '1' ,
                'user_id' => $result_user['id'] ,
                'user_name' =>  $result_user['first_name'] ,
                ) ); // auth_options is hidden field from Picatic Options page
        $this->access_key_check['access_key'] = strip_tags($new_instance['auth_access_key']); // update a access key back to wp_options table
        $this->access_key_check['auth_options'] = '1'; // already connected
      }// end of add the access key

    } else {

      // for sell tickets update
      $instance['title'] = strip_tags($new_instance['title']);
      $instance['event'] = strip_tags($new_instance['event']);
      $instance['show_event_title'] = strip_tags($new_instance['show_event_title']);
      $instance['show_ticket_desc'] = strip_tags($new_instance['show_ticket_desc']);
      $instance['theme_options'] = strip_tags($new_instance['theme_options']);
    }

    return $instance;
  }


  // display widget
  function widget($args, $instance) {
     extract( $args );

     // these are the widget options
     $title = apply_filters('widget_title', $instance['title']);
     $event = $instance['event'];
     $show_event_title = $instance['show_event_title'];
     $show_ticket_desc = $instance['show_ticket_desc'];
     $theme_options = $instance['theme_options'];

     echo $before_widget;
     echo '<div class="widget-text Picatic_Sell_Tickets_Widget_plugin_box">';
     //--- Display the widget starts here -----------------------------

    // check exist access key before displaying the widget on the theme
    $get_settings = get_option( 'picatic_settings' );
    if ( strlen( $get_settings['access_key'] ) > 0 && strlen( $event ) > 0) {
      include('picatic-sellwidget.php');
    }

     //--- Display the widget ends here --------------------------------
     echo '</div>';
     echo $after_widget;

  }
}

// register widget
add_action( 'widgets_init', 'picatic_process_widget' );

function picatic_process_widget() {

  return register_widget("Picatic_Sell_Tickets_Widget");
}

// end of Picatic Sell Tickets Widget



/**
* Picatic Upcoming Events Widget at Administrator
*/
class Picatic_Upcoming_Events_Widget extends WP_Widget
{
  // constructor
  function Picatic_Upcoming_Events_Widget() {
    parent::WP_Widget(false, $name = __('Picatic: List Upcoming Events', 'Picatic_Upcoming_Events_Widget_plugin') );

    //get the access key from wp_options table.
    //it can be set from Picatic Options page or Widgets
    // 3 indexs are access_key, auth_options, user_id
    $this->access_key_check = get_option( 'picatic_settings' );
  }


  function form($instance)
  {

    if ( empty( $this->access_key_check['access_key'] ) )  // check the exist access key in DB
    {
      include( 'admin-wg-auth-form.php' ); // call authentication form

    } else {
      // Check values
      if( $instance) {

         $title = esc_attr($instance['title']);

      } else {

         $title = '';
      }

      // call upcoming event form
      ?>

      <p>
      <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'Picatic_Upcoming_Events_Widget_plugin'); ?></label>
      <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
      </p>

      <?php  //Do not delete this tag
    }

  }

  function update($new_instance, $old_instance)
  {
    $instance = $old_instance;

    // for authentication update
    if ( strlen(strip_tags($new_instance['auth_access_key'])) > 0 ) {

      // check the authentication and add the new access key
      // call authentication API
      $picaticInstance = PicaticAPI::instance();
      $picaticInstance->setApiKey( $new_instance['auth_access_key'] );
      $user = $picaticInstance->factory()->modelCreate('User')->find('me');
      $result_user = $user->getValues();

      if ( isset( $result_user['id'] ) ) {
        update_option( 'picatic_settings' , array(
                'access_key' => strip_tags($new_instance['auth_access_key']) ,
                'auth_options' => '1' ,
                'user_id' => $result_user['id'] ,
                'user_name' =>  $result_user['first_name'] ,
                ) ); // auth_options is hidden field from Picatic Options page
        $this->access_key_check['access_key'] = strip_tags($new_instance['auth_access_key']); // update a access key back to wp_options table
        $this->access_key_check['auth_options'] = '1'; // already connected
      }// end of add the access key

    } else {

      // for up coming event update
      $instance['title'] = $new_instance['title'];
    }

    return $instance;
  }

  function widget($args, $instance)
  {
    extract($args, EXTR_SKIP);

    // these are the widget options
    $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);

    // these are the widget options
    echo $before_widget;

    if (!empty($title)) {
      echo $before_title . $title . $after_title;;
    }
    echo '<div class="widget-text Picatic_Upcoming_Events_Widget_plugin_box">';
    //--- Display the widget starts here -----------------------------

    // check exist access key before displaying the widget on the theme
    $get_settings = get_option( 'picatic_settings' );
    if ( strlen( $get_settings['access_key'] ) > 0 && strlen( $get_settings['user_id'] ) > 0) {
      include('picatic-eventswidget.php');
    }

    //--- Display the widget ends here -----------------------------
    echo '</div>';
    echo $after_widget;
  }

}
add_action( 'widgets_init', create_function('', 'return register_widget("Picatic_Upcoming_Events_Widget");') );

// end of Picatic Upcoming Events Widget



/**
 * add shortcode button in text Editor
 */
function pu_shortcode_button()
{
  if( current_user_can('edit_posts') &&  current_user_can('edit_pages') )
  {
    add_filter( 'mce_external_plugins', 'pu_add_buttons' );
    add_filter( 'mce_buttons', 'pu_register_buttons' );
  }
}


function pu_add_buttons( $plugin_array )
{
  //show the Picatic button in text editor tool bar
  global $wp_version;
  if ( $wp_version >= 3.8 ) {
    $jsfile = 'picatic-shortcode/inc/js/shortcode-tinymce-button-3.9.js';
  } else {
    $jsfile = 'picatic-shortcode/inc/js/shortcode-tinymce-button.js';
  }
  $plugin_array['pushortcodes'] = plugin_dir_url( __FILE__ ) . $jsfile;

  return $plugin_array;
}


function pu_register_buttons( $buttons )
{
  array_push( $buttons, 'separator', 'pushortcodes' );

  return $buttons;
}


function pu_get_shortcodes()
{
  global $shortcode_tags;

  echo '<script type="text/javascript">
  var shortcodes_button = new Array();';

  $count = 0;

  foreach($shortcode_tags as $tag => $code)
  {
    echo "shortcodes_button[{$count}] = '{$tag}';";
    $count++;
  }

  echo '</script>';
}
add_action('admin_init', 'pu_shortcode_button'); // add button to WYSIWYG Editor
add_action('admin_footer', 'pu_get_shortcodes'); // add button to WYSIWYG Editor

// -- END of add shortcode button in text Editor -- //



/**
 * declare the shortcode ([shortcode_name][/shortcode_name]) and
 * return to result of shortcode to frontend
 */
function picatic_sell_tickets_shortcode( $atts, $content = null  ){
  // provide defaults -- shows title and desc by default?
  extract(shortcode_atts( array(
    'event' => null,
    'title' => 'yes',
    'description' => 'yes',
    'theme_options' => 'ptw-light'
    ), $atts ) );

  ob_start(); //allows for variable scope in file before it is returned
  include('picatic-sellwidget.php');
  $obj = ob_get_clean(); // needed to place content correctly in the page
  return $obj;
}
add_shortcode( 'picatic-sell-tickets', 'picatic_sell_tickets_shortcode' );


// [picatic_upcoming_events user='123'][/picatic_upcoming_events]
function picatic_upcoming_events_shortcode( $atts, $content = null  ){
  // provide defaults -- shows title and desc by default?
  extract(shortcode_atts( array(
    'user' => null
  ), $atts ) );

  ob_start(); //allows for variable scope in file before it is returned
  include('picatic-eventswidget.php');
  $obj = ob_get_clean(); // needed to place content correctly in the page
  return $obj;
  //return (include('picatic-eventswidget.php'));
}
add_shortcode( 'picatic-upcoming-events', 'picatic_upcoming_events_shortcode' );
//-- end of declare the shortcode --//

// Set cache settings option on plugin activation
function picatic_activate() {
  $cache_settings = array(
    'cache' => '1',
    'cache_duration' => '3600',
  );
  add_option( 'picatic_settings_cache', $cache_settings );
}
register_activation_hook( __FILE__, 'picatic_activate' );

?>
