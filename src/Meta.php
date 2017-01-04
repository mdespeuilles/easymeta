<?php
/**
 * @file
 * Contains Drupal\easymeta\Meta.
 */

namespace Drupal\easymeta;
/**
 * Meta Class.
 */
class Meta {

  protected $id;
  protected $value;
  protected $url;
  protected $language;

  /**
   * Construct method.
   */
  public function __construct($language = NULL, $url = NULL) {
    if ($language && $url) {
      $metas = $this->getMetasForUrlAndLanguage($url, $language);
      if ($metas) {
        $this->id = $metas->emid;
        $this->value = $metas->metas;
        $this->url = $url;
        $this->language = $language;
      }
    }
  }

  /**
   * Get Meta value.
   *
   * @return mixed
   *   Meta Value.
   */
  public function getValue() {
    return unserialize($this->value);
  }

  /**
   * Set Meta value.
   *
   * @param mixed $value
   *   The Meta value.
   */
  public function setValue($value) {
    $this->value = serialize($value);
  }

  /**
   * Get Meta Value.
   *
   * @return mixed
   *   The meta url.
   */
  public function getUrl() {
    return $this->url;
  }

  /**
   * Set Meta url.
   *
   * @param mixed $url
   *   The meta url.
   */
  public function setUrl($url) {
    $this->url = $url;
  }

  /**
   * Get Meta Language.
   *
   * @return mixed
   *   Meta language.
   */
  public function getLanguage() {
    return $this->language;
  }

  /**
   * Set Meta language.
   *
   * @param mixed $language
   *   Meta Language.
   */
  public function setLanguage($language) {
    $this->language = $language;
  }

  /**
   * Get Meta Id.
   *
   * @return mixed
   *   Meta Id.
   */
  public function getId() {
    return $this->id;
  }

  /**
   * Set Meta Id.
   *
   * @param mixed $id
   *   Meta Id.
   */
  public function setId($id) {
    $this->id = $id;
  }

  /**
   * Save and persist Meta.
   */
  public function save() {
    $array = [
      "url" => $this->getUrl(),
      "language" => $this->getLanguage(),
      "metas" => serialize($this->getValue()),
    ];

    if ($this->id) {
      return db_update("easymeta")->fields($array)->condition('emid', $this->id)->execute();
    }
    else {
      return db_insert("easymeta")->fields($array)->execute();
    }
  }

  /**
   * Retrieve Meta from $url and $language.
   */
  public function getMetasForUrlAndLanguage($url, $language) {
    $q = db_select("easymeta", "em");
    $q->fields('em');
    $q->condition('url', $url);
    $q->condition('language', $language);

    return $q->execute()->fetch();
  }

}
