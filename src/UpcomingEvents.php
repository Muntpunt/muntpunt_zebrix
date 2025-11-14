<?php

namespace Drupal\muntpunt_zebrix;

class UpcomingEvents {
  private const EVENT_TYPE_NORMAL = '11,24,25,30,26,27,28,31,29,36,44,33,32,9,35,6,20,49,50,52,53';
  private const EVENT_TYPE_TE_GAST = '39,48';
  private const WHOLE_DAY = 1;
  private const FROM_NOW = 2;
  private const MAX_NUM_EVENTS = 8;
  private $daoEvents;
  private $daoTeGast;
  private const batchsize = 4;

  public function printUpcomingEvents($conferenceRooms) {
    $this->fillDao($conferenceRooms);
    $this->printHtmlHeader();
    $this->printTodaysDate();
    $this->printEvents();
    $this->printEventsTeGast();
    $this->printHtmlFooter();
  }

  private function fillDao($conferenceRooms) {
  	$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
	  $offset_guest = isset($_GET['offset_guest']) ? intval($_GET['offset_guest']) : 0;

		$this->daoEvents = $this->getEvents(self::EVENT_TYPE_NORMAL, self::FROM_NOW, $conferenceRooms, 0 , 100);
   	$this->daoTeGast = $this->getEvents(self::EVENT_TYPE_TE_GAST, self::FROM_NOW, $conferenceRooms,0 , 100);

    if ($offset >= $this->daoEvents->N) {
			$offset = 0;
		}

		if ($offset_guest >= $this->daoTeGast->N) {
			$offset_guest = 0;
		}

    if ($this->daoEvents->N + $this->daoTeGast->N > self::MAX_NUM_EVENTS) {
    if ($this->daoTeGast->N > 0) {
      if($this->daoTeGast->N < 4) {
        $limit = 8 - $this->daoTeGast->N;
      }
      else {
        $limit = 4;
      }
    }
    else {
      $limit = 8;
    }

    $this->daoEvents = $this->getEvents(self::EVENT_TYPE_NORMAL, self::FROM_NOW, $conferenceRooms, $offset , $limit);
    $this->daoTeGast = $this->getEvents(self::EVENT_TYPE_TE_GAST, self::FROM_NOW, $conferenceRooms, $offset_guest, 4);

    $nextOffset = $offset + $limit;
    $nextOffset_guest = $offset_guest + 4;

    // Output JavaScript to automatically reload the page with the next set of events
    echo '<script type="text/javascript">
      setTimeout(function() {
        window.location.href = "?offset=' . $nextOffset . '&offset_guest='. $nextOffset_guest . '";
      }, 10000); // Reload every 10 seconds
      </script>';
    }
    else {
      $this->daoEvents = $this->getEvents(self::EVENT_TYPE_NORMAL, self::FROM_NOW, $conferenceRooms, 0, 100);
      $this->daoTeGast = $this->getEvents(self::EVENT_TYPE_TE_GAST, self::FROM_NOW, $conferenceRooms, 0, 100);
    }
  }

  private function printHtmlHeader() {
    echo '
      <!DOCTYPE html>
      <html lang="en">
        <head>
          <meta charset="UTF-8">
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <meta http-equiv="X-UA-Compatible" content="ie=edge">
          <meta http-equiv="refresh" content="300" >
          <title>Muntpunt evenementen</title>
          <link rel="stylesheet" href="/modules/custom/muntpunt_zebrix/css/style.css">
        </head>
        <body>
    ';
  }

  private function printTodaysDate() {
    if (!empty($_GET['view']) && $_GET['view'] == "intranet") {
	    echo '<p style="font-size: 14px;" class="datumvandaag">';
    }
    else {
	    echo '<p style="font-size: 90px;" class="datumvandaag">';
    }

    echo $this->getDateWeekDay() . ' ';
    echo $this->getDateDay() . '.';
    echo $this->getDateMonth();
    echo '</p>';
  }

  private function printEvents() {
   echo '<table>';
   while ($this->daoEvents->fetch()) {
     if (!empty($_GET['view']) && $_GET['view'] == "intranet") {
       echo '<p style="margin: 0px";><span style="font-size: 13px;">' . $this->daoEvents->title . '</span><br><span style="font-size: 12px;">';
     }
     else {
	     $title =  $this->daoEvents->title ;
       if (strpos($title, ':') !== false) {
         $parts = explode(':', $title);
    		 $title =  $parts[0] . ":<br>" . $parts[1];
       }

       echo '<tr><td width="70%"><span style="font-size: 50px;line-height: 60px"; class="titelevent">' . $title . '</span></td><td><span style="font-size: 45px;line-height: 60px ">';
    }

    $today = date('Y-m-d');
    $einddatum = $this->daoEvents->Einddatum;
    if ($einddatum !== $today) {
      echo 'DOORLOPEND';
    }
    else {
      echo $this->daoEvents->Startuur . ' - ' . $this->daoEvents->Einduur;
    }

    $zaal = strtoupper(preg_replace("/\x01/",", ",substr($this->daoEvents->Zaal,1,-1)));

    if ($zaal == "HET HONDERDHANDENHUIS +1"){
      echo '<br>HET HONDERD- HANDENHUIS +1</span></td></tr>';
    }
    else {
      echo '<br>' . $zaal . '</span></td></tr>';
    }

    echo '<tr><td colspan="2"><hr ></td></tr>';
   }

   echo '</table>';
  }

