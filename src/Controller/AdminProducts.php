<?php

/**
 *
 * Jan 2018
 * CNC/EX SF Products and Machines Wanted query and node creation
 *
 */


namespace Drupal\admin_products\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;

/**
 * Provides route responses for the Example module.
 */
class AdminProducts extends ControllerBase {

  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function product_list() {

    $adminsf = \Drupal::state()->get('productsf');
    $sfCred = json_decode($adminsf);

    if (strlen($sfCred->adminsf_wsdl) > 0) {
      $defaultFile = \Drupal::entityTypeManager()->getStorage('file')->loadByProperties(['uri' => $sfCred->adminsf_wsdl]);;
      foreach ($defaultFile as $key => $image) {
        $fid = $key;
      }

        if (isset($sfCred)) {
          $SFbuilder = new \Phpforce\SoapClient\ClientBuilder(
            $sfCred->adminsf_wsdl,
            $sfCred->adminsf_user,
            $sfCred->adminsf_pass,
            $sfCred->adminsf_api
          );
          $client = $SFbuilder->build();
       }

    $prodIds = [];
    $wantedIds = [];
    $cust_wantedIds = [];

          // ## COLLECT PRODUCT SALESFORCE IDs
          $results = $client->query("SELECT Id, IsActive FROM Product2");
            foreach ($results as $key => $row) {
              if ($row->IsActive == 1) {
                $prodIds[] = $row->Id;
              }
            }

          // ## COLLECT WANTED SALESFORCE IDs
          $results_wanted = $client->query("SELECT Id FROM Wanted_Machine__c");
            foreach ($results_wanted as $key => $row) {
                $wantedIds[] = $row->Id;
            }

            // An array of published products
            $nids = \Drupal::entityQuery('node')
              ->condition('status', 1)
              ->condition('type', 'product')
              ->execute();
            $inDrupal_Machine = Node::loadMultiple($nids);


           // An array of wanted products
           $inDrupal_Wanted = db_select('node__field_wanted_number', 'w')
                            ->fields('w', array('entity_id'))
                            ->execute()
                            ->fetchAll();

          // Count Arrays
          $inSalesforce_Machines = count($prodIds);
          $inDrupal_Machines = count($inDrupal_Machine);
          $inSalesforce_Wanted = count($wantedIds);
          $inDrupal_Wanted = count($inDrupal_Wanted);


    // Present
    $markup = '<ul>';
    $markup .= '<li>Salesforce Machines: ' . $inSalesforce_Machines;
    $markup .= '<li>Drupal Machines: ' . $inDrupal_Machines;
    $markup .= '<li>Salesforce Wanted: ' . $inSalesforce_Wanted;
    $markup .= '<li>Drupal Wanted: ' . $inDrupal_Wanted;
    $markup .= '</ul>';

    $element = array(
      '#markup' => $markup,
    );

    return $element;
    }
  }
}
