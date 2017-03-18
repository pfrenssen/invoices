<?php

/**
 * @file
 * Drush integration for the Business module.
 */

use Drupal\business\Entity\Business;

/**
 * Implements hook_drush_command().
 */
function business_drush_command() {
  return [
    'business-create' => [
      'description' => 'Create a new business and link it to a user.',
      'arguments' => [
        'business' => 'Name of the business to create.',
        'user' => 'Name of the user the business will be linked to.',
      ],
      'options' => [
        'email' => [
          'description' => 'Email address',
          'example-value' => 'business@example.com',
        ],
        'country' => [
          'description' => 'Country code',
          'example-value' => 'BE',
        ],
        'address' => [
          'description' => 'Address',
          'example-value' => 'Grote Markt 7',
        ],
        'postal-code' => [
          'description' => 'Postal code',
          'example-value' => '1000',
        ],
        'locality' => [
          'description' => 'Locality',
          'example-value' => 'Brussel',
        ],
        'vat' => [
          'description' => 'VAT number',
          'example-value' => 'BE0123456789',
        ],
        'phone' => [
          'description' => 'Phone number',
          'example-value' => '+3223456789',
        ],
        'mobile' => [
          'description' => 'Mobile phone number',
          'example-value' => '+32475286372',
        ],
        'bic' => [
          'description' => 'BIC number',
          'example-value' => 'BBBBEBB',
        ],
        'iban' => [
          'description' => 'IBAN number',
          'example-value' => 'BE68273690082883',
        ],
      ],
      'drupal dependencies' => ['business'],
      'examples' => [
        'drush business-create \'My Company\' \'business_owner\'' => 'Creates a company names \'My Company\' and links it to the user \'business_owner\'.',
      ],
      'required-arguments' => TRUE,
      'category' => 'invoicing',
    ],
  ];
}

/**
 * Drush command. Creates a business.
 */
function drush_business_create($business_name, $user_name) {
  if ($user = user_load_by_name($user_name)) {
    $values = [
      'name' => $business_name,
      'field_business_email' => drush_get_option('email'),
      'field_business_address' => [
        'country_code' => drush_get_option('country'),
        'address_line1' => drush_get_option('address'),
        'postal_code' => drush_get_option('postal-code'),
        'locality' => drush_get_option('locality'),
      ],
      'field_business_vat' => drush_get_option('vat'),
      'field_business_bic' => drush_get_option('bic'),
      'field_business_iban' => drush_get_option('iban'),
    ];

    if ($phone = drush_get_option('phone')) {
      $values['field_business_phone'] = drush_get_option('phone');
    }

    if ($mobile = drush_get_option('mobile')) {
      $values['field_business_mobile'] = drush_get_option('mobile');
    }

    $business = Business::create($values);
    $business->setOwner($user);
    $business->save();
  }
  else {
    drush_set_error('User not found.');
  }
}
