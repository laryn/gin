<?php
/**
 * @file
 * Gin preprocess functions and theme function overrides.
 */

/**
 * Load include files which contain additional theming logic.
 */
foreach (glob(path_to_theme('gin') . '/includes/*.php') as $file) {
  include $file;
}

/**
 * Implements hook_preprocess_layout().
 */
function gin_preprocess_layout(&$variables) {
  if (gin_content_form_paths()) {
    $variables['classes'][] = 'edit-form--sidebar';
  }
  if (config_get('admin_bar.settings', 'position_fixed')) {
    $variables['classes'][] = 'admin-bar--fixed';
  }
}

/**
 * Implements hook_preprocess_HOOK() for form.
 */
function gin_preprocess_form(&$variables) {
  if (theme_get_setting('sticky_action_buttons', 'gin')) {
    $exclude_form_ids = gin_ignore_sticky_form_actions();
    $form_id = backdrop_html_class($variables['element']['#form_id']);
    if (!in_array($form_id, $exclude_form_ids)) {
      backdrop_add_js(array('Gin' => array('actions_form_id' => $form_id)), 'setting');
      backdrop_add_library('gin', 'gin_more_actions', TRUE);
    }
  }
}

/**
 * Overrides theme_breadcrumb().
 */
function gin_breadcrumb($variables) {
  $breadcrumbs = $variables['breadcrumb'];
  $output = '';
  if (!empty($breadcrumbs)) {
    $output .= '<nav role="navigation" class="gin-breadcrumb" aria-labelledby="system-breadcrumb">';
    $output .= '<h2 id="system-breadcrumb" class="visually-hidden">Breadcrumb</h2>';
    $output .= '<ol class="gin-breadcrumb__list">';
    foreach ($breadcrumbs as $breadcrumb) {
      $breadcrumb = str_replace('<a ', '<a class="gin-breadcrumb__link" ', $breadcrumb);
      $output .= '<li class="gin-breadcrumb__item">' . $breadcrumb . '</li>';
    }
    $output .= '</ol>';
    $output .= '</nav>';
  }
  return $output;
}

/**
 * Overrides theme_tablesort_indicator().
 *
 * @param $variables
 *   An associative array containing:
 *   - style: Set to either 'asc' or 'desc', this determines which icon to
 *     show.
 */
function gin_tablesort_indicator($variables) {
  return '<span class="tablesort tablesort--' . $variables['style'] . '">
    <span class="visually-hidden">
      Sort ' . $variables['style'] . 'ending
        </span>
    </span>';
}


/**
 * Overrides theme_menu_local_action().
 */
function gin_menu_local_action($variables) {
  $link = $variables['element']['#link'];
  $classes = array(
    'button',
    'button-action',
    'button-primary',
  );
  if (!empty($link['localized_options']['attributes']['class']) && is_array($link['localized_options']['attributes']['class'])) {
    $link['localized_options']['attributes']['class'] += $classes;
  }
  else {
    $link['localized_options']['attributes']['class'] = $classes;
  }

  $output = '<li class="local-actions__item">';
  if (isset($link['href'])) {
    $output .= l($link['title'], $link['href'], isset($link['localized_options']) ? $link['localized_options'] : array());
  }
  elseif (!empty($link['localized_options']['html'])) {
    $output .= $link['title'];
  }
  else {
    $output .= check_plain($link['title']);
  }
  $output .= "</li>\n";

  return $output;
}

/**
 * Overrides theme_menu_local_actions().
 */
function gin_menu_local_actions($variables) {
  $output = backdrop_render($variables['actions']);
  if ($output) {
    $output = '<ul class="local-actions">' . $output . '</ul>';
  }
  return $output;
}

/**
 * Overrides theme_menu_local_task().
 */
