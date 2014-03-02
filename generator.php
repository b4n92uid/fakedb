<?php

require_once('filters.php');
require_once('tools.php');

class Generator
{
    private $pdo;
    private $table;
    private $printOnly;

    private $dataMap;
    private $parent;
    private $vars;

    function __construct($filename, $printOnly = false)
    {
        global $argv;

        $this->printOnly = $printOnly;
        $this->table = simplexml_load_file($filename);

        $this->paths = array(
            'w' => dirname($argv[0]).'/words.txt',
            'p' => dirname($argv[0]).'/paragraph.txt',
        );

        if(!$this->printOnly)
        {
            list($host, $user, $pass, $dbname) = array(
                $this->table['host'],
                $this->table['user'],
                $this->table['pass'],
                $this->table['dbname']
            );

            try
            {
                $this->pdo = new PDO("mysql:dbname=$dbname;host=$host", $user, $pass);
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
            catch(PDOException $e)
            {
                die($e->getMessage());
            }
        }

        mt_srand(make_seed());
    }

    protected function applyFilter($value, $filter)
    {
        $func = 'filter_'.$filter;

        if(function_exists($func))
            $value = $func($value);

        return $value;
    }

    protected function generateEcho($params)
    {
        return implode(' ', $params);
    }

    protected function generateSequence($params)
    {
        if($params == null)
            $params = array(1,1);

        list($min, $max) = $params;

        $count = mt_rand($min, $max);
        $sequence = range(0, $count);
        $array = array('0','1','2','3','4','5','6','7','8','9');

        foreach($sequence as &$c)
            $c = array_rand_value($array);

        return implode('', $sequence);
    }

    protected function generateChar($params)
    {
        if($params == null)
            $params = array(1,1);

        list($min, $max) = $params;

        $count = mt_rand($min, $max);

        $chars = range(0, $count);

        $array = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n',
                        'o','p','q','r','s','t','u','v','w','x','y','z');

        if(isset($params[2]) and $params[2] == true)
            $array = array_merge($array, array('0','1','2','3','4','5','6','7','8','9'));

        foreach($chars as &$c)
            $c = array_rand_value($array);

        return implode('', $chars);
    }

    protected function generateText($params)
    {
        if($params == null)
            $params = array(1,100);

        list($min, $max) = $params;

        $count = mt_rand($min, $max);

        $content = file_get_contents($this->paths['p']);
        $content = str_replace("\r\n", "\n", $content);

        $paragraphs = explode("\n\n", $content);

        $p = array_rand_value($paragraphs);
        $w = explode(' ', $p);
        $w = array_slice_rand($w, $count);
        $p = implode(' ', $w);

        return ucfirst($p).'.';
    }

    protected function generateWord($params)
    {
        if($params == null)
            $params = array(1,1);

        list($min, $max) = $params;

        $words = file($this->paths['w']);

        $count = mt_rand($min, $max);

        $filter = function($word) {return $word[0] != '#';};
        $words= array_filter($words, $filter);

        $chars = array_rand_value($words, $count);

        if(!is_array($chars))
            $chars = array($chars);

        $chars = array_map('trim', $chars);

        $chars = implode(' ', $chars);
        $chars = ucfirst($chars);

        return $chars;
    }

    protected function generateNumber($params)
    {
        if($params == null)
            $params = array(0,100);

        list($min, $max) = $params;

        $step = 1;

        if(isset($params[2]))
            $step = $params[2];

        return (string)round(mt_rand($min, $max), $step);
    }

    protected function generateArray($params)
    {
        return serialize($params);
    }

    protected function generateFromField($params)
    {
        return $this->dataMap[$params[0]];
    }

    protected function generateFromDataBase($params)
    {
        if($this->printOnly)
            return '?generated?';

        $link = explode('.',$params[0]);

        $q = $this->pdo->query("SELECT $link[1] FROM $link[0] ORDER BY RAND() LIMIT 1");
        $q = $q->fetch();

        return $q[$link[1]];
    }

    protected function generateSet($params)
    {
        return array_rand_value($params);
    }

    protected function generateFromPath($params)
    {
        $files = glob($params[0]);

        if(!empty($files))
        {
            return array_rand_value($files);
        }
        else
            return null;
    }

