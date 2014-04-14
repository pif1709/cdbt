<?php
if ($_SERVER['SCRIPT_FILENAME'] == __FILE__) die();

if (is_admin() && !check_admin_referer(self::DOMAIN .'_list', '_cdbt_token')) 
	die(__('access is not from admin panel!', self::DOMAIN));

foreach ($_REQUEST as $k => $v) {
	${$k} = $v;
//var_dump($k .' = "'. $v .'"');
}
if (!isset($mode)) 
	$mode = 'list';
if (!isset($_cdbt_token)) 
	$_cdbt_token = wp_create_nonce(self::DOMAIN .'_'. $mode);

list($result, $table_name, $table_schema) = $this->get_table_schema();
if ($result && !empty($table_name) && !empty($table_schema)) {
	create_console_menu($_cdbt_token);
	
	$page_num = (!isset($page_num) || empty($page_num)) ? 1 : intval($page_num);
	if (!isset($per_page) || empty($per_page)) {
		for ($i=0; $i<count($this->options['tables']); $i++) {
			if ($this->options['tables'][$i]['table_name'] == $this->current_table) 
				$data = intval($this->options['tables'][$i]['show_max_records']);
		}
		$per_page = (!empty($data) && $data > 0) ? $data : intval(get_option('posts_per_page', 10));
	} else {
		$per_page = intval($per_page);
	}
	$list_html = '<h3 class="dashboard-title">%s</h3>%s<table id="'. $table_name .'" class="table table-bordered table-striped table-hover">%s%s</table>%s';
	list($result, $value) = $this->get_table_comment($table_name);
	if ($result) {
		$title = sprintf(__('%s table (table comment: %s)', self::DOMAIN), $table_name, $value);
	} else {
		$title = sprintf(__('%s table', self::DOMAIN), $table_name);
	}
	$information_html = '';
	if (wp_verify_nonce($_cdbt_token, self::DOMAIN .'_'. $mode)) {
		$list_index_row = $list_rows = $pagination = null;
		$nonce_field = wp_nonce_field(self::DOMAIN .'_'. $mode, '_cdbt_token', true, false);
		
		$limit = $per_page;
		$offset = ($page_num - 1) * $limit;
		$view_cols = null; // array('ID', 'code_number', 'name', 'bin_data', 'created', 'updated'); // This value is null when all columns display.
		$order_by = null; // null eq array('created' => 'DESC')
		if (isset($action) && $action == 'search') {
			if (isset($search_key) && !empty($search_key)) {
				$data = $this->find_data($table_name, $table_schema, $search_key, $view_cols, $order_by, $limit, $offset);
			}
		} else {
			// $order_by['name'] = 'ASC';
			$data = $this->get_data($table_name, $view_cols, null, $order_by, $limit, $offset);
		}
		$is_controller = true;
		$is_checkbox_controller = false;
		$is_display_list_num = true;
		
		if ($is_controller) {
			$controller_block_base = '<form method="post" class="controller-form" role="form">%s';
			$controller_block_base .= ($mode == 'list') ? '</form>' : '';
			$controller_block_title = __('Cosole', self::DOMAIN);
			$search_key = (!isset($search_key)) ? '' : $search_key;
			$search_key_placeholder = __('Search keyword', self::DOMAIN);
			$search_button_label = __('Search', self::DOMAIN);
			$content = <<<NAV
<nav class="navbar navbar-default" role="navigation">
	<div class="container-fluid">
		<span class="navbar-brand">$controller_block_title</span>
		<input type="hidden" name="mode" value="$mode" />
		<input type="hidden" name="action" value="" />
		<input type="hidden" name="ID" value="" />
		$nonce_field
	</div>
	<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
		<div class="navbar-form navbar-right" role="search">
			<div class="form-group">
				<input type="text" name="search_key" class="form-control" placeholder="$search_key_placeholder" value="$search_key" />
			</div>
			<button type="button" class="btn btn-default" id="search_items" data-mode="$mode" data-action="search"><span class="glyphicon glyphicon-search"></span> $search_button_label</button>
		</div>
	</div>
</nav>
NAV;
			$controller_block = sprintf($controller_block_base, $content);
		} else {
			$controller_block = null;
		}
		
		if (!empty($data)) {
			$list_num = 1 + (($page_num - 1) * $per_page);
			foreach ($data as $record) {
				if ($list_num == (1 + (($page_num - 1) * $per_page))) {
					$list_index_row = '<thead><tr>';
					$list_index_row .= ($is_checkbox_controller) ? '<th><input type="checkbox" id="all_checkbox_controller" /></th>' : '';
					$list_index_row .= ($is_display_list_num) ? '<th>'. __('No.', self::DOMAIN) .'</th>' : '';
					foreach ($record as $key => $val) {
						if (array_key_exists($key, $table_schema)) 
							$key = $table_schema[$key]['logical_name'];
						$list_index_row .= '<th>'. $key .'</th>';
					}
					$list_index_row .= ($mode == 'edit') ? '<th>'. __('Controll', self::DOMAIN) .'</th>' : '';
					$list_index_row .= '</tr></thead>';
				}
				$list_rows .= '<tr>';
				$list_rows .= ($is_checkbox_controller) ? '<th><input type="checkbox" id="checkbox_controller_'. $list_num .'" class="inherit_checkbox" value="'. $record->ID .'" /></th>' : '';
				$list_rows .= ($is_display_list_num) ? '<td>'. $list_num .'</td>' : '';
				$is_include_binary_file = false;
				foreach ($record as $key => $val) {
					// strlen('a:*:{s:11:"origin_file";') = 24
					$is_binary = (preg_match('/^a:\d:\{s:11:\"origin_file\"\;$/i', substr($val, 0, 24))) ? true : false;
					$is_include_binary_file = ($is_binary) ? true : $is_include_binary_file;
					$val = ($is_binary) ? '<a href="#"><span class="glyphicon glyphicon-paperclip"></span></a>' : $val;
					$list_rows .= '<td>'. $val .'</td>';
				}
				if ($mode == 'edit') {
					$list_rows .= '<td><div class="btn-group-vertical">';
					$list_rows .= "\t" . '<button type="button" class="btn btn-default btn-sm edit-row" data-id="'. $record->ID .'" data-mode="input" data-action="update"><span class="glyphicon glyphicon-edit"></span> '. __('Edit', self::DOMAIN) .'</button>';
					if ($is_include_binary_file) 
						$list_rows .= "\t" . '<button type="button" class="btn btn-default btn-sm download-binary" data-id="'. $record->ID .'" data-mode="edit" data-action="download"><span class="glyphicon glyphicon-download"></span> '. __('Download', self::DOMAIN) .'</button>';
					$list_rows .= "\t" . '<button type="button" class="btn btn-default btn-sm delete-row" data-id="'. $record->ID .'" data-mode="edit" data-action="delete" data-toggle="modal" data-target=".confirmation"><span class="glyphicon glyphicon-trash"></span> '. __('Delete', self::DOMAIN) .'</button>';
					$list_rows .= '</div></td>';
				}
				$list_rows .= '</tr>';
				$list_num++;
			}
			
			$total_data = $this->get_data($table_name, 'COUNT(*)');
			foreach (array_shift($total_data) as $val) {
				$total_data = intval($val);
				break;
			}
			if ($total_data > $per_page) {
				$pagination = $this->create_pagination(intval($page_num), intval($per_page), $total_data, $mode);
			} else {
				$pagination = null;
			}
			$pagination .= <<<EOH
<form method="post" class="change-page" role="form">
	<input type="hidden" name="mode" value="$mode" />
	<input type="hidden" name="page" value="$page_num" />
	<input type="hidden" name="per_page" value="$per_page" />
	$nonce_field
</form>
EOH;
			$pagination = (($mode == 'edit') ? '</form>' : '') . $pagination;
			printf($list_html, $title, $information_html.$controller_block, $list_index_row, '<tbody>' . $list_rows . '</tbody>', $pagination);
		} else {
			if (isset($action) && $action == 'search') {
				$msg_str = sprintf(__('No data to match for "%s".', self::DOMAIN), $search_key);
			} else {
				$msg_str = __('Data is none.', self::DOMAIN);
			}
			$information_html = '<div class="alert alert-info">'. $msg_str .'</div>';
			printf($list_html, $title, $controller_block, '', '', $information_html);
		}
	}
} else {
?>
	<div class="alert alert-info"><?php _e('The enabled tables is not exists currently.<br />Please create tables.', self::DOMAIN); ?></div>
<?php
}

create_console_footer();
