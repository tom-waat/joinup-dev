<?php

namespace Drupal\rdf_entity\Entity\Query\Sparql;
use Drupal\Component\Utility\UrlHelper;

/**
 * Class SparqlArg.
 *
 * Wrap Sparql arguments. This provides a central point for escaping.
 *
 * @todo Return SparqlArgument objects in order to distinguish between
 * raw strings and sanitized ones. Query should expect objects.
 *
 * @package Drupal\rdf_entity\Entity\Query\Sparql
 */
class SparqlArg {

  const URI = 0;
  const LITERAL = 1;
  const URI_LIST = 2;

  protected $value;

  protected $type;

  function __construct($value, $type = NULL) {
    $this->value = $value;
    $this->determineType($value);
    if ($type && $type != $this->type) {
      throw new \Exception('Actual argument type does not match the expected type.');
    }
  }

  function __toString() {
    switch ($this->type) {
      case $this::URI:
        return self::uri($this->value);

      case $this::LITERAL:
        return self::literal($this->value);

      case $this::URI_LIST:
        return self::uri_list($this->value);
    }
    throw new \Exception('Unsupported type.');
  }

  protected function determineType($value) {
    if (is_string($value)) {
      if (UrlHelper::isValid($value, TRUE)) {
        return $this->type = $this::URI;
      }
      return $this->type = $this::LITERAL;
    }
    if (is_array($value)) {
      foreach ($value as $item) {
        if ($this->determineType($item) != $this::URI) {
          throw new \Exception('List supplied, but not all items are valid uris.');
        }
      }
      $this->type = $this::URI_LIST;
    }
    return $this->type;
  }

  public function getType() {
    return $this->type;
  }

  /**
   * @todo Turn into protected method.
   * URI Query argument.
   *
   * @param string $uri
   *    A valid URI to use as a query parameter.
   *
   * @return string
   *    Sparql validated URI.
   *
   * @throws \Exception
   *    Inform the user that $uri variable is not a URI.
   */
  public static function uri($uri) {
    if (!UrlHelper::isValid($uri)) {
      throw new \Exception(t('Provided value is not a URI: %value', ['%value' => $uri]));
    }
    return '<' . $uri . '>';
  }

  /**
   * @todo Turn into protected method.
   * Literal Query argument.
   *
   * @param string $value
   *    An unescaped text string to use as a Sparql query.
   *
   * @return string
   *    Sparql escaped string literal.
   */
  public static function literal($value) {
    // @todo Support all xml data types, as well as language extensions.
    $matches = 1;
    while ($matches) {
      $matches = 0;
      $value = str_replace('"""', '', $value, $matches);
    }

    return '"""' . $value . '"""';
  }

  /**
   * @todo Turn into protected method.
   * @param $list
   * @return string
   * @throws \Exception
   */
  public static function uri_list($list) {
    $uri_list = [];
    foreach ($list as $item) {
      $uri_list[] = self::uri($item);
    }
    return "<" . implode(">, <", $uri_list) . ">";
  }

}
