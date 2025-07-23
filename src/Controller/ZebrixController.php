<?php

namespace Drupal\muntpunt_zebrix\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\muntpunt_zebrix\UpcomingEvents;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ZebrixController extends ControllerBase {
  protected $upcomingEvents;

  /**
   * @param \Drupal\muntpunt_zebrix\UpcomingEvents $upcomingEvents
   */
  public function __construct(UpcomingEvents $upcomingEvents) {
    $this->upcomingEvents = $upcomingEvents;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('muntpunt_zebrix.upcomingEvents')
    );
  }

  public function show() {
    $conferenceRooms = $this->getConferenceRooms();

    $this->upcomingEvents->printUpcomingEvents($conferenceRooms);
    exit;
  }

  private function getConferenceRooms() {
    $rooms = [];

    if (!empty($_GET['locatie'])) {
      if ($_GET['locatie'] == 'achterbouw') {
        $rooms = [
          'zinneke',
          'zinneke1',
          'zinneke2',
          'ketje',
          'literairsalon',
          'mallemunt',
        ];
      }
       if ($_GET['locatie'] == '2deachterbouw') {
        $rooms = [
          'zinneke',
          'zinneke1',
          'zinneke2',
          'ketje'
          ];
      }

    }

    if (!empty($_GET['zaal'])) {
      if ($_GET['zaal'] == 'zinneke1' || $_GET['zaal'] == 'zinneke2') {
        $rooms = [
          'zinneke',
          $_GET['zaal'],
        ];
      }
      else {
        $rooms = [
          $_GET['zaal'],
        ];
      }
    }

    return $rooms;
  }

}
