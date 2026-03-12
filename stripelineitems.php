<?php

require_once 'stripelineitems.civix.php';
// phpcs:disable
use CRM_Stripelineitems_ExtensionUtil as E;
// phpcs:enable

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function stripelineitems_civicrm_config(&$config): void {
  _stripelineitems_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function stripelineitems_civicrm_install(): void {
  _stripelineitems_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function stripelineitems_civicrm_enable(): void {
  _stripelineitems_civix_civicrm_enable();
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 */
//function stripelineitems_civicrm_preProcess($formName, &$form): void {
//
//}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
//function stripelineitems_civicrm_navigationMenu(&$menu): void {
//  _stripelineitems_civix_insert_navigation_menu($menu, 'Mailings', [
//    'label' => E::ts('New subliminal message'),
//    'name' => 'mailing_subliminal_message',
//    'url' => 'civicrm/mailing/subliminal',
//    'permission' => 'access CiviMail',
//    'operator' => 'OR',
//    'separator' => 0,
//  ]);
//  _stripelineitems_civix_navigationMenu($menu);
//}

/*
function stripelineitems_civicrm_post ($op, $objectName, $objectId, &$objectRef) {
  if ( ($op == 'create' || 'edit') && ($objectName == 'Contribution') ) {

    //Get job so we can get mandatory param 'payment_processor_id' for api call
    $jobs = civicrm_api3('Job', 'get', [
      'sequential' => 1,
      'name' => "Stripe Sync Line Items",
      'options' => ['limit' => 1],
    ]);

    if (empty($jobs['count'])) {
      throw new Exception("A scheduled job 'Stripe Sync Line Items' must exist");
    }

    $job_params = array();

    $asArr = explode( PHP_EOL, $jobs['values'][0]['parameters'] );

    foreach( $asArr as $val ){
      $tmp = explode( '=', $val );
      $job_params[ $tmp[0] ] = $tmp[1];
    }

    $update_stripe = civicrm_api3('Stripe', 'synclineitems', [
      'contribution_id' => $objectId,
      'payment_processor_id' => intval($job_params['payment_processor_id']),
    ]);
    Civi::log()->debug('contribution updated via post hook $update_stripe:'.print_r($update_stripe,1));
  }
}
*/