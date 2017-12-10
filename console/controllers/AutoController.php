<?php
/**
 * Контроллер для работы с файлами и заполнения базы данных
 */

namespace console\controllers;

use backend\models\CurrentNom;
use backend\models\Customers;
use backend\models\FtpWork;
use backend\models\Phone;
use backend\models\Responsible;
use backend\models\Typeprice;
use yii\console\Controller;

class AutoController extends Controller
{
	/**
	 * @return array
	 */
	public static function getArrFile(){
		return [
			'CurrentNom' => ['ext' => 'insite/CooreNom.txt', 'loc' => '/../../backend/web/GoodsFile/CurrentNom.txt'],
			'Customers' => ['ext' => 'insite/contract.txt', 'loc' => '/../../backend/web/CustomersFile/Customers.txt'],
			'Responsible' => ['ext' => 'insite/ContPers.txt', 'loc' => '/../../backend/web/CustomersFile/Responsible.txt'],
		];
	}
	
	/**
	 * Скачиваем файлы с ftp
	 */
	public static function downloadFiles() {
		$ftp   = new FtpWork();
		$files = self::getArrFile();
		foreach ($files as $file) {
			$ftp->download($file['ext'], __DIR__ . $file['loc']);
		}
	}

	/**
	 * Разбираем "Актуальную номенклатуру" и загружаем данные в базу
	 */
	public static function parseCurrentNom(){
		CurrentNom::deleteAll(); //очищаем таблицу перед заполнением
		$files = self::getArrFile()['CurrentNom'];
		$fileName = __DIR__ . $files['loc'];
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
		echo "Актуальная номенклатура обработана.\r\n";
	}
	
	/**
	 * Разбираем файл "Контрагенты" и заносим в базу
	 */
	public static function parseCustomers(){
		$files = self::getArrFile()['Customers'];
		$fileName = __DIR__ . $files['loc'];
		$strs = file($fileName, FILE_IGNORE_NEW_LINES);
		foreach ($strs as $str) {
			$items = explode(';', $str);
			//имя пользователя[0]; код контрагента[1]; имя контрагента[2]; тип цен[3]; ИНН[4]; email[5]; телефон[6]
			$tpname = substr($items[3],0,9);
			if (empty($tpname))
				$tp = Typeprice::findOne(['type_price_name' => '00001    ']);
			else
				$tp = Typeprice::findOne(['type_price_name' => $tpname]);
			if (!$tp) {
				$tp = new Typeprice();
				$tp->type_price_name = $tpname;
				$tp->save();
			}
			
			$customer = Customers::findOne(['customer_1c_id' => $items[1]]);
			if ($customer) {
				$customer->customer_name = trim($items[2]);
				$customer->customer_email = trim($items[5]);
				$customer->inn = $items[4];
				$customer->typeprices_id = $tp->type_price_id;
				$customer->save();
			} else {
				$customer = new Customers();
				$customer->customer_1c_id = $items[1];
				$customer->customer_name = trim($items[2]);
				$customer->inn = $items[4];
				$customer->customer_email = trim($items[5]);
				$customer->typeprices_id = $tp->type_price_id;
				$customer->save();
			}
			$items[6] = preg_replace('/[^0-9]/u', '', $items[6]);
			$phone = Phone::findOne(['phone' => $items[6]]);
			if ($phone){}
			else {
				$phone = new Phone();
				$phone->customer_id = $customer->customer_id;
				$phone->phone = $items[6];
				$phone->sort = 1;
				$phone->save();
			}
		}
		echo "файл Контрагенты обработан.\n\r";
	}
	
	/**
	 * Разбираем файл "представителей" контрагента
	 */
	public static function parseResponsible(){
		$files = self::getArrFile()['Responsible'];
		$fileName = __DIR__ . $files['loc'];
		$strs = file($fileName, FILE_IGNORE_NEW_LINES);
		foreach ($strs as $str) {
			$items = explode(';', $str);
			$items[0] = preg_replace('/[^a-zA-Zа-яА-ЯЁё0-9&\/ ]/u', '', $items[0]); // Код
			$items[1] = trim($items[1]); //ФИО
			$items[2] = preg_replace('/[^0-9]/u', '', $items[2]); // Номер
			
			$customer = Customers::findOne(['customer_1c_id' => $items[0]]);
			if ($customer) {
				$responsible = Responsible::findOne(['name' => $items[1]]);
				if ($responsible) {
					continue;
				} else {
					$responsible = new Responsible();
					$responsible->customer_id = $customer->customer_id;
					$responsible->name = $items[1];
					$responsible->sort = 1;
					if ($responsible->save()){
						echo $items[1] . " добавлен.\n\r";
					} else {
						echo serialize($responsible->errors);
						return false;
					}
 				}
			} else {
				echo $items[0] . " контрагент не найден.\n\r";
			}
		}
		echo 'Файл с "Представителями" обработан.';
	}

	/**
	 * Основная функция, запускающая все обработки
	 */
	public function actionDownloadAndParse(){
		self::downloadFiles();
		self::parseCurrentNom();
		self::parseCustomers();
		self::parseResponsible();
	}
}