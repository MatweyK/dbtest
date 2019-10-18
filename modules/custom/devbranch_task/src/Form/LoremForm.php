<?php

namespace Drupal\devbranch_task\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class for lorem form.
 */
class LoremForm extends FormBase {

  /**
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Class constructor.
   *
   * @param \Drupal\Core\Datetime\DateFormatterInterface $dateFormatter
   * @param \Drupal\Component\Datetime\TimeInterface $time
   */
  public function __construct(DateFormatterInterface $dateFormatter, TimeInterface $time, RendererInterface $renderer) {
    $this->dateFormatter = $dateFormatter;
    $this->time = $time;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
    // Load the service required to construct this class.
      $container->get('date.formatter'),
      $container->get('datetime.time'),
      $container->get('renderer')
    );
  }

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
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Wrapper for error messages.
    $form["wrapper"] = ["#markup" => "<div id='test-ajax'></div>"];
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
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $quantity = $form_state->getValue('quantity');
    if (empty($quantity)) {
      $form_state->setErrorByName('quantity', 'quantity field can not be empty');
    }
    parent::validateForm($form, $form_state);
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
    $messages = ['#type' => 'status_messages'];
    $response = new AjaxResponse();
    if ($form_state->hasAnyErrors()) {
      $response->addCommand(new HtmlCommand('#test-ajax', $this->renderer
        ->renderRoot($messages)));
      if ($form_state->getError($form['quantity'])) {
        $fieldCss = [
          'border' => '1px solid #e62600',
          'background-color' => 'hsla(15,75%,97%,1)',
        ];
        $response->addCommand(new CssCommand('#edit-quantity', $fieldCss));
      }
      return $response;
    }
    $quantity = $form_state->getValue('quantity');
    // Date options.
    $request_time = $this->time->getCurrentTime();
    $now = $this->dateFormatter->format($request_time, 'custom', 'Y-m-d, H:i:s');
    $time = strtotime($now);
    // Minus three hours 10 min.
    $time = $time - 11400;
    $beforeThreeHours = $this->dateFormatter->format($time, 'custom', 'Y-m-d, D H:i:s, M');
    $config = \Drupal::config('devbranch_task.settings');
    $pattern = $config->get('devbranch_task.pattern');
    $renderHtml['#source_text'] = [];
    $renderHtml['#source_text'][] = $beforeThreeHours;
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
        $renderHtml['#source_text'][] = $value;
      }
    }
    else {
      $repertory = explode(' ', $pattern);
      $words = '';
      foreach ($repertory as $key => $value) {
        $newValue = trim(trim($value, " "), ".,\t\n\r\0\x0B");
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
      $renderHtml['#source_text'][] = ucfirst(trim(mb_strtolower($words))) . ".";
    }
    $renderHtml['#theme'] = 'devbranch_task';
    if (!empty($messages)) {
      $response->addCommand(new HtmlCommand('#test-ajax', ''));
      $fieldCss = [
        'border' => '1px solid #ccc',
        'background-color' => '#fff',
      ];
      $response->addCommand(new CssCommand('#edit-quantity', $fieldCss));
    }

    $response->addCommand(
      new HtmlCommand(
        '.result_message',
        $renderHtml
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
