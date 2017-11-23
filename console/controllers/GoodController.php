<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 22.11.2017
 * Time: 0:12
 */

namespace console\controllers;

use yii\console\Controller;

class GoodController extends Controller
{
	public $fileName = '/../../backend/web/GoodsFile/CooreNom.txt';

	public function actionParseFile(){
		$fileName = __DIR__ . $this->fileName;
		$strs = file($fileName, FILE_IGNORE_NEW_LINES);

		foreach ($strs as $str){
			$items = explode(';', $str);
			$items[0] = preg_replace('/[^a-zA-Zа-яА-ЯЁё0-9&\/ ]/u', '', $items[0]);

			$print = '';
			foreach ($items as $item){
				$print .= trim($item) . ' ';
			}
			echo $print . "\n\r";
		}
	}
}