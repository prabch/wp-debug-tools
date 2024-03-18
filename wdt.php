<?php
define("MU_PATH", "wp-content/mu-plugins");
define("ER_URL", "https://raw.githubusercontent.com/mbissett/error-revealer/master/error-revealer.php");
define("BACKUP_CONFIG_FILENAME", "wp-config.my-backup");
define("LOGVIEW_FILEZIE_LIMIT_MB", 50);

$config_constants = [
    'WP_DEBUG' => 'true',
    'WP_DEBUG_LOG' => 'true',
    'WP_DEBUG_DISPLAY' => 'false',
    'SCRIPT_DEBUG' => 'false',
];

$debug_log_file_path = __DIR__ . '/wp-content/debug.log';
$highlightable_errors = ['exception', 'fatal', 'exhausted'];

$home_url = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
$current_url = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$current_view = isset($_GET['view']) ? $_GET['view'] : "";
$highlight_errors = (isset($_GET["highlight-errors"]) && $_GET["highlight-errors"] == "true") ? true : false;
$filter_errors = (isset($_GET["filter-errors"]) && $_GET["filter-errors"] == "true") ? true : false;
$bypass_fs_limit = (isset($_GET["bypass-file-limit"]) && $_GET["bypass-file-limit"] == "true") ? true : false;
$error = (isset($_GET["error"])) ? $_GET["error"] : "unknown error";
$debug_enabled = true;
$random = time();

