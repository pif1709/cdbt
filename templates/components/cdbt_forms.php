<?php
/**
 * Form Options array `$this->component_options` scheme
 * [
 * 'id' => @string is element id [optional] For default is `cdbtForm`
 * 'entryTable' => @string [require] To entry table name
 * 'useBootstrap' => @boolean [require] For default is True
 * 'outputTitle' => @string [optional] For default is null
 * 'formElements' => @array [require] As follow is: 
 *   [
 *   'elementName' => @string [require] Column name
 *   'elementLabel' => @string [require] Column's logical name
 *   'elementType' => @string [require] Input type
 *   'isRequired' => @boolean [optional] For default is false (Enable if element type is text, search, url, tel, email, password, datetime, date, month, week, time, number, checkbox, radio, file)
 *   'defaultValue' => @string [optional] Default value; Default of selected or checked if select list or multiple checkbox
 *   'placeholder' => @string [optional] Attribute of placeholder (Enable if element type is text, search, url, tel, email, password)
 *   'addClass' => @string [optional] Attribute of additional class
 *   'selectableList' => @string (like assoc) [optional/require] Required if select list or multiple checkbox; For example 'label1:value1,label2:value2,lable3:value3,...'
 *   'horizontalList' => @boolean [optional] For default is false (Enable if element type is checkbox, radio)
 *   'elementSize' => @string [optional] Styling width of element. Integer of pixel as width if does not use bootstrap style, otherwise will be able to use class name as `col-sm-n`.
 *   'helperText' => @string [optional] Helper text is displayed at the bottom of the input form
 *   'elementExtras' => @array (assoc) [optional] Freely addition attributes for using when generating content in input form; As follow is: 
 *     [
 *     'accept' => (anything) Enable only if element type is file
 *     'autocomplete' => (on|off|default) Enable if element type is text, search, url, tel, email, password, datetime, date, month, week, time, number, range, color
 *     'list' => (anything) Enable if element type is text, search, url, tel, email, datetime, date, month, week, time, number, range, color
 *     'max' => (integer) Enable if element type is datetime, date, month, week, time, number, range
 *     'min' => (integer) Enable if element type is datetime, date, month, week, time, number, range
 *     'step' => (integer) Enable if element type is datetime, date, month, week, time, number, range
 *     'maxlength' => (integer) Enable if element type is text, search, url, tel, email, password
 *     'multiple' => () Enable if element type is email, file
 *     'pattern' => () Enable if element type is text, search, url, tel, email, password
 *     'size' => () Enable if element type is text, search, url, tel, email, password
 *     'rows' => (integer) Enable only if element type is textarea
 *     'data-moment-locale' => (string) l18n location name; cf. `en`, `fr`,... For default is `en`
 *     'data-moment-format' => (string) Date format for `moment.js`, for default is `L`
 *     ]
 *   ]
 * ]
 */

/**
 * Parse options
 * ---------------------------------------------------------------------------
 */

// `id` section
if (isset($this->component_options['id']) && !empty($this->component_options['id'])) {
  $modal_id = esc_attr__($this->component_options['id']);
} else {
  $modal_id = 'cdbtForm';
}

// `entryTable` section
if (!isset($this->component_options['entryTable']) || empty($this->component_options['entryTable'])) 
  return;

// `outputTitle` section
if (isset($this->component_options['outputTitle']) && !empty($this->component_options['outputTitle'])) {
  $form_title = esc_html($this->component_options['outputTitle']);
} else {
  $form_title = '';
}

// `useBootstrap` section
if (isset($this->component_options['useBootstrap']) && !empty($this->component_options['useBootstrap'])) {
  $use_bootstrap = $this->strtobool($this->component_options['useBootstrap']);
} else {
  $use_bootstrap = true;
}

// `formElements` section
if (!isset($this->component_options['formElements']) || empty($this->component_options['formElements'])) 
  return;

$form_elements = $this->component_options['formElements'];

