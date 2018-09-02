<?php

include 'simpleCalDAV/SimpleCalDAVClient.php';

class TineClient extends SimpleCalDAVClient {
  private $parsedEvents;
  /**
  * Constructor
  * connects to CalDAV-server and sets calendar from CalDAV-Url
  */
  function __construct($serverUrl, $calDAVUrl, $user, $password) {
    $this->connect($serverUrl, $user, $password);
    $calendar = new CalDAVCalendar($calDAVUrl);
    $this->setCalendar($calendar);
    $this->parsedEvents = [
      0 => '', 1 => '', 2 => '', 3 => '', 4 => '', 5 => '', 6 => '',
      7 => '',8 => '', 9 => '', 10 => '', 11 => '', 12 => '', 13 => ''
    ];
  }

  /**
  * parses a calendar event into the correct string for AIS
  */
  private function parseCalDAV($event) {
    // get all needed parameters for an event
    $name = $this->findCalDAVEntry($event, 'SUMMARY:', 'TRANSP:');
    $startTime = $this->findCalDAVEntry($event, 'DTSTART;TZID=Europe/Berlin:', 'DTEND;');
    $endTime = $this->findCalDAVEntry($event, 'DTEND;TZID=Europe/Berlin:', 'ORGANIZER;');

    // get the weekday of the event
    $weekday = date('w', strtotime(substr($startTime, 0, 4). '-'. substr($startTime, 4, 2). '-'. substr($startTime, 6, 2)));

    // parse starting time to correct format
    $startTime = substr($startTime, 9, 13);
    $parsedStartTime = substr($startTime,0,2).':'.substr($startTime,2,2);

    // parse ending time to correct format
    $endTime = substr($endTime, 9, 13);
    $parsedEndTime = substr($endTime,0,2).':'.substr($endTime,2,2);

    // concat everything to the final string
    // subdivide into morning and afternoon
    if(intval($startTime) < 120000) {
      if(intval($endTime) > 120000) {
        $finalString = $name. '</br>'. $parsedStartTime. ' - 12:00</br>';
        $this->parsedEvents[intval($weekday)] = $this->parsedEvents[intval($weekday)]. $finalString;
        $finalString = $name. '</br>12:00 - '. $parsedEndTime. '</br>';
        $this->parsedEvents[intval($weekday)+7] = $this->parsedEvents[intval($weekday)+7]. $finalString;
      } else {
        $finalString = $name. '</br>'. $parsedStartTime. ' - '. $parsedEndTime. '</br>';
        $this->parsedEvents[intval($weekday)] = $this->parsedEvents[intval($weekday)]. $finalString;
      }
    } else {
      $finalString = $name. '</br>'. $parsedStartTime. ' - '. $parsedEndTime. '</br>';
      $this->parsedEvents[intval($weekday)+7] = $this->parsedEvents[intval($weekday)+7]. $finalString;
    }
  }

  /**
  * find the correct entry from the given calDAV event
  */
  private function findCalDAVEntry($event, $prefix, $suffix) {
    $prePosition = strpos($event, $prefix);
    $sufPosition = strpos($event, $suffix);
    $var = substr($event, $prePosition + strlen($prefix), $sufPosition - $prePosition - strlen($prefix) - 1);
    return $var;
  }

  /**
  * gets correct index for each event
  *
  *
  */
  private function getIndex() {

  }

  /**
  * gets all Entries as an array of strings
  *
  * @param string $dateStart The starting date for getting events
  * @param string $endDate The ending date for getting events
  *
  * @return array String array that contains all events in the correct format
  */
  function getEntries($dateStart, $dateEnd) {
    $cals = $this->getEvents($dateStart, $dateEnd);

    // parse each calDAV event and push to array
    foreach ($cals as $entry) {
      $newEnt = $entry->getData();
      $this->parseCalDAV($newEnt);
    }
    return $this->parsedEvents;
  }
}

$start = '20180903T080000Z';
$end = '20180909T200000Z';

$tine = new TineClient('https://tine.informatik.kit.edu/', '/calendars/bd26cdeb8a7f9c836a00035e8cb1cdf7b41a13cf/109', 'meuer', 'Schwobbl110');
var_dump($tine -> getEntries($start, $end));
//var_dump(intval(str_replace('-', '', date('Y-m-d'))));
$dayofweek = date('w', strtotime(date('Y-m-d')));
var_dump($dayofweek);
?>
