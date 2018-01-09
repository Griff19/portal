<?php
/**
 * Пишем лог работы системы
 */

namespace backend\helpers;

use Yii;
use backend\models\User;
use DateTime;

/**
 * @package backend\helpers
 */
class Logs
{
    const LOG_DIR = 'logs/log';

    public function add($str){
	    var_dump(Yii::$app->request); die;
        if (php_sapi_name() == 'cli') {
            echo $str . "\r\n";
            $userIp = '127.0.0.1';
            $userId = 'console';
            $userName = 'console';
            $fileLog = __DIR__ . '/../../backend/web/' . self::LOG_DIR;
        } elseif (Yii::$app->request) {
	    } else {
		    $userIp = Yii::$app->request->userIP;
		    $userId = Yii::$app->user->id;
		    $userName = User::getName($userId);
		    $fileLog = self::LOG_DIR;
	    }

        $date = new DateTime();
        $over = $date->format('Y-m-d H:i:s') . '; '.$userIp.'; ' . $userId . '; ' . $userName . '; ';

        $w = fopen($fileLog, 'a');//открываем файл для записи в конец
        fputs($w, $over . $str . "\r\n");//пишем строку в файл
        fclose($w);  //закрываем файл
    }
}