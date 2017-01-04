<?php
/**
 * @file
 * Contains Drupal\easymeta\Form\EasyMetaForm.
 */

namespace Drupal\easymeta\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\easymeta\Meta;
use Drupal\easymeta\MetaType;
/**
 * Build and process meta form.
 */
class EasyMetaForm extends FormBase {

  protected $metas = [];
  protected $metasType = [];

  /**
   * Implements \Drupal\Core\Form\FormInterface::getFormID().
   */
  public function getFormId() {
    return 'easymeta_form';
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::buildForm().
   */
  public function buildForm(array $form, FormStateInterface $form_state, $configuration = 'NULL') {

    $this->init();

    $current_path = \Drupal::service('path.current')->getPath();
    $current_language = \Drupal::languageManager()->getCurrentLanguage()->getId();

    $meta = new Meta($current_language, $current_path);
    $meta_values = $meta->getValue();

    foreach ($this->metasType as $meta_type) {
      /* @var $meta_type MetaType */

      $value = null;
      if (isset($meta_values[$meta_type->getName()])) {
        $value = ($meta_values[$meta_type->getName()]['value']) ? $meta_values[$meta_type->getName()]['value'] : '';
      }
      $value = (!empty($value)) ? $value : $meta_type->getDefaultValue();

      $form['meta'][$meta_type->getName()] = [
        '#type' => $meta_type->getFieldType(),
        '#title' => $meta_type->getLabel(),
        '#required' => FALSE,
        '#default_value' => $value,
      ];

      if ($meta_type->getFieldType() == "select") {
        $form['meta'][$meta_type->getName()]['#options'] = $meta_type->getOptions();
        $form['meta'][$meta_type->getName()]['#empty_option'] = t("None");
      }

      if ($meta_type->getFieldType() == "managed_file") {
        $form['meta'][$meta_type->getName()][ '#upload_location'] = 'public://';
      }
    }

    $form['meta_id'] = array(
      '#type' => 'hidden',
      '#value' => ($meta->getId()) ? $meta->getId() : '',
    );

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save'),
    );

    $form['markup'] = array(
      '#markup' => '<span class="easymeta-open">' . t("Meta") . '</span>',
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $current_path = \Drupal::service('path.current')->getPath();
    $current_language = \Drupal::languageManager()->getCurrentLanguage()->getId();

    $metas = [];
    if (empty($form_state->getValue('meta_id'))) {
      foreach ($this->metasType as $meta_type) {
        /* @var $meta_type MetaType */
        $metas[$meta_type->getName()]['value'] = $form_state->getValue($meta_type->getName());
        $metas[$meta_type->getName()]['name'] = $meta_type->getName();
        $metas[$meta_type->getName()]['name_property'] = $meta_type->getNameProperty();
        $metas[$meta_type->getName()]['property'] = $meta_type->getProperty();
        $metas[$meta_type->getName()]['tag'] = $meta_type->getTag();

        if ($meta_type->getFieldType() == "managed_file") {
          $this->persistImage($form_state->getValue($meta_type->getName()));
        }
      }
      $meta = new Meta();
      $meta->setUrl($current_path);
      $meta->setLanguage($current_language);
      $meta->setValue($metas);
      $meta->save();
    }
    else {
      foreach ($this->metasType as $meta_type) {
        /* @var $metaType MetaType */
        $metas[$meta_type->getName()]['value'] = $form_state->getValue($meta_type->getName());
        $metas[$meta_type->getName()]['name'] = $meta_type->getName();
        $metas[$meta_type->getName()]['name_property'] = $meta_type->getNameProperty();
        $metas[$meta_type->getName()]['property'] = $meta_type->getProperty();
        $metas[$meta_type->getName()]['tag'] = $meta_type->getTag();

        if ($meta_type->getFieldType() == "managed_file") {
          $this->persistImage($form_state->getValue($meta_type->getName()));
        }
      }
      $meta = new Meta($current_language, $current_path);
      $meta->setId($form_state->getValue('meta_id'));
      $meta->setValue($metas);
      $meta->save();
    }
  }

  private function persistImage($fid) {
    if ($fid) {
      /*dpm($fid);
      $file =  file_load((int)$fid);
      dpm($file);*/
      db_update("file_managed")->fields(["status" => 1])->condition('fid', $fid)->execute();
    }
  }

  /**
   * Init function to build metas to load.
   */
  private function init() {

    $title = new MetaType();
    $title->setFieldType("textfield");
    $title->setLabel(t("Title"));
    $title->setName("title");
    $title->setIsTitle(TRUE);
    array_push($this->metasType, $title);

    $description = new MetaType();
    $description->setFieldType("textarea");
    $description->setLabel(t("Description"));
    $description->setName("description");
    $description->setNameProperty("description");
    $description->setTag("meta");
    $description->setIsTitle(FALSE);
    array_push($this->metasType, $description);


    $ogtitle = new MetaType();
    $ogtitle->setFieldType("textfield");
    $ogtitle->setLabel(t("Og Title"));
    $ogtitle->setName("og_title");
    $ogtitle->setProperty("og:title");
    $ogtitle->setNameProperty(NULL);
    $ogtitle->setTag("meta");
    $ogtitle->setIsTitle(FALSE);
    array_push($this->metasType, $ogtitle);
  /*
    $ogtype = new MetaType();
    $ogtype->setFieldType("select");
    $ogtype->setLabel(t("Og Type"));
    $ogtype->setName("og_type");
    $ogtype->setProperty("og:type");
    $ogtype->setTag("meta");
    $ogtype->setNameProperty(NULL);
    $ogtype->setOptions([
      'article' => 'article',
      'book' => 'book',
      'profile' => 'profile',
      'website' => 'website',
    ]);
    $ogtype->setIsTitle(FALSE);
    array_push($this->metasType, $ogtype);
*/
    /*$ogurl = new MetaType();
    $ogurl->setFieldType("textfield");
    $ogurl->setLabel(t("Og url"));
    $ogurl->setName("og_url");
    $ogurl->setProperty("og:url");
    $ogurl->setTag("meta");
    $ogurl->setNameProperty(NULL);
    $ogurl->setDefaultValue(strtok('http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}", '?'));
    $ogurl->setIsTitle(FALSE);
    array_push($this->metasType, $ogurl);*/

    $ogdescription = new MetaType();
    $ogdescription->setFieldType("textarea");
    $ogdescription->setLabel(t("Og description"));
    $ogdescription->setName("og_description");
    $ogdescription->setProperty("og:description");
    $ogdescription->setTag("meta");
    $ogdescription->setNameProperty(NULL);
    $ogdescription->setIsTitle(FALSE);
    array_push($this->metasType, $ogdescription);

    $ogImage = new MetaType();
    $ogImage->setFieldType("managed_file");
    $ogImage->setLabel(t("Og Image"));
    $ogImage->setName("og_image");
    $ogImage->setProperty("og:image");
    $ogImage->setTag("meta");
    $ogImage->setNameProperty(NULL);
    $ogImage->setIsTitle(FALSE);
    array_push($this->metasType, $ogImage);

    return $this->metasType;
  }

}
