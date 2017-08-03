<?php

namespace Drupal\joinup_notification;

/**
 * Define events for the joinup notification module.
 */
final class NotificationEvents {

  /**
   * An event that sends notifications on community content CRUD operations.
   *
   * @Event
   *
   * @var string
   */
  const COMMUNITY_CONTENT_CRUD = 'joinup_notification.cc.notify';

  /**
   * An event that sends notifications on RDF entity CRUD operations.
   *
   * @Event
   *
   * @var string
   */
  const RDF_ENTITY_CRUD = 'joinup_notification.rdf.notify';

  /**
   * An event that sends notifications on comment CRUD operations.
   *
   * @Event
   *
   * @var string
   */
  const COMMENT_CRUD = 'joinup_notification.comment.notify';

  /**
   * An event that sends notifications when a membership state is changed.
   *
   * @Event
   *
   * @var string
   */
  const OG_MEMBERSHIP_MANAGEMENT = 'joinup_notification.og_membership.management';

}