$current_url = (is_admin()) ? add_query_arg([ 'page' => $this->query['page'] ]) : $_SERVER['REQUEST_URI'];
$action_url = esc_url($current_url);
$admin_hidden = [];
if (is_admin()) {
  $admin_hidden[] = sprintf( '<input type="hidden" name="page" value="%s">', $this->query['page'] );
  $admin_hidden[] = sprintf( '<input type="hidden" name="active_tab" value="%s">', $this->query['tab'] );
  $wp_nonce_action = 'cdbt_management_console-' . $this->query['page'];
} else {
  $wp_nonce_action = 'cdbt_entry_data_' . $this->component_options['entryTable'];
}

/**
 * Render the Form common header
 * ---------------------------------------------------------------------------
 */
?>
<div class="cdbt-entry-data-form">
  <form method="post" action="<?php echo $action_url; ?>" class="form-horizontal">
    <?php if (empty(!$admin_hidden)) { echo implode("\n", $admin_hidden); } ?>
    <input type="hidden" name="action" value="entry_data">
    <input type="hidden" name="table" value="<?php echo $this->component_options['entryTable']; ?>">
    <?php wp_nonce_field( $wp_nonce_action ); ?>
    
    <?php if (!empty($form_title)) { echo $form_title; } ?>
    
<?php

