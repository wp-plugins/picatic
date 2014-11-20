<?php
// this file contains the contents of the popup window
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>Picatic Shortcodes</title>

    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.js"></script>
    <script language="javascript" type="text/javascript" src="../../../../wp-includes/js/tinymce/tiny_mce_popup.js"></script> <!-- tiny_mce_popup.js is needed for returning the values back to the content area -->
    <link rel="stylesheet" type="text/css" href="inc/css/pt-style.css"> <!-- add CSS to tabs and Js -->

    <script type="text/javascript">
      var ButtonDialog = {
        local_ed : 'ed',
        init : function(ed) {
          ButtonDialog.local_ed = ed;
          tinyMCEPopup.resizeToInnerSize();
        },
        insert : function insertButton(ed) {

          // Try and remove existing style / blockquote
          tinyMCEPopup.execCommand('mceRemoveNode', false, null);

          // set up variables to contain our input values

          var event = jQuery('#event').val();
          var showtitle = jQuery('#show_title').val();
          var showdesc = jQuery('#show_desc').val();
          var themeoptions = jQuery('#theme_options').val();
          var output = '';

          // setup the output of our shortcode
          output = '[picatic-sell-tickets ';
          output += 'event=' + event + " ";
          output += 'title=' + showtitle + " ";
          output += 'description=' + showdesc + " ";
          output += 'theme_options=' + themeoptions;
          output += '][/picatic-sell-tickets]';

          tinyMCEPopup.execCommand('mceReplaceContent', false, output);

          // Return
          tinyMCEPopup.close();
        }
      };
      var ButtonDialog2 = {
        local_ed : 'ed',
        init : function(ed) {
          ButtonDialog2.local_ed = ed;
          tinyMCEPopup.resizeToInnerSize();
        },
        insert : function insertButton(ed) {

          // Try and remove existing style / blockquote
          tinyMCEPopup.execCommand('mceRemoveNode', false, null);
          // setup the output of our shortcode
          var userid = jQuery('#userid').val();
          output = '[picatic-upcoming-events][/picatic-upcoming-events]';

          tinyMCEPopup.execCommand('mceReplaceContent', false, output);

          // Return
          tinyMCEPopup.close();
        }
      };
      tinyMCEPopup.onInit.add(ButtonDialog.init, ButtonDialog);
      tinyMCEPopup.onInit.add(ButtonDialog2.init, ButtonDialog2);

  </script>
  <script type="text/javascript">
    // This is for the modal windows
    jQuery(document).ready(function() {
      jQuery( ".tabs .tab-links a" ).click(function()  {
            var currentAttrValue = jQuery(this).attr('href');
            // Show/Hide Tabs
            jQuery('.tabs ' + currentAttrValue).show().siblings().hide();
            // Change/remove current tab to active
            jQuery(this).parent('li').addClass('active').siblings().removeClass('active');
      });
      jQuery('input:checkbox[name=show_title]').click(function()
        {
            if(jQuery(this).is(':checked')) {
            jQuery(this).val('yes');
              } else {
                jQuery(this).val('no');
              }
        });
      jQuery('input:checkbox[name=show_desc]').click(function()
        {
            if(jQuery(this).is(':checked')) {
            jQuery(this).val('yes');
              } else {
                jQuery(this).val('no');
              }
        });
    });


  </script>
  </head>
  <body>


    <?php
    require_once('../../../../wp-load.php');  // load WP functions so we can call get_option()
    ?>
    <h2></h2>

    <div class="tabs">
      <div class="tab-header">
        <ul class="tab-links">
          <li class="active"><a href="#tab1"><?php _e('Sell Tickets', 'Picatic_Sell_Tickets_Widget_plugin'); ?></a></li>
          <li><a href="#tab2"><?php _e('Upcoming Events', 'Picatic_Sell_Tickets_Widget_plugin'); ?></a></li>
        </ul><!-- /.tab-links -->
      </div>

      <div class="tab-content">
        <div id="tab1" class="tab active">
          <form action="">

            <?php
            // get Picatic Options
            $getOptions = get_option( 'picatic_settings' );
            $userid =  $getOptions['user_id'];

            $allEvents = PicaticLib::getEventsForUserShort();
            ?>

            <?php if ( !empty($userid) ) { ?>
              <p><strong><?php _e('Event', 'Picatic_Sell_Tickets_Widget_plugin'); ?></strong></p>
              <div class="form-group">
                <select id="event" name="event" class="" required="required">
                <?php foreach ($allEvents as $theEvent) { ?>
                  <option value="<?php echo $theEvent['id'] ?>"><?php echo $theEvent['title'] ?></option>
                <?php } ?>
                </select>
              </div><!-- /.form-group -->

              <p><strong><?php _e('Options', 'Picatic_Sell_Tickets_Widget_plugin'); ?></strong></p>
              <div class="form-group">
                <label class="block">
                  <input type="checkbox" id="show_title" name="show_title" value="no">
                  <?php _e('Show Event Title', 'Picatic_Sell_Tickets_Widget_plugin'); ?>
                </label>
                <label class="block">
                  <input type="checkbox" id="show_desc" name="show_desc" value="no">
                  <?php _e('Show Ticket Description', 'Picatic_Sell_Tickets_Widget_plugin'); ?>
                </label>
              </div><!-- /.form-group -->

              <p><strong><?php _e('Widget Theme', 'Picatic_Sell_Tickets_Widget_plugin'); ?></strong></p>
              <div class="form-group">
                <select id="theme_options" name="theme_options">
                  <option value="ptw-light"><?php _e('Light Theme', 'Picatic_Sell_Tickets_Widget_plugin'); ?></option>
                  <option value="ptw-dark"><?php _e('Dark Theme', 'Picatic_Sell_Tickets_Widget_plugin'); ?></option>
                </select>
              </div><!-- /.form-group -->

              <div class="tab-footer">
                <a href="javascript:ButtonDialog.insert(ButtonDialog.local_ed)" id="insert" >Insert Shortcode</a>
              </div><!-- /.tab-footer -->
            <?php } else { ?>
              <h3><?php _e('Sell Tickets Shortcode', 'Picatic_Sell_Tickets_Widget_plugin'); ?></h3>
              <p><?php _e('In order to use this shortcode, you must authenticate yourself with the Picatic plugin using an API key from Picatic.', 'Picatic_Sell_Tickets_Widget_plugin'); ?></p>
              <p><?php _e('Go to the Picatic Options page to set your API key.', 'Picatic_Sell_Tickets_Widget_plugin'); ?></p>
            <?php } ?>
          </form>
        </div><!-- /.tab -->

        <div id="tab2" class="tab">
          <?php if ( !empty($userid) ) { ?>
            <form action="">
              <input type="hidden" id="userid" name="userid" value="<?php echo $userid ?>"/>
              <h3><?php _e('Upcoming Events Shortcode', 'Picatic_Sell_Tickets_Widget_plugin'); ?></h3>
              <p>[picatic-upcoming-events][/picatic-upcoming-events]</p>
              <div class="tab-footer">
                <a href="javascript:ButtonDialog2.insert(ButtonDialog2.local_ed)" id="insert">Insert Shortcode</a>
              </div><!-- /.tab-footer -->
            </form>
          <?php } else { ?>
            <h3><?php _e('Upcoming Events Shortcode', 'Picatic_Sell_Tickets_Widget_plugin'); ?></h3>
            <p><?php _e('In order to use this shortcode, you must authenticate yourself with the Picatic plugin using an API key from Picatic.', 'Picatic_Sell_Tickets_Widget_plugin'); ?></p>
            <p><?php _e('Go to the Picatic Options page to set your API key.', 'Picatic_Sell_Tickets_Widget_plugin'); ?></p>
          <?php } ?>
        </div><!-- /.tab -->
      </div><!-- /.tab-content -->

    </div><!-- /.tabs -->

  </body>
</html>
