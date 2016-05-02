<?php

namespace Drupal\rdf_entity\Entity\Query\Sparql;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Query\QueryBase;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Entity\Query\Sql\ConditionAggregate;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\rdf_entity\Database\Driver\sparql\Connection;

/**
 * The base entity query class for Rdf entities.
 */
class Query extends QueryBase implements QueryInterface {
  protected $defaultGraph = NULL;

  protected $sortQuery = NULL;

  /** @var null Set to the entity id when explicit filtering on id. */
  protected $entity_id = NULL;

  /** @var \Drupal\rdf_entity\Entity\Query\Sparql\SparqlArg List of bundles */
  protected $bundles = NULL;

  public $query = '';

  /**
   * Filters.
   *
   * @var \Drupal\Core\Entity\Query\ConditionInterface
   */
  protected $filter;

  protected $results = NULL;

  /** @var \Drupal\rdf_entity\Entity\RdfEntitySparqlStorage $entityStorage */
  protected $entityStorage;

  /**
   * Constructs a query object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param string $conjunction
   *   - AND: all of the conditions on the query need to match.
   *   - OR: at least one of the conditions on the query need to match.
   * @param \Drupal\rdf_entity\Database\Driver\sparql\Connection $connection
   *   The database connection to run the query against.
   * @param array $namespaces
   *   List of potential namespaces of the classes belonging to this query.
   */
  public function __construct(EntityTypeInterface $entity_type, $conjunction, Connection $connection, array $namespaces) {
    parent::__construct($entity_type, $conjunction, $namespaces);
    $this->filter = new SparqlFilter();
    $this->connection = $connection;
    // @todo rename graph to a proper uri.
    $this->defaultGraph = new SparqlArg('http://localhost:8890/DAV', SparqlArg::URI);

    // @todo Inject properly...

    $this->entityStorage = \Drupal::service('entity.manager')
      ->getStorage('rdf_entity');
  }

  public function setDefaultGraph($uri) {
    $this->defaultGraph = new SparqlArg($uri);
  }

  /**
   * {@inheritdoc}
   */
  public function count($field = TRUE) {
    $this->count = $field;
    return $this;
  }

  /**
   * Implements \Drupal\Core\Entity\Query\QueryInterface::execute().
   */
  public function execute() {
    return $this
      ->prepare()
      ->addConditions()
      ->addSort()
      ->addPager()
      ->run()
      ->result();
  }

  /**
   * Initialize the query.
   *
   * @return $this
   */
  protected function prepare() {
    // Running as count query?
    if ($this->count) {
      if (is_string($this->count)) {
        $this->query = 'SELECT count(' . $this->count . ') AS ?count ';
      }
      else {
        $this->query = 'SELECT count(?entity) AS ?count ';
      }
    }
    else {
      $this->query = 'SELECT ?entity ';
    }
    $this->query .= "\n";
    return $this;
  }