function gin_menu_local_task($variables) {
  $link = $variables['element']['#link'];
  $link_text = $link['title'];

  if (!empty($variables['element']['#active'])) {
    // Add text to indicate active tab for non-visual users.
    $active = '<span class="visually-hidden">' . t('(active tab)') . '</span>';

    // If the link does not contain HTML already, check_plain() it now.
    // After we set 'html'=TRUE the link will not be sanitized by l().
    if (empty($link['localized_options']['html'])) {
      $link['title'] = check_plain($link['title']);
    }
    $link['localized_options']['html'] = TRUE;
    $link_text = t('!local-task-title!active', array('!local-task-title' => $link['title'], '!active' => $active));
    $link['localized_options']['attributes']['class'][] = 'is-active';
  }
  $link['localized_options']['attributes']['class'][] = 'tabs__link';
  $link['localized_options']['attributes']['class'][] = 'js-tabs-link';
  $output = '<li class="tabs__tab js-tab' . (!empty($variables['element']['#active']) ? ' is-active js-active-tab' : '') . '">';
  $output .= l($link_text, $link['href'], $link['localized_options']);
  $output .= "</li>\n";
  return  $output;
}

/**
 * Overrides theme_menu_local_tasks().
 */
function gin_menu_local_tasks($variables) {
  $output = '';

  if (!empty($variables['primary'])) {
    $variables['primary']['#prefix'] = '<h2 id="primary-tabs-title" class="visually-hidden">' . t('Primary tabs') . '</h2>';
    $variables['primary']['#prefix'] .= '<ul class="tabs tabs--primary clearfix">';
    $variables['primary']['#suffix'] = '</ul>';
    $output .= backdrop_render($variables['primary']);
  }
  if (!empty($variables['secondary'])) {
    $variables['secondary']['#prefix'] = '<h2 id="secondary-tabs-title" class="visually-hidden">' . t('Secondary tabs') . '</h2>';
    $variables['secondary']['#prefix'] .= '<ul class="tabs tabs--secondary clearfix">';
    $variables['secondary']['#suffix'] = '</ul>';
    $output .= backdrop_render($variables['secondary']);
  }

  return $output;
}

/**
 * Overrides theme_update_status_label().
 *
 * Returns HTML for a label to display for a project's update status.
 *
 * @param array $variables
 *   An associative array containing:
 *   - status: The integer code for a project's current update status.
 *
 * @see update_calculate_project_data()
 * @ingroup themeable
 */
function gin_update_status_label($variables) {
  $output = '';
  $statuses = array(
    UPDATE_NOT_SECURE => array(
      'label' => t('Security update required!'),
      'class' => 'gin-status--danger',
    ),
    UPDATE_REVOKED => array(
      'label' => t('Revoked!'),
      'class' => 'gin-status--danger',
    ),
    UPDATE_NOT_SUPPORTED => array(
      'label' => t('Not supported!'),
      'class' => 'gin-status--danger',
    ),
    UPDATE_UNKNOWN => array(
      'label' => t('No available releases found'),
      'class' => 'gin-status--warning',
    ),
    UPDATE_NOT_CURRENT => array(
      'label' => t('Update available'),
      'class' => 'gin-status--warning',
    ),
    UPDATE_CURRENT => array(
      'label' => t('Up to date'),
      'class' => 'gin-status--success',
    ),
  );
  $status = $variables['status'];
  if (!empty($statuses[$status])) {
    $output .= '<div class="update__status gin-status ' . $statuses[$status]['class'] . '">';
    $output .= '<span class="gin-status-icon"></span>';
    $output .= '<span>' . $statuses[$status]['label'] . '</span>';
    $output .= '</div>';
  }
  return $output;
}

/**
 * Implements hook_preprocess_block().
 *
 * Add classes for Gin theming.
 */
function gin_preprocess_block(&$variables) {
  $variables['panel'] = FALSE;
  /* @var Layout $layout */
  $layout = $variables['layout'];
  if (is_a($layout, 'Layout') && $layout->hasContexts(array('dashboard'))) {
    $block = $variables['block'];
    $region_name = $layout->getBlockPosition($block->uuid);
    if (!in_array($region_name, array('header', 'footer'))) {
      $variables['classes'][] = 'panel';
      $variables['classes'][] = 'gin-layer-wrapper';
      $variables['panel'] = TRUE;
    }
  }
}

