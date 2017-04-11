<?php

/**
 * этот файл выполняется автоматически при помощи утилиты cron
 * User: Griff19
 * Date: 10.02.2016
 * Time: 17:14
 *
 * @property  string ip_server
 * @property  string username
 * @property  string pass
 */

class Ftp {
    private static $ip_server;
    private static $username;
    private static $pass;

    function __construct(){
        $params = require(__DIR__ . '/../config/params-local.php');

        $this->ip_server = $params['ftp']['server'];
        $this->username = $params['ftp']['user'];
        $this->pass = $params['ftp']['pass'];
    }

    public function upload($local_file, $server_file){
        $conn_id = ftp_connect($this->ip_server);
        if (!$conn_id) {echo 'Ошибка выполнения'; return false;}
        $login_result = ftp_login($conn_id, $this->username, $this->pass);
        ftp_pasv($conn_id, true);

        $dir = dirname($server_file);
        $filename = basename($server_file);
        if (!empty($dir)){
            ftp_chdir($conn_id, $dir);
        }

        if (ftp_put($conn_id, $filename, $local_file, FTP_BINARY)){
            ftp_close($conn_id);
            return true;
        } else {
            ftp_close($conn_id);
            return false;
        }
    }
}

/**
 * @property  string dns
 * @property  string user
 * @property  string pass
 */
class useDB {
    private static $dns;
    private static $user;
    private static $pass;

    function __construct(){
        $params = require(__DIR__ . '/../../common/config/main-local.php');
        $this->dns = $params['components']['db']['dsn'];
        $this->user = $params['components']['db']['username'];
        $this->pass = $params['components']['db']['password'];
    }

    public function exportOrders(){
        $filename =  __DIR__ . '/../web/OrdersFile/orderstmp' . date("Ymd") . '.txt';
        $filelog = __DIR__ . '/../web/OrdersFile/log/test' . date("Ymd") . '.txt';
        $log = fopen($filelog, 'a');
        fputs($log, date('H:i:s'). " выполняется скрипт \r\n");

        $w = fopen($filename, 'a');//открываем файл для записи

        $db = new PDO($this->dns, $this->user, $this->pass);
        $orders =  $db->query("Select order_id From orders Where status = 'Размещен'");
        foreach ($orders as $order) {
            $order_id = $order['order_id'];
            $query = $db->query('
                Select order_id, order_date, customer_1c_id, customer_name, good_1c_id, good_detail_guid, good_name, good_price, good_count
                From listofgoods, goods, customers, orders
                Where order_id ='. $order_id .'
                and goods_good_1c_id = hash_id
                and customers_customer_id = customer_id
                and order_id = orders_order_id');
            foreach ($query as $row){
                $strtofile = '';
                $strtofile .= $row['order_id'] . ';';

                $order_date = $row['order_date'];
                if ( (date('Y-m-d', strtotime($order_date)) <= date('Y-m-d')) ){
                    $order_date = date('Y-m-d', strtotime("now +1 day"));
                }
                $strtofile .= $order_date . ';';

                $strtofile .= $row['customer_1c_id'] . ';';
                $strtofile .= $row['customer_name'] . ';';
                $strtofile .= $row['good_1c_id'] . ';';
                $strtofile .= $row['good_detail_guid'] . ';';
                $strtofile .= $row['good_name'] . ';';
                $good_price = $row['good_price'] / 100;
                $strtofile .= $good_price . ';';
                $strtofile .= $row['good_count'] . ';';
                $strtofile .= "\r\n";
                echo $strtofile;
                fputs($w, $strtofile);//пишем строку в файл
                fputs($log, date('H:i:s'). " " . $strtofile);
            }
            $db->query("Update orders Set status = 'Обработан' Where order_id =" . $order_id);
        }
        fclose($log);
        fclose($w);  //закрываем файл

        $db = null;
        $ftp = new Ftp();
        $ftp->upload($filename, "outsite/orders/orders" . date('Ymd') . ".txt");
    }

    public function exportCustomers(){
        $filename = __DIR__ . '/../web/CustomersFile/customers_export' . date("Ymd") . '.txt';
        $w = fopen($filename, 'w');//открываем файл для записи, перезаписываем

        $db = new PDO($this->dns, $this->user, $this->pass);
        //$users = $db->prepare("Select username From user Where id = :user_id ");
        $customers = $db->query("Select user_id, customer_1c_id From customers Where user_id > 0;");
        foreach ($customers as $customer){
            $strtofile = '';
            $user = $db->query('Select username From "user" Where id='. $customer['user_id'])->fetchAll();
            $strtofile .= $customer['customer_1c_id'] . ';';
            $strtofile .= $customer['user_id'] . ';';
            $strtofile .= $user[0]['username'] . ';';
            $strtofile .= "\r\n";
            echo $strtofile;
            fputs($w, $strtofile);
        }
        fclose($w);
        $db = null;

        $ftp = new Ftp();
        $ftp->upload($filename, "outsite/customers/customers_export.txt");
    }
}

$base = new useDB();
$base->exportOrders();
$base->exportCustomers();


