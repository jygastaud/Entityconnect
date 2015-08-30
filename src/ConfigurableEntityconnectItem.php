<?php

namespace Drupal\entityconnect;

use Drupal\Core\Config\Config;
use Drupal\entity_reference\ConfigurableEntityReferenceItem;
use Drupal\Core\Form\FormStateInterface;


class ConfigurableEntityconnectItem extends ConfigurableEntityReferenceItem {

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $field = $form_state->getFormObject()->getEntity();

    $form = parent::fieldSettingsForm($form, $form_state);

    $form['entityconnect'] = array(
      '#type' => 'details',
      '#title' => $this->t('EntityConnect default Parameters'),
      '#open' => TRUE,
      '#tree' => TRUE,
    );

    $form['entityconnect']['button'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Buttons display Parameters'),
    );

    $form['entityconnect']['button']['button_add'] = array(
      '#required' => '1',
      '#default_value' => $this->getSetting('button_add'),
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

    $form['entityconnect']['button']['button_edit'] = array(
      '#required' => '1',
      '#default_value' => $this->getSetting('button_edit'),
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

    $form['entityconnect']['icon'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Icons display Parameters'),
    );

    $form['entityconnect']['icon']['icon_add'] = array(
      '#required' => '1',
      '#key_type_toggled' => '1',
      '#default_value' => $this->getSetting('icon_add'),
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

    $form['entityconnect']['icon']['icon_edit'] = array(
      '#required' => '1',
      '#default_value' => $this->getSetting('icon_edit'),
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

    return $form;
  }

  public function setSettings() {

  }

}
