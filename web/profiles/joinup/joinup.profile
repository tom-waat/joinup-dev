<?php
/**
 * @file
 * Enables modules and site configuration for the Joinup profile.
 */

use \Drupal\Core\Form\FormStateInterface;
use \Drupal\Core\Database\Database;

/**
 * Implements hook_form_FORMID_alter().
 *
 * Add the Sparql endpoint fields to the configure database install step.
 */
function joinup_form_install_settings_form_alter(&$form, FormStateInterface $form_state) {
  $form['sparql'] = array(
    '#type' => 'fieldset',
    '#title' => 'Sparql endpoint',
    '#tree' => TRUE,
  );
  $form['sparql']['host'] = array(
    '#type' => 'textfield',
    '#title' => 'Host',
    '#default_value' => 'localhost',
    '#size' => 45,
    '#required' => TRUE,
  );
  $form['sparql']['port'] = array(
    '#type' => 'number',
    '#title' => 'Port',
    '#default_value' => '8890',
    '#min' => 0,
    '#max' => 65535,
    '#required' => TRUE,
  );

  $form['actions']['save']['#limit_validation_errors'][] = ['sparql'];
  $form['actions']['save']['#submit'][] = 'joinup_form_install_settings_form_save';
}

/**
 * Submit callback: Save the Sparql connection string to the settings file.
 */
function joinup_form_install_settings_form_save($form, FormStateInterface $form_state) {
  $host = $form_state->getValue(['sparql', 'host']);
  $port = $form_state->getValue(['sparql', 'port']);
  // @see rdf_entity.services.yml
  $key = 'sparql_default';
  $target = 'sparql';
  $database = array(
    'prefix' => '',
    'host' => $host,
    'port' => $port,
    'namespace' => 'Drupal\\rdf_entity\\Database\\Driver\\sparql',
    'driver' => 'sparql',
  );
  $settings['databases'][$key][$target] = (object) array(
    'value' => $database,
    'required' => TRUE,
  );
  drupal_rewrite_settings($settings);
  // Load the database connection to make it available in the current request.
  Database::addConnectionInfo($key, $target, $database);
}

/**
 * Implements hook_entity_type_alter().
 */
function joinup_entity_type_alter(array &$entity_types) {
  // Add the "Propose" form operation to nodes and RDF entities so that we can
  // add propose form displays to them.
  /** @var \Drupal\Core\Entity\EntityTypeInterface[] $entity_types */
  $entity_types['rdf_entity']->setFormclass('propose', 'Drupal\rdf_entity\Form\RdfForm');
}

/**
 * Implements hook_form_FORM_ID_alter().
 */
function joinup_form_field_config_edit_form_alter(&$form) {
  // Increase the maximum length of the file extension field to allow
  // registration of large amounts of extensions.
  if (isset($form['settings']['file_extensions']['#maxlength'])) {
    $form['settings']['file_extensions']['#maxlength'] = 1024;
  }
}
