<?php
/**
 * Created by PhpStorm.
 * User: CPR007
 * Date: 2017/11/4
 * Time: 9:56
 */
/**
 * 打印函数
 * @param array $data
 */
function p($data = []) {
    echo '<pre>';
    print_r($data);
}

$config = ['host' => '127.0.0.1', 'port' => 3306, 'username' => 'root', 'password' => ''];
$dbObj = PdoDb::getWriteDb($config);
/*
//预处理虽能防止sql注入，但大批量数据插入情景下效率太慢
$sTime = microtime(true);
for ($i = 0; $i < 1000; $i++) {
    $dbObj->selectDB('test')->add('user', ['name' => 'test', 'ps' => 123]);
}
//批量新增拼接sql，效率快，但安全性方面存在隐患，插入数据需要进行特殊字符过滤 验证
$sql="insert into user (name,ps) values";
for ($i = 0; $i < 10000; $i++) {
    $sql.="('test',123),";
}
$sql=substr($sql,0,-1);
$num=$dbObj->selectDB('test')->query($sql);
$eTime = microtime(true);
$total = $eTime - $sTime;
echo '共耗时' . number_format($total, 2). 's';
//本地测试新增1000条数据预处理方式新增耗时共耗时53.13s,10000条数据拼接sql新增耗时0.81秒
echo '<hr>'.$num.'条';die;*/
//$rs=$dbObj->selectDB("test")->update('user',['name'=>'ccc1'],['id'=>1]);
//$rs=$dbObj->selectDB('test')->query('select * from user');
//$rs=$dbObj->selectDB('test')->query("update user set name='cl' where ps='123'");
//p($rs);

class PdoDb {
    static private $writeDbInstance = null;
    static private $readDbInstance = null;
    private $pdo = null;
    private $host;
    private $port;
    private $user;
    private $password;
    private $dbName;
    private $charset;
    private $pconnect = false;

    private function __construct($dbConfig) {
        $this->host = $dbConfig['host'];
        $this->port = $dbConfig['port'] ?: 3306;
        $this->user = isset($dbConfig['username']) ? $dbConfig['username'] : (isset($dbConfig['user']) ? $dbConfig['user'] : '');
        $this->password = $dbConfig['password'];
        $this->charset = isset($dbConfig['charset']) ? $dbConfig['charset'] : 'UTF8';
        $this->pconnect = isset($dbConfig['pconnect']) ? $dbConfig['pconnect'] : false;
    }

    public static function getWriteDb($config) {
        if (!self::$writeDbInstance) {
            self::$writeDbInstance = new self($config);
        }
        return self::$writeDbInstance;
    }

    public static function getReadDb($config) {
        if (!self::$readDbInstance) {
            self::$readDbInstance = new self($config);
        }
        return self::$readDbInstance;
    }

    private function __clone() {

    }

    public function selectDB($dbName) {
        $this->dbName = $dbName;
        $this->_connect();
        return $this;
    }

    private function _connect() {
        try {
            $this->pdo = new PDO(
                'mysql:host=' . $this->host . ';dbname=' . $this->dbName . ';port=' . $this->port, $this->user, $this->password,
                [
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . $this->charset,
                    PDO::ATTR_PERSISTENT => (bool)$this->pconnect,
                ]
            );
            //抛出异常
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public function _close() {
        $this->pdo = null;
    }

    public function beginTransaction() {
        $this->pdo->beginTransaction();
    }

    public function commit() {
        $this->pdo->commit();
    }

    public function rollBack() {
        $this->pdo->rollBack();
    }

    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }

    //新增单条数据 user ['username'=>'test','ps'=>'1']
    public function add($table, $data) {
        try {
            $fieldList = $valList = $paramArr = [];
            foreach ($data as $key => $value) {
                $fieldList[] = $key;
                $valList[] = ':' . $key;
                $paramArr[':' . $key] = $value;
            }
            $fieldStr = implode(',', $fieldList);
            $valStr = implode(',', $valList);
            $sql = "INSERT INTO $table ($fieldStr) VALUES ($valStr)";
            $stmt = $this->pdo->prepare($sql, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
            $stmt->execute($paramArr);
            return $this->lastInsertId();
        } catch (Exception $e) {
            echo $e->getMessage();
            die;
        }
    }

    //删除
    public function delete($table, $whereData) {
        try {

            $where = '';
            $paramArr = [];
            foreach ($whereData as $key => $value) {
                $where .= $key . ' = :' . $key . ' and ';
                $paramArr[':' . $key] = $value;
            }
            $where = substr($where, 0, -4);
            $sql = "DELETE FROM $table where $where LIMIT 1";
            $stmt = $this->pdo->prepare($sql, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
            $stmt->execute($paramArr);
            return $stmt->rowCount();
        } catch (Exception $e) {
            echo $e->getMessage();
            die;
        }
    }

    //更新
    public function update($table, $updateData, $whereData) {
        try {
            $where = $setData = '';
            $paramArr = [];
            foreach ($whereData as $key => $value) {
                $where .= $key . ' = :' . $key . ' and ';
                $paramArr[':' . $key] = $value;
            }
            foreach ($updateData as $key => $value) {
                $setData .= $key . ' = :' . $key . $key . ' , ';
                $paramArr[':' . $key . $key] = $value;
            }
            $setData = substr($setData, 0, -2);
            $where = substr($where, 0, -4);
            $sql = "UPDATE $table SET $setData WHERE $where LIMIT 1";
            $stmt = $this->pdo->prepare($sql, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
            $stmt->execute($paramArr);
            return $stmt->rowCount();
        } catch (Exception $e) {
            echo $e->getMessage();
            die;
        }
    }

    //返回一列
    public function queryColumn($sql, $paramArr = []) {
        try {
            $stmt = $this->pdo->prepare($sql, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
            $stmt->execute($paramArr);
            $res = [];
            while ($obj = $stmt->fetchColumn()) {
                $res[] = $obj;
            }
            return $res;
        } catch (Exception $e) {
            echo $e->getMessage();
            die;
        }
    }

    //执行sql
    public function query($sql) {
        try {
            $sqlInfo = explode(' ', trim($sql));
            $sqlType = strtolower(preg_replace('/\s/', '', $sqlInfo[0]));
            if (in_array($sqlType, ['show', 'select'])) {
                //返回所有查询项
                return $this->queryAll($sql);
            } else if (in_array($sqlType, ['update', 'insert', 'delete'])) {
                //返回影响的条数
                return $this->execSql($sql);
            }
        } catch (Exception $e) {
            echo $e->getMessage();
            die;
        }
    }


    //返回所有
    public function queryAll($sql) {
        try {
            $query = $this->pdo->query($sql);
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            echo $e->getMessage();
            die;
        }
    }

    //返回影响行数
    public function execSql($sql) {
        try {
            return $this->pdo->exec($sql);
        } catch (Exception $e) {
            echo $e->getMessage();
            die;
        }
    }
}
