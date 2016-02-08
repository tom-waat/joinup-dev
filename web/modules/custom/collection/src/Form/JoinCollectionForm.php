<?php

/**
 * @file
 * Contains \Drupal\collection\Form\JoinCollectionForm.
 */

namespace Drupal\collection\Form;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Url;
use Drupal\collection\CollectionInterface;
use Drupal\collection\Entity\Collection;
use Drupal\og\Og;
use Drupal\og\OgGroupAudienceHelper;
use Drupal\og\OgMembershipInterface;
use Drupal\user\Entity\User;

/**
 * A simple form with a button to join or leave a collection.
 *
 * @package Drupal\collection\Form
 */
class JoinCollectionForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'join_collection_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, AccountProxyInterface $user = NULL, CollectionInterface $collection = NULL) {
    $user = User::load($user->id());
    $form['collection_id'] = [
      '#type' => 'hidden',
      '#title' => $this->t('Collection ID'),
      '#value' => $collection->id(),
    ];
    $form['user_id'] = [
      '#type' => 'hidden',
      '#title' => $this->t('User ID'),
      '#value' => $user->id(),
    ];

    // If the user is already a member of the collection, show a link to the
    // confirmation form, disguised as a form submit button. The confirmation
    // form should open in a modal dialog for JavaScript-enabled browsers.
    if (Og::isMember($collection, $user)) {
      $form['leave'] = [
        '#type' => 'link',
        '#title' => $this->t('Leave this collection'),
        '#url' => Url::fromRoute('collection.leave_confirm_form', [
          'collection' => $collection->id(),
        ]),
        '#attributes' => [
          'class' => ['use-ajax', 'button', 'button--small'],
          'data-dialog-type' => 'modal',
          'data-dialog-options' => Json::encode(['width' => 'auto']),
        ],
      ];
      $form['#attached']['library'][] = 'core/drupal.ajax';
    }

    // If the user is not yet a member, show the join button.
    else {
      $form['join'] = [
        '#type' => 'submit',
        '#value' => $this->t('Join this collection'),
      ];
    }

    // This form varies by user and collection.
    $metadata = new CacheableMetadata();
    $metadata
      ->merge(CacheableMetadata::createFromObject($user))
      ->merge(CacheableMetadata::createFromObject($collection))
      ->applyTo($form);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $collection = Collection::load($form_state->getValue('collection_id'));

    // Only authenticated users can join a collection.
    /** @var \Drupal\user\UserInterface $user */
    $user = User::load($form_state->getValue('user_id'));
    if ($user->isAnonymous()) {
      $form_state->setErrorByName('user', $this->t('<a href=":login">Log in</a> or <a href=":register">register</a> to change your group membership.', [
        ':login' => Url::fromRoute('user.login'),
        ':register' => Url::fromRoute('user.register'),
      ]));
    }

    // Check if the user is already a member of the collection.
    if (Og::isMember($collection, $user)) {
      $form_state->setErrorByName('collection', $this->t('You already are a member of this collection.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var CollectionInterface $collection */
    $collection = Collection::load($form_state->getValue('collection_id'));
    /** @var \Drupal\user\UserInterface $user */
    $user = User::load($form_state->getValue('user_id'));

    $membership = Og::membershipStorage()->create(Og::membershipDefault());
    $membership
      ->setFieldName(OgGroupAudienceHelper::DEFAULT_FIELD)
      ->setMemberEntityType('user')
      ->setMemberEntityId($user->id())
      ->setGroupEntityType('collection')
      ->setGroupEntityid($collection->id())
      ->setState(OgMembershipInterface::STATE_ACTIVE)
      ->save();

    drupal_set_message($this->t('You are now a member of %collection.', [
      '%collection' => $collection->getName(),
    ]));
  }

}