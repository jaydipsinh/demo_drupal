<?php

namespace Drupal\general_section\EventSubscriber;

use \Drupal\Core\EventSubscriber\FinishResponseSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
	
class HeaderResponseSubscriber implements EventSubscriberInterface {
  public function onRespond(FilterResponseEvent $event) {
    $response = $event->getResponse();
	$response->headers->set('X-DRCC-No-Cache-Reason', '');
	$response->headers->set('Last-Modified',gmdate('D, d M Y H:i:s') . ' GMT');
    $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, pre-check=0, post-check=0, max-age=0'); 
    $response->headers->set('Pragma', 'no-cache'); 
    $response->headers->set('Expires', 'Thu, 19 Nov 1981 08:52:00 GMT');	
  }

  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = array('onRespond');
    return $events;
  }
}
?>