/**
 * Overrides theme_admin_block().
 * Returns HTML for an administrative block for display.
 *
 * @param $variables
 *   An associative array containing:
 *   - block: An array containing information about the block:
 *     - show: A Boolean whether to output the block. Defaults to FALSE.
 *     - title: The block's title.
 *     - content: (optional) Formatted content for the block.
 *     - description: (optional) Description of the block. Only output if
 *       'content' is not set.
 *
 * @ingroup themeable
 */
function gin_admin_block($variables) {
  $block = $variables['block'];
  $classes = array(
    'admin-panel',
    'panel',
    'gin-layer-wrapper',
    );
  // Construct a more specific class.
  if (isset($variables['block']['path'])) {
    $path_parts = explode('/', $variables['block']['path']);
    $class_suffix = end($path_parts);
    $classes[] = ' ' . backdrop_clean_css_identifier('admin-panel-' . $class_suffix);
  }

  $output = '';
  // Don't display the block if it has no content to display.
  if (empty($block['show'])) {
    return $output;
  }

  $output .= '<div class="' . implode(' ', $classes) . '">';
  if (!empty($block['title'])) {
    $output .= '<h3 class="panel__title">' . $block['title'] . '</h3>';
  }
  if (!empty($block['content'])) {
    $output .= '<div class="panel__content body">' . $block['content'] . '</div>';
  }
  else {
    $output .= '<div class="panel__content description">' . $block['description'] . '</div>';
  }
  $output .= '</div>';

  return $output;
}

/**
 * Implements hook_admin_bar_output_build().
 */
function gin_admin_bar_output_build(&$content) {
  // dpm($content);
}

/**
 * Returns HTML for the content of an administrative block.
 * Overrides theme_admin_block_content().
 *
 * @param $variables
 *   An associative array containing:
 *   - content: An array containing information about the block. Each element
 *     of the array represents an administrative menu item, and must at least
 *     contain the keys 'title', 'href', and 'localized_options', which are
 *     passed to l(). A 'description' key may also be provided.
 *
 * @ingroup themeable
 */
function gin_admin_block_content($variables) {
  $content = $variables['content'];

  return _gin_admin_list($content, '');
}

/**
 * Returns HTML for a list of available node types for node creation.
 *
 * @param $variables
 *   An associative array containing:
 *   - content: An array of content types.
 *
 * @see node_add_page()
 * @ingroup themeable
 */
function gin_node_add_list($variables) {
  $content = $variables['content'];
  $empty_message = t('You have not created any content types yet. Go to the <a href="@create-content">content type creation page</a> to add a new content type.', array('@create-content' => url('admin/structure/types/add')));
  return _gin_admin_list($content, $empty_message);
}

/**
 * Helper function to generate an admin list.
 * @todo This could be moved into a theme hook and tpl file.
 */
function _gin_admin_list($content, $empty_message = '') {
  $output = '';

  if (!empty($content)) {
    $output .= '<dl class="admin-list gin-layer-wrapper">';
    foreach ($content as $item) {
      $item['localized_options']['attributes']['class'][] = 'admin-item__link';
      $output .= '<div class="admin-item">';
      $output .= l($item['title'], $item['href'], $item['localized_options']);
      $output .= '<dt class="admin-item__title">' . $item['title'] . '</dt>';
      if (isset($item['description'])) {
        $desc = filter_xss_admin($item['description']);
        $item['localized_options']['attributes']['title'] = $desc;
        $output .= '<dd class="admin-item__description">' . $desc . '</dd>';
      }
      $output .= '</div>';
    }
    $output .= '</dl>';
  }
  elseif (!empty($empty_message)) {
    $output = '<p>' . filter_xss_admin($empty_message) . '</p>';
  }
  return $output;
}

/**
 * Implements hook_css_alter().
 */
