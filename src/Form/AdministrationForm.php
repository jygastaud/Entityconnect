<?php

/**
 * @file
 * Contains Drupal\entityconnect\Form\AdministrationForm.
 */

namespace Drupal\entityconnect\Form;

/**
 * Class DefaultForm.
 *
 * @package Drupal\entityconnect\Form
 */
class AdministrationForm {

    public function __construct(array &$form, array $defaults)
    {
        self::attach($form, $defaults);
        $this->submitButton($form);
        $this->resetButton($form);
    }

    /**
     * Attach the common entityconnect settings to the given form.
     *
     * @param array $form
     * @param array $defaults
     *
     */
    public static function attach(array &$form, array $defaults) {

        $form['entityconnect'] = array(
            '#type' => 'details',
            '#title' => t('EntityConnect default Parameters'),
            '#open' => TRUE,
            '#tree' => TRUE,
        );

        $form['entityconnect']['buttons'] = array(
            '#type' => 'fieldset',
            '#title' => t('Buttons display Parameters'),
            '#weight' => '1',
        );

        $form['entityconnect']['buttons']['button_add'] = array(
            '#required' => '1',
            '#default_value' => $defaults['buttons']['button_add'],
            '#description' => t('Default: "off"<br />
                            Choose "on" if you want the "add" buttons displayed by default.<br />
                            Each field can override this value.'),
            '#weight' => '0',
            '#type' => 'radios',
            '#options' => array(
                '0' => t('on'),
                '1' => t('off'),
            ),
            '#title' => t('Default Entity Connect "add" button display'),
        );

        $form['entityconnect']['buttons']['button_edit'] = array(
            '#required' => '1',
            '#default_value' => $defaults['buttons']['button_edit'],
            '#description' => t('Default: "off"<br />
                            Choose "on" if you want the "edit" buttons displayed by default.<br />
                            Each field can override this value.'),
            '#weight' => '1',
            '#type' => 'radios',
            '#options' => array(
                '0' => t('on'),
                '1' => t('off'),
            ),
            '#title' => t('Default Entity Connect "edit" button display'),
        );

        $form['entityconnect']['icons'] = array(
            '#type' => 'fieldset',
            '#title' => t('Icons display Parameters'),
            '#weight' => '2',
        );

        $form['entityconnect']['icons']['icon_add'] = array(
            '#required' => '1',
            '#key_type_toggled' => '1',
            '#default_value' => $defaults['icons']['icon_add'],
            '#description' => t('Default: "Icon only"<br />
                           Choose "Icon + Text" if you want to see the edit (pencil) icon + the text displayed by default.<br />
                           Choose "Text only" if you don\'t want to see the edit (pencil) icon displayed by default.<br />
                           Each field can override this value.'),
            '#weight' => '0',
            '#type' => 'radios',
            '#options' => array(
                '0' => t('Icon only'),
                '1' => t('Icon + Text'),
                '2' => t('Text only')
            ),
            '#title' => t('Default Entity Connect "add (+) icon" display'),
        );

        $form['entityconnect']['icons']['icon_edit'] = array(
            '#required' => '1',
            '#default_value' => $defaults['icons']['icon_edit'],
            '#description' => t('Default: "Icon only"<br />
                           Choose "Icon + Text" if you want to see the edit (pencil) icon + the text displayed by default.<br />
                           Choose "Text only" if you don\'t want to see the edit (pencil) icon displayed by default.<br />
                           Each field can override this value.'),
            '#weight' => '1',
            '#type' => 'radios',
            '#options' => array(
                '0' => t('Icon only'),
                '1' => t('Icon + Text'),
                '2' => t('Text only')
            ),
            '#title' => t('Default Entity Connect "edit (pencil) icon" display'),
        );
    }

    /**
     * Attach submit button to a form
     */
    private function submitButton(array &$form) {
        $form['submit'] = array(
            '#type' => 'submit',
            '#value' => 'Save',
            '#weight' => '2',
        );
    }

    private function resetButton(array &$form) {
        $form['reset'] = array(
            '#type' => 'submit',
            '#value' => 'Reset to default',
            '#weight' => '3',
        );
    }
}
