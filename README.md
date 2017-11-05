# Posts Management for Backlog

## 概要

記事投稿とBacklogを連動させ、管理するためのプラグインです。  
詳しくは以下を参照してください。

* 紹介記事：https://tech.linkbal.co.jp/2932/

## 設定内容

プラグインを有効化後、「一般設定 ＞ 投稿設定」内の「Backlog連携」から以下を設定してください。  
一つでも設定漏れがありますと、一切動作致しません。  

### スペースID

https://[スペースID].backlog.jp/

### プロジェクトID

以下よりIDを確認してください。  
https://[スペースID].backlog.jp/EditIssueType.action?project.id=[課題種別ID]（プロジェクト設定 ＞ 基本設定）

### 課題種別ID

以下よりIDを確認してください。  
https://[スペースID].backlog.jp/EditIssueType.action?issueType.id=[課題種別ID]（プロジェクト設定 ＞ 種別）

### APIキー

以下からAPIキーを発行してください。  
https://[スペースID].backlog.jp/EditApiSettings.action（個人設定 ＞ API）

* 参考URL：http://www.backlog.jp/help/usersguide/personal-settings/userguide2378.html