function gin_css_alter(&$css) {
    unset($css[backdrop_get_path('module','system').'/css/messages.theme.css']);
    unset($css[backdrop_get_path('module','admin_bar').'/css/admin_bar.css']);
}

/**
 * Implements hook_form_BASE_FORM_ID_alter() for node_form.
 *
 * Changes vertical tabs to container.
 */
function gin_form_node_form_alter(&$form, &$form_state, $form_id) {
  if (empty($form_state['input']['dialogOptions'])) {
    _gin_convert_to_sidebar_edit_form($form);
  }
}

/**
 * Implements hook_form_BASE_FORM_ID_alter() for taxonomy_form_term.
 *
 * Changes vertical tabs to container.
 */
function gin_form_taxonomy_form_term_alter(&$form, &$form_state, $form_id) {
  if (empty($form_state['input']['dialogOptions'])) {
    _gin_convert_to_sidebar_edit_form($form);
  }
}

/**
 * Implements hook_form_BASE_FORM_ID_alter() for user_profile_form.
 *
 * Changes vertical tabs to container.
 */
function gin_form_user_profile_form_alter(&$form, &$form_state, $form_id) {
  if (empty($form_state['input']['dialogOptions'])) {
    _gin_convert_to_sidebar_edit_form($form);
  }
}

/**
 * Implements hook_form_BASE_FORM_ID_alter() for user_register_form.
 *
 * Changes vertical tabs to container.
 */
function gin_form_user_register_form_alter(&$form, &$form_state, $form_id) {
  if (empty($form_state['input']['dialogOptions'])) {
    _gin_convert_to_sidebar_edit_form($form);
  }
}

/**
 * Helper function to convert an edit form to use the sidebar edit.
 */
function _gin_convert_to_sidebar_edit_form(&$form) {
  if (gin_content_form_paths()) {
    $first_key = '';
    $first_weight = 999;
    foreach (element_children($form) as $key) {
      if (!empty($form[$key]['#group']) && $form[$key]['#group'] == 'additional_settings') {
          $form[$key]['#collapsed'] = TRUE;
          $form[$key]['#collapsible'] = TRUE;
          if ($form[$key]['#weight'] < $first_weight) {
            $first_weight = $form[$key]['#weight'];
            $first_key = $key;
          }
        }
    }
    $form[$first_key]['#collapsed'] = FALSE;
    $form['additional_settings']['#type'] = 'fieldset';
    $form['additional_settings']['#attributes']['class'][] = 'content-edit-settings';
    $form_id = backdrop_html_class($form['#form_id']);
    backdrop_add_js(array('Gin' => array(
      'sidebar_form_id' => $form_id,
      'actions_form_id' => $form_id,
    )), 'setting');
    $form['#attached']['library'][] = ['gin', 'gin_edit_form'];
    /*
    $form['#attached']['js'][] = backdrop_get_path('theme', 'gin') . '/dist/js/more_actions.js';
    $form['#attached']['css'][] = backdrop_get_path('theme', 'gin') . '/dist/css/components/edit_form.css';
    $form['#attached']['css'][] = backdrop_get_path('theme', 'gin') . '/dist/css/components/sidebar.css';
    $form['#attached']['js'][] = backdrop_get_path('theme', 'gin') . '/dist/js/sidebar.js';
    */
  }
}

/**
 * Overrides theme_pager().
 *
 * @param array $variables
 *  An associative array containing variables for the pager.
 * @return string
 *   The rendered pager.
 */
