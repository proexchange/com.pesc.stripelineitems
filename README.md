# com.pesc.stripelineitems

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

* PHP v7.4+
* CiviCRM (5.0)


## Installation (CLI, Zip)

Sysadmins and developers may download the `.zip` file for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
cd <extension-dir>
cv dl com.pesc.stripelineitems@https://github.com/proexchange/com.pesc.stripelineitems/archive/master.zip
```

## Installation (CLI, Git)

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone https://github.com/proexchange/com.pesc.stripelineitems.git
cv en stripelineitems
```


## Setup

Creates a new api action on the Stripe entity. You need to create a scheduled job named "Stripe Sync Line Items" that uses the entity "Stripe" and the action "synclineitems". 

Parameters must include "payment_processor_id". This is used to look up the api key and filters only for contribitions processed with that payment processor. It should be set to run daily. 

## Misc

Once activated, the scheduled job will process lines items from the current day onward. If you would like to backfill you can run the the stripe>synclineitems api with the "payment_processor_id" and "contribution_date" paramaters, with the "contribution_date" set to how far bac you want to backfill. 

Example:
```bash
$result = civicrm_api3('Stripe', 'synclineitems', [
  'payment_processor_id' => 10,
  'contribution_date' => "2023-05-01",
]);
```
