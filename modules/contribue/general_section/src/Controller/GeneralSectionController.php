<?php

namespace Drupal\general_section\Controller;

use Drupal\general_section\GeneralSectionManager;
use Drupal\general_section\GeneralSectionManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Ajax;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\Core\Routing\TrustedRedirectResponse;

/**
 * Returns responses for autologout module routes.
 */
class GeneralSectionController extends ControllerBase {

  /**
   * The autologout manager service.
   *
   * @var \Drupal\autologout\AutologoutManagerInterface
   */
  protected $GeneralSectionManager;

  /**
   * Constructs an AutologoutSubscriber object.
   *
   * @param \Drupal\autologout\AutologoutManagerInterface $autologout
   *   The autologout manager service.
   */
//  public function __construct(GeneralSectionManagerInterface $autoimport) {
//    $this->GeneralSectionManager = $autoimport;
//  }

  public function myredirect($nid){
	  echo $nid;exit;
  }
    /**
   * AJAX callback that performs the actual logout and redirects the user.
   */
  public function getcomoperations($tid) {

$oquery = db_query("SELECT n.nid,field_parent_legal_target_id,
(select nd1.title from node_field_data nd1 where nd1.nid = np.field_parent_legal_target_id) as data,nd.title,
nc.field_type_commercial_target_id
FROM `node` n
inner join node_field_data nd on nd.nid =n.nid
inner join node__field_type_commercial nc on nc.entity_id = n.nid
left join node__field_parent_legal np on np.entity_id = n.nid
where n.type = 'commercial_operations'
and nc.field_type_commercial_target_id = 298
");
$mydt = $oquery->fetchAll();
echo '<pre>';print_r($mydt);exit;

    \Drupal\general_section\GeneralSectionManager::getcontentfromtaxo($tid);

    //$response = new AjaxResponse();
    //$response->setStatusCode(200);
    //return $response;
  }

 

}