function gin_pager($variables) {
  $tags = $variables['tags'];
  $element = $variables['element'];
  $parameters = $variables['parameters'];
  $quantity = empty($variables['quantity']) ? 0 : (int) $variables['quantity'];
  global $pager_page_array, $pager_total;

  // Return if there is no pager to be rendered.
  if (!isset($pager_page_array[$element]) || empty($pager_total)) {
    return '';
  }

  // Calculate various markers within this pager piece:
  // Middle is used to "center" pages around the current page.
  $pager_middle = ceil($quantity / 2);
  // current is the page we are currently paged to
  $pager_current = $pager_page_array[$element] + 1;
  // first is the first page listed by this pager piece (re quantity)
  $pager_first = $pager_current - $pager_middle + 1;
  // last is the last page listed by this pager piece (re quantity)
  $pager_last = $pager_current + $quantity - $pager_middle;
  // max is the maximum page number
  $pager_max = $pager_total[$element];
  // End of marker calculations.

  // Prepare for generation loop.
  $i = $pager_first;
  if ($pager_last > $pager_max) {
    // Adjust "center" if at end of query.
    $i = $i + ($pager_max - $pager_last);
    $pager_last = $pager_max;
  }
  if ($i <= 0) {
    // Adjust "center" if at start of query.
    $pager_last = $pager_last + (1 - $i);
    $i = 1;
  }
  // End of generation loop preparation.

  $li_first = theme('pager_first', array('text' => (isset($tags[0]) ? $tags[0] : t('« First')), 'element' => $element, 'parameters' => $parameters));
  $li_previous = theme('pager_previous', array('text' => (isset($tags[1]) ? $tags[1] : t('‹ Previous')), 'element' => $element, 'interval' => 1, 'parameters' => $parameters));
  $li_next = theme('pager_next', array('text' => (isset($tags[3]) ? $tags[3] : t('Next ›')), 'element' => $element, 'interval' => 1, 'parameters' => $parameters));
  $li_last = theme('pager_last', array('text' => (isset($tags[4]) ? $tags[4] : t('Last »')), 'element' => $element, 'parameters' => $parameters));

  if ($pager_total[$element] > 1) {
    if ($li_first) {
      $items[] = array(
        'class' => array('pager__item--first'),
        'data' => $li_first,
      );
    }
    if ($li_previous) {
      $items[] = array(
        'class' => array('pager__item--previous'),
        'data' => $li_previous,
      );
    }

    // When there is more than one page, create the pager list.
    if ($i != $pager_max) {
      if ($i > 1) {
        $items[] = array(
          'class' => array('pager-ellipsis'),
          'data' => '…',
        );
      }
      // Now generate the actual pager piece.
      for (; $i <= $pager_last && $i <= $pager_max; $i++) {
        if ($i < $pager_current) {
          $items[] = array(
            'class' => array('pager__item'),
            'data' => theme('pager_previous', array('text' => $i, 'element' => $element, 'interval' => ($pager_current - $i), 'parameters' => $parameters)),
          );
        }
        if ($i == $pager_current) {
          $items[] = array(
            'class' => array(
              'pager__item--current',
              'pager__item',
            ),
            'data' => $i,
          );
        }
        if ($i > $pager_current) {
          $items[] = array(
            'class' => array('pager__item'),
            'data' => theme('pager_next', array(
              'text' => $i,
              'element' => $element,
              'interval' => ($i - $pager_current),
              'parameters' => $parameters,
            )),
          );
        }
      }
      if ($i < $pager_max) {
        $items[] = array(
          'class' => array('pager-ellipsis'),
          'data' => '…',
        );
      }
    }
    // End generation.
    if ($li_next) {
      $items[] = array(
        'class' => array(
          'pager__item--next',
            'pager__item',
          ),
        'data' => $li_next,
      );
    }
    if ($li_last) {
      $items[] = array(
        'class' => array(
          'pager__item--last',
            'pager__item',
          ),
        'data' => $li_last,
      );
    }
    $output = '<nav class="pager" role="navigation" aria-labelledby="pagination-heading">';
    $output .= '<h4 id="pagination-heading" class="element-invisible">' . t('Pagination') . '</h4>';
    $output .= theme('item_list', array(
      'items' => $items,
      'attributes' => array('class' => array('pager__items')),
    ));
    $output .= '</nav>';
    return $output;
  }

  // Single page of contents, no pager needed.
  return '';
}

/**
 * Overrides theme_pager_link().
 *
 * @param array $variables
 *  An associative array containing variables for the pager link.
 * @return string
 *  The rendered pager link.
 */