if($current_view == "debug-log-viewer") {
	$wp_config = new WPConfigTransformer( 'wp-config.php' );

	foreach ($config_constants as $key => $value) {
		if($wp_config->get_value( 'constant', $key) != "'$value'")$debug_enabled = false;
	}

	if (!$debug_enabled && isset($_GET["enable-debugging"]) && $_GET["enable-debugging"] == "true") {
		//install error revealer
		if (isset($_GET["enable-er"]) && $_GET["enable-er"] == "true") {
			$file_name = basename(ER_URL);
			if (file_exists($file_name) || file_exists(MU_PATH . '/' . $file_name)) {
				$location = $home_url . '?view=error&error=error revealer is already installed';
				header("Location: $location");
				die();
			}

			if (!file_put_contents($file_name . $random, file_get_contents(ER_URL))) {
				$location = $home_url . '?view=error&error=failed to download error revealer from source';
				header("Location: $location");
				die();
			}

			if (!file_exists(MU_PATH)) mkdir(MU_PATH, 0755, true);
			rename($file_name . $random, MU_PATH . '/' . $file_name);
			//unlink($file_name . $random);
		}

		//enable debugging
		if (!file_exists(BACKUP_CONFIG_FILENAME)) {
			copy('wp-config.php', BACKUP_CONFIG_FILENAME);
			foreach ($config_constants as $key => $value) {
				$wp_config->update( 'constant', $key, $value);
			}
		} else {
			$location = $home_url . '?view=error&error=wp-config.php backup file already exists';
			header("Location: $location");
			die();
		}

		$debug_enabled = true;
	}

	if ($debug_enabled && isset($_GET["disable-debugging"]) && $_GET["disable-debugging"] == "true") {
		//delete error revealer
		if (file_exists(MU_PATH . '/error-revealer.php')) {
			unlink(MU_PATH . '/error-revealer.php');
		}

		//@todo: store the er filename and if mu-plugins folder was there in the update wp-config and refer to it over here

		//disable debugging
		if (file_exists(BACKUP_CONFIG_FILENAME)) {
			copy(BACKUP_CONFIG_FILENAME, BACKUP_CONFIG_FILENAME . $random);
			unlink('wp-config.php');
			rename(BACKUP_CONFIG_FILENAME, 'wp-config.php');
			unlink(BACKUP_CONFIG_FILENAME . $random);
		} else {
			$location = $home_url . '?view=error&error=wp-config.php backup file does not exist';
			header("Location: $location");
			die();
		}

		$debug_enabled = false;
	}

	if ($debug_enabled && isset($_GET["download-log-file"]) && $_GET["download-log-file"] == "true") {
		if (file_exists($debug_log_file_path)) {
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="' . basename($debug_log_file_path) . '"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($debug_log_file_path));
			flush();
			readfile($debug_log_file_path);
			exit;
		} else {
			echo "Debug log file does not exist.";
		}
	}

	if ($debug_enabled && isset($_GET["rotate-log-file"]) && $_GET["rotate-log-file"] == "true") {
		if (file_exists($debug_log_file_path)) {
			$path_info = pathinfo($debug_log_file_path);
			$new_name = $path_info['dirname'] . DIRECTORY_SEPARATOR . $path_info['filename'] . '-' . $random . '.' . $path_info['extension'];
			rename($debug_log_file_path, $new_name);
		}
	}

	if ($debug_enabled && isset($_GET["delete-log-file"]) && $_GET["delete-log-file"] == "true") {
		if (file_exists($debug_log_file_path)) {
			unlink($debug_log_file_path);
		}
	}
} else if($current_view == "delete-tool") {
	unlink(__FILE__);
	$location = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
	header("Location: $location");
	die();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>WordPress Debugging Tools</title>
    <style>
        body {
            margin: 0;
            font-family: 'Courier New', monospace;
            background-color: #1e1e1e;
            color: #d4d4d4;
        }

        /* Common styles for buttons and submit inputs */
        button, input[type="submit"] {
            padding: 10px 20px;
            background-color: #007acc;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover, input[type="submit"]:hover {
            background-color: #005a9e;
        }

		button.btn-red, input[type="submit"].btn-red{
			background-color: #cc0046;
		}

        button.btn-red:hover, input[type="submit"].btn-red:hover {
			background-color: #930535;
		}

        /* Ribbon bar styles */
        .ribbon-bar {
            background-color: #2c2c2c;
            padding: 10px 0;
            overflow: hidden;
            display: flex;
            justify-content:flex-start; /* Distribute buttons evenly */
            flex-wrap: wrap;
        }

        .ribbon-bar form {
            margin: 10px;
        }

        /* Content area styles */
        .content-area {
            padding: 20px;
            overflow-y: auto;
        }

        /* Landing page container */
        .landing-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 50px;
            padding: 20px;
            background-color: #252526;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            margin: auto;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .landing-container form,
        .landing-container input,
        .landing-container label,
        .landing-container button {
            font-family: 'Arial', sans-serif; /* Arial is a clean and widely available font */
        }

        .landing-container form,
        .landing-container input[type="text"],
		.landing-container input[type="submit"],
        .landing-container button,
		.landing-container a {
			width: 100%;
        }

        .header {
            font-size: 24px;
            font-family: 'Verdana', sans-serif; /* Use Verdana, a web-safe font */
        }

        /* Styles for text inputs and checkboxes */
        input[type="text"], .checkbox-container {
            width: 100%;
            margin-top: 5px;
            margin-bottom: 20px; /* Increase space between inputs */
        }

        input[type="text"] {
            padding: 10px;
            border: 1px solid #333;
            background-color: #333336;
            color: #d4d4d4;
            box-sizing: border-box;
        }

        .checkbox-container {
            display: flex;
            align-items: center;
        }

        input[type="checkbox"] {
            accent-color: #007acc;
        }

        .checkbox-label {
            margin-left: 10px;
        }

		span.tip {
			display: block;
			font-size: 13px;
			color: gray;
			margin-bottom: 20px;
			margin-top: -10px;
		}

        /* Log entry styles */
        .log-entry {
            background-color: #333336;
            border-left: 5px solid #007acc;
            padding: 10px;
            margin-bottom: 10px;
            white-space: pre-wrap;
        }

		.log-entry.highlight{
			background-color: #5d0000;
    		border-left: 5px solid #cc003b;
		}

        /* Responsive styles */
        @media (max-width: 600px) {
            .ribbon-bar form {
                width: 100%; /* Full width on small screens */
                margin: 5px 0; /* Stack buttons vertically */
            }
        }
    </style>
</head>
<body>

	<?php if($current_view == "debug-log-viewer"):?>
		<?php if(!$debug_enabled):?>
			<div class="landing-container">
				<div class="header">Debug Log Viewer</div>
				<form method="get" action="<?php echo $current_url; ?>">
					<input type="checkbox" name="enable-er" id="enable-er" value="true" checked>
					<label for="enable-er" class="checkbox-label">Install Error Revealer</label>
					<br><br>
					<input type="checkbox" name="enable-debugging" id="enable-debugging" value="true" checked>
					<label for="enable-debugging" class="checkbox-label">Modify wp-config.php file</label>
					<br><br>
					<input type="hidden" name="view" value="debug-log-viewer">
					<input type="submit" value="Enable Debugging" class="btn-red"/>
				</form>
				<a href="<?php echo $home_url; ?>"><button>Home</button></a>
			</div>
		<?php else:?>
			<div class="ribbon-bar">
				<form method="get" action="<?php echo $home_url; ?>">
					<div class="form-field">
						<input type="hidden" name="view" value="debug-log-viewer">
						<?php if($bypass_fs_limit):?><input type="hidden" name="bypass-file-limit" value="true"><?php endif;?>
						<input type="hidden" name="highlight-errors" value="<?php echo ($highlight_errors == true) ? "false" : "true";?>">
						<input type="submit" value="Highlight errors" class="<?php echo ($highlight_errors == true) ? "btn-red" : "";?>"/>
					</div>
				</form>

				<form method="get" action="<?php echo $home_url; ?>">
					<div class="form-field">
						<input type="hidden" name="view" value="debug-log-viewer">
						<?php if($bypass_fs_limit):?><input type="hidden" name="bypass-file-limit" value="true"><?php endif;?>
						<input type="hidden" name="filter-errors" value="<?php echo ($filter_errors == true) ? "false" : "true";?>">
						<input type="submit" value="Filter errors" class="<?php echo ($filter_errors == true) ? "btn-red" : "";?>"/>
					</div>
				</form>

				<form method="get" action="<?php echo $home_url; ?>">
					<div class="form-field">
						<input type="hidden" name="view" value="debug-log-viewer">
						<input type="hidden" name="download-log-file" value="true">
						<input type="submit" value="Download log file"/>
					</div>
				</form>

				<form method="get" action="<?php echo $home_url; ?>">
					<div class="form-field">
						<input type="hidden" name="view" value="debug-log-viewer">
						<input type="hidden" name="rotate-log-file" value="true">
						<input type="submit" value="Rotate log file"/>
					</div>
				</form>

				<form method="get" action="<?php echo $home_url; ?>">
					<div class="form-field">
						<input type="hidden" name="view" value="debug-log-viewer">
						<input type="hidden" name="delete-log-file" value="true">
						<input type="submit" value="Delete log file" class="btn-red"/>
					</div>
				</form>

				<form method="get" action="<?php echo $home_url; ?>">
					<div class="form-field">
						<input type="hidden" name="view" value="debug-log-viewer">
						<input type="hidden" name="disable-debugging" value="true">
						<input type="submit" value="Disable Debugging" />
					</div>
				</form>

				<form method="get" action="<?php echo $home_url; ?>">
					<div class="form-field">
					<a href="<?php echo $home_url; ?>"><button>Home</button></a>
					</div>
				</form>

				
			</div>
			<div class="content-area">
				<?php
					if (file_exists($debug_log_file_path)) {
						if (filesize($debug_log_file_path) > LOGVIEW_FILEZIE_LIMIT_MB * 1024 * 1024 && !$bypass_fs_limit) {
							echo '<div class="log-entry highlight">Log file is larger than ' . LOGVIEW_FILEZIE_LIMIT_MB . 'MB. Its recommended to download the file and view using a text editor. </div>';
							echo '<form method="get" action="' . $home_url. '"><div class="form-field"><input type="hidden" name="view" value="debug-log-viewer"><input type="hidden" name="bypass-file-limit" value="true"><input type="submit" value="Bypass the limitation"/></div></form>';
						} else {
							$error_logs = file($debug_log_file_path);
							if (is_array($error_logs)) {
								$error_logs = array_reverse($error_logs);
								foreach ($error_logs as $log) {
									$is_error = false;
									if ($filter_errors || $highlight_errors) $is_error = string_contains_any_of_array($log, $highlightable_errors);

									if ($filter_errors) {
										if ($is_error) echo '<div class="log-entry highlight">' . $log . '</div>';
									} else {
										echo '<div class="' . (($is_error) ? 'log-entry highlight' : 'log-entry') . '">' . $log . '</div>';
									}
								}
							}
						}
					} else {
						echo '<div class="log-entry">No error logs found</div>';
					}

				?>
			</div>
		<?php endif;?>
	<?php elseif($current_view == "list-versions"):?>
		<?php $versions = wp_details(); ?>
		<div class="ribbon-bar">
				<form method="get" action="<?php echo $home_url; ?>">
					<div class="form-field">
					<a href="<?php echo $home_url; ?>"><button>Home</button></a>
					</div>
				</form>

		</div>
		<div class="content-area">
			<?php
				echo '<div class="log-entry"><b>WordPress</b><br>' . (isset($versions['wp_version']) ? $versions['wp_version'] : "failed to read") . '</div><br>';

				echo '<div class="log-entry"><b>' . (isset($versions['theme']['name']) ? $versions['theme']['name'] . '</b> (theme)' : "Theme</b>") . '<br>' . (isset($versions['theme']['version']) ? $versions['theme']['version'] : "failed to read") . '</div><br>';

				foreach ($versions['plugins'] as $plugin => $version) {
					echo '<div class="log-entry"><b>' . $plugin . '</b><br>' . $version . '</div>';
				}
			?>
		</div>
	<?php elseif($current_view == "error"): ?>
		<div class="landing-container">
			<div class="header">Error!</div>
			<div class="log-entry highlight"><?php echo $error; ?></div>
			<a href="<?php echo $home_url; ?>"><button>Home</button></a>
		</div>
	<?php else:?>
		<div class="landing-container">
			<div class="header">WordPress Debugging Tools</div>
			<form method="get" action="<?php echo $current_url; ?>">
				<input type="hidden" name="view" value="debug-log-viewer">
				<input type="submit" value="Debug Log Viewer" />
			</form>
			<form method="get" action="<?php echo $current_url; ?>">
				<input type="hidden" name="view" value="list-versions">
				<input type="submit" value="List Versions" />
			</form>
			<form method="get" action="<?php echo $current_url; ?>">
				<input type="hidden" name="view" value="delete-tool">
				<input type="submit" value="Delete This Tool" class="btn-red"/>
			</form>
			<!--<span class="tip">prabch</span>-->
		</div>
	<?php endif; ?>

</body>
</html>

<?php
	function string_contains_any_of_array($string, $array) {
		$pattern = '/' . implode('|', array_map('preg_quote', $array)) . '/i';
		return preg_match($pattern, $string) === 1;
	}

	function wp_details() {
		$wp_version = '';
		$theme_details = [];
		$plugins_details = [];

		// Get WordPress version
		if (file_exists('wp-includes/version.php')) {
			$version_file = file_get_contents('wp-includes/version.php');
			if (preg_match('/\$wp_version\s*=\s*\'([^\']+)\'/', $version_file, $matches)) {
				$wp_version = $matches[1];
			}
		}

		// Get current theme details
		$themes_dir = 'wp-content/themes';
		if ($active_theme = get_active_theme($themes_dir)) {
			$style_css = $themes_dir . '/' . $active_theme . '/style.css';
			if (file_exists($style_css)) {
				$theme_data = parse_file_data($style_css, ['Theme Name', 'Version']);
				$theme_details = [
					'name' => $theme_data['Theme Name'] ?? 'Unknown',
					'version' => $theme_data['Version'] ?? 'Unknown'
				];
			}
		}

		// Get plugins details
		$plugins_dir = 'wp-content/plugins';
		if (is_dir($plugins_dir)) {
			$dir = new DirectoryIterator($plugins_dir);
			foreach ($dir as $fileinfo) {
				if ($fileinfo->isDir() && !$fileinfo->isDot()) {
					$plugin_files = glob($fileinfo->getPathname() . '/*.php');
					foreach ($plugin_files as $file) {
						if (is_plugin_file($file)) {
							$plugin_data = parse_file_data($file, ['Plugin Name', 'Version']);
							if (!empty($plugin_data['Plugin Name'])) {
								$plugins_details[$plugin_data['Plugin Name']] = $plugin_data['Version'] ?? 'Unknown';
							}
						}
					}
				}
			}
		}

		return [
			'wp_version' => $wp_version,
			'theme' => $theme_details,
			'plugins' => $plugins_details
		];
	}

	function get_active_theme($themes_dir) {
		// Assuming the active theme will have been used more recently
		$latest_mtime = 0;
		$active_theme = '';
		foreach (new DirectoryIterator($themes_dir) as $dir) {
			if ($dir->isDir() && !$dir->isDot()) {
				$theme_dir = $dir->getPathname();
				$mtime = filemtime($theme_dir);
				if ($mtime > $latest_mtime) {
					$latest_mtime = $mtime;
					$active_theme = $dir->getFilename();
				}
			}
		}
		return $active_theme;
	}

	function is_plugin_file($file) {
		$file_contents = file_get_contents($file);
		return strpos($file_contents, 'Plugin Name:') !== false;
	}

	function parse_file_data($file, $fields) {
		$contents = file_get_contents($file);
		$data = [];
		foreach ($fields as $field) {
			if (preg_match('/' . preg_quote($field) . ':\s*(.+)/i', $contents, $matches)) {
				$data[$field] = trim($matches[1]);
			}
		}
		return $data;
	}

	/**
	 * Transforms a wp-config.php file.
	 */
	class WPConfigTransformer {
		/**
		 * Append to end of file
		 */
		const ANCHOR_EOF = 'EOF';

		/**
		 * Path to the wp-config.php file.
		 *
		 * @var string
		 */
		protected $wp_config_path;

		/**
		 * Original source of the wp-config.php file.
		 *
		 * @var string
		 */
		protected $wp_config_src;

		/**
		 * Array of parsed configs.
		 *
		 * @var array
		 */
		protected $wp_configs = array();

		/**
		 * Instantiates the class with a valid wp-config.php.
		 *
		 * @throws Exception If the wp-config.php file is missing.
		 * @throws Exception If the wp-config.php file is not writable.
		 *
		 * @param string $wp_config_path Path to a wp-config.php file.
		 */
		public function __construct( $wp_config_path ) {
			$basename = basename( $wp_config_path );

			if ( ! file_exists( $wp_config_path ) ) {
				throw new Exception( "{$basename} does not exist." );
			}

			if ( ! is_writable( $wp_config_path ) ) {
				throw new Exception( "{$basename} is not writable." );
			}

			$this->wp_config_path = $wp_config_path;
		}

		/**
		 * Checks if a config exists in the wp-config.php file.
		 *
		 * @throws Exception If the wp-config.php file is empty.
		 * @throws Exception If the requested config type is invalid.
		 *
		 * @param string $type Config type (constant or variable).
		 * @param string $name Config name.
		 *
		 * @return bool
		 */
		public function exists( $type, $name ) {
			$wp_config_src = file_get_contents( $this->wp_config_path );

			if ( ! trim( $wp_config_src ) ) {
				throw new Exception( 'Config file is empty.' );
			}
			// Normalize the newline to prevent an issue coming from OSX.
			$this->wp_config_src = str_replace( array( "\n\r", "\r" ), "\n", $wp_config_src );
			$this->wp_configs    = $this->parse_wp_config( $this->wp_config_src );

			if ( ! isset( $this->wp_configs[ $type ] ) ) {
				throw new Exception( "Config type '{$type}' does not exist." );
			}

			return isset( $this->wp_configs[ $type ][ $name ] );
		}

		/**
		 * Get the value of a config in the wp-config.php file.
		 *
		 * @throws Exception If the wp-config.php file is empty.
		 * @throws Exception If the requested config type is invalid.
		 *
		 * @param string $type Config type (constant or variable).
		 * @param string $name Config name.
		 *
		 * @return string|null
		 */
		public function get_value( $type, $name ) {
			$wp_config_src = file_get_contents( $this->wp_config_path );

			if ( ! trim( $wp_config_src ) ) {
				throw new Exception( 'Config file is empty.' );
			}

			$this->wp_config_src = $wp_config_src;
			$this->wp_configs    = $this->parse_wp_config( $this->wp_config_src );

			if ( ! isset( $this->wp_configs[ $type ] ) ) {
				throw new Exception( "Config type '{$type}' does not exist." );
			}

			return (isset($this->wp_configs[ $type ][ $name ]['value']) ? $this->wp_configs[ $type ][ $name ]['value'] : "");
		}

		/**
		 * Adds a config to the wp-config.php file.
		 *
		 * @throws Exception If the config value provided is not a string.
		 * @throws Exception If the config placement anchor could not be located.
		 *
		 * @param string $type    Config type (constant or variable).
		 * @param string $name    Config name.
		 * @param string $value   Config value.
		 * @param array  $options (optional) Array of special behavior options.
		 *
		 * @return bool
		 */
		public function add( $type, $name, $value, array $options = array() ) {
			if ( ! is_string( $value ) ) {
				throw new Exception( 'Config value must be a string.' );
			}

			if ( $this->exists( $type, $name ) ) {
				return false;
			}

			$defaults = array(
				'raw'       => false, // Display value in raw format without quotes.
				'anchor'    => "/* That's all, stop editing!", // Config placement anchor string.
				'separator' => PHP_EOL, // Separator between config definition and anchor string.
				'placement' => 'before', // Config placement direction (insert before or after).
			);

			list( $raw, $anchor, $separator, $placement ) = array_values( array_merge( $defaults, $options ) );

			$raw       = (bool) $raw;
			$anchor    = (string) $anchor;
			$separator = (string) $separator;
			$placement = (string) $placement;

			if ( self::ANCHOR_EOF === $anchor ) {
				$contents = $this->wp_config_src . $this->normalize( $type, $name, $this->format_value( $value, $raw ) );
			} else {
				if ( false === strpos( $this->wp_config_src, $anchor ) ) {
					throw new Exception( 'Unable to locate placement anchor.' );
				}

				$new_src  = $this->normalize( $type, $name, $this->format_value( $value, $raw ) );
				$new_src  = ( 'after' === $placement ) ? $anchor . $separator . $new_src : $new_src . $separator . $anchor;
				$contents = str_replace( $anchor, $new_src, $this->wp_config_src );
			}

			return $this->save( $contents );
		}

		/**
		 * Updates an existing config in the wp-config.php file.
		 *
		 * @throws Exception If the config value provided is not a string.
		 *
		 * @param string $type    Config type (constant or variable).
		 * @param string $name    Config name.
		 * @param string $value   Config value.
		 * @param array  $options (optional) Array of special behavior options.
		 *
		 * @return bool
		 */
		public function update( $type, $name, $value, array $options = array() ) {
			if ( ! is_string( $value ) ) {
				throw new Exception( 'Config value must be a string.' );
			}

			$defaults = array(
				'add'       => true, // Add the config if missing.
				'raw'       => false, // Display value in raw format without quotes.
				'normalize' => false, // Normalize config output using WP Coding Standards.
			);

			list( $add, $raw, $normalize ) = array_values( array_merge( $defaults, $options ) );

			$add       = (bool) $add;
			$raw       = (bool) $raw;
			$normalize = (bool) $normalize;

			if ( ! $this->exists( $type, $name ) ) {
				return ( $add ) ? $this->add( $type, $name, $value, $options ) : false;
			}

			$old_src   = $this->wp_configs[ $type ][ $name ]['src'];
			$old_value = $this->wp_configs[ $type ][ $name ]['value'];
			$new_value = $this->format_value( $value, $raw );

			if ( $normalize ) {
				$new_src = $this->normalize( $type, $name, $new_value );
			} else {
				$new_parts    = $this->wp_configs[ $type ][ $name ]['parts'];
				$new_parts[1] = str_replace( $old_value, $new_value, $new_parts[1] ); // Only edit the value part.
				$new_src      = implode( '', $new_parts );
			}

			$contents = preg_replace(
				sprintf( '/(?<=^|;|<\?php\s|<\?\s)(\s*?)%s/m', preg_quote( trim( $old_src ), '/' ) ),
				'$1' . str_replace( '$', '\$', trim( $new_src ) ),
				$this->wp_config_src
			);

			return $this->save( $contents );
		}

		/**
		 * Removes a config from the wp-config.php file.
		 *
		 * @param string $type Config type (constant or variable).
		 * @param string $name Config name.
		 *
		 * @return bool
		 */
		public function remove( $type, $name ) {
			if ( ! $this->exists( $type, $name ) ) {
				return false;
			}

			$pattern  = sprintf( '/(?<=^|;|<\?php\s|<\?\s)%s\s*(\S|$)/m', preg_quote( $this->wp_configs[ $type ][ $name ]['src'], '/' ) );
			$contents = preg_replace( $pattern, '$1', $this->wp_config_src );

			return $this->save( $contents );
		}

		/**
		 * Applies formatting to a config value.
		 *
		 * @throws Exception When a raw value is requested for an empty string.
		 *
		 * @param string $value Config value.
		 * @param bool   $raw   Display value in raw format without quotes.
		 *
		 * @return mixed
		 */
		protected function format_value( $value, $raw ) {
			if ( $raw && '' === trim( $value ) ) {
				throw new Exception( 'Raw value for empty string not supported.' );
			}

			return ( $raw ) ? $value : var_export( $value, true );
		}

		/**
		 * Normalizes the source output for a name/value pair.
		 *
		 * @throws Exception If the requested config type does not support normalization.
		 *
		 * @param string $type  Config type (constant or variable).
		 * @param string $name  Config name.
		 * @param mixed  $value Config value.
		 *
		 * @return string
		 */
		protected function normalize( $type, $name, $value ) {
			if ( 'constant' === $type ) {
				$placeholder = "define( '%s', %s );";
			} elseif ( 'variable' === $type ) {
				$placeholder = '$%s = %s;';
			} else {
				throw new Exception( "Unable to normalize config type '{$type}'." );
			}

			return sprintf( $placeholder, $name, $value );
		}

		/**
		 * Parses the source of a wp-config.php file.
		 *
		 * @param string $src Config file source.
		 *
		 * @return array
		 */
		protected function parse_wp_config( $src ) {
			$configs             = array();
			$configs['constant'] = array();
			$configs['variable'] = array();

			// Strip comments.
			foreach ( token_get_all( $src ) as $token ) {
				if ( in_array( $token[0], array( T_COMMENT, T_DOC_COMMENT ), true ) ) {
					$src = str_replace( $token[1], '', $src );
				}
			}

			preg_match_all( '/(?<=^|;|<\?php\s|<\?\s)(\h*define\s*\(\s*[\'"](\w*?)[\'"]\s*)(,\s*(\'\'|""|\'.*?[^\\\\]\'|".*?[^\\\\]"|.*?)\s*)((?:,\s*(?:true|false)\s*)?\)\s*;)/ims', $src, $constants );
			preg_match_all( '/(?<=^|;|<\?php\s|<\?\s)(\h*\$(\w+)\s*=)(\s*(\'\'|""|\'.*?[^\\\\]\'|".*?[^\\\\]"|.*?)\s*;)/ims', $src, $variables );

			if ( ! empty( $constants[0] ) && ! empty( $constants[1] ) && ! empty( $constants[2] ) && ! empty( $constants[3] ) && ! empty( $constants[4] ) && ! empty( $constants[5] ) ) {
				foreach ( $constants[2] as $index => $name ) {
					$configs['constant'][ $name ] = array(
						'src'   => $constants[0][ $index ],
						'value' => $constants[4][ $index ],
						'parts' => array(
							$constants[1][ $index ],
							$constants[3][ $index ],
							$constants[5][ $index ],
						),
					);
				}
			}

			if ( ! empty( $variables[0] ) && ! empty( $variables[1] ) && ! empty( $variables[2] ) && ! empty( $variables[3] ) && ! empty( $variables[4] ) ) {
				// Remove duplicate(s), last definition wins.
				$variables[2] = array_reverse( array_unique( array_reverse( $variables[2], true ) ), true );
				foreach ( $variables[2] as $index => $name ) {
					$configs['variable'][ $name ] = array(
						'src'   => $variables[0][ $index ],
						'value' => $variables[4][ $index ],
						'parts' => array(
							$variables[1][ $index ],
							$variables[3][ $index ],
						),
					);
				}
			}

			return $configs;
		}

		/**
		 * Saves new contents to the wp-config.php file.
		 *
		 * @throws Exception If the config file content provided is empty.
		 * @throws Exception If there is a failure when saving the wp-config.php file.
		 *
		 * @param string $contents New config contents.
		 *
		 * @return bool
		 */
		protected function save( $contents ) {
			if ( ! trim( $contents ) ) {
				throw new Exception( 'Cannot save the config file with empty contents.' );
			}

			if ( $contents === $this->wp_config_src ) {
				return false;
			}

			$result = file_put_contents( $this->wp_config_path, $contents, LOCK_EX );

			if ( false === $result ) {
				throw new Exception( 'Failed to update the config file.' );
			}

			return true;
		}
	}
?>
