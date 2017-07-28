<?php
/*
Plugin Name: Posts Management By Backlog
Plugin URI: https://tech.linkbal.co.jp/2932/
Description: BacklogでWordPressの投稿を管理する
Version: 1.0
Author: hseki
Author URI: https://tech.linkbal.co.jp/author/hseki/
License: GPL2
*/

require_once('functions.php');

register_activation_hook(__FILE__, 'add_backlog_ids_table');

/* Backlog連携設定 */
function register_backlog_setting_field(){
	add_settings_section('backlog', 'Backlog連携', 'backlog_section_message', 'writing');
	add_settings_field('backlog_space_id', 'スペースID', 'backlog_space_id_form', 'writing', 'backlog');
	add_settings_field('backlog_project_id', 'プロジェクトID', 'backlog_project_id_form', 'writing', 'backlog');
	add_settings_field('backlog_issues_type_id', '課題種別ID', 'backlog_issues_type_id_form', 'writing', 'backlog');
	add_settings_field('backlog_api_key', 'APIキー', 'backlog_api_key_form', 'writing', 'backlog');
}

function backlog_section_message(){
	$webhook_url = plugin_dir_url(__FILE__).'webhook.php';
?>
<p>
	Backlog上で「WebHook URL」として登録してください。<br />
	<?php echo $webhook_url; ?>
</p>
<?php
}

function add_backlog_setting_field($whitelist_options){
	$whitelist_options['writing'][] = 'backlog_space_id';
	$whitelist_options['writing'][] = 'backlog_project_id';
	$whitelist_options['writing'][] = 'backlog_issues_type_id';
	$whitelist_options['writing'][] = 'backlog_api_key';
	return $whitelist_options;
}

function backlog_space_id_form(){
?>
<input name="backlog_space_id" type="text" id="backlog_space_id" value="<?php echo esc_html(get_option('backlog_space_id', '')); ?>" class="regular-text">
<?php
}

function backlog_project_id_form(){
?>
<input name="backlog_project_id" type="text" id="backlog_project_id" value="<?php echo esc_html(get_option('backlog_project_id', '')); ?>" class="regular-text">
<?php
}

function backlog_issues_type_id_form(){
?>
<input name="backlog_issues_type_id" type="text" id="backlog_issues_type_id" value="<?php echo esc_html(get_option('backlog_issues_type_id', '')); ?>" class="regular-text">
<?php
}

function backlog_api_key_form(){
?>
<input name="backlog_api_key" type="text" id="backlog_api_key" value="<?php echo esc_html(get_option('backlog_api_key', '')); ?>" class="regular-text">
<?php
}

add_filter('whitelist_options', 'add_backlog_setting_field');
add_filter('admin_init', 'register_backlog_setting_field');
?>