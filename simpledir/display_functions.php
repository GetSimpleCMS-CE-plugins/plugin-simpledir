<?php
// Display functions
function simpledir_display_callback($matches) {
	$_params = $matches[1];
	$_params = explode('|', $_params);
	$params = array();
	$args = array();

	foreach ($_params as $param) {
		$param = explode('=', $param);
		$key = trim($param[0], '" ');

		if (isset($param[1])) {
			$value = trim($param[1], '" ');
			$params[$key] = $value;
		}
	}

	// Set default values to avoid undefined array key warnings
	if (!isset($params['dirpath'])) {
		$params['dirpath'] = null;
	}

	if (!isset($params['urlpath'])) {
		$params['urlpath'] = null;
	}

	if (!isset($params['ignore'])) {
		$params['ignore'] = array();
	} else {
		$params['ignore'] = explode(',', $params['ignore']);
	}

	if (!isset($params['key'])) {
		$params['key'] = 'dir';
	}

	if (!isset($params['order'])) {
		$params['order'] = '+name';
	}

	if (!isset($params['columns'])) {
		$params['columns'] = array('name', 'date', 'size');
	} else {
		$params['columns'] = explode(',', $params['columns']);
	}

	if (!isset($params['showinitial'])) {
		$params['showinitial'] = 0;
	} else {
		$params['showinitial'] = (int) $params['showinitial'];
	}

	// Remove DataTables-specific parameters since we're not using it anymore
	if (isset($params['showfilter'])) {
		unset($params['showfilter']);
	}
	
	if (isset($params['sortable'])) {
		unset($params['sortable']);
	}

	return return_simpledir_display($params);
}

/***********************************************************************************
*
* Public Functions
*
***********************************************************************************/

