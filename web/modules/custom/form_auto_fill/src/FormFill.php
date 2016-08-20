<?php

namespace Drupal\form_auto_fill;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;

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
   * @var EntityInterface
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
   * @return boolean
   */
  public function applies() {
    return $this->isEnabled() && (!empty($this->entity)) && ($this->entity instanceof ContentEntityInterface);
  }

  /**
   * @return boolean
   */
  public function isEnabled() {
    return $this->config->get('activate_auto_fill');
  }

  /**
   * @return boolean
   */
  public function isManual() {
    return $this->config->get('manual_fill');
  }

  /**
   * @return bool
   */
  public function hasEntity() {
    return !empty($this->entity);
  }

  /**
   * @return string
   */
  public function getFormId() {
    return $this->formId;
  }

  /**
   * @param string $formId
   */
  public function setFormId($formId) {
    $this->formId = $formId;
  }

  /**
   * @return \Drupal\Core\Entity\EntityInterface
   */
  public function getEntity() {
    return $this->entity;
  }

  /**
   * @param \Drupal\Core\Entity\EntityInterface $entity
   */
  public function setEntity($entity) {
    $this->entity = $entity;
  }

  public function processEntity() {
    // If the auto fill is disabled, return.
    if ($this->config->get('activate_auto_fill') == FALSE) {
      return;
    }

    if (!$this->config->get('manual_fill')) {
      $this->fillEntityFields();
    }
  }

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