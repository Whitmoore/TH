<?php

namespace Tygh;

use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;
use \Monolog\Handler\NativeMailerHandler;
use \Monolog\Processor\IntrospectionProcessor;

class LogFacade
{
    protected static $instance;
    protected static $log;
    protected function __construct()
    {
        /* if (!isset(self::$instance))
        self::$instance = new static; */
        //fn_print_die($_SERVER);
        fn_mkdir(DIR_ROOT . '/var/logs');
        $stream = new StreamHandler(DIR_ROOT . '/var/logs/log', Logger::DEBUG);

        $stream->setFormatter(new LineFormatter("[%datetime%] %channel%.%level_name%: %referer% %message%\n"));

        self::$log = new Logger('log');

        $server = "";

        if ( !empty($_SERVER['SERVER_NAME']) ) {
            $server = "[{$_SERVER['SERVER_NAME']}]";
        } elseif ( !empty($_SERVER['HOST_NAME']) ) {
            $server = "[{$_SERVER['HOST_NAME']}]";
        }

        self::$log->pushHandler($stream);
        self::$log->pushHandler(new NativeMailerHandler('admin@tennishouse.ru', "TennisHouse ERROR {$server}", 'admin@tennishouse.ru', Logger::ERROR));
        
    }

    protected function __clone() {}

    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }

    public static function add_audit_details($message)
    {
        $header = "";

        if ( IsSet($_SESSION['auth']['company_id']) )
                $header .= "[cid:{$_SESSION['auth']['company_id']}]";

        if ( IsSet($_SESSION['auth']['user_id']) )
                $header .= "[uid:{$_SESSION['auth']['user_id']}]";

        if ( IsSet($_SESSION['auth']['user_type']) )
                $header .= "[type:{$_SESSION['auth']['user_type']}]";

        if ( !empty($_SESSION['auth']['is_root']) && $_SESSION['auth']['is_root'] == 'Y' )
                $header .= "[is_root]";

        if ( !empty($_SESSION['auth']['act_as_user']) )
                $header .= "[act_as:{$_SESSION['auth']['act_as_user']}]";

        return "$header $message";
    }
    
    public static function add_backtrace($message)
    {
        $backtrace = "";
        $stack = debug_backtrace();
        if ( !empty($stack) ) {
            $stack = array_reverse($stack, true);
            $func = '';
            foreach ($stack as $v) {
                if (empty($v['file'])) {
                    $func = $v['function'];
                    continue;
                } elseif (!empty($func)) {
                    $v['function'] = $func;
                    $func = '';
                }
                $backtrace .= " {file:{$v['file']}, line:{$v['line']}, function:{$v['function']}}";
            }
        }
        return $message . $backtrace;
    }

    public static function debug($message)
    {
        if (!isset(self::$instance))
            self::$instance = new static;
        self::$log->addDebug(self::add_audit_details($message));
    }

    public static function info($message)
    {
        if (!isset(self::$instance))
            self::$instance = new static;
        self::$log->addInfo(self::add_audit_details($message));
    }

    public static function notice($message)
    {
        if (!isset(self::$instance))
            self::$instance = new static;
        self::$log->addNotice(self::add_audit_details($message));
    }

    public static function warning($message)
    {
        if (!isset(self::$instance))
            self::$instance = new static;
        self::$log->addWarning(self::add_audit_details($message));
    }

    public static function error($message)
    {
        if (!isset(self::$instance))
            self::$instance = new static;
        self::$log->addError(self::add_audit_details($message));
    }

    public static function critical($message)
    {
        if (!isset(self::$instance))
            self::$instance = new static;
        // This adds a backtrace to the message
        self::$log->addCritical(self::add_audit_details(self::add_backtrace($message)));
    }

    public static function alert($message)
    {
        if (!isset(self::$instance))
            self::$instance = new static;
        // This adds a backtrace to the message
        self::$log->addAlert(self::add_audit_details(self::add_backtrace($message)));
    }

    public static function emergency($message)
    {
        if (!isset(self::$instance))
            self::$instance = new static;
        // This adds a backtrace to the message
        self::$log->addEmergency( self::add_audit_details(self::add_backtrace($message)));
    }
}

class AuditLog extends LogFacade
{
    protected function __construct()
    {
        $stream = new StreamHandler(DIR_ROOT . '/logs/audit_log', Logger::DEBUG);
        $stream->setFormatter(new LineFormatter("[%datetime%] %channel%.%level_name%: %message%\n"));
        parent::$log = new Logger('audit');
        parent::$log->pushHandler($stream);
    }
}
