<?php
/**
 * Контроллер для работы с номенклатурой
 */

namespace console\controllers;

use backend\models\CurrentNom;
use backend\models\FtpWork;
use yii\console\Controller;

class GoodController extends Controller
{
	private $fileFtp = 'insite/CooreNom.txt';
	private $fileCoore = '/../../backend/web/GoodsFile/CooreNom.txt';

	/**
	 * Скачиваем файл "Актуальная номенклатура"
	 */
	public function actionDownloadCooreNom(){
		$ftp = new FtpWork();
		if ($ftp->download($this->fileFtp, __DIR__ . $this->fileCoore)){
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Разбираем файл "Актуальная номенклатура", загружаем данные в базу
	 */
	public function actionParseFile(){
		CurrentNom::deleteAll(); //очищаем таблицу перед заполнением

		$fileName = __DIR__ . $this->fileCoore;
		$strs = file($fileName, FILE_IGNORE_NEW_LINES);

		foreach ($strs as $str) {
			$items = explode(';', $str);
			$items[0] = preg_replace('/[^a-zA-Zа-яА-ЯЁё0-9&\/ ]/u', '', $items[0]);

			$curr_nom = CurrentNom::findOne(['good_1c_id' => trim($items[0]), 'guid_1c' => trim($items[2])]);
			if ($curr_nom) {
				continue;
			} else {
				$curr_nom = new CurrentNom();
				$curr_nom->good_1c_id = $items[0];
				$curr_nom->guid_1c    = trim($items[2]);
				if (!$curr_nom->save()) {
					echo serialize($curr_nom->errors) . "\n\r";
				}
			}
		}
		echo 'Обработка завершена.' . "\r\n";
	}

	/**
	 * Скачиваем файл "Актуальная номенклатура" и заносим данные в базу
	 */
	public function actionDownloadAndParse(){
		self::actionDownloadCooreNom();
		self::actionParseFile();
	}
}