# WordPress Debugging Tools

WordPress debugging tools is designed to help developers and site administrators easily enable debugging, view error logs, manage log files, and more within their WordPress environment. It provides a convenient interface for modifying `wp-config.php` constants related to debugging, viewing error logs directly from the browser, and performing other debugging-related tasks without the need for manual file modifications.

![image](https://github.com/prabch/wp-debug-tools/assets/25548840/a9f11ac8-6c80-4f46-9ddf-fc28c197a23e)
![image](https://github.com/prabch/wp-debug-tools/assets/25548840/5af3e394-2bbe-4af3-bfec-ea4567acb985)
![image](https://github.com/prabch/wp-debug-tools/assets/25548840/ecdc216a-f4fa-4a7b-b9f4-b6db284e9e8c)
![image](https://github.com/prabch/wp-debug-tools/assets/25548840/080e6b0d-c341-499a-87d0-5e837aac0fc0)

## Features

- **Easy Debugging Toggle**: Quickly enable or disable WordPress debugging settings with a single click.
- **Log Viewer**: View `debug.log` contents directly in the browser, with options to highlight errors, filter log entries, and bypass file size limitations.
- **Log Management**: Download, rotate, or delete the `debug.log` file directly from the tool.
- **Error Revealer**: Automatically download and install the error revealer script for advanced debugging.
- **Backup and Restore**: Automatically creates a backup of `wp-config.php` before making changes, allowing for easy restoration if needed.
- **WP-CLI wp-config-transformer Integration**: Utilizes the [wp-config-transformer](https://github.com/wp-cli/wp-config-transformer) library for safely modifying `wp-config.php` constants, ensuring compatibility and preventing syntax errors.

## Installation

1. Download the "wdt.php" file from the GitHub repository.
2. Place it in your WordPress site's root directory.
3. Access the tools by navigating to `http://yourwordpresssite.com/wdt.php` in your web browser.

## Usage

### Enabling Debugging

1. Open the debugging tools in your browser.
2. Click on the "Enable Debugging" button to modify the `wp-config.php` file and enable WordPress debugging features.

### Viewing and Managing Logs

- Access the log viewer by clicking on the "Debug Log Viewer" button.
- Use the provided buttons to highlight errors, filter log entries, download the log file, rotate log files, or delete the log file.

### Error Revealer

- Enable the error revealer by checking the "Install Error Revealer" checkbox when enabling debugging. This downloads and activates a script that helps reveal hidden errors.

### Disabling Debugging

- Disable debugging by clicking on the "Disable Debugging" button. This restores the original `wp-config.php` file from backup.

### List versions

- Lists the version of WordPress core, active theme and all the plugins without using any of the native WordPress functions. This is still experimental so might not work on some installations. 

## Configuration

You can modify the following constants in the `wdt.php` file to customize the behavior of the debugging tools:

- `MU_PATH`: Path to the Must-Use plugins directory.
- `ER_URL`: URL to the error revealer script.
- `BACKUP_CONFIG_FILENAME`: Filename for the `wp-config.php` backup.
- `LOGVIEW_FILEZIE_LIMIT_MB`: Maximum file size for viewing the log file in the browser.

## Libraries

This tool relies on the `wp-config-transformer` library provided by WP-CLI for modifying the `wp-config.php` file. For more information, visit the [wp-config-transformer GitHub page](https://github.com/wp-cli/wp-config-transformer).

## Requirements

- WordPress 4.0 or higher.
- PHP 5.6 or higher.

## Contributing

Contributions are welcome! Please feel free to submit pull requests or create issues for bugs and feature requests.

## License

WordPress debugging tools is open-source software licensed under the GNU General Public License.