// Return the HTML table of files in a directory
function return_simpledir_display($params = array()) {
	// Default parameters
	$defaultColumns = array('name', 'date', 'type', 'size');
	$params = array_merge(array(
		'dirpath' => null,
		'urlpath' => null,
		'ignore'	=> array(),
		'key'		 => 'dir',
		'columns' => array('name', 'date', 'size'),
		'showinitial' => 0,
		'LABEL_NAME'	=> simpledir_i18n('LABEL_NAME'),
		'LABEL_SIZE'	=> simpledir_i18n('LABEL_SIZE'),
		'LABEL_DATE'	=> simpledir_i18n('LABEL_DATE'),
	), $params);

	$dirpath = $params['dirpath'];
	$urlpath = $params['urlpath'];
	$ignore	= $params['ignore'];
	$key		 = $params['key'];

	$simpledir_conf = array_merge(array(), simpledir_loadconf());
	$simpledir_conf['ignore'] = $ignore;

	$tmp_content = '';
	$currentdir = "";

	if((isset($_GET[$key])) && ($_GET[$key]<>'')) {
		$currentdir = urldecode($_GET[$key]) . '/';
	}

	$pretty_urls = isset($GLOBALS['PRETTYURLS']) && (string) $GLOBALS['PRETTYURLS'] == '1';
	$current_url = explode('?', $_SERVER["REQUEST_URI"]);
	$current_url = $current_url[0];
	$current_url = $pretty_urls ? $current_url : '';

	// Copy the $_GET parameters to a new variable (used for generating full url correctly)
	$query = array();

	foreach ($_GET as $k => $v) {
		$query[$k] = $v;
	}

	// Remove the id parameter is pretty urls are disabled
	if (isset($query['id']) && $pretty_urls) {
		unset($query['id']);
	}

	if ($currentdir == "") {
		$simpledir_dir = $simpledir_conf['dirpath'];
	} else {
		$simpledir_dir = $simpledir_conf['dirpath'] . $currentdir;
	}

	$list = return_simpledir_results(array_merge($params, array(
		'dirpath' => ($dirpath ?? '') . $currentdir,
		'urlpath' => $urlpath ?? '',
		'ignore'	=> $ignore,
		'order'	 => $params['order'],
	)));

	// Check for directory traversal attempt and scrub to base directory
	$realDirPath = realpath($simpledir_conf['dirpath']);
	$realCurrentDir = realpath($simpledir_dir);
	
	if ($realDirPath === false || $realCurrentDir === false || strpos($realCurrentDir, $realDirPath) !== 0) {
		$simpledir_dir = $simpledir_conf['dirpath'];
		$currentdir = "";
	}

	if ($currentdir !== '') {
		$currentdir = rtrim($currentdir, '/') . '/';
	}

	$simpledir_content = '';

	// display list of files
	$filearray = $list['files'];
	$subdirarray = $list['subdirs'];

	// Add responsive wrapper
	$simpledir_content .= '<div class="simpledir-container">';
	$simpledir_content .= '<table class="simpledir-table">';

	if ($currentdir == "") {
		$simpledir_content .= '<caption>' . simpledir_i18n('DIR_LIST') . '</caption>';
	} else {
		$simpledir_content .= '<caption>' . htmlspecialchars(str_replace('%s', $currentdir, simpledir_i18n('SUBDIR_LIST'))) . '</caption>';
	}

	// Columns
	$columns = array_intersect($defaultColumns, $params['columns']);
	$simpledir_content .= '<thead><tr>';

	if (in_array('name', $columns)) {
		$simpledir_content .= '<th scope="col" class="col-name">' . htmlspecialchars($params['LABEL_NAME']) . '</th>';
	}

	if (in_array('date', $columns)) {
		$simpledir_content .= '<th scope="col" class="col-date">' . htmlspecialchars($params['LABEL_DATE']) . '</th>';
	}

	if (in_array('size', $columns)) {
		$simpledir_content .= '<th scope="col" class="col-size">' . htmlspecialchars($params['LABEL_SIZE']) . '</th>';
	}

	$simpledir_content .= '</tr></thead>';

	// generate listing:
	$simpledir_content .= '<tbody>';

	$rowclass = "";

	// up to parent
	if ($currentdir !== '') {
		$parentdir = dirname($currentdir);
		$parentdir = ($parentdir == '.') ? '' : $parentdir;

		$query[$key] = $parentdir;

		$simpledir_content .= '<tr' . $rowclass . '>';

		if (in_array('name', $columns)) {
			$simpledir_content .= '<td class="col-name" data-label="' . htmlspecialchars($params['LABEL_NAME']) . '"><a href="' . htmlspecialchars($current_url .	'?' . http_build_query($query))
												 . '" title="' . htmlspecialchars(simpledir_i18n('PARENT_DIR')) . '"><img src="' . htmlspecialchars(SIMPLEDIR_IMGURL) . '/upfolder.png" width="16" height="16" alt="' . htmlspecialchars(simpledir_i18n('PARENT_DIR')) . '">&nbsp;' . htmlspecialchars(simpledir_i18n('PARENT_DIR')) . '</a></td>';
		}

		if (in_array('date', $columns)) {
			$simpledir_content .= '<td class="col-date" data-label="' . htmlspecialchars($params['LABEL_DATE']) . '"></td>';
		}

		if (in_array('size', $columns)) {
			$simpledir_content .= '<td class="col-size" data-label="' . htmlspecialchars($params['LABEL_SIZE']) . '"></td>';
		}

		$simpledir_content .= '</tr>';
		$rowclass = ' class="alt"';
	}

	// subdirectories
	$filecount = count($subdirarray);

	if ($filecount > 0) {
		foreach ($subdirarray as $file) {
			$query[$key] = $currentdir . $file['name'];

			$simpledir_content .= '<tr' . $rowclass . '>';

			if (in_array('name', $columns)) {
				$simpledir_content .= '<td class="col-name" data-label="' . htmlspecialchars($params['LABEL_NAME']) . '"><a href="' . htmlspecialchars($current_url .	'?' . http_build_query($query))
												 . '"><img src="' . htmlspecialchars(SIMPLEDIR_IMGURL) . 'folder.png" width="16" height="16" alt="' . htmlspecialchars($file['name']) . '">&nbsp;' . htmlspecialchars($file['name']) . '</a></td>';
			}

			if (in_array('date', $columns)) {
				$simpledir_content .= '<td class="col-date" data-label="' . htmlspecialchars($params['LABEL_DATE']) . '">' . htmlspecialchars($file['date']) . '</td>';
			}

			if (in_array('size', $columns)) {
				$simpledir_content .= '<td class="col-size" data-label="' . htmlspecialchars($params['LABEL_SIZE']) . '"></td>';
			}

			$simpledir_content .= '</tr>';

			if ($rowclass === "") {
				$rowclass = ' class="alt"';
			} else {
				$rowclass = "";
			}
		}
	}

	// files
	$filecount = count($filearray);

	if ($filecount > 0) {
		foreach ($filearray as $file) {
			$simpledir_content .= '<tr' . $rowclass . '>';

			if (in_array('name', $columns)) {
				$fileUrl = $simpledir_conf['urlpath'] . ($urlpath ?? '') . $currentdir . $file['name'];
				$simpledir_content .= '<td class="col-name" data-label="' . htmlspecialchars($params['LABEL_NAME']) . '"><a href="' . htmlspecialchars($fileUrl) . '" download>'
						 . '<img src="' . htmlspecialchars(SIMPLEDIR_IMGURL) . '/' . htmlspecialchars($file['type']) . '.png" width="16" height="16" alt="' . htmlspecialchars($file['name']) . '">&nbsp;' . htmlspecialchars($file['name'])
						 . '</a></td>';
			}

			if (in_array('date', $columns)) {
				$timestamp = strtotime($file['date']);
				$simpledir_content .= '<td class="col-date" data-label="' . htmlspecialchars($params['LABEL_DATE']) . '">' . htmlspecialchars($file['date']) . '</td>';
			}

			if (in_array('size', $columns)) {
				$simpledir_content .= '<td class="col-size" data-label="' . htmlspecialchars($params['LABEL_SIZE']) . '">' . htmlspecialchars(simpledir_format_bytes($file['size'])) . '</td>';
			}

			$simpledir_content .= '</tr>';

			if ($rowclass === "") {
				$rowclass = ' class="alt"';
			} else {
				$rowclass = "";
			}
		}
	}

	$simpledir_content .= '</tbody><tfoot><tr><td colspan="' . count($columns) . '">';

	if ($filecount == 1) {
		$simpledir_content .= $filecount . ' file';
	} else {
		$simpledir_content .= $filecount . ' files';
	}

	$simpledir_content .= ' totaling ' . htmlspecialchars(simpledir_format_bytes($list['total']));
	$simpledir_content .= '</td></tr></tfoot></table>';
	$simpledir_content .= '</div>'; // Close simpledir-container

	return $simpledir_content;
}

/***********************************************************************************
*
* Display Functions
*
***********************************************************************************/
// Print the HTML table of the files in a directory
function get_simpledir_display($params = array()) {
	echo return_simpledir_display($params);
}