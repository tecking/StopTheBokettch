<?php

/**
 * [Stop the Bokettch for baserCMS] Stop the Bokettch for baserCMS ビューイベントリスナ
 *
 * @copyright  Copyright 2015 - , tecking
 * @link       https://github.com/tecking
 * @package    tecking.bcplugins.stop_the_bokettch
 * @since      baserCMS v 3.0.6.1
 * @version    1.0.1
 * @license    MIT License
 */

// イベントリスナの登録
class StopTheBokettchViewEventListener extends BcViewEventListener {

	// イベントの登録
	public $events = ['afterElement', 'beforeLayout'];
	
	// ツールバーへのメッセージ挿入処理
	public function afterElement(CakeEvent $event) {
		
		// イベント発動元のオブジェクトとイベント固有のデータを参照
		$Subject = $event->subject();
		$data = $event->data;

		// エレメントの種類を判定（ツールバーなら true）
		if (preg_match('/admin\/toolbar/', $data['name']) || (preg_match('/^admin_/', $Subject->request->params['action']) && preg_match('/toolbar/', $data['name']))) {
			
			// サイト公開状態を判定し、メンテナンス中ならツールバーの文字列を置換（＝メッセージ表示）
			if ($Subject->viewVars['siteConfig']['maintenance'] !== '0') {

				// HTML の読み込み
				//   DOMDocument::loadHTML で LIBXML_HTML_NOIMPLIED, LIBXML_HTML_NODEFDTD
				//   オプションを使うと script 要素の配置で不具合が起きるので div 要素でラップして読み込む
				$dom = new DOMDocument;
				@$dom->loadHTML(mb_convert_encoding('<div>' . $data['out'] . '</div>', 'HTML-ENTITIES', 'UTF-8'));

				// 読み込んだ HTML の子ノードを削除（doctype）, 置換（div を最上位の子ノードに）
				$dom->removeChild($dom->firstChild);
				$dom->replaceChild($dom->firstChild->firstChild->firstChild, $dom->firstChild);				
				
				$xpath = new DOMXPath($dom);

				// 要素ノードの作成と属性の追加
				$el = [];
				$el['li'] = $dom->createElement('li');
				$el['li']->setAttribute('class', 'tool-menu');
				$el['div'] = $dom->createElement('div');
				$el['div']->setAttribute('class', 'bca-toolbar__tools-mode');
				$el['span'] = $dom->createElement('span');
				$el['span']->setAttribute('id', 'StopTheBokettch');
				$el['i'] = $dom->createElement('i');
				$el['i']->setAttribute('class', 'fas fa-exclamation-triangle');

				// テキストノードの作成
				$msg = $dom->createTextNode('サイトメンテナンス中');

				// 管理画面のテーマに応じて処理を分岐
				if (Configure::read('BcSite.admin_theme') === 'admin-third' || $Subject->viewVars['siteConfig']['admin_theme'] === 'admin-third') { // admin-third

					// XPath 式の評価
					$node = $xpath->query('//*[@id="ToolMenu"]')->item(0);
					
					// 要素の追加
					$node->appendChild($el['div']);
					$el['div']->appendChild($el['span']);
					$el['span']->appendChild($el['i']);
					$el['span']->appendChild($msg);

				}
				else { // それ以外（admin-second）

					// XPath 式の評価
					$node = $xpath->query('//*[@id="ToolMenu"]/ul')->item(0);

					// 要素の追加
					$node->appendChild($el['li']);
					$el['li']->appendChild($el['span']);
					$el['span']->appendChild($el['i']);
					$el['span']->appendChild($msg);

				}			

				// エレメントの文字列を置換
				return $dom->saveHTML($dom);

			}
	
		}
		
	}
	
	// <head> セクションへの CSS 挿入処理
	public function beforeLayout(CakeEvent $event) {
		
		// イベント発動元のオブジェクトを参照
		$Subject = $event->subject();
		
		// ログイン状態とサイト公開状態を判定し、どちらも true なら CSS を挿入
		if (!empty($Subject->viewVars['user']) && ($Subject->viewVars['siteConfig']['maintenance'] !== '0')) {
			$Subject->Helpers->BcBaser->css(['StopTheBokettch.style', '//use.fontawesome.com/releases/v5.0.13/css/all.css'], ['inline' => false]);
		}
				
		return;
	}
	
}