    protected function generateDate($params)
    {
        $min = new Datetime($params[0]);
        $max = new Datetime($params[1]);

        $time = mt_rand($min->getTimestamp(), $max->getTimestamp());

        $date = new Datetime();
        $date->setTimestamp($time);

        return $date->format('Y-m-d');
    }

    protected function generateRelation($params)
    {
        return (string)$this->parent[$params[0]];
    }

    protected function generateWebSite($params)
    {
        $host = 'http://';

        $words = file($this->paths['w']);

        $words= array_filter($words, function($word) {
            return $word[0] != '#';
        });

        $host .= trim(array_rand_value($words));

        if($params == null)
        {
            $tld = array('.com', '.org', '.fr', '.net', '.dz');
            $host .= array_rand_value($tld);
        }
        else
            $host .= array_rand_value($params);

        return $host;
    }

    protected function generateFromFileUrl($params)
    {
        list($src, $dst, $ext) = $params;

        if(!is_dir($dst) and !$this->printOnly)
            mkdir($dst, 0777, true);

        $dst = rtrim($dst, '/') . '/' . md5(time()) . '.' . $ext;

        if(!$this->printOnly)
        {
            $filecontent = file_get_contents($src);
            file_put_contents($dst, $filecontent);
        }

        return $dst;
    }

    protected function generateMail($params)
    {
        $words = file($this->paths['w']);

        $filter = function($word) {return $word[0] != '#';};
        $words= array_filter($words, $filter);
        $email = trim(array_rand_value($words));

        $host = '';

        if($params == null)
        {
            $tld = array('.com', '.org', '.fr', '.net', '.dz');
            $host = trim(array_rand_value($words)) . array_rand_value($tld);
        }
        else
            $host = array_rand_value($params);

        $variation = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n',
                        'o','p','q','r','s','t','u','v','w','x','y','z',
                        '0','1','2','3','4','5','6','7','8','9');

        $variation = implode('', array_rand_value($variation, 3));

