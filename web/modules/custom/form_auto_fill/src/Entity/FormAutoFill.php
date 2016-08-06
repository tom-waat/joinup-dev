<?php

namespace Drupal\form_auto_fill\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\form_auto_fill\FormAutoFillInterface;

/**
 * Defines the Form auto fill entity.
 *
 * @ConfigEntityType(
 *   id = "form_auto_fill",
 *   label = @Translation("Form auto fill"),
 *   handlers = {
 *     "list_builder" = "Drupal\form_auto_fill\FormAutoFillListBuilder",
 *     "form" = {
 *       "add" = "Drupal\form_auto_fill\Form\FormAutoFillForm",
 *       "edit" = "Drupal\form_auto_fill\Form\FormAutoFillForm",
 *       "delete" = "Drupal\form_auto_fill\Form\FormAutoFillDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\form_auto_fill\FormAutoFillHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "form_auto_fill",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/form_auto_fill/{form_auto_fill}",
 *     "add-form" = "/admin/structure/form_auto_fill/add",
 *     "edit-form" = "/admin/structure/form_auto_fill/{form_auto_fill}/edit",
 *     "delete-form" = "/admin/structure/form_auto_fill/{form_auto_fill}/delete",
 *     "collection" = "/admin/structure/form_auto_fill"
 *   }
 * )
 */
class FormAutoFill extends ConfigEntityBase implements FormAutoFillInterface {
  /**
   * The Form auto fill ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Form auto fill label.
   *
   * @var string
   */
  protected $label;

}
