<?php

namespace Drupal\form_auto_fill\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class FormAutoFillForm.
 *
 * @package Drupal\form_auto_fill\Form
 */
class FormAutoFillForm extends EntityForm {
  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $form_auto_fill = $this->entity;
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $form_auto_fill->label(),
      '#description' => $this->t("Label for the Form auto fill."),
      '#required' => TRUE,
    );

    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $form_auto_fill->id(),
      '#machine_name' => array(
        'exists' => '\Drupal\form_auto_fill\Entity\FormAutoFill::load',
      ),
      '#disabled' => !$form_auto_fill->isNew(),
    );

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $form_auto_fill = $this->entity;
    $status = $form_auto_fill->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Form auto fill.', [
          '%label' => $form_auto_fill->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Form auto fill.', [
          '%label' => $form_auto_fill->label(),
        ]));
    }
    $form_state->setRedirectUrl($form_auto_fill->urlInfo('collection'));
  }

}
