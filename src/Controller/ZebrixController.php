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
    $this->upcomingEvents->printUpcomingEvents();
    exit;
  }

}
