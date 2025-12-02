<?php
// Common functions
// Formatting for bytes
function simpledir_format_bytes($size) {
	$units = array('B', 'KB', 'MB', 'GB', 'TB');
	for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
	return round($size, 2).$units[$i];
}

// Get config settings from file
function simpledir_loadconf($refresh = false) {
	// Initialize
	simpledir_init();

	// Load the data
	static $vals = array();

	if ($refresh || empty($vals)) {
		$configfile = SIMPLEDIR_CONFIGFILE;

		if (!file_exists($configfile)) {
			return array(
				'dirpath' => GSDATAUPLOADPATH,
				'urlpath' => '/' . str_replace(GSROOTPATH, '', GSDATAUPLOADPATH),
				'ignore'	=> array('php')
			);
		}

		$xml_root = simplexml_load_file($configfile);

		if ($xml_root !== FALSE) {
			$vals = array();
			$node = $xml_root->children();

			$vals['dirpath'] = (string)$node->dirpath;
			$vals['urlpath'] = (string)$node->urlpath;
			$vals['ignore'] =	explode(',', (string)$node->ignore);

			if (empty($vals['dirpath'])) {
				$vals['dirpath'] = GSDATAUPLOADPATH;
			}

			if (empty($vals['urlpath'])) {
				$vals['urlpath'] = '/' . str_replace(GSROOTPATH, '', GSDATAUPLOADPATH);
			}
		}
	}

	return $vals;
}

// Initialize the plugin
function simpledir_init() {
	$configfile = SIMPLEDIR_CONFIGFILE;
	$succ = true;

	if (!file_exists($configfile)) {
		// Initialize the file
		$succ = simpledir_saveconf(array(
			'dirpath' => GSDATAUPLOADPATH,
			'urlpath' => str_replace(GSROOTPATH, '', '/' . GSDATAUPLOADPATH),
			'ignore'	=> array('php'),
		));

		// Ensure file permissions are correct
		if ($succ && file_exists($configfile)) {
			if (defined('GSCHMOD')) {
				$succ = chmod($configfile, GSCHMOD);
			} else {
				$succ = chmod($configfile, 0755);
			}
		}
	}

	return $succ;
}

// Save config settings to file
function simpledir_saveconf($simpledir_conf) {
	// Ensure ignore is an array
	if (is_string($simpledir_conf['ignore'])) {
		$simpledir_conf['ignore'] = explode(',', $simpledir_conf['ignore']);
	}
	
	// Build the XML
	$xml_root = new SimpleXMLElement('<settings></settings>');
	$xml_root->addchild('dirpath', $simpledir_conf['dirpath']);
	$xml_root->addchild('urlpath', $simpledir_conf['urlpath']);
	$xml_root->addchild('ignore', implode(',', $simpledir_conf['ignore']));

	return $xml_root->asXML(SIMPLEDIR_CONFIGFILE) !== false;
}

// Get an array of the files/subdirs in a directory
function return_simpledir_results($params = array()) {
	// Default parameters
	$params = array_merge(array(
		'dirpath' => null,
		'urlpath' => null,
		'ignore'	=> array(),
		'order'	 => '+name',
	), $params);

	$dirpath = $params['dirpath'];
	$urlpath = $params['urlpath'];
	$ignore	= $params['ignore'];

	// Copy the global $simpledir_conf
	$simpledir_conf = array_merge(array(), simpledir_loadconf());

	// Merge defaults
	if (!empty($dirpath)) {
		$simpledir_conf['dirpath'] .= $dirpath;
	}

	if (!empty($urlpath)) {
		$simpledir_conf['urlpath'] .= $urlpath;
	}

	$simpledir_conf['ignore'] = $ignore;

	$simpledir_dir = $simpledir_conf['dirpath'];

	// check for directory traversal attempt and scrub to base directory
	$realDirPath = realpath($simpledir_conf['dirpath']);
	$realCurrentDir = realpath($simpledir_dir);
	
	if ($realDirPath === false || $realCurrentDir === false || strpos($realCurrentDir, $realDirPath) !== 0) {
		$simpledir_dir = $simpledir_conf['dirpath'];
	}

	// rebuild clean param for links
	$currentdir = '';
	if ($realDirPath !== false && $realCurrentDir !== false) {
		$relativePath = substr($realCurrentDir, strlen($realDirPath));
		if ($relativePath !== '') {
			$currentdir = $relativePath . '/';
		}
	}

	// display list of files
	$dir_handle = @opendir($simpledir_dir);
	if ($dir_handle === false) {
		return array(
			'files'	 => array(),
			'subdirs' => array(),
			'total'	 => 0,
		);
	}

	$filearray	 = array();
	$subdirarray = array();

	// get files
	$filetot = 0;

	while (($filename = readdir($dir_handle)) !== false) {
		// ignore dot files.
		if (substr($filename, 0, 1) !== '.') {
			$fullPath = $simpledir_dir . $filename;
			
			// if directory
			if (is_dir($fullPath)) {
				$subdirarray[] = array(
					'name' => $filename,
					'date' => date("Y/m/d H:i:s", filemtime($fullPath)),
					'size' => null,
					'type' => 'directory'
				);
			} elseif (is_file($fullPath) && !in_array(strtolower(pathinfo($filename, PATHINFO_EXTENSION)), $simpledir_conf['ignore'])) {
				$filesize = filesize($fullPath);
				$filearray[] = array(
					'name' => $filename,
					'date' => date("Y/m/d H:i:s", filemtime($fullPath)),
					'size' => $filesize,
					'type' => strtolower(pathinfo($filename, PATHINFO_EXTENSION))
				);
				$filetot += $filesize;
			}
		}
	}
	closedir($dir_handle);

	// Sort the files
	$order = $params['order'];
	$asc	 = substr($order, 0, 1);

	if ($asc == '+' || $asc == '-') {
		$order = substr($order, 1);
	} else {
		$asc = '+';
	}

	// Use anonymous functions for sorting (PHP 5.3+)
	$sortFunction = function($a, $b) use ($order, $asc) {
		$multiplier = ($asc === '+') ? 1 : -1;
		
		if ($order == 'size') {
			return ($a["size"] - $b["size"]) * $multiplier;
		} elseif ($order == 'date') {
			return (strtotime($a["date"]) - strtotime($b["date"])) * $multiplier;
		} else {
			return strcmp($a["name"], $b["name"]) * $multiplier;
		}
	};

	usort($filearray, $sortFunction);
	usort($subdirarray, $sortFunction);

	return array(
		'files'	 => $filearray,
		'subdirs' => $subdirarray,
		'total'	 => $filetot,
	);
}

function simpledir_i18n($hash, $echo = false) {
	return i18n(SIMPLEDIR . '/' . $hash, $echo);
}