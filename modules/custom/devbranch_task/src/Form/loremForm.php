<?php

namespace Drupal\devbranch_task\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class for lorem form.
 */
class LoremForm extends FormBase {

  /**
   * Returns a unique string identifying the form.
   *
   * The returned ID should be a unique string that can be a valid PHP function
   * name, since it's used in hook implementation names such as
   * hook_form_FORM_ID_alter().
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'devbranch_task_form';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['quantity'] = [
      '#title' => $this->t('Chose quantity of words/paragraphs'),
      '#type' => 'number',
      '#min' => '1',
      '#max' => '100',
    ];
    $form['type'] = [
      '#type' => 'radios',
      '#title' => $this
        ->t('Chose type of generated content'),
      '#default_value' => 0,
      '#options' => [
        0 => $this
          ->t('Paragraphs'),
        1 => $this
          ->t('Words'),
      ],
    ];
    $form['actions'] = [
      '#type' => 'submit',
      '#value' => $this->t('Generate'),
      '#ajax' => [
        'callback' => '::generate',
      ],
    ];
    $form['message'] = [
      '#type' => 'markup',
      '#markup' => '<div class="result_message"></div>',
    ];
    return $form;
  }

  /**
   * Form validation.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $quantity = $form_state->getValue('quantity');
    if (empty($quantity)) {
      $form_state->setError($form['quantity'], 'quantity field can not be empty');
    }
  }

  /**
   * Ajax response.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return object
   *   The form structure.
   */
  public function generate(array &$form, FormStateInterface $form_state) {
    $messages = \Drupal::messenger()->messagesByType('error');
    if ($form_state->hasAnyErrors() || !empty($messages)) {
      // If the form has errors, reload it.
      $response = new AjaxResponse();
      $response->addCommand(new RedirectCommand('http://lorem.loc/devbranch-task'));
      return $response;
    }
    $quantity = $form_state->getValue('quantity');
    $response = new AjaxResponse();
    // Date options.
    $now = date('m/d/Y h:i:s a', time());
    $time = strtotime($now);
    // Minus three hours.
    $time = $time - 11400;
    $beforeThreeHours = date('Y-m-d, D H:i:s, M', $time);

    $config = \Drupal::config('devbranch_task.settings');
    $pattern = $config->get('devbranch_task.pattern');

    $renderHtml = '<div>' . 'Generated: ' . $beforeThreeHours . '</div>' . '<br>';
    // If paragraphs.
    if ($form_state->getValue('type') === '0') {

      $repertory = explode(PHP_EOL, $pattern);
      $paragraphs = [];

      for ($i = 1; $i <= $quantity; $i++) {
        $thisParagraph = '';
        $random_phrases = mt_rand(2, 10);
        // don't repeat the last phrase.
        $last_number = 0;
        $next_number = 0;
        for ($j = 1; $j <= $random_phrases; $j++) {
          do {
            $next_number = floor(mt_rand(0, count($repertory) - 1));
          } while ($next_number === $last_number && count($repertory) > 1);
          $thisParagraph .= $repertory[$next_number] . ' ';
          $last_number = $next_number;
        }
        $paragraphs[] = $thisParagraph;
      }

      foreach ($paragraphs as $key => $value) {
        $renderHtml .= '<div >' . $value . '</div>' . '<br>';
      }
    }
    else {
      $repertory = explode(' ', $pattern);
      $words = '';
      foreach ($repertory as $key => $value) {
        $newValue = trim($value, '.,');
        $newRepertory[] = $newValue;
      }

      for ($i = 1; $i <= $quantity; $i++) {
        $last_number = 0;
        $next_number = 0;
        do {
          $next_number = floor(mt_rand(0, count($newRepertory) - 1));
        } while ($next_number === $last_number && count($newRepertory) > 1);
        $words .= $newRepertory[$next_number] . ' ';
        $last_number = $next_number;
      }
      $renderHtml .= ucfirst(trim(mb_strtolower($words))) . ".";
    }

    $response->addCommand(
      new HtmlCommand(
        '.result_message',
        [
          '#type' => 'markup',
          '#markup' => $renderHtml,
        ]
      )
    );
    return $response;
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // TODO: Implement submitForm() method.
  }

}
