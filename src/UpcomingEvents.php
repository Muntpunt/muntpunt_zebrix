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

  public function printUpcomingEvents() {
    $this->fillDao();

    $this->printHtmlHeader();
    $this->printTodaysDate();
    $this->printEvents();
    $this->printEventsTeGast();
    $this->printHtmlFooter();
  }

  private function fillDao() {
    $this->daoEvents = $this->getEvents(self::EVENT_TYPE_NORMAL, self::WHOLE_DAY);
    $this->daoTeGast = $this->getEvents(self::EVENT_TYPE_TE_GAST, self::WHOLE_DAY);

    if ($this->daoEvents->N + $this->daoTeGast->N >= self::MAX_NUM_EVENTS) {
      $this->daoEvents = $this->getEvents(self::EVENT_TYPE_NORMAL, self::FROM_NOW);
      $this->daoTeGast = $this->getEvents(self::EVENT_TYPE_TE_GAST, self::FROM_NOW);
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
    if ($_GET['view'] == "intranet"){	 	  
	    echo '<p style="font-size: 14px;" class="datumvandaag">';
    }else{
	    echo '<p style="font-size: 54px;" class="datumvandaag">';
    }
    echo $this->getDateWeekDay() . ' ';
    echo $this->getDateDay() . ' ';
    echo $this->getDateMonth();
    echo '</p>';
  }

  private function printEvents() {
    while ($this->daoEvents->fetch()) {
      if ($_GET['view'] == "intranet"){
         echo '<p style="margin: 0px";><span style="font-size: 13px;">' . $this->daoEvents->title . '</span><br><span style="font-size: 12px;">';
      }else{
         echo '<p><span style="font-size: 40px;">' . $this->daoEvents->title . '</span><br><span style="font-size: 32px;">';
      }	      
      $today = date('Y-m-d');
      $einddatum = $this->daoEvents->Einddatum;
      if ($einddatum !== $today) {
        echo 'DOORLOPEND';
      }
      else {
        echo $this->daoEvents->Startuur . ' -  ' . $this->daoEvents->Einduur;
      }

      echo ' / ' . strtoupper(preg_replace("/\x01/",", ",substr($this->daoEvents->Zaal,1,-1))) . '</span></p>';
      echo '<hr >';
    }
  }

  private function printEventsTeGast() {
  if ($this->daoTeGast->N > 0) {
	if ($_GET['view'] == "intranet"){	    
		echo '<p  style="font-size: 15px;" class="tegast">TE GAST</p>';
	}else{
		echo '<p  style="font-size: 60px;" class="tegast">TE GAST</p>';
	}
      while ($this->daoTeGast->fetch()) {
	 if ($_GET['view'] == "intranet"){
		echo '<p style="margin:0px"><span style="font-size: 13px;">' . $this->daoTeGast->title . '</span><br/><span style="font-size: 12px;">';
	}else{
		echo '<p><span style="font-size: 40px;">' . $this->daoTeGast->title . '</span><br/><span style="font-size: 32px;">';
	}
        echo $this->daoTeGast->Startuur . ' -  ' . $this->daoTeGast->Einduur;
        echo ' / ' . strtoupper(preg_replace("/\x01/", ", ", substr($this->daoTeGast->Zaal, 1, -1))) . '</span></p>';
        echo '<hr >';
      }
    }
  }

  private function printHtmlFooter() {
    echo '
      </body>
      </html>
    ';
  }

  private function getDateDay() {
    return date('j');
  }

  private function getDateMonth() {
    $months = [
      'January' => 'JANUARI',
      'February' => 'FEBRUARI',
      'March' => 'MAART',
      'April' => 'APRIL',
      'May' => 'MEI',
      'June' => 'JUNI',
      'July' => 'JULI',
      'August' => 'AUGUSTUS',
      'September' => 'SEPTEMBER',
      'October' => 'OKTOBER',
      'November' => 'NOVEMBER',
      'December' => 'DECEMBER'
    ];

    $month = date('F');

    return $months[$month];
  }

  private function getDateWeekDay() {
    $days = [
      'Monday' => 'MAANDAG',
      'Tuesday' => 'DINSDAG',
      'Wednesday' => 'WOENSDAG',
      'Thursday' => 'DONDERDAG',
      'Friday' => 'VRIJDAG',
      'Saturday' => 'ZATERDAG',
      'Sunday' => 'ZONDAG'
    ];

    $day = date('l');

    return $days[$day];
  }

  private function getWhereClauseEventsOfToday($period) {
    if ($period == self::WHOLE_DAY) {
      $sqlWhere = "DATE_FORMAT(now(),'%d %M %Y') = DATE_FORMAT (start_date, '%d %M %Y')";
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


  private function getEvents($eventTypeList, $period) {
    $eventsOfToday = $this->getWhereClauseEventsOfToday($period);
    $eventsStillRunning = $this->getWhereClauseEventsStillRunning();

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
        d.muntpunt_zalen NOT LIKE ''
      AND
        d.activiteit_status IN (2,5)
      AND
        c.option_group_id = 15
      AND
        event_type_id IN ($eventTypeList)
      ORDER BY
        start_date, Startuur, title
      LIMIT
        0,8
    ";

    \Drupal::service('civicrm')->initialize();
    return \CRM_Core_DAO::executeQuery($sql);
  }

}
