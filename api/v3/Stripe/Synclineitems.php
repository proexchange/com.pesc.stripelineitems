<?php
use CRM_Stripelineitems_ExtensionUtil as E;

$private_key = '';


function _civicrm_api3_stripe_Synclineitems_spec(&$spec) {
  $spec['payment_processor_id']['api.required'] = 1;
  $spec['contribution_id']['api.required'] = 0;
  $spec['contribution_date']['api.required'] = 0;
}

function civicrm_api3_stripe_Synclineitems($params) {

  global $private_key;

  $returnValues = array();

  //Get last run on this job
  $jobs = civicrm_api3('Job', 'get', [
    'sequential' => 1,
    'name' => "Stripe Sync Line Items",
    'options' => ['limit' => 1],
  ]);

  if (empty($jobs['count'])) {
    throw new Exception("A scheduled job 'Stripe Sync Line Items' must exist");
  }

  $payment_processor = civicrm_api3('PaymentProcessor', 'getsingle', [
    'id' => intval($params['payment_processor_id']),
  ]);

  $private_key = $payment_processor['password'];

  if (empty($jobs['values'][0]['last_run'])) {
    $last_run = date('Y-m-d 00:00:00',strtotime('-3 days'));
  }
  else {
    $last_run = date('Y-m-d 00:00:00',strtotime($jobs['values'][0]['last_run'].' -3 days'));
  }

  //Get contributions since last run
  $contributions = \Civi\Api4\Contribution::get()
    ->addSelect('trxn_id', 'total_amount', 'contact_id', 'receive_date')
    
    //->addWhere('receive_date', '>=', '2023-04-13 00:00:00')
    ->addOrderBy('receive_date', 'ASC')
    ->setLimit(0)
    ->addChain('transaction', \Civi\Api4\FinancialTrxn::get()
      ->addWhere('trxn_id', '=', '$trxn_id')
      ->addWhere('payment_processor_id', '=', $params['payment_processor_id'])
      ->setLimit(1)
      ->addSelect('trxn_id', 'total_amount', 'payment_processor_id')
    )
    ->addChain('lineitems', \Civi\Api4\LineItem::get()
      ->addWhere('contribution_id', '=', '$id')
      ->addSelect('line_total', 'financial_type_id')
      ->addChain('account', \Civi\Api4\FinancialType::get()
        ->addWhere('id', '=', '$financial_type_id')
        ->addSelect('name', 'description'),
        0)
    );

    //assemble query with params
    if (!empty($params['contribution_id'])) { //single contribution
      $contributions = $contributions->addWhere('id', '=', $params['contribution_id']);
    }
    if (!empty($params['contribution_date'])) { //a whole range using from date
      $contributions = $contributions->addWhere('receive_date', '>=', $params['contribution_date']);
    }
    if (empty($params['contribution_date']) && empty($params['contribution_id'])) { // default last_run
      $contributions = $contributions->addWhere('receive_date', '>=', $last_run);

    }
    //execute
    $contributions = $contributions->execute();

  foreach ($contributions as $ckey => $contribution) {
    if (empty(count($contribution['transaction']))) {
      continue;
    }

    foreach ($contribution['lineitems'] as $lkey => $lineitem) {

      //get account code and move up to parent array
      $account_var = 'description';
      if (empty($lineitem['account']['description'])) {
        $account_var = 'name';
      }

      if (!empty($lineitem['account'][$account_var])) {
        $account_code = $lineitem['account'][$account_var];
        if (empty($contribution['metadata'][$account_code])) {
          $contribution['metadata'][$account_code] = $lineitem['line_total'];
        } 
        else {
          $contribution['metadata'][$account_code] += $lineitem['line_total'];
        }
      }
    }
    $charge['id'] = $contribution['trxn_id'];
    $charge['metadata'] = $contribution['metadata'];

    // Update Stripe charge
    if (count($contribution['transaction'])){
      $update = civicrm_api3_stripe_updateCharge ($charge);

      if (isset($update->error)){
        $returnValues[$contribution['id'].'|'.$contribution['trxn_id']] = (array) $update;
      }
      else {
        $returnValues[$contribution['id'].'|'.$contribution['trxn_id']] = (array) $update->metadata;
      }
    }
    else {
      $returnValues[$contribution['id'].'|'.$contribution['trxn_id']] = 'SKIP No transaction';
    }
  }
  return civicrm_api3_create_success($returnValues, $params, 'Stripe', 'Synclineitems');
}


function civicrm_api3_stripe_updateCharge ($charge) {

  global $private_key;

  $trxn_id = $charge['id'];
  $endpoint = "https://api.stripe.com/v1/charges/".$trxn_id;

  // Initialize a cURL session
  $ch = curl_init($endpoint);

  // Set the Bearer token header
  curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer $private_key"));

  //format array in the keys stripe likes
  $metadata = array();
  foreach ($charge['metadata'] as $account => $value) {
    $metadata['metadata['.$account.']']=$value;
  }

  //POST data
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, 
    http_build_query($metadata)
  );

  // Return the transfer as a string
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

  // Execute the cURL request
  $response = json_decode(curl_exec($ch));

  // Close the cURL session
  curl_close($ch);

  // return the response
  return $response;
}