  private function printEventsTeGast() {
    //check if there are events te gast and print "TE GAST"
    if ($this->daoTeGast->N > 0) {
      echo '<table>';
		  if (!empty($_GET['view']) && $_GET['view'] == "intranet"){
			  echo '<p  style="font-size: 15px;" class="tegast">TE GAST</p>';
		  }
      else {
        echo '<br><br><br>';
        echo '<p  style="font-size: 65px;line-height:90px" class="tegast">Te gast</p>';
    	}

      //get all the 'te gast' events
      while ($this->daoTeGast->fetch()) {
        if (!empty($_GET['view']) && $_GET['view'] == "intranet") {
          echo '<p style="margin:0px"><span style="font-size: 13px;">' . $this->daoTeGast->title . '</span><br><span style="font-size: 12px;">';
        }
        else {
          echo '<tr><td width="70%"><span style="font-size: 50px;line-height:60px"; class="titelevent">' . $this->daoTeGast->title . '</span></td><td><span style="font-size: 45px;line-height: 60px">';
        }

        echo $this->daoTeGast->Startuur . ' -  ' . $this->daoTeGast->Einduur;
        echo ' <br> ' . strtoupper(preg_replace("/\x01/", ", ", substr($this->daoTeGast->Zaal, 1, -1))) . '</span></td></tr>';
        echo '<tr><td colspan="2"><hr ></td></tr>';
      }
    }
    echo '</table>';
  }

  private function printHtmlFooter() {
    echo '
      </body>
      </html>
    ';
  }

  private function getDateDay() {
    return date('d');
  }

  private function getDateMonth() {
    return date('m');
  }

  private function getDateWeekDay() {
    $days = [
      'Monday' => 'maandag',
      'Tuesday' => 'dinsdag',
      'Wednesday' => 'woensdag',
      'Thursday' => 'donderdag',
      'Friday' => 'vrijdag',
      'Saturday' => 'zaterdag',
      'Sunday' => 'zondag'
    ];

    $day = date('l');

    return $days[$day];
  }

  private function getWhereClauseEventsOfToday($period) {
    if ($period == self::WHOLE_DAY) {
      $sqlWhere = "DATE_FORMAT(now(),'%d %M %Y') = DATE_FORMAT(start_date, '%d %M %Y')";
    }
    else {
      $sqlWhere = "(DATE_FORMAT(now(),'%d %M %Y') = DATE_FORMAT(start_date, '%d %M %Y') and end_date > now())";
    }

    return $sqlWhere;
  }

  private function getWhereClauseEventsStillRunning() {
    $sqlWhere = "
      (
        DATE_FORMAT(start_date,'%Y %m %d') < DATE_FORMAT(now(),'%Y %m %d')
      AND
        DATE_FORMAT(end_date,'%Y %m %d') > DATE_FORMAT(now(),'%Y %m %d')
      )
    ";

    return $sqlWhere;
  }

  private function getWhereClauseMuntpuntZaal($conferenceRooms) {
    if (empty($conferenceRooms)) {
      return "d.muntpunt_zalen NOT LIKE '' ";
    }

    if (count($conferenceRooms) == 1) {
      return "d.muntpunt_zalen LIKE '%" . $this->convertConferenceRoom($conferenceRooms[0]) . "%' ";
    }

    $whereClause = '';
    foreach ($conferenceRooms as $conferenceRoom) {
      if ($whereClause != '') {
        $whereClause .= ' OR ';
      }

      $whereClause .= " d.muntpunt_zalen LIKE '%" . $this->convertConferenceRoom($conferenceRoom) . "%' ";
    }

    if ($whereClause) {
      $whereClause = '(' . $whereClause . ')';
    }

    return $whereClause;
  }


  private function getEvents($eventTypeList, $period, $conferenceRooms, $offset, $limit) {
    $eventsOfToday = $this->getWhereClauseEventsOfToday($period);
    $eventsStillRunning = $this->getWhereClauseEventsStillRunning();
    $roomFilter = $this->getWhereClauseMuntpuntZaal($conferenceRooms);

    $sql = "
      SELECT
        title,
        start_date,
        end_date,
        DATE_FORMAT(start_date,'%H:%i') AS Startuur,
        DATE_FORMAT(end_date,'%H:%i') AS Einduur,
        DATE_FORMAT(start_date,'%Y-%m-%d') AS Startdatum,
        DATE_FORMAT(end_date,'%Y-%m-%d') AS Einddatum,
        d.muntpunt_zalen As Zaal,
        d.activiteit_status,
        event_type_id,
        c.label
      FROM
        civicrm_event a
      LEFT JOIN
        civicrm_value_extra_evenement_info d ON a.id = d.entity_id
      LEFT JOIN
        civicrm_option_value c ON event_type_id = c.value
      WHERE
        (
          $eventsOfToday
        OR
          $eventsStillRunning
        )
      AND
        $roomFilter
      AND
        d.activiteit_status IN (2,5)
      AND
        c.option_group_id = 15
      AND
        event_type_id IN ($eventTypeList)
      ORDER BY
        start_date, Startuur, title
	  LIMIT $limit OFFSET $offset
      ";

    \Drupal::service('civicrm')->initialize();
    return \CRM_Core_DAO::executeQuery($sql);
  }

  private function convertConferenceRoom($conferenceRoom) {
    $validRooms = [
      'zinneke' => 'Zinneke S2',
      'zinneke1' => 'Zinneke 1, S2',
      'zinneke2' => 'Zinneke 2, S2',
      'ketje' => 'Ketje S2',
      'literairsalon' => 'Literair Salon S1',
      'mallemunt' => 'Mallemunt S3',
      'wolken' => 'De Wolken +5',
      'tribune' => 'Agora Tribune', 
    ];

    if (array_key_exists($conferenceRoom, $validRooms)) {
      return $validRooms[$conferenceRoom];
    }
    else {
      return 'ONGELDIG';
    }
  }

}
