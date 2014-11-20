<?php

if ( ! defined( 'ABSPATH' ) )
  die( "Can't load this file directly" );

// get picatic options
$getOptions = get_option( 'picatic_settings' );
$userid =  $getOptions['user_id'];

// call events Api
$allEvents = PicaticLib::getEventsForUserLong();

//widget options
$get_widget_settings = get_option( 'widget_picatic_upcoming_events_widget' );

?>
<div class="pt-upcoming-events">
<?php if ( !empty($allEvents) ) { ?>
  <?php foreach($allEvents as $theEvent) { ?>
    <div class="pt-event-box <?php echo (PicaticLib::isCFEvent($theEvent)) ? 'cf-event-box' : 'reg-event-box'; ?>" itemscope itemtype="http://schema.org/Event">

      <div class="pt-event-box-img">
        <a href="https://www.picatic.com/<?php echo $theEvent['slug'] ?>" target="_blank" title="<?php echo $theEvent['title'] ?>">
          <img src="<?php echo ($theEvent['cover_image_uri']) ? $theEvent['cover_image_uri'] : plugins_url('images/event_category-default.jpg', __FILE__); ?>" alt="Event Image">
        </a>
      </div><!-- /.pt-event-box-img -->

      <div class="pt-event-box-content">
        <h3 class="pt-event-box-title">
          <a href="https://www.picatic.com/<?php echo $theEvent['slug'] ?>" target="_blank" title="<?php echo $theEvent['title'] ?>" itemprop="url">
            <span itemprop="summary"><?php echo PicaticLib::truncateString($theEvent['title'], 54); ?></span>
          </a>
        </h3>
        <?php if ( PicaticLib::isCFEvent($theEvent) === false ) { ?>
          <div class="pt-event-box-desc" itemprop="description">
            <?php
            echo PicaticLib::truncateString(PicaticLib::eventDescription($theEvent['summary'], $theEvent['description']), 140);
            ?>
          </div><!-- /.pt-event-box-desc -->
        <?php  } ?>
      </div><!-- /.pt-event-box-content -->

      <div class="pt-event-box-date">
        <div>
          <i class="pt-icon-calendar"></i> <span><?php echo PicaticLib::eventStartDate($theEvent['start_date']); ?></span>
        </div>
        <?php if ( PicaticLib::compiledVenueLocation($theEvent) !== '' ): ?>
          <div class="pt-event-box-venue">
            <i class="pt-icon-venue"></i>
            <span><?php echo PicaticLib::compiledVenueLocation($theEvent); ?></span>
          </div><!-- /.pt-event-box-venue -->
        <?php endif; ?>
      </div><!-- /.pt-event-box-date -->

      <?php if ( PicaticLib::isCFEvent($theEvent) ): ?>
        <div class="pt-event-box-funding-area">
          <?php if ( PicaticLib::cfStatus($theEvent) === 'funding' ) { ?>
            <div class="pt-event-box-tickets-left">
              <?php if ( $theEvent['funding_seconds'] > 0 && $theEvent['crowd_funded'] == true ) { ?>
                <span><?php echo PicaticLib::currencySymbol($theEvent['_currency']['code']); ?><?php echo ($theEvent['funding_total'] == 0) ? 0 : $theEvent['funding_total']; ?> OF <?php echo PicaticLib::currencySymbol($theEvent['_currency']['code']) . $theEvent['funding_goal']; ?> <?php _e('FUNDED', 'Picatic_Sell_Tickets_Widget_plugin'); ?></span>
              <?php } ?>
            </div><!-- /.event-box-tickets-left -->
          <?php } else if ( PicaticLib::cfStatus($theEvent) === 'funded' ) { ?>
            <div class="pt-event-box-countdown-success">
              <?php _e('FUNDED', 'Picatic_Sell_Tickets_Widget_plugin'); ?>!
            </div><!-- /.pt-event-box-countdown-success -->
          <?php } else if ( PicaticLib::cfStatus($theEvent) === 'notfunded' ) { ?>
            <div class="pt-event-box-countdown-failure">
              <?php _e('FUNDING UNSUCCESSFUL', 'Picatic_Sell_Tickets_Widget_plugin'); ?>
            </div><!-- /.pt-event-box-countdown-failure -->
          <?php } ?>
          <div class="pt-event-box-tilt-progress progress progress-success">
            <div class="bar" style="width: <?php echo PicaticLib::simulatedProgress($theEvent); ?>%;"></div>
          </div><!-- /.pt-event-box-tilt-progress -->
          <div class="pt-event-box-cf-info">
            <div class="pt-event-box-cf-funded">
              <span><?php echo PicaticLib::trueProgress($theEvent); ?>%</span>
              <?php _e('Funded', 'Picatic_Sell_Tickets_Widget_plugin'); ?>
            </div><!-- /.pt-event-box-cf-funded -->
            <div class="pt-event-box-cf-contributors">
              <span><?php echo $theEvent['funding_contributors']; ?></span>
              <?php _e('Contributors', 'Picatic_Sell_Tickets_Widget_plugin'); ?>
            </div><!-- /.pt-event-box-cf-contributors -->
            <div class="pt-event-box-cf-time">
              <span><?php echo PicaticLib::timeRemaining($theEvent['funding_seconds']); ?></span>
              <?php echo PicaticLib::timeType($theEvent['funding_seconds']); ?> <?php _e('Left', 'Picatic_Sell_Tickets_Widget_plugin'); ?>
            </div><!-- /.pt-event-box-cf-time -->
          </div><!-- /.pt-event-box-cf-info -->
        </div><!-- /.pt-event-box-funding-area -->
      <?php endif; ?>

    </div><!-- /.pt-event-box -->
  <?php } // end theEvent ?>
<?php } else { ?>
  <p><?php _e('No upcoming events at this time', 'Picatic_Sell_Tickets_Widget_plugin'); ?>.</p>
<?php } ?>
</div><!-- /.pt-upcoming-events -->
