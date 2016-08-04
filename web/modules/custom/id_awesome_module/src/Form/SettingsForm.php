<?php

namespace Drupal\id_awesome_module\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SettingsForm.
 *
 * @package Drupal\id_awesome_module\Form
 */
class SettingsForm extends ConfigFormBase {

  /**
   * SettingsForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *    The config factory object.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    parent::__construct($config_factory);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'id_awesome_module.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'id_awesome_module_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('id_awesome_module.settings');
    $form['activate_auto_fill'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled'),
      '#default_value' => $config->get('activate_auto_fill'),
      '#weight' => 0,
    ];
    $form['fields_to_fill'] = [
      '#type' => 'select',
      '#title' => $this->t('Fields to autofill'),
      '#options' => [
        'label' => $this->t('Label'),
        'required' => $this->t('Required'),
        'optional' => $this->t('Optional in form'),
      ],
      '#multiple' => TRUE,
      '#size' => 4,
      '#default_value' => $config->get('fields_to_fill'),
      '#weight' => 1,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('id_awesome_module.settings')
      ->set('fields_to_fill', $form_state->getValue('fields_to_fill'))
      ->set('activate_auto_fill', $form_state->getValue('activate_auto_fill'))
      ->save();
  }

}
