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
use backend\models\Goods;
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
            'Goods' => ['ext' => 'insite/GoodCost.txt', 'loc' => '/../../backend/web/GoodsFile/Goods.txt'],
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
					echo serialize($curr_nom->errors) . ' ' .strlen($items[0]) ."\n\r";
				}
			}
		}
		echo "Файл Актуальная номенклатура обработана.\r\n";
	}

    /**
     * Разбираем файл "Товары" и заносим данные в базу
     */
	public static function parseGoods(){
	    $files = self::getArrFile()['Goods'];
        $fileName = __DIR__ . $files['loc'];
        $strs = file($fileName, FILE_IGNORE_NEW_LINES);
        Goods::updateAll(['status' => 0]);
        foreach ($strs as $str) {
            $items = explode(';', $str);
            if (strpos($items[0], '~') !== false) continue;
            //убираем все непечатные символы из строки (могут встречаться вначале файла)
            //$items[0] = preg_replace('/[^a-zA-Zа-яА-ЯЁё0-9&\/ ]/u', '', $items[0]);

            $tp = Typeprice::find()->where(['type_price_name' => $items[4]])->one();

            if (!isset($tp)) { continue; }

            $price = $items[5]; //проводим махинации чтобы цена была в том виде в котором надо
            $price = str_replace(',', '.', $price);
            $price = preg_replace('/[^x\d|*\.]/', '', $price);

            $discount = stripos($items[3], 'акция') === false ? false : true;
            $hash = md5($items[0] . $items[2] . $items[4]);
            $hash = substr($hash, 0, 11);
            $good = Goods::find()->where(['hash_id' => $hash])->one();
            if (isset($good)) {
                /** @var Goods $good */
                //echo 'существует объект' . $goodfnd->good_name . '<br>';
                //$good = $this->findModel($goodfnd->good_id);
                $good->good_name = $items[1];
                //$good->good_1c_id = $items[0];
                //$good->good_detail_guid = $items[2];
                $good->good_description = $items[3];
                $good->good_price = $price * 100; //в базе цены хранятся в целом типе
                $good->typeprices_id = $tp->type_price_id;
                $good->status = $discount ? Goods::DISCOUNT : Goods::ENABLE;
                $good->save();
            } else {
                echo 'новый объект: ' . $items[1] . "\n\r";
                $good = new Goods();
                //echo 'cod: ';
                //var_dump($items[0]);
                $good->hash_id = $hash;
                $good->good_1c_id = $items[0];
                $good->good_name = $items[1];
                $good->good_detail_guid = $items[2];
                $good->good_description = $items[3];
                $good->good_price = $price * 100;
                $good->typeprices_id = $tp->type_price_id;
                $good->status = $discount ? Goods::DISCOUNT : Goods::ENABLE;
                $good->save();
            }
        }
        echo "Файл Товары обработан. \n\r";
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
			$numbers = explode(',', $items[6]);
			foreach ($numbers as $number) {
			    echo $number;
                $number = preg_replace('/(^8)|(^\(8)|[^0-9]/u', '', trim($number));
                echo '->' .$number. "\n\r";
                $phone = Phone::findOne(['phone' => $number]);
                if ($phone) {
                } else {
                    $phone = new Phone();
                    $phone->customer_id = $customer->customer_id;
                    $phone->phone = $number;
                    $phone->sort = 1;
                    $phone->save();
                }
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
		echo "Файл Представителей обработан.\n\r";
	}

	/**
	 * Основная функция, запускающая все обработки
	 */
	public function actionDownloadAndParse(){
		self::downloadFiles();
		self::parseCurrentNom();
		self::parseGoods();
		self::parseCustomers();
		self::parseResponsible();
	}
}