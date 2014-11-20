<?php
/**
 * Name: Picatic Lib
 *
 * Description: Event/widget related public functions
 */

if ( ! defined( 'ABSPATH' ) )
  die( "Can't load this file directly" );


class PicaticLib {

  public static function currencySymbol($code) {
    $symbol = '$';
    if ( $code === 'GBP' ) {
      $symbol = '£';
    } else if ( $code === 'EUR' ) {
      $symbol = '€';
    }
    return $symbol;
  }

  public static function truncateString($string, $limit) {
    if ( strlen($string) <= $limit ) return $string;
    if ( false !== ($breakpoint = strpos($string, ' ', $limit)) ) {
      if ( $breakpoint < strlen($string) - 1 ) {
        $string = substr($string, 0, $breakpoint) . '&hellip;';
      }
    }
    return $string;
  }

  public static function isCFEvent($event) {
    if ( $event['type'] === 'crowd_funded' || $event['crowd_funded'] === true ) {
      return true;
    } else {
      return false;
    }
  }

  public static function eventDescription($summary, $description) {
    $ed = '';
    if ( $summary ) {
      $ed = strip_tags($summary);
    } else if ( $description ) {
      $ed = strip_tags($description);
    }
    return $ed;
  }

  public static function eventStartDate($startDate) {
    if ($startDate) {
      $date = strtotime($startDate);
      return date('F j Y', $date);
    } else {
      return '';
    }
  }

  public static function compiledVenueLocation($event) {
    $venueLocation = '';
    $venueLocality = true;
    $venueRegion = true;
    $venueCountry = true;
    if ( empty($event['venue_locality']) ) {
      $venueLocality = false;
    }
    if ( empty($event['venue_region_id']) ) {
      $venueRegion = false;
    }
    if ( empty($event['venue_country_id']) ) {
      $venueCountry = false;
    }
    if ( $venueLocality === true ) {
      $venueLocation = $event['venue_locality'];
      if ( $venueRegion === true or $venueCountry === true ) {
        $venueLocation .= ', ';
      }
    }
    if ( $venueRegion === true ) {
      $venueLocation .= $event['_venue_region']['iso'];
      if ( $venueCountry === true ) {
        $venueLocation .= ', ';
      }
    }
    if ( $venueCountry === true ) {
      $venueLocation .= $event['_venue_country']['country'];
    }
    return $venueLocation;
  }

  public static function cfStatus($event) {
    $status = 'notfunded';
    if ( $event['funding_seconds'] > 0 && $event['crowd_funded'] == true ) {
      $status = 'funding';
    } else if ( $event['funding_successful'] === true ) {
      $status = 'funded';
    }
    return $status;
  }

  public static function trueProgress($event) {
    $progress = 0;
    if ( $event['funding_goal'] > 0 ) {
      $progress = floor( ($event['funding_total'] / $event['funding_goal']) * 100 );
      if ( $progress === 0 && $event['funding_total'] > 0 ){
        $progress = 1;
      }
    }
    return $progress;
  }

  public static function simulatedProgress($event) {
    $simulatedProgress = 0;
    $progress = PicaticLib::trueProgress($event);
    if ( $progress > 0 and $progress < 10 ){
      $simulatedProgress = 10;
    } else if ( $progress > 0 ) {
      $simulatedProgress = $progress;
    }
    return $simulatedProgress;
  }

  public static function timeRemaining($fundingSeconds) {
    if ( $fundingSeconds <= 0 || is_nan($fundingSeconds) ) {
      $timeLeft = '0';
      return $timeLeft;
    }

    $days = strval( floor($fundingSeconds / (60 * 60 * 24)) );
    $divisorForHours = $fundingSeconds % (60 * 60 * 24);
    $hours = strval( floor($divisorForHours / (60 * 60)) );
    $divisorForMinutes = $fundingSeconds % (60 * 60);
    $minutes = strval( floor($divisorForMinutes / 60) );

    $timeLeft = $days;
    if ( $days === '0' ) {
      $timeLeft = $hours;
      if ( $hours === '0' ) {
        $timeLeft = $minutes;
      }
    }

    return $timeLeft;
  }

  public static function timeType($fundingSeconds) {
    if ( $fundingSeconds <= 0 || is_nan($fundingSeconds) ) {
      $timeType = 'Minutes';
      return $timeType;
    }

    $days = strval( floor($fundingSeconds / (60 * 60 * 24)) );
    $divisorForHours = $fundingSeconds % (60 * 60 * 24);
    $hours = strval( floor($divisorForHours / (60 * 60)) );
    $divisorForMinutes = $fundingSeconds % (60 * 60);
    $minutes = strval( floor($divisorForMinutes / 60) );

    $timeType = 'Days';
    if ( $days === '1' ) {
      $timeType = 'Day';
    } else if ( $days === '0' ) {
      $timeType = 'Hours';
      if ( $hours === '1' ) {
        $timeType = 'Hour';
      } else if ( $hours === '0' ) {
        $timeType = 'Minutes';
        if ( $minutes === '1' ) {
          $timeType = 'Minute';
        }
      }
    }

    return $timeType;
  }

