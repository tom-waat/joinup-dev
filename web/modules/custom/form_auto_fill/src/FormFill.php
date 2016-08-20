<?php

namespace Drupal\form_auto_fill;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * FormFill service.
 */
class FormFill {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The form Id.
   *
   * @var string
   */
  protected $formId;

  /**
   * The entity that the form is about.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * Form auto fill settings.
   *
   * @var array
   */
  protected $config;

  /**
   * Creates a FormFill service.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
    $this->config = $this->configFactory->get('form_auto_fill.settings');
  }

  /**
   * Whether the service applies and the entity is valid for auto fill.
   *
   * @return bool
   *    Whether the service applies.
   */
  public function applies() {
    return $this->isEnabled() && (!empty($this->entity)) && ($this->entity instanceof ContentEntityInterface);
  }

  /**
   * Checks if the auto fill service is enabled.
   *
   * @return bool
   *    Whether the service is enabled.
   */
  public function isEnabled() {
    return $this->config->get('activate_auto_fill');
  }

  /**
   * Checks if the manual fill is activated.
   *
   * @return bool
   *    If true, manual fill is activated, otherwise it is automatic.
   */
  public function isManual() {
    return $this->config->get('manual_fill');
  }

  /**
   * Checks if the service has an entity passed.
   *
   * @return bool
   *    Whether the service has an entity.
   */
  public function hasEntity() {
    return !empty($this->entity);
  }

  /**
   * Returns the form ID.
   *
   * @return string
   *    The form ID.
   */
  public function getFormId() {
    return $this->formId;
  }

  /**
   * Sets the form ID.
   *
   * @param string $formId
   *    The form ID.
   */
  public function setFormId($formId) {
    $this->formId = $formId;
  }

  /**
   * Returns the entity saved in the service.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *    The entity object.
   */
  public function getEntity() {
    return $this->entity;
  }

  /**
   * Sets the entity object to the service.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *    The entity object.
   */
  public function setEntity(EntityInterface $entity) {
    $this->entity = $entity;
  }

  /**
   * Process the settings to determine whether to fill the entity.
   */
  public function processEntity() {
    // If the auto fill is disabled, return.
    if ($this->config->get('activate_auto_fill') == FALSE) {
      return;
    }

    if (!$this->config->get('manual_fill')) {
      $this->fillEntityFields();
    }
  }

  /**
   * Fills the entity's fields with random values.
   *
   * Does not change already existing values.
   */
  public function fillEntityFields() {
    // Skip fields handled by core (label will still be filled later on).
    $keys_to_skip = $this->entity->getEntityType()->getKeys();
    $auto_fill_types = $this->config->get('fields_to_fill');

    if (in_array('label', $auto_fill_types)) {
      $this->entity->label->generateSampleItems();
    }

    $field_definitions = $this->entity->getFieldDefinitions();
    foreach ($field_definitions as $id => $field_definition) {
      if (in_array($id, $keys_to_skip)) {
        continue;
      }

      if ($field_definition->isRequired() && in_array('required', $auto_fill_types)) {
        $this->entity->{$id}->generateSampleItems();
      }
      elseif ((!$field_definition->isRequired()) && in_array('optional', $auto_fill_types)) {
        $this->entity->{$id}->generateSampleItems();
      }
    }
  }

}
