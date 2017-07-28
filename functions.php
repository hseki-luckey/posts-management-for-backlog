<?php
/* Backlog課題用テーブル追加 */
function add_backlog_ids_table(){
	global $wpdb;

	$table_name = 'backlog_ids';
	$charset_collate = $wpdb->get_charset_collate();
	$query = <<<EOS
		CREATE TABLE {$table_name} (
			post_id bigint(20) NOT NULL,
			backlog_id varchar(20) NOT NULL
		) {$charset_collate};
		ALTER TABLE {$table_name};
EOS;

	require_once(ABSPATH.'wp-admin/includes/upgrade.php');
	dbDelta($query);
}

/* 設定内容を取得 */
function get_backlog_setting(){
	$space_id = get_option('backlog_space_id');
	$project_id = get_option('backlog_project_id');
	$type_id = get_option('backlog_issues_type_id');
	$api_key = get_option('backlog_api_key');

	if($space_id && $project_id && $type_id && $api_key){
		$setting = array(
			'space_id' => $space_id,
			'project_id' => $project_id,
			'type_id' => $type_id,
			'api_key' => $api_key,
			'headers' => array('Content-Type:application/x-www-form-urlencoded')
		);
	}else{
		$setting = false;
	}

	return $setting;
}

/* レビュー待ちに変更時に動作 */
function custom_post_status_action_for_backlog($new_status, $old_status, $post){
	if($old_status == 'draft' && $new_status == 'pending'){
		$author = get_userdata($post->post_author);
		if($author){
			$post_url = get_permalink($post->ID);
			$message = <<<EOS
{$author->display_name}さんから以下の通り、レビュー申請がされました。
担当者は確認をお願いします。

## 確認URL

{$post_url}

## タイトル

{$post->post_title}

## 著者

{$author->display_name}

## チェック項目

* [ ] タイトル、本文に誤字がないか 
* [ ] カテゴリ、タグ付けが適切にされているか

EOS;
			add_new_backlog_subject($post->ID, $post->post_title, $message);
		}
	}
}
add_action('transition_post_status', 'custom_post_status_action_for_backlog', 10, 3);

/* 公開時に動作 */
function custom_published_action_for_backlog($post_id){
	global $wpdb;

	$backlog_setting = get_backlog_setting();
	$backlog_id = $wpdb->get_var("SELECT backlog_id FROM backlog_ids WHERE post_id = {$post_id}");

	if($backlog_setting && $backlog_id){
		$issue_url = 'https://'.$backlog_setting['space_id'].".backlog.jp/api/v2/issues/{$backlog_id}";
		add_comment_backlog_subject($issue_url, $backlog_setting['api_key'], $backlog_setting['headers'], 'Published');
		complete_backlog_subject_status($issue_url, $backlog_setting['headers'], $backlog_setting['api_key']);
	}
}
add_action('publish_post', 'custom_published_action_for_backlog', 1 ,6);

/* Backlogに課題追加 */
function add_new_backlog_subject($post_id, $subject, $message){
	global $wpdb;

	$backlog_setting = get_backlog_setting();
	if($backlog_setting){
		$params = array(
			'projectId' => $backlog_setting['project_id'],
			'summary' => $subject,
			'description' => $message,
			'issueTypeId' => $backlog_setting['type_id'],
			'priorityId' => 3
		);
		$url = 'https://'.$backlog_setting['space_id'].'.backlog.jp/api/v2/issues?apiKey='.$backlog_setting['api_key'].'&'.http_build_query($params, '','&');
		$context = array(
			'http' => array(
				'method' => 'POST',
				'header' => $backlog_setting['headers'],
				'ignore_errors' => true
			)
		);
		$response = file_get_contents($url, false, stream_context_create($context));
		if($response){
			$results = json_decode($response, true);

			if(!empty($results['issueKey'])){
				$data = array(
					'post_id' => $post_id,
					'backlog_id' => $results['issueKey']
				);
				$wpdb->insert('backlog_ids', $data, array('%d', '%s'));
			}
		}
	}
}

/* 該当課題にコメント追加 */
function add_comment_backlog_subject($issue_url, $api_key, $headers, $message){
	$url = $issue_url."/comments?apiKey=".$api_key.'&content='.$message;
	$context = array(
		'http' => array(
			'method' => 'POST',
			'header' => $headers,
			'ignore_errors' => true
		)
	);
	file_get_contents($url, false, stream_context_create($context));
}

/* 該当課題を完了させる */
function complete_backlog_subject_status($issue_url, $headers, $api_key){
	$params = array(
		'statusId' => 4,
		'resolutionId' => 0
	);
	$url = $issue_url.'?apiKey='.$api_key.'&'.http_build_query($params, '','&');
	$context = array(
		'http' => array(
			'method' => 'PATCH',
			'header' => $headers,
			'ignore_errors' => true
		)
	);
	file_get_contents($url, false, stream_context_create($context));
}
?>