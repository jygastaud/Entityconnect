<?php

/**
 * @file
 * Contains Drupal\entityconnect\Form\AdministrationForm.
 */

namespace Drupal\entityconnect\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class DefaultForm.
 *
 * @package Drupal\entityconnect\Form
 */
class AdministrationForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'entityconnect.administration_config'
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'entityconnect_administration_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('entityconnect.administration_config');

    $form['entityconnect'] = array(
      '#type' => 'details',
      '#title' => $this->t('EntityConnect default Parameters'),
      '#open' => TRUE,
      '#tree' => TRUE,
    );

    $form['entityconnect']['buttons'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Buttons display Parameters'),
    );

    $form['entityconnect']['buttons']['button_add'] = array(
      '#required' => '1',
      '#default_value' => $config->get('buttons.button_add'),
      '#description' => $this->t('Default: "off"<br />
                            Choose "on" if you want the "add" buttons displayed by default.<br />
                            Each field can override this value.'),
      '#weight' => '0',
      '#type' => 'radios',
      '#options' => array(
        '0' => $this->t('on'),
        '1' => $this->t('off'),
      ),
      '#title' => $this->t('Default Entity Connect "add" button display'),
    );

    $form['entityconnect']['buttons']['button_edit'] = array(
      '#required' => '1',
      '#default_value' => $config->get('buttons.button_edit'),
      '#description' => $this->t('Default: "off"<br />
                            Choose "on" if you want the "edit" buttons displayed by default.<br />
                            Each field can override this value.'),
      '#weight' => '1',
      '#type' => 'radios',
      '#options' => array(
        '0' => $this->t('on'),
        '1' => $this->t('off'),
      ),
      '#title' => $this->t('Default Entity Connect "edit" button display'),
    );

    $form['entityconnect']['icons'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Icons display Parameters'),
    );

    $form['entityconnect']['icons']['icon_add'] = array(
      '#required' => '1',
      '#key_type_toggled' => '1',
      '#default_value' => $config->get('icons.icon_add'),
      '#description' => $this->t('Default: "Icon only"<br />
                           Choose "Icon + Text" if you want to see the edit (pencil) icon + the text displayed by default.<br />
                           Choose "Text only" if you don\'t want to see the edit (pencil) icon displayed by default.<br />
                           Each field can override this value.'),
      '#weight' => '2',
      '#type' => 'radios',
      '#options' => array(
        '0' => $this->t('Icon only'),
        '1' => $this->t('Icon + Text'),
        '2' => $this->t('Text only')
      ),
      '#title' => $this->t('Default Entity Connect "add (+) icon" display'),
    );

    $form['entityconnect']['icons']['icon_edit'] = array(
      '#required' => '1',
      '#default_value' => $config->get('icons.icon_edit'),
      '#description' => $this->t('Default: "Icon only"<br />
                           Choose "Icon + Text" if you want to see the edit (pencil) icon + the text displayed by default.<br />
                           Choose "Text only" if you don\'t want to see the edit (pencil) icon displayed by default.<br />
                           Each field can override this value.'),
      '#weight' => '3',
      '#type' => 'radios',
      '#options' => array(
        '0' => $this->t('Icon only'),
        '1' => $this->t('Icon + Text'),
        '2' => $this->t('Text only')
      ),
      '#title' => $this->t('Default Entity Connect "edit (pencil) icon" display'),
    );

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

    $this->config('entityconnect.administration_config')
      ->set('icons.icon_add', $form_state->getValue(array('entityconnect', 'icons', 'icon_add')))
      ->set('icons.icon_edit', $form_state->getValue(array('entityconnect', 'icons', 'icon_edit')))
      ->set('buttons.button_add', $form_state->getValue(array('entityconnect', 'buttons', 'button_add')))
      ->set('buttons.button_edit', $form_state->getValue(array('entityconnect', 'buttons', 'button_edit')))
      ->save();
  }

}
