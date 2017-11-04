<?php


Class SafeRedis
{
    public $host;
    public $port;
    public $timeout;
    public $errno;
    public $errstr;
    public $handle = null;

    static public $basicCommands = array(
        'del',
        'dump',
        'exists',
        'expire',
        'expireat',
        'keys',
        'migrate',
        'move',
        'object',
        'persist',
        'pexpire',
        'pexpireat',
        'pttl',
        'randomkey',
        'rename',
        'renamenx',
        'restore',
        'sort',
        'ttl',
        'type',
        'wait',
        'scan',
        'psubscribe',
        'pubsub',
        'punish',
        'punsubscribe',
        'subscribe',
        'unsubscribe',
        'eval',
        'evalsha',
        'auth',
        'echo',
        'ping',
        'quit',
        'select',
        'bgrewriteaof',
        'bfsave',
        'dbsize',
        'flushall',
        'flushdb',
        'info',
        'lastsave',
        'monitor',
        'role',
        'save',
        'shutdown',
        'slaveof',
        'slowlog',
        'sync',
        'time',
        'append',
        'bitcount',
        'bitfield',
        'bitop',
        'bitpos',
        'decr',
        'decrby',
        'get',
        'getbit',
        'getrange',
        'getset',
        'incr',
        'incrby',
        'incrbyfloat',
        'mget',
        'mset',
        'msetnx',
        'psetex',
        'set',
        'setbit',
        'setex',
        'setnx',
        'setrange',
        'strlen',
        'blpop',
        'brpop',
        'brpoplpush',
        'lindex',
        'linsert',
        'llen',
        'lpop',
        'lpush',
        'lpushx',
        'lrange',
        'lrem',
        'lset',
        'ltrim',
        'rpop',
        'rpoplpush',
        'rpush',
        'rpushx',
        'sadd',
        'scard',
        'sdiff',
        'sdiffstore',
        'sinter',
        'sinterstore',
        'sismember',
        'smembers',
        'smove',
        'spop',
        'srandmember',
        'srem',
        'sunion',
        'sunionstore',
        'sscan',
        'zadd',
        'zcard',
        'zcount',
        'zincrby',
        'zinterstrore',
        'zlexcount',
        'zrange',
        'zrangebylex',
        'zrevrangebylex',
        'zrangebyscore',
        'zrank',
        'zrem',
        'zremrangebylex',
        'zremrangebyrank',
        'zremrangebyscore',
        'zrevrange',
        'zrevrangebyscore',
        'zrevrank',
        'zscore',
        'zunionstore',
        'zscan',
        'hdel',
        'hexists',
        'hget',
        'hgetall',
        'hincrby',
        'hincrbyfloat',
        'hkeys',
        'hlen',
        'hmget',
        'hmset',
        'hset',
        'hsetnx',
        'hstrlen',
        'hvals',
        'hscan',
    );

    static public $specialCommand = array(
        'client' => array(
            'kill',
            'list',
            'getname',
            'pause',
            'setname',
        ),
        'command' => array(
            '',
            'count',
            'getkeys',
            'info',
            'get',
        ),
        'config' => array(
            'get',
            'rewrite',
            'set',
            'resetstat',
        ),
        'debug' => array(
            'object',
            'segfault',
        ),
        'script' => array(
            'exists',
            'flush',
            'kill',
            'load',
        ),
    );


    public function __construct($host = '127.0.0.1',$port = 6379,$timeout = null,&$errno = null, &$errstr = null)
    {
        $this->host = $host;
        $this->port = $port;
        $this->timeout = $timeout;
        $this->errno = $errno;
        $this->errstr = $errstr;
        $this->handle = fsockopen($this->host,$this->port,$this->errno, $this->errstr,$this->timeout);
        if(!$this->handle){
            echo 'redis server is not run';die;
        }
    }


    public function __call($function,$args)
    {
        try{
            if($this->handle){
                if(in_array(strtolower($function),self::$basicCommands,true)){
                    $crlf = "\r\n";
                    array_unshift($args,$function);
                    $command = '*' . count($args) . $crlf;

                    foreach ($args as $arg) {
                        $command .= '$' . strlen($arg) . $crlf . $arg . $crlf;
                    }
                    $fwrite = fwrite($this->handle,$command);
                    if($fwrite === false){
                        throw new \Exception('Failed to write response from stream');
                    }
                    return $this->readResponse();
                }elseif(in_array(strtolower($function),array_keys(self::$specialCommand),true)){
                    if(empty($args[0]) || empty(self::$specialCommand[$function])){
                        throw new \Exception('error param');
                    }
                    $crlf = "\r\n";
                    array_unshift($args,$function);
                    $command = '*' . count($args) . $crlf;
                    foreach ($args as $arg) {
                        $command .= '$' . strlen($arg) . $crlf . $arg . $crlf;
                    }
                    $fwrite = fwrite($this->handle,$command);
                    if($fwrite === false){
                        throw new \Exception('Failed to write response from stream');
                    }
                    return $this->readResponse();
                }
            }
            return false;
        }catch (\Exception $e){
            return false;
        }
    }


    private function readResponse()
    {
        $response = false;
        $reply = trim(fgets($this->handle,1024));
        switch(substr($reply,0,1)){
            case '-':
                $response = new \Exception(trim(substr($reply, 4)));
                break;
            case '+':
                $response = substr(trim($reply), 1);
                if ($response === 'OK') {
                    $response = true;
                }
                break;
            case '$':
                if ($reply == '$-1') {
                    return false;
                }
                $read = 0;
                $size = intval(substr($reply, 1));
                if ($size > 0) {
                    do {
                        $block_size = ($size - $read) > 1024 ? 1024 : ($size - $read);
                        $r = fread($this->handle, $block_size);
                        if ($r === false) {
                            throw new \Exception('Failed to read response from stream');
                        } else {
                            $read += strlen($r);
                            $response .= $r;
                        }
                    } while ($read < $size);
                }
                break;
            case '*':
                $count = intval(substr($reply, 1));
                if ($count == '-1') {
                    return $response;
                }
                $response = array();
                for ($i = 0; $i < $count; $i++) {
                    $response[] = $this->readResponse();
                }
                break;
            case ':':
                $response = boolval(substr(trim($reply), 1));
                break;
            default:
                $response = new \Exception("Unknown response: {$reply}");
                break;
        }
        return $response;
    }
}