function gin_pager_link($variables) {
  $text = $variables['text'];
  $page_new = $variables['page_new'];
  $element = $variables['element'];
  $parameters = $variables['parameters'];
  $attributes = $variables['attributes'];
  $attributes['class'][] = 'pager__link';

  $page = isset($_GET['page']) ? $_GET['page'] : '';
  if ($new_page = implode(',', pager_load_array($page_new[$element], $element, explode(',', $page)))) {
    $parameters['page'] = $new_page;
  }

  $query = array();
  if (count($parameters)) {
    $query = backdrop_get_query_parameters($parameters, array());
  }
  if ($query_pager = pager_get_query_parameters()) {
    $query = array_merge($query, $query_pager);
  }

  // Set each pager link title
  if (!isset($attributes['title'])) {
    static $titles = NULL;
    if (!isset($titles)) {
      $titles = array(
        t('« First') => t('Go to first page'),
        t('‹ Previous') => t('Go to previous page'),
        t('Next ›') => t('Go to next page'),
        t('Last »') => t('Go to last page'),
      );
    }
    if (isset($titles[$text])) {
      $attributes['title'] = $titles[$text];
    }
    elseif (is_numeric($text)) {
      $attributes['title'] = t('Go to page @number', array('@number' => $text));
    }
  }

  // @todo l() cannot be used here, since it adds an 'active' class based on the
  //   path only (which is always the current path for pager links). Apparently,
  //   none of the pager links is active at any time - but it should still be
  //   possible to use l() here.
  // @see http://drupal.org/node/1410574
  $attributes['href'] = url($_GET['q'], array('query' => $query));
  return '<a' . backdrop_attributes($attributes) . '>' . check_plain($text) . '</a>';
}

/**
 * Implements hook_views_pre_render().
 */
function gin_views_pre_render(&$view) {
  if ($view->name == 'image_library') {
    $view->field["uri"]->options["image_style"] = 'medium';
    $view->field["uri"]->options["thumbnail_style"] = 'medium';
  }
}

/**
 * Implements theme_form_element().
 *
 * We're overriding/duplicating this function to allow adjustments to the
 * description and implement Gin's description toggle functionality.
 */