if ($use_bootstrap) {
  
  foreach ($form_elements as $element) {
    // Parse element options
    $is_required = $this->strtobool($element['isRequired']);
    $selectable_list = $this->strtohash($element['selectableList']);
    $element_size = empty($element['elementSize']) || !preg_match('/^col-.*/iU', $element['elementSize']) ? 'col-sm-9' : esc_attr($element['elementSize']);
    $placeholder = empty($element['placeholder']) ? sprintf( __('Please enter the %s', CDBT), $element['elementLabel'] ) : esc_attr($element['placeholder']);
    $input_attributes = [];
    if (!empty($element['elementExtras'])) {
      foreach($element['elementExtras'] as $attr_name => $attr_value) {
        $input_attributes[] = sprintf('%s="%s"', esc_attr($attr_name), esc_attr($attr_value));
      }
    }
    $add_attributes = implode(' ', $input_attributes);
    
    switch ($element['elementType']) {
/**
 * Render the Form using Bootstrap style
 * ---------------------------------------------------------------------------
search, datetime, date, month, week, time, color
 */
      case 'text': 
      case 'url': 
      case 'tel': 
      case 'email': 
      case 'password': 
?>
    <div class="form-group">
      <label for="entry-data-<?php esc_attr_e($element['elementName']); ?>" class="col-sm-2 control-label"><?php echo $element['elementLabel']; ?><?php if ($is_required) : ?><h6><span class="label label-danger"><?php _e('require', CDBT); ?></span></h6><?php endif; ?></label>
      <div class="<?php echo $element_size; ?>">
        <input id="entry-data-<?php esc_attr_e($element['elementName']); ?>" name="<?php echo $this->domain_name; ?>[<?php esc_attr_e($element['elementName']); ?>]" type="<?php esc_attr_e($element['elementType']); ?>" value="<?php esc_attr_e($element['defaultValue']); ?>" class="form-control <?php esc_attr_e($element['addClass']); ?>" placeholder="<?php echo $placeholder; ?>" <?php echo $add_attributes; ?><?php if ($is_required) { echo ' required'; } ?>>
      </div>
      <div class="col-sm-10">
      <?php if (isset($element['helperText']) && !empty($element['helperText'])) : ?><p class="help-block col-sm-10"><?php esc_html_e($element['helperText']); ?></p><?php endif; ?>
      </div>
    </div><!-- /entry-data-<?php esc_attr_e($element['elementName']); ?> -->
<?php
        break;
      case 'number': 
      case 'range': 
?>
    <div class="form-group">
      <label for="entry-data-<?php esc_attr_e($element['elementName']); ?>" class="col-sm-2 control-label"><?php echo $element['elementLabel']; ?><?php if ($is_required) : ?><h6><span class="label label-danger"><?php _e('require', CDBT); ?></span></h6><?php endif; ?></label>
      <div class="col-sm-10">
        <div class="spinbox disits-3 <?php esc_attr_e($element['addClass']); ?>" data-initialize="spinbox" id="entry-data-<?php esc_attr_e($element['elementName']); ?>">
          <input type="text" name="<?php echo $this->domain_name; ?>[<?php esc_attr_e($element['elementName']); ?>]" value="<?php esc_attr_e($element['defaultValue']); ?>" class="form-control input-mini spinbox-input" placeholder="<?php echo $placeholder; ?>" <?php echo $add_attributes; ?><?php if ($is_required) { echo ' required'; } ?>>
          <div class="spinbox-buttons btn-group btn-group-vertical">
            <button type="button" class="btn btn-default spinbox-up btn-xs"><span class="glyphicon glyphicon-chevron-up"></span><span class="sr-only"><?php echo __('Increase', CDBT); ?></span></button>
            <button type="button" class="btn btn-default spinbox-down btn-xs"><span class="glyphicon glyphicon-chevron-down"></span><span class="sr-only"><?php echo __('Decrease', CDBT); ?></span></button>
          </div>
        </div>
        <?php if (isset($element['helperText']) && !empty($element['helperText'])) : ?><p class="help-block"><?php esc_html_e($element['helperText']); ?></p><?php endif; ?>
      </div>
    </div><!-- /entry-data-<?php esc_attr_e($element['elementName']); ?> -->
<?php
        break;
      case 'textarea': 
?>
    <div class="form-group">
      <label for="entry-data-<?php esc_attr_e($element['elementName']); ?>" class="col-sm-2 control-label"><?php echo $element['elementLabel']; ?><?php if ($is_required) : ?><h6><span class="label label-danger"><?php _e('require', CDBT); ?></span></h6><?php endif; ?></label>
      <div class="col-sm-9">
        <textarea id="entry-data-<?php esc_attr_e($element['elementName']); ?>" name="<?php echo $this->domain_name; ?>[<?php esc_attr_e($element['elementName']); ?>]" class="form-control <?php esc_attr_e($element['addClass']); ?>" placeholder="<?php echo $placeholder; ?>" <?php echo $add_attributes; ?><?php if ($is_required) { echo ' required'; } ?>><?php esc_attr_e($element['defaultValue']); ?></textarea>
      </div>
      <?php if (isset($element['helperText']) && !empty($element['helperText'])) : ?><p class="help-block"><?php esc_html_e($element['helperText']); ?></p><?php endif; ?>
    </div><!-- /entry-data-<?php esc_attr_e($element['elementName']); ?> -->
<?php
        break;
      case 'combobox': 
?>


<?php
        break;
      case 'select': 
?>
    <div class="form-group">
      <label for="entry-data-<?php esc_attr_e($element['elementName']); ?>" class="col-sm-2 control-label"><?php echo $element['elementLabel']; ?><?php if ($is_required) : ?><h6><span class="label label-danger"><?php _e('require', CDBT); ?></span></h6><?php endif; ?></label>
      <div class="col-sm-10">
        <div class="btn-group selectlist <?php esc_attr_e($element['addClass']); ?>" data-resize="auto" data-initialize="selectlist" id="entry-data-<?php esc_attr_e($element['elementName']); ?>">
          <button class="btn btn-default dropdown-toggle" data-toggle="dropdown" type="button">
            <span class="selected-label"></span>
            <span class="caret"></span>
            <span class="sr-only"><?php esc_attr_e('Toggle Dropdown'); ?></span>
          </button>
          <ul class="dropdown-menu" role="menu">
          <?php foreach ($selectable_list as $list_label => $list_value) : ?>
            <li data-value="<?php esc_attr_e($list_value); ?>"<?php if ($list_value === $element['defaultValue']) : ?> selected="selected"<?php endif; ?>><a href="#"><?php esc_html_e($list_label); ?></a></li>
          <?php endforeach; ?>
          </ul>
          <input class="hidden hidden-field" name="<?php echo $this->domain_name; ?>[<?php esc_attr_e($element['elementName']); ?>]" readonly="readonly" aria-hidden="true" type="text"/>
        </div>
      <?php if (isset($element['helperText']) && !empty($element['helperText'])) : ?><p class="help-block"><?php esc_html_e($element['helperText']); ?></p><?php endif; ?>
      </div>
    </div><!-- /entry-data-<?php esc_attr_e($element['elementName']); ?> -->
<?php
        break;
      case 'checkbox': 
        $index_num = 0;
        $is_horizontal = $element['horizontalList'];
?>
    <div class="form-group">
      <label for="entry-data-<?php esc_attr_e($element['elementName']); ?>" class="col-sm-2 control-label"><?php echo $element['elementLabel']; ?><?php if ($is_required) : ?><h6><span class="label label-danger"><?php _e('require', CDBT); ?></span></h6><?php endif; ?></label>
      <div class="col-sm-10">
      <?php foreach ($selectable_list as $list_label => $list_value) : $index_num++; ?>
        <?php if (!$is_horizontal) : ?><div class="checkbox <?php esc_attr_e($element['addClass']); ?>"><?php endif; ?>
          <label class="checkbox-custom<?php if ($is_horizontal) : ?> checkbox-inline<?php esc_attr_e($element['addClass']); endif; ?>" data-initialize="checkbox" id="entry-data-<?php esc_attr_e($element['elementName']); ?><?php echo $index_num; ?>">
            <input class="sr-only" name="<?php echo $this->domain_name; ?>[<?php esc_attr_e($element['elementName']); ?>][]" type="checkbox" value="<?php esc_attr_e($list_value); ?>"<?php if ($list_value === $element['defaultValue']) : ?> checked="checked"<?php endif; ?>>
            <span class="checkbox-label"><?php esc_html_e($list_label); ?></span>
          </label>
        <?php if (!$is_horizontal) : ?></div><?php endif; ?>
      <?php endforeach; ?>
      <?php if (isset($element['helperText']) && !empty($element['helperText'])) : ?><p class="help-block"><?php esc_html_e($element['helperText']); ?></p><?php endif; ?>
      </div>
    </div><!-- /entry-data-<?php esc_attr_e($element['elementName']); ?> -->
<?php
        unset($index_num, $is_horizontal);
        break;
      case 'radio': 
        $index_num = 0;
        $is_horizontal = $element['horizontalList'];
?>
    <div class="form-group">
      <label for="entry-data-<?php esc_attr_e($element['elementName']); ?>" class="col-sm-2 control-label"><?php echo $element['elementLabel']; ?><?php if ($is_required) : ?><h6><span class="label label-danger"><?php _e('require', CDBT); ?></span></h6><?php endif; ?></label>
      <div class="col-sm-10">
      <?php foreach ($selectable_list as $list_label => $list_value) : $index_num++; ?>
        <?php if (!$is_horizontal) : ?><div class="radio <?php esc_attr_e($element['addClass']); ?>"><?php endif; ?>
          <label class="radio-custom<?php if ($is_horizontal) : ?> radio-inline<?php esc_attr_e($element['addClass']); endif; ?>" data-initialize="radio" id="entry-data-<?php esc_attr_e($element['elementName']); ?><?php echo $index_num; ?>">
            <input class="sr-only" name="<?php echo $this->domain_name; ?>[<?php esc_attr_e($element['elementName']); ?>][]" type="radio" value="<?php esc_attr_e($list_value); ?>"<?php if ($list_value === $element['defaultValue']) : ?> checked="checked"<?php endif; ?>>
            <span class="radio-label"><?php esc_html_e($list_label); ?></span>
          </label>
        <?php if (!$is_horizontal) : ?></div><?php endif; ?>
      <?php endforeach; ?>
      <?php if (isset($element['helperText']) && !empty($element['helperText'])) : ?><p class="help-block"><?php esc_html_e($element['helperText']); ?></p><?php endif; ?>
      </div>
    </div><!-- /entry-data-<?php esc_attr_e($element['elementName']); ?> -->
<?php
        unset($index_num, $is_horizontal);
        break;
      case 'boolean': 
        $checked = ($element['defaultValue']) ? ' checked="checked"' : '';
?>
    <div class="form-group">
      <label for="entry-data-<?php esc_attr_e($element['elementName']); ?>" class="col-sm-2 control-label"><?php echo $element['elementLabel']; ?><?php if ($is_required) : ?><h6><span class="label label-danger"><?php _e('require', CDBT); ?></span></h6><?php endif; ?></label>
      <div class="col-sm-10">
        <div class="checkbox <?php esc_attr_e($element['addClass']); ?>" id="entry-data-<?php esc_attr_e($element['elementName']); ?>">
          <label class="checkbox-custom" data-initialize="checkbox">
            <input class="sr-only" name="<?php echo $this->domain_name; ?>[<?php esc_attr_e($element['elementName']); ?>][]" type="checkbox" value="1"<?php echo $checked; ?> <?php echo $add_attributes; ?><?php if ($is_required) { echo ' required'; } ?>>
            <span class="checkbox-label"><?php if (isset($element['helperText']) && !empty($element['helperText'])) : ?><?php esc_html_e($element['helperText']); ?><?php else : ?><?php echo $element['elementLabel']; ?><?php endif; ?></span>
          </label>
        </div>
      </div>
    </div><!-- /entry-data-<?php esc_attr_e($element['elementName']); ?> -->
<?php
        unset($checked);
        break;
      case 'file': 
?>
    <div class="form-group">
      <label for="entry-data-<?php esc_attr_e($element['elementName']); ?>" class="col-sm-2 control-label"><?php echo $element['elementLabel']; ?><?php if ($is_required) : ?><h6><span class="label label-danger"><?php _e('require', CDBT); ?></span></h6><?php endif; ?></label>
      <div class="col-sm-10">
        <input class="<?php esc_attr_e($element['addClass']); ?>" type="file" id="entry-data-<?php esc_attr_e($element['elementName']); ?>" name="<?php echo $this->domain_name; ?>[<?php esc_attr_e($element['elementName']); ?>]" value="<?php esc_attr($element['defaultValue']); ?>">
        <input class="hidden hidden-field" type="hidden" name="cache_file" value="<?php esc_attr($element['defaultValue']); ?>">
      <?php if (isset($element['helperText']) && !empty($element['helperText'])) : ?><p class="help-block"><?php esc_html_e($element['helperText']); ?></p><?php endif; ?>
      </div>
    </div><!-- /entry-data-<?php esc_attr_e($element['elementName']); ?> -->
<?php
        break;
      case 'datetime': 
        $month_list = [
          [ 'fullname' => __('January', CDBT), 'ailiase' => __('Jan', CDBT) ], 
          [ 'fullname' => __('February', CDBT), 'ailiase' => __('Feb', CDBT) ], 
          [ 'fullname' => __('March', CDBT), 'ailiase' => __('Mar', CDBT) ], 
          [ 'fullname' => __('April', CDBT), 'ailiase' => __('Apr', CDBT) ], 
          [ 'fullname' => __('May', CDBT), 'ailiase' => __('May', CDBT) ], 
          [ 'fullname' => __('June', CDBT), 'ailiase' => __('Jun', CDBT) ], 
          [ 'fullname' => __('July', CDBT), 'ailiase' => __('Jul', CDBT) ], 
          [ 'fullname' => __('August', CDBT), 'ailiase' => __('Aug', CDBT) ], 
          [ 'fullname' => __('September', CDBT), 'ailiase' => __('Sep', CDBT) ], 
          [ 'fullname' => __('October', CDBT), 'ailiase' => __('Oct', CDBT) ], 
          [ 'fullname' => __('November', CDBT), 'ailiase' => __('Nov', CDBT) ], 
          [ 'fullname' => __('December', CDBT), 'ailiase' => __('Dec', CDBT) ], 
        ];
        $week_list = [
          [ 'fullname' => __('Sunday', CDBT), 'ailiase' => __('Su', CDBT) ], 
          [ 'fullname' => __('Monday', CDBT), 'ailiase' => __('Mo', CDBT) ], 
          [ 'fullname' => __('Tuesday', CDBT), 'ailiase' => __('Tu', CDBT) ], 
          [ 'fullname' => __('Wednesday', CDBT), 'ailiase' => __('We', CDBT) ], 
          [ 'fullname' => __('Thurseday', CDBT), 'ailiase' => __('Th', CDBT) ], 
          [ 'fullname' => __('Friday', CDBT), 'ailiase' => __('Fr', CDBT) ], 
          [ 'fullname' => __('Saturday', CDBT), 'ailiase' => __('Sa', CDBT) ], 
        ];
        if (!empty($element['defaultValue'])) {
          list($_date, $_time) = explode(' ', $element['defaultValue']);
          if ('0000-00-00' !== $_date) {
            list($_year, $_month, $_day) = explode('-', $_date);
            $default_date = sprintf('%s/%s/%s', $_month, $_day, $_year);
          }
        }
        
        if (isset($_time) && !empty($_time)) {
          list($_hour, $_minite, $_second) = explode(':', $_time);
        }
?>
    <div class="form-group">
      <label for="entry-data-<?php esc_attr_e($element['elementName']); ?>-date" class="col-sm-2 control-label"><?php echo $element['elementLabel']; ?><?php if ($is_required) : ?><h6><span class="label label-danger"><?php _e('require', CDBT); ?></span></h6><?php endif; ?></label>
      <div class="col-sm-10">
        <div class="datepicker cdbt-datepicker" data-initialize="datepicker" id="entry-data-<?php esc_attr_e($element['elementName']); ?>-date" <?php if (isset($default_date) && !empty($default_date)) : ?>data-date="<?php echo $default_date; ?>"<?php endif; ?> data-allow-past-dates="allowPastDates" <?php echo $add_attributes; ?>>
          <div class="input-group col-sm-3 pull-left">
            <input class="form-control text-center" type="text">
            <div class="input-group-btn">
              <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                <span class="glyphicon glyphicon-calendar"></span>
                <span class="sr-only"><?php _e('Toggle Calendar', CDBT); ?></span>
              </button>
              <div class="dropdown-menu dropdown-menu-right datepicker-calendar-wrapper" role="menu">
                <div class="datepicker-calendar">
                  <div class="datepicker-calendar-header">
                    <button type="button" class="prev"><span class="glyphicon glyphicon-chevron-left"></span><span class="sr-only"><?php _e('Previous Month', CDBT); ?></span></button>
                    <button type="button" class="next"><span class="glyphicon glyphicon-chevron-right"></span><span class="sr-only"><?php _e('Next Month', CDBT); ?></span></button>
                    <button type="button" class="title">
                      <span class="month">
                      <?php foreach ($month_list as $i => $month_data) : ?>
                        <span data-month="<?php echo $i; ?>"><?php echo $month_data['fullname']; ?></span>
                      <?php endforeach; ?>
                      </span>
                      <span class="year"></span>
                    </button>
                  </div>
                  <table class="datepicker-calendar-days">
                    <thead>
                      <tr>
                      <?php foreach ($week_list as $week_data) : ?>
                        <th><?php echo $week_data['ailiase']; ?></th>
                      <?php endforeach; ?>
                      </tr>
                    </thead>
                    <tbody></tbody>
                  </table>
                  <div class="datepicker-calendar-footer">
                    <button type="button" class="datepicker-today"><?php _e('Today', CDBT); ?></button>
                  </div>
                </div>
                <div class="datepicker-wheels" aria-hidden="true">
                  <div class="datepicker-wheels-month">
                    <h2 class="header"><?php _e('Month', CDBT); ?></h2>
                    <ul>
                    <?php foreach ($month_list as $i => $month_data) : ?>
                      <li data-month="<?php echo $i; ?>"><button type="button"><?php echo $month_data['aliase']; ?></button></li>
                    <?php endforeach; ?>
                    </ul>
                  </div>
                  <div class="datepicker-wheels-year">
                    <h2 class="header"><?php _e('Year', CDBT); ?></h2>
                    <ul></ul>
                  </div>
                  <div class="datepicker-wheels-footer clearfix">
                    <button type="button" class="btn datepicker-wheels-back"><span class="glyphicon glyphicon-arrow-left"></span><span class="sr-only"><?php _e('Return to Calendar', CDBT); ?></span></button>
                    <button type="button" class="btn datepicker-wheels-select"><?php _e('Select', CDBT); ?> <span class="sr-only"><?php _e('Month and Year', CDBT); ?></span></button>
                  </div>
                </div>
              </div>
            </div>
          </div><!-- /data-picker -->
        </div>
        <div class="clock-mark pull-left"><span class="glyphicon glyphicon-time text-muted"></span></div>
        <div class="col-sm-2 pull-left">
          <div class="input-group input-append dropdown combobox" data-initialize="combobox">
            <input type="text"  id="entry-data-<?php esc_attr_e($element['elementName']); ?>-hour" value="<?php echo $_hour; ?>" class="form-control text-center" pattern="^[0-9]+$">
            <div class="input-group-btn">
              <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
              <ul class="dropdown-menu dropdown-menu-right">
              <?php for ($hour=0; $hour<24; $hour++) : ?>
                <li data-value="<?php echo $hour; ?>"><a href="#"><?php printf('%02d', $hour); ?></a></li>
              <?php endfor; ?>
              </ul>
            </div>
          </div><!-- /hour-combobox -->
        </div>
        <p class="help-block pull-left"><b class="time-separater text-muted">:</b></p>
        <div class="col-sm-2 pull-left">
          <div class="input-group input-append dropdown combobox" data-initialize="combobox">
            <input type="text"  id="entry-data-<?php esc_attr_e($element['elementName']); ?>-hour" value="<?php echo $_minite; ?>" class="form-control text-center" pattern="^[0-9]+$">
            <div class="input-group-btn">
              <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
              <ul class="dropdown-menu dropdown-menu-right">
              <?php for ($minit=0; $minit<60; $minit++) : ?>
                <li data-value="<?php echo $hour; ?>"><a href="#"><?php printf('%02d', $minit); ?></a></li>
              <?php endfor; ?>
              </ul>
            </div>
          </div><!-- /hour-combobox -->
        </div>
        <p class="help-block pull-left"><b class="time-separater text-muted">:</b></p>
        <div class="col-sm-2 pull-left">
          <div class="input-group input-append dropdown combobox" data-initialize="combobox">
            <input type="text"  id="entry-data-<?php esc_attr_e($element['elementName']); ?>-hour" value="<?php echo $_second; ?>" class="form-control text-center" pattern="^[0-9]+$">
            <div class="input-group-btn">
              <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
              <ul class="dropdown-menu dropdown-menu-right">
              <?php for ($second=0; $second<60; $second++) : ?>
                <li data-value="<?php echo $hour; ?>"><a href="#"><?php printf('%02d', $second); ?></a></li>
              <?php endfor; ?>
              </ul>
            </div>
          </div><!-- /hour-combobox -->
        </div>
      </div>
      <input type="hidden" name="<?php echo $this->domain_name; ?>[<?php esc_attr_e($element['elementName']); ?>]" value="<?php esc_attr_e($element['defaultValue']); ?>">
    </div><!-- /entry-data-<?php esc_attr_e($element['elementName']); ?> -->
<?php
        unset($default_date, $_time, $_hour, $_minite, $_second);
        break;
      default: 
        break;
    }
  }
  
} else {
  foreach ($form_elements as $element) {
    switch ($element['elementType']) {
/**
 * Render the Form at normal html
 * ---------------------------------------------------------------------------
 */
      case 'text': 
?>


<?php
        break;
      default: 
        break;
    }
  }
  
}
/**
 * Render the Form common footer
 * ---------------------------------------------------------------------------
 */
?>
    
    <div class="form-group">
      <div class="col-sm-offset-2 col-sm-10">
        <button type="submit" class="btn btn-primary"><?php _e('Register Data', CDBT); ?></button>
      </div>
    </div>
    
  </form>
</div>