  /* Fetch Data */

  /**
   * Get the PicaticAPI factory configured with API key
   * @return [type] [description]
   */
  public static function getFactory() {
    $getOptions = get_option( 'picatic-settings' );
    // call events Api
    $picaticInstance = PicaticAPI::instance();
    $picaticInstance->setApiKey( $getOptions['access_key'] );
    return $picaticInstance->factory();
  }

  /**
   * Get a short list of events for a user
   * @return [type] [description]
   */
  public static function getEventsForUserShort() {
    $events = PicaticLib::cacheRead('events_short');
    if ($events !== false) {
      return $events;
    }
    $getOptions = get_option( 'picatic-settings' );
    $events = PicaticLib::getFactory()->modelCreate('Event')->findAll(array(
      'user_id' => $getOptions['user_id'] ,
      'status' => 'active' ,
      'fields' => 'id,title,status'
      )
    );
    $normalizedEvents = array();
    foreach($events as $event) {
      $normalizedEvents[] = $event->getValues();
    }
    PicaticLib::cacheWrite('events_short', $normalizedEvents);
    return $normalizedEvents;
  }

  /**
   * Get the detailed list of events for a user-
   * @return [type] [description]
   */
  public static function getEventsForUserLong() {
    $events = PicaticLib::cacheRead('events_long');
    if ($events !== false) {
      return $events;
    }
    $getOptions = get_option( 'picatic-settings' );
    $events  =PicaticLib::getFactory()->modelCreate('Event')->findAll(array(
      'user_id' => $getOptions['user_id'] ,
      'status' => 'active',
      'extend' => 'venue_region,venue_country,currency',
      'fields' => 'id,title,status,type,crowd_funded,cover_image_uri,slug,start_date,start_time,end_date,end_time,venue_street,venue_locality,venue_region_id,venue_country_id,summary,description,funding_seconds,funding_successful,funding_goal,funding_total,funding_contributors,_venue_region,_venue_country,_currency'
      )
    );
    $normalizedEvents = array();
    foreach($events as $event) {
      $normalizedEvents[] = $event->getValues();
    }
    PicaticLib::cacheWrite('events_long', $normalizedEvents);
    return $normalizedEvents;
  }

  /**
   * Get an event
   * @param  [type] $eventId [description]
   * @return [type]          [description]
   */
  public static function getEvent($eventId) {
    $events = PicaticLib::cacheRead('events');
    if (isset($events[$eventId])) {
      return $events[$eventId];
    }
    $event  = PicaticLib::getFactory()->modelCreate('Event')->find( $eventId, array(
      'extend' => 'currency'
    ));
    if (!is_array($events)) {
      $events = array();
    }
    $events[$eventId] = $event->getValues();
    PicaticLib::cacheWrite('events', $events);
    return $event;
  }

  /**
   * Get the tickets for an event
   * @param  [type] $eventId [description]
   * @return [type]          [description]
   */
  public static function getTicketsForEvent($eventId) {
    $tickets = PicaticLib::cacheRead('tickets');
    if (isset($tickets[$eventId])) {
      return $tickets[$eventId];
    }
    $_tickets = PicaticLib::getFactory()->modelCreate('Ticket_Price')->findAll(array(
      'event_id' => $eventId,
      'extend' => 'ticket_price_discount',
      )
    );
    if(!is_array($tickets)) {
      $tickets = array();
    }
    $normalizedTickets = array();
    foreach($_tickets as $ticket) {
      $normalizedTickets[] = $ticket->getValues();
    }
    $tickets[$eventId] = $normalizedTickets;
    PicaticLib::cacheWrite('tickets', $tickets);
    return $normalizedTickets;
  }

  /* Cache Related */

  public static $cache_prefix = "pt_cache_";

  public static function cacheWrite($key, $value, $timeout=3600) {
    $settings = get_option('picatic_settings_cache');
    if (isset($settings['cache']) && $settings['cache'] == "1") {
      if ( isset($settings['cache_duration']) ) {
        $timeout = $settings['cache_duration'];
      }
      $full_key = sprintf("%s%s", PicaticLib::$cache_prefix, $key);
      set_transient($full_key, $value, $timeout);
    }

  }

  public static function cacheRead($key) {
    $full_key = sprintf("%s%s", PicaticLib::$cache_prefix, $key);
    return get_transient($full_key);
  }

  /**
   * Clear the cached keys
   * @HACK static key names implies the above functions are terrible, make it not terrible
   * @return [type] [description]
   */
  public static function cacheClear() {
    $keys = array('events_short', 'events_long', 'events', 'tickets');
    foreach($keys as $key) {
      delete_transient($key);
    }
  }
}