function gin_form_element($variables) {
  $element = &$variables['element'];

  // This function is invoked as theme wrapper, but the rendered form element
  // may not necessarily have been processed by form_builder().
  $element += array(
    '#title_display' => 'before',
    '#wrapper_attributes' => array(),
  );
  $attributes = $element['#wrapper_attributes'];

  // Add element #id for #type 'item'.
  if (isset($element['#markup']) && !empty($element['#id'])) {
    $attributes['id'] = $element['#id'];
  }
  // Add element's #type and #name as class to aid with JS/CSS selectors.
  $attributes['class'][] = 'form-item';
  if (!empty($element['#type'])) {
    $attributes['class'][] = 'form-type-' . strtr($element['#type'], '_', '-');
    if (isset($element['#parents']) && form_get_error($element) !== NULL && !empty($element['#validated'])) {
      $attributes['class'][] = 'form-error';
    }
  }
  if (!empty($element['#name'])) {
    $attributes['class'][] = 'form-item-' . strtr($element['#name'], array(' ' => '-', '_' => '-', '[' => '-', ']' => ''));
  }
  // Add a class for disabled elements to facilitate cross-browser styling.
  if (!empty($element['#attributes']['disabled'])) {
    $attributes['class'][] = 'form-disabled';
  }
  // Add indentation.
  if (isset($element['#indentation'])) {
    $attributes['class'][] = 'form-item-indentation';
    $attributes['class'][] = 'form-item-indentation-' . $element['#indentation'];
    $attributes['data-indentation-depth'] = $element['#indentation'];
  }
  // Add description toggle classes if settings call for them.
  $show_description_toggle = theme_get_setting('show_description_toggle', 'gin');
  $help_icon_open = '';
  $help_icon_close = '';
  if (!empty($element['#description'])) {
    $description_attributes['class'][] = 'description';
    if ($show_description_toggle) {
      backdrop_add_library('gin', 'gin_description_toggle');
      $attributes['class'][] = 'help-icon__description-container';
      $description_attributes['class'][] = 'visually-hidden';
      $help_icon_open = '<div class="help-icon">';
      $help_icon_close = '<button class="help-icon__description-toggle"></button></div>';
    }
  }
  $output = '<div' . backdrop_attributes($attributes) . '>' . "\n";

  // If #title is not set, we don't display any label or required marker.
  if (!isset($element['#title'])) {
    $element['#title_display'] = 'none';
  }
  $prefix = isset($element['#field_prefix']) ? '<span class="field-prefix">' . $element['#field_prefix'] . '</span> ' : '';
  $suffix = isset($element['#field_suffix']) ? ' <span class="field-suffix">' . $element['#field_suffix'] . '</span>' : '';

  switch ($element['#title_display']) {
    case 'before':
    case 'invisible':
      $output .= $help_icon_open;
      $output .= ' ' . theme('form_element_label', $variables);
      $output .= ' ' . $prefix . $element['#children'] . $suffix . "\n";
      $output .= $help_icon_close;
      break;

    case 'after':
      $output .= $help_icon_open;
      $output .= ' ' . $prefix . $element['#children'] . $suffix;
      $output .= ' ' . theme('form_element_label', $variables) . "\n";
      $output .= $help_icon_close;
      break;

    case 'none':
    case 'attribute':
      // Output no label and no required marker, only the children.
      $output .= ' ' . $prefix . $element['#children'] . $suffix . "\n";
      break;
  }

  if (!empty($element['#description'])) {
    $show_description_toggle = theme_get_setting('show_description_toggle', 'gin');
    $output .= '<div' . backdrop_attributes($description_attributes) . '>' . $element['#description'] . "</div>\n";
  }

  $output .= "</div>\n";

  return $output;
}

/**
 * Custom function to determine if we are viewing a content edit form.
 * Other modules can add or alter the paths that are considered content forms:
 *  - hook_gin_content_form_paths
 *  - gin_content_form_paths_alter
 *
 * @return bool
 *  TRUE if we are viewing a content edit form that should use the sidebar.
 */
function gin_content_form_paths() {
  // This is only relevant if the sidebar is turned on.
  if (!theme_get_setting('edit_form_sidebar', 'gin')) {
    return FALSE;
  }
  else {
    $is_content_form = FALSE;
    $current_path = current_path();
    $sidebar_edit_paths_cached = cache_get('gin_content_form_paths');
    if (empty($sidebar_edit_paths_cached)) {
      $sidebar_edit_paths = array(
        'node/*/edit',
        'node/add/*',
        'taxonomy/term/*/edit',
        'admin/structure/taxonomy/*/add',
        'admin/people/create',
        'user/*/edit',
      );
      $additional_paths = module_invoke_all('gin_content_form_paths');
      $sidebar_edit_paths = array_merge($additional_paths, $sidebar_edit_paths);

      // Allow alteration of the paths.
      backdrop_alter('gin_content_form_paths', $sidebar_edit_paths);
      cache_set('gin_content_form_paths', $sidebar_edit_paths);
    }
    else {
      $sidebar_edit_paths = $sidebar_edit_paths_cached->data;
    }
    $is_content_form = backdrop_match_path($current_path, implode("\n", $sidebar_edit_paths));
    return $is_content_form;
  }
}

/**
 * Return any excluded form IDs for the sticky action buttons.
 *
 * @see hook_gin_ignore_sticky_form_actions
 */
function gin_ignore_sticky_form_actions() {
  $exclude_form_ids = array(
    'layout-block-configure-form',
    'layout-title-settings-form',
    'layout-configure-region-page',
    'views-ui-preview-form',
    'file-managed-file-browser-form',
  );
  $exclude_form_ids += module_invoke_all('gin_ignore_sticky_form_actions');
  return $exclude_form_ids;
}
