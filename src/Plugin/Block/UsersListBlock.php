<?php

namespace Drupal\users_list\Plugin\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'Users List' block.
 *
 * @Block(
 *   id = "users_list",
 *   admin_label = @Translation("Users List"),
 * )
 */
class UsersListBlock extends BlockBase {
	/**
   * {@inheritdoc}
   */
  public function build() {
    // Retrieve existing configuration for this block.
    $config = $this->getConfiguration();
    $total_record = isset($config['how_many_users']) ? $config['how_many_users'] : 5;
    $sql = 'SELECT uid, name FROM {users_field_data}';
    $start = 2; // Skip anonymous and admin user.
    $results = db_query_range($sql, $start, $total_record);

    // Loop through the user object.
    foreach ($results as $result) {
        $url = Url::fromUri('internal:/user/' . $result->uid);
        $link_options = [
          'attributes' => ['title' => $result->name],
        ];
        $url->setOptions($link_options);
        $list[] = Link::fromTextAndUrl(t($result->name), $url);
    }

    // Return.
    return [
      '#theme' => 'item_list',
      '#list_type' => 'ol',
      '#items' => $list,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
  
    // Retrieve existing configuration for this block.
    $config = $this->getConfiguration();
    $values = [5, 10, 15];
    $options = array_combine($values, $values);

    $form['how_many_users'] = [
      '#type' => 'select',
      '#title' => $this->t('How many users do you wanna show on block'),
      '#options' => $options,
      '#default_value' => isset($config['how_many_users']) ? $config['how_many_users'] : '',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    // Save our custom settings when the form is submitted.
    $this->setConfigurationValue('how_many_users', $form_state->getValue('how_many_users'));
  }
}