        return $email . $variation . '@' . $host;
    }

    protected function patternCallback($pattern)
    {
        $type = $pattern[1];
        $params = (isset($pattern[2]) and !empty($pattern[2])) ? explode(',', $pattern[2]) : null;
        $filter = (isset($pattern[3]) and !empty($pattern[3])) ? $pattern[3] : null;

        if($params != null) foreach($params as &$p)
            if($p[0] == '$')
            {
                $name = substr($p, 1);

                if(isset($this->localVars[$name]))
                    $p = $this->localVars[$name];
                else
                    throw new Exception("Undifined variable `$name`");
            }

        $callmap = array(
            /**
             * [e:string1, string2, ...]
             * just echo given string with space
             */
            'e' => 'generateEcho',

            /**
             * [w:min,max]
             * generate a word with with random length
             * between min and max
             */
            'w' => 'generateWord',

            /**
             * [t:min,max]
             * generate a text with with random words
             * between min and max
             */
            't' => 'generateText',

            /**
             * [n:min,max]
             * generate a number between min and max
             */
            'n' => 'generateNumber',

            /**
             * [w:min,max,numeric]
             * generate a sequence of chararcters
             * between min and max length and optionally include number
             */
            'c' => 'generateChar',

            /**
             * [q:min,max]
             * generate a sequence of number only
             * between min and max length
             */
            'q' => 'generateSequence',

            /**
             * [a:item1,item2,...]
             * return a serialized array with given items
             */
            'a' => 'generateArray',
            'array' => 'generateArray',

            /**
             * [f:column]
             * return the value of the column of the current record
             * must be already generated
             */
            'f' => 'generateFromField',
            'field' => 'generateFromField',

            /**
             * return the value of the column of the parent record
             */
            'r' => 'generateRelation',
            'rel' => 'generateRelation',

            /**
             * [b:table.column]
             * return the value of the column from table
             * internaly make a sql query to fetch value
             * must be already recorded
             */
            'b' => 'generateFromDataBase',
            'database' => 'generateFromDataBase',

            /**
             * [s:item1,item2,...]
             * pick a random item from the given list
             */
            's' => 'generateSet',
            'set' => 'generateSet',

            /**
             * [p:path]
             * pick a random filename from specified path
             */
            'p' => 'generateFromPath',
            'path' => 'generateFromPath',

            /**
             * [d:start,end]
             * return a random date between start and end
             */
            'd' => 'generateDate',
            'date' => 'generateDate',

            /**
             * [m:host.tld]
             * generate a mail with optionally
             * with a specified host and tld
             */
            'm' => 'generateMail',
            'mail' => 'generateMail',

            /**
             * [url:src,dst,ext]
             * fetch a file from src param, save it in dst dirpath
             * with a generated filename and optionally
             * the specified extention
             */
            'url' => 'generateFromFileUrl',

            /**
             * [web]
             * generate a url adress of a website
             * with optionally specified tld
             */
            'web' => 'generateWebSite',
        );

        $method = $callmap[$type];

        $value = $this->$method($params);

        if($filter !== null)
            $value = $this->applyFilter($value, $filter);

        return $value;
    }

    protected function generatePattern($pattern)
    {
        $pattern = preg_replace_callback(
                '#\[([a-z]+)(?::([^\|]+))?(?:\|([^\]]+))?\]#U',
                array($this, 'patternCallback'),
                $pattern
            );

        return $pattern;
    }

    protected function generateValue($data)
    {
        $this->localVars = $this->vars;

        foreach($data->attributes() as $k => $v)
            if(preg_match('#^var-(.+)#', $k, $m))
                $this->localVars[$m[1]] = (string)$v;

        $column = $data['column'];
        $value = null;

        if(!isset($data['pattern']))
            throw new Exception("Pattern field is absent for column '$data[column]'");

        $value = $this->generatePattern($data['pattern']);

        return $value;
    }

    protected function fillTable($table, $parent = null)
    {
        $this->dataMap = array();
        $this->parent = $parent;

        foreach($table->data as $data)
        {
            $value = $this->generateValue($data);

            $k = (string)$data['column'];
            $this->dataMap[$k] = $value;

            if($this->printOnly)
            {
                if(strlen($value) > 100)
                    $value = substr($value, 0, 100) . '... ' . '('.strlen($value).')';

                echo "$k = `$value`\n";
            }
        }

        $quote = function($val) { return '`'.mysql_real_escape_string($val).'`'; };
        $map_keys = array_keys($this->dataMap);
        $map_keys = array_map($quote, $map_keys);
        $sql_keys = implode(', ', $map_keys);

        $quote = function($val) { return '\''.mysql_real_escape_string($val).'\''; };
        $map_vals = array_values($this->dataMap);
        $map_vals = array_map($quote, $map_vals);
        $sql_vals = implode(', ', $map_vals);

        echo "> Inserting in $table[name]\n";

        $sql = "INSERT INTO $table[name]($sql_keys) VALUES($sql_vals)\n";

        if(!$this->printOnly)
            $this->pdo->exec($sql);

        if(!empty($table->table))
        {
            if(!$this->printOnly)
            {
                $id = $this->pdo->lastInsertId();

                if($id)
                {
                    $q = $this->pdo->query("SELECT * FROM $table[name] WHERE id=$id LIMIT 1");

                    $data = $q->fetch(PDO::FETCH_ASSOC);
                    $q->closeCursor();

                    foreach($table->table as $t)
                        $this->fillTable($t, $data);
                }

                else
                    echo "> Unavailable last inserted id in table `$table[name]`\n";
            }

            else foreach($table->table as $t)
                $this->fillTable($t, $data);
        }
    }

    public function process($count = 1)
    {
        if($this->printOnly)
        {
            for($i=0; $i<$count; $i++)
                $this->fillTable($this->table);
        }

        else
        {
            for($i=0; $i<$count; $i++)
            {
                try
                {
                    $this->pdo->beginTransaction();

                    $this->fillTable($this->table);

                    if(!$this->pdo->commit())
                    {
                        $err = $this->pdo->errorInfo();
                        throw new Exception($err[2]);
                    }
                }

                catch(Exception $e)
                {
                    $this->pdo->rollback();
                    echo '*** '.$e->getMessage()."\n";
                }
            }
        }
    }

    public function setVar($key, $value)
    {
        $this->vars[$key] = $value;
    }
}
