<?php

namespace backend\models;

use Yii;
use backend\helpers\Logs;

/**
 * Class FtpWork
 * @property  string ip_server
 * @property  string username
 * @property  string pass
 * @package backend\models
 */
class FtpWork
{
    private $ip_server;
    private $username;
    private $pass;

    /**
     * Инициируем свойства
     */
    function __construct() {
        $this->ip_server = Yii::$app->params['ftp']['server'];
        $this->username = Yii::$app->params['ftp']['user'];
        $this->pass = Yii::$app->params['ftp']['pass'];
    }

    /**
     * Скачиваем файл с сервера ftp
     * @param string $server_file имя файла на ftp
     * @param string $local_file имя файла в web
     * @return bool
     */
    public function download($server_file, $local_file){

        $conn_id = ftp_connect($this->ip_server);
        if (!$conn_id) {
            Yii::$app->session->setFlash('error', 'Не удалось подключиться к серверу' . $this->ip_server);
            Logs::add('Не удалось подключиться к серверу' . $this->ip_server);
            return false;
        }
        $login_result = ftp_login($conn_id, $this->username, $this->pass);
        Logs::add('Авторизации на ftp: ' . ($login_result ? 'Успешно.' : 'Не удалась...'));
        ftp_pasv($conn_id, true);

        $dir = dirname($server_file); //Получаем имя каталога
        $filename = basename($server_file); //Получаем имя файла
        if (!empty($dir)){
            ftp_chdir($conn_id, $dir); //если в имени файла указан каталог то выбираем его
        }

        if (ftp_get($conn_id, $local_file, $filename, FTP_BINARY)){
            ftp_close($conn_id);
            Logs::add('Файл ' . $server_file . 'скачан как ' . $local_file);
            return true;
        } else {
            ftp_close($conn_id);
            return false;
        }
    }

    /**
     * Скачиваем каталог с сервера
     * @param $server_catalog
     * @param $local_catalog
     * @return bool
     */
    public function downloadAll($server_catalog, $local_catalog){
        $conn_id = ftp_connect($this->ip_server);
        if (!$conn_id) {
            Yii::$app->session->setFlash('error', 'Не удалось подключиться к серверу' . $this->ip_server);
            Logs::add('Не удалось подключиться к серверу' . $this->ip_server);
            return false;
        }
        $login_result = ftp_login($conn_id, $this->username, $this->pass);
        Logs::add('Результат аторизации на ftp: ' . $login_result);
        ftp_pasv($conn_id, true);

        //echo $local_catalog . '<br>';
        if (!empty($dir)){
            ftp_chdir($conn_id, $dir); //выделяем каталог из полученного имени и выбераем его
        }

        $list_files = ftp_nlist($conn_id, $server_catalog);
        foreach ($list_files as $file_name) {
            if ($file_name == '.' || $file_name == '..') continue;
            //echo $file_name . '<br>'; continue;
            if (pathinfo($file_name, PATHINFO_EXTENSION) != 'xlsx') continue;

            if (ftp_get($conn_id, $local_catalog.'/'.$file_name, $server_catalog.'/'.$file_name, FTP_BINARY)){
                Logs::add('Файл скачан ' . $server_catalog.'/'.$file_name . ' как ' . $local_catalog.'/'.$file_name);
            }
        }

        ftp_close($conn_id);
        //die;
        return true;
    }

    /**
     * Закачиваем файл на сервер
     * @param $local_file
     * @param $server_file
     * @return bool
     */
    public function upload($local_file, $server_file){
        $conn_id = ftp_connect($this->ip_server);
        if (!$conn_id) {
            Yii::$app->session->setFlash('error', 'Не удалось подключиться к серверу' . $this->ip_server);
            Logs::add('Не удалось подключиться к серверу' . $this->ip_server);
            return false;
        }
        $login_result = ftp_login($conn_id, $this->username, $this->pass);
        ftp_pasv($conn_id, true);

        $dir = dirname($server_file);
        $filename = basename($server_file);
        if (!empty($dir)){
            ftp_chdir($conn_id, $dir);
        }

        Logs::add('Файл загружен на ftp-сервер: ' . $server_file . ' <- ' . $local_file);

        if (ftp_put($conn_id, $filename, $local_file, FTP_BINARY)){
            ftp_close($conn_id);
            return true;
        } else {
            ftp_close($conn_id);
            return false;
        }
    }
}