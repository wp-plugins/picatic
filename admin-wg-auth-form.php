<?php
/**
 * Widget Name: Picatic_Sell_Tickets_Widget_plugin
 * Description: render a authentication form
 */
?>

  <p>
  <label for="<?php echo $this->get_field_id('auth_access_key'); ?>"><?php _e('Picatic Authentication', 'Picatic_Sell_Tickets_Widget_plugin'); ?></label>
  <input class="widefat" id="<?php echo $this->get_field_id('auth_access_key'); ?>" name="<?php echo $this->get_field_name('auth_access_key'); ?>" type="text" value="" />
  </p>

  <?php //Note: Do not delete this PHP tag
?>