  /**
   * Add the registered conditions to the WHERE clause.
   *
   * @return $this
   */
  protected function addConditions() {
    if ($this->defaultGraph) {
      $this->query .= 'FROM ' . $this->defaultGraph . "\n";
    }
    $this->query .=
      "WHERE{\n";
    // Handling of base properties.
    // Entity id.
    $entity_arg = '?entity';
    if ($this->entity_id) {
      $entity_arg = SparqlArg::uri($this->entity_id);
    }
    if (!$this->bundles) {
      $this->condition->condition($entity_arg, 'rdf:type', '?type');
    }
    elseif ($this->bundles->getType() == SparqlArg::URI) {
      $this->condition->condition($entity_arg, 'rdf:type', $this->bundles);
    }
    elseif ($this->bundles->getType() == SparqlArg::URI_LIST) {
      $this->condition->condition($entity_arg, 'rdf:type', '?type');
      $this->filter->filter('?entity IN ' . $this->bundles);
    }
    else {
      throw new \Exception('Bundle should be either a uri or a list of uris.');
    }
    $this->condition->compile($this);
    $this->filter->compile($this);
    $this->query .= "}\n";

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function filter($filter, $type = 'FILTER') {
    $this->filter->filter($filter, $type);
    return $this;
  }

  function condition_value($field_rdf_name, $value) {
    switch ($field_rdf_name) {
      // Bundle types are passed in as Drupal entity types and need conversion.
      case 'rdf:type':
        if (is_array($value)) {
          $bundles = $this->entityStorage->getRdfBundleList($value);
        }
        else {
          
        }
        var_dump($bundles);
        var_dump($value);

    }
    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public function condition($field, $value = NULL, $operator = '=', $langcode = NULL) {
    $field_rdf_name = $this->predicateFromField($field);
    $value = $this->condition_value($field_rdf_name, $value);
    $value = new SparqlArg($value);
    /** @todo Get rid of org_value */
    $org_value = $value;



    $key = $field . '-' . $operator;

    /*
     * Ok, so what is all this:
     * We need to convert our conditions into some sparql compatible conditions.
     */
    switch ($key) {
//      case 'rid-IN':
//        $rdf_bundles = $entity_storage->getRdfBundleList($org_value);
//        if ($rdf_bundles) {
//          $this->condition->condition('?entity', 'rdf:type', '?type');
//          $this->filter->filter('?type IN ' . $rdf_bundles);
//        }
//        return $this;

//      case 'rid-=':
//        $mapping = $entity_storage->getRdfBundleMapping();
//        $mapping = array_flip($mapping);
//        $bundle = $mapping[$value];
//        if ($bundle) {
//          $this->condition->condition('?entity', 'rdf:type', SparqlArg::uri($bundle));
//        }
//        return $this;

      case 'id-IN':
        if ($value) {
          if ($value->getType() != SparqlArg::URI_LIST) {
            throw new \Exception('Trying to run an IN query on a non-list.');
          }
          $this->filter->filter('?entity IN (' . $value . ')');
        }
        return $this;

      case 'id-NOT IN':
      case 'id-<>':
        if ($value) {
          $this->filter->filter('!(?entity IN ' . $value . ')');
        }
        return $this;

      case 'id-=':
        if (!$value) {
          return $this;
        }
        // @todo $this->entity_id = $value
        $this->condition->condition('?entity', 'rdf:type', '?type');
        $this->filter->filter('?entity IN ' . $value);
        break;

      case 'label-=':
        preg_match('/\((.*?)\)/', $value, $matches);
        $matching = array_pop($matches);
        if ($matching) {
          $ids = "(<$matching>)";
          $this->filter->filter('?entity IN ' . $ids);
        }
        else {
          if (file_valid_uri($value)) {
            $ids = "(<$value>)";
            $this->filter->filter('?entity IN ' . $ids);
          }
          else {
            $mapping = $this->entityStorage->getLabelMapping();
            $label_list = "(<" . implode(">, <", array_unique(array_values($mapping))) . ">)";
            $this->condition->condition('?entity', '?label_type', '?label');
            $this->filter->filter('?label_type IN ' . $label_list);
            $this->filter->filter('regex(?label, "' . $value . '", "i")');
          }
        }

        return $this;

      case 'label-CONTAINS':
        $mapping = $this->entityStorage->getLabelMapping();
        $label_list = "(<" . implode(">, <", array_unique(array_values($mapping))) . ">)";
        $this->condition->condition('?entity', '?label_type', '?label');
        $this->filter->filter('?label_type IN ' . $label_list);
        if ($value) {
          $this->filter->filter('regex(?label, "' . $value . '", "i")');
        }
        return $this;

      // @TODO This looks wrong. Abusing the field name for other logic?
      case '_field_exists-EXISTS':
      case '_field_exists-NOT EXISTS':
        if (!filter_var($field_rdf_name, FILTER_VALIDATE_URL) === FALSE) {
          $field_rdf_name = SparqlArg::uri($field_rdf_name);
        }
        if ($field_rdf_name) {
          $this->filter('?entity ' . $field_rdf_name . ' ?c', 'FILTER ' . $operator);
        }
        return $this;

    }
    if ($operator == '=') {
      if (!$value) {
        return $this;
      }
      $this->condition->condition('?entity', SparqlArg::uri($field_rdf_name), $value);
    }

    return $this;
  }

  /**
   * Get the RDF property corresponding to a given property (field).
   *
   * @param string $field
   *    A field name (with possible property, e.g. field_body.format)
   * @return mixed|null
   *    An RDF predicate.
   * @throws \Exception
   */
  protected function predicateFromField($field) {
    // @todo Get from injected manager.
    $field_storage_definitions = \Drupal::service('entity.manager')
      ->getFieldStorageDefinitions('rdf_entity');
    $property_parts = explode('.', $field);
    $field_name = $property_parts[0];
    $column = NULL;
    if (isset($property_parts[1])) {
      $column = $property_parts[1];
    }

    if (empty($field_storage_definitions[$field_name])) {
      throw new \Exception('Unknown field ' . $field_name);
    }
    /** @var \Drupal\field\Entity\FieldStorageConfig $field_storage */
    $field_storage = $field_storage_definitions[$field_name];
    if (empty($column)) {
      $column = $field_storage->getMainPropertyName();
    }
    if ($field_storage instanceof BaseFieldDefinition) {
      return $this->predicateFromBaseField($field);
      // var_dump($field_storage);
      // $field_rdf_name = $field_storage->getThirdPartySetting('rdf_entity', 'mapping_' . $column, FALSE);
      //die();
    }
    else {
      //var_dump($field_storage); die();
      $field_rdf_name = $field_storage->getThirdPartySetting('rdf_entity', 'mapping_' . $column, FALSE);
    }

    if (empty($field_rdf_name)) {
      throw new \Exception('No 3rd party field settings for ' . $field_name);
    }
    return $field_rdf_name;
  }

  function predicateFromBaseField($field) {
    switch ($field) {
      case 'rid':
        return 'rdf:type';
      default:
        throw new \Exception('Unimplemented base field' . $field);
    }
  }

  /**
   * Returns an rdf property name for the given field.
   *
   * @param string $field_name
   *   The machine name of the field.
   * @param array $field_storage_definitions
   *   The field storage definition Item.
   *
   * @return string
   *   The property name of the field. If it is a uri, wrap it with '<', '>'.
   *
   * @throws \Exception
   *   Thrown when the field has not a valid rdf property name.
   */
  public function getFieldRdfPropertyName($field_name, $field_storage_definitions) {
    if (empty($field_storage_definitions[$field_name])) {
      throw new \Exception('Unknown field ' . $field_name);
    }
    /** @var \Drupal\field\Entity\FieldStorageConfig $field_storage */
    $field_storage = $field_storage_definitions[$field_name];
    if (empty($column)) {
      $column = $field_storage->getMainPropertyName();
    }
    $field_rdf_name = $field_storage->getThirdPartySetting('rdf_entity', 'mapping_' . $column, FALSE);
    if (empty($field_rdf_name)) {
      throw new \Exception('No 3rd party field settings for ' . $field_name);
    }

    return $field_rdf_name;
  }

  /**
   * Adds the sort to the build query.
   *
   * @return \Drupal\rdf_entity\Entity\Query\Sparql\Query
   *   Returns the called object.
   */
  protected function addSort() {
    if (!$this->sortQuery) {
      return $this;
    }
    if ($this->count) {
      $this->sort = array();
    }
    // Simple sorting. For the POC, only uri's and bundles are supported.
    // @todo Implement sorting on bundle fields?
    if ($this->sort) {
      $sort = array_pop($this->sort);
      switch ($sort['field']) {
        case 'id':
          $this->query .= 'ORDER BY ' . $sort['direction'] . ' (?entity)';
          break;

        case 'rid':
          $this->query .= 'ORDER BY ' . $sort['direction'] . ' (?bundle)';
          break;
      }
    }
    return $this;
  }

  /**
   * Add pager to query.
   */
  protected function addPager() {
    $this->initializePager();
    if (!$this->count && $this->range) {
      $this->query .= 'LIMIT ' . $this->range['length'] . "\n";
      $this->query .= 'OFFSET ' . $this->range['start'] . "\n";
    }
    return $this;
  }

  /**
   * Commit the query to the backend.
   */
  protected function run() {
    /** @var \EasyRdf_Http_Response $results */
    $this->results = $this->connection->query($this->query);
    return $this;
  }

  /**
   * Do the actual query building.
   */
  protected function result() {
    // Count query.
    if ($this->count) {
      foreach ($this->results as $result) {
        return (string) $result->count;
      }
    }
    $uris = [];

    // SELECT query.
    foreach ($this->results as $result) {
      // If the query does not return any results, EasyRdf_Sparql_Result still
      // contains an empty result object. If this is the case, skip it.
      if (!empty((array) $result)) {
        $uri = (string) $result->entity;
        $uris[$uri] = $uri;
      }
    }
    return $uris;
  }

  /**
   * {@inheritdoc}
   */
  public function existsAggregate($field, $function, $langcode = NULL) {
    return $this->conditionAggregate->exists($field, $function, $langcode);
  }

  /**
   * {@inheritdoc}
   */
  public function notExistsAggregate($field, $function, $langcode = NULL) {
    return $this->conditionAggregate->notExists($field, $function, $langcode);
  }

  /**
   * {@inheritdoc}
   */
  public function conditionAggregateGroupFactory($conjunction = 'AND') {
    return new ConditionAggregate($conjunction, $this);
  }

  /**
   * {@inheritdoc}
   */
  protected function conditionGroupFactory($conjunction = 'AND') {
    $class = static::getClass($this->namespaces, 'SparqlCondition');
    return new $class($conjunction, $this, $this->namespaces);
  }

  /**
   * Return the query string for debugging help.
   *
   * @return string
   *   Query.
   */
  public function __toString() {
    return $this->query;
  }

}
