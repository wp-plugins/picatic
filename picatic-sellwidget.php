<?php

if ( ! defined( 'ABSPATH' ) )
  die( "Can't load this file directly" );

// required for shortcode creation
if (!isset($show_ticket_desc)) {
  $show_ticket_desc = "";
}
if (!isset($show_event_title)) {
  $show_event_title = "";
}
if (!isset($theme_options)) {
  $theme_options = "";
}
?>


<!--add class of  ptw-dark  for dark theme -->
<div class="ptw ptw-wide<?php echo " ".$theme_options ?>">


<?php

// get Picatic Options
$getOptions = get_option( 'picatic_settings' );
$userid =  $getOptions['user_id'];

$theEvent = PicaticLib::getEvent($event);
$getTickets = PicaticLib::getTicketsForEvent($event);

// get widget options
$widget_settings = get_option( 'widget_picatic_sell_tickets_widget' );

// render the result on theme
?>

  <h1 <?php if($show_event_title != 1 && $title != "yes") echo 'style=display:none;' ?>><?php echo $theEvent['title']; ?></h1>
  <h2><?php _e('Select your tickets', 'Picatic_Sell_Tickets_Widget_plugin'); ?></h2>
  <hr>
  <div class="ptw-ticket-block">
    <form action="<?php echo 'https://www.picatic.com/' . $theEvent['slug'] . '/checkout'; ?>" target="_blank" id="TicketPurchaseWidgetForm" method="post" accept-charset="utf-8" class="">
      <div style="display:none;"><input type="hidden" name="_method" value="POST"></div>
      <input type="hidden" name="data[Event][id]" value="<?php echo $theEvent['id']; ?>" id="EventId"> <?php //used in shortcode generator ?>

      <table class="ptw-table">
        <thead>
          <tr>
            <th><?php _e('Description', 'Picatic_Sell_Tickets_Widget_plugin'); ?></th>
            <th><?php _e('Price', 'Picatic_Sell_Tickets_Widget_plugin'); ?></th>
            <th><?php _e('Quantity', 'Picatic_Sell_Tickets_Widget_plugin'); ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($getTickets as $index=>$currentTicket) {
            if ( $theEvent['type'] === 'crowd_funded' && $theEvent['crowd_funded'] === true ) {
              if ( $currentTicket['type'] === 'regular' ){
                continue;
              }
            } else {
              if ( $currentTicket['type'] !== 'regular' ) {
                continue;
              }
            }
            if($currentTicket['status'] !== 'hidden') {
              if ($currentTicket['price'] == 0) {
                $price = __('Free', 'Picatic_Sell_Tickets_Widget_plugin');
              } else{
                if ( !empty($currentTicket['_ticket_price_discount']) && $currentTicket['_ticket_price_discount'][0]['type'] === 'crowd_funded' ) {
                  $price = "".PicaticLib::currencySymbol($theEvent['_currency']['code'])."".$currentTicket['_ticket_price_discount'][0]['amount'];
                  $ticketPriceDiscountId = $currentTicket['_ticket_price_discount'][0]['id'];
                } else {
                  $price = "".PicaticLib::currencySymbol($theEvent['_currency']['code'])."".$currentTicket['price'];
                  $ticketPriceDiscountId = '';
                }
              }
              ?>
              <tr itemprop="tickets" itemscope itemtype="http://data-vocabulary.org/Offer">
                <td class="ptw-wide-ticket-name">
                  <input type="hidden" name="data[TicketPrice][$index][id]" value="<?php echo $currentTicket['id'] ?>">
                  <input type="hidden" name="data[TicketPrice][$index][ticket_price_discount_id]" value="<?php echo $ticketPriceDiscountId; ?>">
                  <?php echo $currentTicket['name'] ?>
                </td>
                <td>
                  <div class="ptw-wide-ticket-price">
                    <span itemprop="price">
                      <?php echo $price ?>
                      <span itemprop="currency">
                        <?php echo $theEvent['_currency']['code']; ?>
                      </span>
                      <?php
                      if ( $currentTicket['taxable'] === true ) {
                        echo ' + ' . __('TAX', 'Picatic_Sell_Tickets_Widget_plugin');
                      }
                      ?>
                  </span>
                  </div>
                </td>
                <td width="40">
                  <div class="ptw-wide-ticket-quantity">
                    <?php if($currentTicket['status'] == 'open') { ?>
                      <select name="data[TicketPrice][$index][quantity]" class="input-mini" required="required">
                      <?php
                      $ticketsMin = $currentTicket['min_quantity'];
                      $ticketsMax = ($currentTicket['max_quantity'] == 0) ? 20 : $currentTicket['max_quantity'];
                      for ($j=$ticketsMin; $j <= $ticketsMax; $j++) { ?>
                        <option value="<?php echo $j ?>"><?php echo $j ?></option>
                      <?php } ?>
                      </select>
                    <?php } else { ?>
                      <?php _e('CLOSED', 'Picatic_Sell_Tickets_Widget_plugin'); ?>
                    <?php } ?>
                  </div><!-- /.ptw-wide-ticket-quantity -->
                </td>
              </tr>
              <?php // Also show this row if ticket->type is 'crowd_funded' ?>
              <?php
              if ( ($show_ticket_desc == 1 && $description === 'yes') || $currentTicket['type'] === 'crowd_funded' ):
              ?>

                <tr class="no-border">
                  <td colspan="3">
                  <?php
                  if ( $show_ticket_desc == 1 || $description == 'yes' ) {
                    echo $currentTicket['description'];
                  }
                  if ( $currentTicket['type'] == 'crowd_funded' && !empty($currentTicket['perk']) ) { ?>
                    <div class="ptw-wide-ticket-perk">
                      <span class="perk-title"><?php _e('Added Perk', 'Picatic_Sell_Tickets_Widget_plugin'); ?>:</span> <?php echo $currentTicket['perk']; ?>
                    </div><!-- /.ptw-wide-ticket-perk -->
                  <?php } ?>
                  </td>
                </tr>

              <?php
              endif;
              ?>
            <?php } //end if status=!hidden ?>

          <?php } //end foreach ?>
        </tbody>
      </table><!-- /.ptw-table -->

      <?php if( $theEvent['donations_enabled'] ) { ?>
        <table class="ptw-table">
          <tbody>
            <tr class="no-border top-padding-6">
              <td class="ptw-wide-ticket-name"><?php _e('Donation', 'Picatic_Sell_Tickets_Widget_plugin'); ?></td>
              <td class="align-right">
                <div class="input-prepend">
                  <span class="add-on">$</span><input type="text" name="data[Donation][amount]" class="input-mini" />
                </div><!-- /.input-prepend -->
              </td>
            </tr>
            <?php if ( isset($theEvent['donation_title']) ) { ?>
              <tr class="no-border">
                <td colspan="2"><?php echo $theEvent['donation_title'] ?></td>
              </tr>
            <?php } ?>
          </tbody>
        </table><!-- /.ptw-table -->
      <?php } //end if(donations) ?>

      <?php if( $theEvent['has_promo_code']) {  ?>
        <script>
        function getPromo() {
          var $href = document.getElementById("url").value;
          var $code = document.getElementById("promoBox").value;
          window.open( $href + $code , "_blank");
        }
        </script>
        <table class="ptw-table">
          <tbody>
            <tr class="no-border top-padding-6">
              <td class="ptw-wide-ticket-name"><?php _e('Promo Code', 'Picatic_Sell_Tickets_Widget_plugin'); ?>:<br></td>
              <td class="align-right">
                <div class="input-append promo-code-input relative">
                  <input id="url" type="hidden" value="<?php echo "https://www.picatic.com/" . $theEvent['slug'] . "?code=" ?>"/>
                  <input id="promoBox" type="text"/><button type="button" onclick="getPromo()" class="btn btn-small">Submit</button>
                </div><!-- /.input-append -->
              </td>
            </tr>
          </tbody>
        </table><!-- /.ptw-table -->
      <?php } //end if(promo) ?>

      <?php if ( $theEvent['status'] == 'active' ) { ?>
        <div class="clearfix">
          <button type="submit" class="btn btn-teal pull-right"><?php _e('Buy Now', 'Picatic_Sell_Tickets_Widget_plugin'); ?></button>
          <div class="ptw-powered-by">
            <?php _e('Powered by', 'Picatic_Sell_Tickets_Widget_plugin'); ?> <a href="https://www.picatic.com/" style="color: #535353" target="_blank"><img src="<?php echo plugins_url( 'images/picatic-logo-flat.png', __FILE__ ); ?>" alt="Picatic"></a>
          </div><!-- /.ptw-powered-by -->
        </div><!-- /.clearfix -->
      <?php } ?>
    </form>
  </div><!-- /.ptw-ticket-block -->
</div><!-- /.ptw -->
