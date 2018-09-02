<?php

include 'simpleCalDAV/SimpleCalDAVClient.php';

class TineClient extends SimpleCalDAVClient {
  /**
  * Constructor
  * connects to CalDAV-server and sets calendar from CalDAV-Url
  */
  function __construct($serverUrl, $calDAVUrl, $user, $password) {
    $this->connect($serverUrl, $user, $password);
    $calendar = new CalDAVCalendar($calDAVUrl);
    $this->setCalendar($calendar);
  }

  /**
  * parses a calendar event into the correct string for AIS
  */
  private function parseCalDAV($event) {
    // get all needed parameters for an event
    $name = $this->findCalDAVEntry($event, 'SUMMARY:', 'TRANSP:');
    $startTime = $this->findCalDAVEntry($event, 'DTSTART;TZID=Europe/Berlin:', 'DTEND;');
    $endTime = $this->findCalDAVEntry($event, 'DTEND;TZID=Europe/Berlin:', 'ORGANIZER;');

    // parse starting time to correct format
    $startTime = substr($startTime, 9, 13);
    $parsedStartTime = substr($startTime,0,2).':'.substr($startTime,2,2);

    // parse ending time to correct format
    $endTime = substr($endTime, 9, 13);
    $parsedEndTime = substr($endTime,0,2).':'.substr($endTime,2,2);

    // concat everything for the final event string
    $finalString = $name. '</br>'. $parsedStartTime. ' - '. $parsedEndTime. '</br>';

    return $finalString;
  }

  /**
  * find the correct entry from the given calDAV event
  */
  private function findCalDAVEntry($event, $prefix, $suffix) {
    $prePosition = strpos($event, $prefix);
    $sufPosition = strpos($event, $suffix);
    return substr($event, $prePosition + strlen($prefix), $sufPosition - $prePosition - strlen($prefix) - 1);
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
    $parsedEvents = [];

    // parse each calDAV event and push to array
    foreach ($cals as $entry) {
      $newEnt = $entry->getData();
      //var_dump($newEnt);
      array_push($parsedEvents, $this->parseCalDAV($newEnt));
    }

    return $parsedEvents;
  }
}

$start = '20180903T080000Z';
$end = '20180930T200000Z';

$tine = new TineClient('https://tine.informatik.kit.edu/', '/calendars/bd26cdeb8a7f9c836a00035e8cb1cdf7b41a13cf/109', 'meuer', 'Schwobbl110');
var_dump($tine -> getEntries($start, $end));

?>
