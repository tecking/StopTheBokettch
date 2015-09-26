<?php

/**
 * [Stop the Bokettch for baserCMS] Stop the Bokettch for baserCMS ビューイベントリスナ
 *
 * @copyright  Copyright 2015 - , tecking
 * @link       http://baser-for-wper.tecking.org
 * @package    tecking.bcplugins.stop_the_bokettch
 * @since      baserCMS v 3.0.6.1
 * @version    0.1.1
 * @license    MIT License
 */

// イベントリスナの登録
class StopTheBokettchViewEventListener extends BcViewEventListener {

	// イベントの登録
	public $events = array('afterElement', 'beforeLayout');
	
	// ツールバーへのメッセージ挿入処理
	public function afterElement(CakeEvent $event) {
		
		// イベント発動元のオブジェクトとイベント固有のデータを参照
		$Subject = $event->subject();
		$data = $event->data;
		
		// エレメントの種類を判定（ツールバーなら true ）
		if (preg_match('/admin\/toolbar/', $data['name']) || (preg_match('/^admin_/', $Subject->request->params['action']) && preg_match('/toolbar/', $data['name']))) {
			
			// サイト公開状態を判定し、メンテナンス中ならツールバーの文字列を置換（＝メッセージ表示）
			if ($Subject->viewVars['siteConfig']['maintenance'] !== '0') {
				$data['out'] = preg_replace('/(<div id="ToolMenu">.+?)(<\/ul>)/s', '$1<li class="tool-menu"><span id="StopTheBokettch"><i class="fa fa-exclamation-triangle"></i>サイトメンテナンス中</span></li>$2', $data['out']);
			}
			
		}
		
		// エレメントの文字列を返却
		return $data['out'];
	}
	
	// <head> セクションへの CSS 挿入処理
	public function beforeLayout(CakeEvent $event) {
		
		// イベント発動元のオブジェクトを参照
		$Subject = $event->subject();
		
		// ログイン状態とサイト公開状態を判定し、どちらも true なら CSS を挿入
		if (array_search('admin', $Subject->viewVars['currentUserAuthPrefixes']) !== null && ($Subject->viewVars['siteConfig']['maintenance'] !== '0')) {
			$Subject->Helpers->BcBaser->css(array('StopTheBokettch.style', '//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css'), array('inline' => false));
		}
		
		return;
	}
	
}
