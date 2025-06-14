<?php

/**
 * ...
 *
 * @var array
 */
function app()
{
    return new class
    {
        /**
         * ...
         *
         * @var array
         */
        public static $matches = [];

        /**
         * ...
         *
         * @var array
         */
        public static $segments = [];

        /**
         * ...
         *
         * @var string
         */
        public static $uri = null;

        /**
         * ...
         *
         * @var array
         */
        public static $regex = [
            ':number' => '([0-9]+)',
            ':string' => '([A-Za-z]+)',
            ':slug'   => '([A-Za-z0-9-_]+)',
            ':any'    => '(.+)',
        ];

        /**
         * ...
         *
         * @param  string $method
         * @return object
         */
        public static function method(string $method)
        {
            if ($_SERVER['REQUEST_METHOD'] == $method) {
                return new self;
            }

            return self::escape();
        }

        /**
         * ...
         *
         * @param  string $string
         * @return object
         */
        public static function uri(string $string)
        {
            if (self::$uri === null) {
                self::$uri = self::get()->path();
            }

            if (preg_match('~^' . strtr($string, self::$regex) . '$~ixs', self::$uri, self::$matches)) {
                array_shift(self::$matches);
                return new self;
            }

            return self::escape();
        }

        /**
         * ...
         *
         * @param  string $string
         * @return object
         */
        public static function segment(string $string)
        {
            if (empty(self::$segments)) {
                self::$segments = self::get()->segments();
            }

            $segmentsbackup = self::$segments;

            if (preg_match('~^' . strtr($string, self::$regex) . '$~ixs', array_shift(self::$segments), self::$matches)) {
                array_shift(self::$matches);
                self::$uri = implode('/', self::$segments);
                return new self;
            }

            self::$segments = $segmentsbackup;
            self::$uri = self::get()->path();

            return self::escape();
        }

        /**
         * ...
         *
         * @param  string $action
         * @return object
         */
        public static function middleware(object $action)
        {
            if (self::callUserFuncArray($action)) {
                return new self;
            }

            return self::escape();
        }

        /**
         * ...
         *
         * @param  string $action
         * @return object
         */
        public static function call($action)
        {
            return self::callUserFuncArray($action);
        }

        /**
         * ...
         *
         * @param  string $action
         * @return object
         */
        public static function die()
        {
            die;
        }

        /**
         * ...
         *
         * @param  string $action
         * @return object
         */
        public static function callUserFuncArray($action)
        {
            switch (gettype($action)) {
                case 'array':
                    call_user_func_array([new $action[0], $action[1]], self::$matches);
                    break;
                case 'object':
                    call_user_func_array($action, self::$matches);
                    break;
            }

            return new self;
        }

        /**
         * ...
         *
         * @param  string $string
         * @return object
         */
        public static function redirect(string $url = '', int $status = 302)
        {
            header('Location: ' . self::get()->base() . $url, true, $status);

            die;
        }

        /**
         * ...
         *
         * @var array
         */
        public static function db()
        {
            return new class
            {
                public static $pdo;
                public static $query;

                public static $host = 'localhost';
                public static $name = 'test';
                public static $user = 'root';
                public static $pass = '1234';

                public function __construct()
                {
                    self::$pdo = new PDO(
                        'mysql:host=' .
                            self::$host . ';dbname=' .
                            self::$name,
                        self::$user,
                        self::$pass
                    );

                    self::$pdo->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, 'SET NAMES utf8');
                    self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
                }

                public static function query(string $sql, array $value = [])
                {
                    self::$query = self::$pdo->prepare($sql);
                    self::$query->execute($value);

                    return self::$query;
                }

                /**
                 * ...
                 */
                public static function host(string $host)
                {
                    self::$host = $host;

                    return new self;
                }
            };
        }

        /**
         * ...
         *
         * @var array
         */
        public static function pdo(string $sql)
        {
            return new class($sql)
            {
                public static $pdo;
                public static $query;

                public static $host = 'localhost';
                public static $name = 'test';
                public static $user = 'root';
                public static $pass = '1234';

                public function __construct($sql)
                {
                    self::$pdo = new PDO(
                        'mysql:host=' .
                            self::$host . ';dbname=' .
                            self::$name,
                        self::$user,
                        self::$pass
                    );

                    self::$pdo->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, 'SET NAMES utf8');
                    self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

                    self::$query = self::$pdo->prepare($sql);
                    self::$query->execute();
                }

                public static function first()
                {
                    return self::$query->fetch();
                }
            };
        }

        /**
         * ...
         *
         * @var array
         */
        public static function sql($sql)
        {
            $pdo = new PDO('mysql:host=localhost;dbname=test', 'root', '1234');

            $pdo->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, 'SET NAMES utf8');
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

            $query = $pdo->prepare($sql);
            $query->execute();

            return $query;
        }

        /**
         * ...
         *
         * @var array
         */
        public static function get()
        {
            return new class
            {
                /**
                 * ...
                 *
                 * @return string '/' || '/shop/'
                 */
                public static function base()
                {
                    return str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
                }

                /**
                 * ...
                 *
                 * @return string 'en/user/edit/3'
                 */
                public static function path()
                {
                    return substr(explode('?', $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'])[0], strlen(self::base()));
                }

                /**
                 * ...
                 *
                 * @return array
                 *
                 * array
                 * (
                 *     [0] => en
                 *     [1] => user
                 *     [2] => edit
                 *     [3] => 3
                 * )
                 */
                public static function segments()
                {
                    return explode('/', self::path());
                }
            };
        }

        /**
         * ...
         *
         * @param  string $string
         * @return object
         */
        public static function escape()
        {
            return new class
            {
                public function __call($name, $arguments)
                {
                    return new self;
                }
            };
        }
    };
}

// var_dump(app()->db()->query('SELECT * FROM test')->fetch());
// var_dump(app()->pdo('SELECT * FROM test')->first());
// var_dump(app()->sql('SELECT * FROM test')->fetch());

app()->method('GET')->call(function () {
    var_dump(4556565);
});

app()->call(function () {
    var_dump(4556565);
})->die();

app()->call(function () {
    var_dump(4556565);
});
