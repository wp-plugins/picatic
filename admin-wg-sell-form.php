<?php
/**
 * Widget Name: Picatic_Sell_Tickets_Widget_plugin
 * Description: render a sell tickets form
 */

if ( ! defined( 'ABSPATH' ) )
  die( "Can't load this file directly" );

  // Check values
  if( $instance) {

     $title = esc_attr($instance['title']);
     $event = esc_attr($instance['event']);
     $show_event_title = esc_attr($instance['show_event_title']);
     $show_ticket_desc = esc_attr($instance['show_ticket_desc']);
     $theme_options = esc_attr($instance['theme_options']);

  } else {

     $title = '';
     $event = '';
     $show_event_title = '';
     $show_ticket_desc = '';
     $theme_options = '';
  }
  ?>


  <p>
  <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'Picatic_Sell_Tickets_Widget_plugin'); ?></label>
  <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
  </p>

  <p>
  <label for="<?php echo $this->get_field_id('event'); ?>"><?php _e('Event', 'Picatic_Sell_Tickets_Widget_plugin'); ?></label>
  <br>
  <select id="<?php echo $this->get_field_id('event'); ?>" name="<?php echo $this->get_field_name('event'); ?>">
  <?php
  $allEvents = PicaticLib::getEventsForUserShort();

  // show the active events
  foreach($allEvents as $anEvent) {
    echo '<option value="'. $anEvent['id'] . '" '. selected( $event, $anEvent['id'] ) .'>'. $anEvent['title'] . '</option>';
  }
  ?>
  </select>
  </p>

  <p>
  <label><?php _e( 'Options' ); ?></label>
  </p>

  <p>
  <label for="<?php echo $this->get_field_id('show_event_title'); ?>">
    <input id="<?php echo $this->get_field_id('show_event_title'); ?>" name="<?php echo $this->get_field_name('show_event_title'); ?>" type="checkbox" value="1" <?php if ( $show_event_title ) echo 'checked="checked"'; ?>/>
    <?php _e('Show Event Title', 'Picatic_Sell_Tickets_Widget_plugin'); ?>
  </label><br>
  <label for="<?php echo $this->get_field_id('show_ticket_desc'); ?>">
    <input id="<?php echo $this->get_field_id('show_ticket_desc'); ?>" name="<?php echo $this->get_field_name('show_ticket_desc'); ?>" type="checkbox" value="1" <?php if ( $show_ticket_desc ) echo 'checked="checked"'; ?>/>
    <?php _e('Show Ticket Description', 'Picatic_Sell_Tickets_Widget_plugin'); ?>
  </label>
  </p>

  <p>
  <label for="<?php echo $this->get_field_id('theme_options'); ?>"><?php _e('Widget Theme', 'Picatic_Sell_Tickets_Widget_plugin'); ?></label>
  <br>
  <select id="<?php echo $this->get_field_id('theme_options'); ?>" name="<?php echo $this->get_field_name('theme_options'); ?>">
  <?php
      $light_class = '';
      $dark_class = 'ptw-dark';
      echo '<option value= "' . $light_class . '"' . selected( $theme_options , $light_class ) . '> ' . __('Light theme', 'Picatic_Sell_Tickets_Widget_plugin') . ' </option>'; // light theme
      echo '<option value= "' . $dark_class . '"' . selected( $theme_options , $dark_class ) . '> ' . __('Dark theme', 'Picatic_Sell_Tickets_Widget_plugin') . ' </option>'; // dark theme
  ?>
  </select>
  </p>

  <?php //Note: Do not delete this PHP tag
?>
