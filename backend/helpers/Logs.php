<?php
/**
 *
 * User: ivan
 * Date: 01.12.2015
 * Time: 11:22
 */

namespace backend\helpers;

use Yii;
use backend\models\User;
use DateTime;

/**
 * ����� ��� ������ ������.
 * Class Logs
 * @package backend\helpers
 */
class Logs
{
    const LOG_DIR = 'logs/log';

    public function add($str){

        $ipuser = Yii::$app->request->userIP;

        $date = new DateTime();
        $over = $date->format('Y-m-d H:i:s') . '; '.$ipuser.'; ' .Yii::$app->user->id . '; ' . User::getName(Yii::$app->user->id) . '; ';

        $w = fopen(Logs::LOG_DIR, 'a');//��������� ���� ��� ������ � �����
        fputs($w, $over . $str . "\r\n");//����� ������ � ����
        fclose($w);  //��������� ����
    }
}