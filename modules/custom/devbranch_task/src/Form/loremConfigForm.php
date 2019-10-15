<?php
  /**
   * @file
   * Contains \Drupal\devbranch_task\Form\loremConfigForm
   */

namespace Drupal\devbranch_task\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class loremConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'devbranch_task_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Form constructor.
    $form = parent::buildForm($form, $form_state);
    // Default settings.
    $config = $this->config('devbranch_task.settings');

    $form['pattern'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Pattern for lorem ipsum generation:'),
      '#default_value' => $config->get('devbranch_task.pattern'),
      '#description' => $this->t('Write one sentence per line. Those sentences will be used to generate random text.'),
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
    $config = $this->config('devbranch_task.settings');
    $config->set('devbranch_task.pattern', $form_state->getValue('pattern'));
    $config->save();
    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'devbranch_task.settings',
    ];
  }

}