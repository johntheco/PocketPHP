<?php


final class SqlDatabase
{
    private $pdo;
    private $stack = [];


    public function __construct($params)
    {
        $connectionParameters = "{$params['type']}:" . implode(";", [
            "host={$params['host']}",
            "port={$params['port']}",
            "dbname={$params['name']}",
        ]);

        $this->pdo = new PDO($connectionParameters, $params['user'], $params['pass']);
        $this->pdo->query("SET NAMES {$params['charset']}");

        foreach ($params['options'] as $key => $value) {
            $this->pdo->setAttribute($key, $value);
        }
    }

    public function select($table, $params = [])
    {
        $statement  = ($params['statement'])    ?: '1';
        $fields     = ($params['fields'])       ?: '*';
        $order      = ($params['order'])        ?: '';
        $group      = ($params['group'])        ?: '';
        $offset     = ($params['offset'])       ?: '0';
        $limit      = ($params['limit'])        ?: '';
        $key        = ($params['key'])          ?: '';
        $debug      = ($params['debug'])        ?: false;

        $query  = "SELECT {$fields} FROM {$table} WHERE {$statement}";
        $query .= ($group) ? " GROUP BY {$group}" : "";
        $query .= ($order) ? " ORDER BY {$order}" : "";

        if (intval($limit) + intval($offset) > 0) {
            $query .= " LIMIT ";

            $dl = "";

            if (! $offset > 0) {
                $offset = "";
            }

            if (! $limit > 0) {
                $limit = "";
            }

            if ($limit > 0 && $offset > 0) {
                $dl = ",";
            }

            $query .= "{$offset}{$dl}{$limit}";
        }

        $result = $this->pdo->query($query);
        $error = $this->pdo->errorInfo();

        if ($debug && $error[0] != '00000') {
            echo "<pre>";
            print_r($error);
            echo "</pre>";
        }

        if ($debug) {
            $trace = debug_backtrace();
            $caller = null;

            if ($trace[2]['function'] == 'selectRow') {
                $caller = $trace[2];
            } else if ($trace[1]['function'] == 'select') {
                $caller = $trace[1];
            }

            echo "<BR><span style='color: #bbb;'>Вызов из файла: {$caller['file']} [стр. {$caller['line']}]</span>";
        }


        // Fetch assoc and fetch group if key is required
        $pdoKeys = ($key) ? PDO::FETCH_ASSOC|PDO::FETCH_GROUP : PDO::FETCH_ASSOC;
        $result = $result->fetchall($pdoKeys);

        if ($key && ! $group) {
            $result = array_map('reset', $result);
        }

        return $result;
    }

    public function selectRow($table, $params)
    {
        $params['limit'] = 1;
        return $this->select($table, $params)[0];
    }

    protected function buildInsert($table, $values = [], $params = [])
    {
        $debug          = ($params['debug'])        ?: false;
        $onDuplicate    = ($params['onDuplicate'])  ?: false;
        $ignore         = ($params['ignore'])       ?: false;
        $mode           = ($params['mode'])         ?: 'insert';

        if (count($values) == 0) {
            return null;
        }

        $columns = [];

        foreach ($values as $key => $value) {
            $columns[] = "`{$key}`";
            $values[$key] = (is_null($value)) ? "null" : "'".addslashes(strval($value))."'";
        }

        $columns = implode(", ", $columns);
        $values = implode(", ", $values);

        $query = ($mode == 'insert')
            ? ($ignore)
                ? "INSERT IGNORE INTO {$table} ({$columns}) VALUES ({$values})"
                : "INSERT INTO {$table} ({$columns}) VALUES ({$values})"
            : "REPLACE INTO {$table} ({$columns}) VALUES ({$values})";

        if ($onDuplicate) {
            $query .= " ON DUPLICATE KEY UPDATE {$onDuplicate}";
        }

        return $query;
    }

    public function insert($table, $values = [], $params = [])
    {
        $query = $this->buildInsert($table, $values, $params);

        try {
            $this->pdo->query($query);
        } catch (PDOException $exception) {
            return false;
        }

        return $this->pdo->lastInsertId();
    }

    public function prepareInsert($table, $values = [], $params = [])
    {
        $this->stack[] = $this->buildInsert($table, $values, $params);
    }

    public function insertIgnore($table, $values = [], $params = [])
    {
        $params['ignore'] = true;
        return $this->insert($table, $values, $params);
    }

    public function prepareInsertIgnore($table, $values = [], $params = [])
    {
        $params['ignore'] = true;
        return $this->prepareInsert($table, $values, $params);
    }

    public function execute()
    {
        if (count($this->stack) === 0) {
            return;
        }

        $query = implode(";", $this->stack);

        echo "<pre>"; var_dump($query); echo "</pre>";

        $this->stack = [];
    }

    public function replace($table, $values = [], $params = [])
    {
        $params['mode'] = "replace";
        return $this->insert($table, $values, $params);
    }

    public function insertOnDuplicate($table, $values = [], $params = [])
    {
        $params['onDuplicate'] = true;
        return $this->insert($table, $values, $params);
    }

    public function update($table, $values = [], $params = [])
    {
        $statement  = ($params['statement'])    ?: '1';
        $debug      = ($params['debug'])        ?: false;

        if (count($values) == 0) {
            return null;
        }

        $update = [];

        foreach ($values as $key => $value) {
            $update[] = "`{$key}` = " . ((is_null($value)) ? "null" : "'".addslashes(strval($value))."'");
        }

        $update = implode(", ", $update);

        $query = "UPDATE {$table} SET {$update} WHERE {$statement}";

        try {
            $this->pdo->query($query);
        } catch (PDOException $exception) {
            if ($debug) {
                Functions::Debug("SqlDatabase :: Update exception", $exception);

                $error = $this->pdo->errorInfo();
                if ($error[0] != '') {
                    Functions::Debug("SqlDatabase :: Update error", $error);
                }
            }

            return false;
        }

        return true;
    }
    
    public function delete($table, $params)
    {
        $statement  = ($params['statement'])    ?: '1';
        $debug      = ($params['debug'])        ?: false;

        $query = "DELETE FROM {$table} WHERE {$statement}";

        try {
            $this->pdo->query($query);
        } catch (PDOException $exception) {
            if ($debug) {
                Functions::Debug("SqlDatabase :: Delete exception", $exception);

                $error = $this->pdo->errorInfo();
                if ($error[0] != '')
                    Functions::Debug("SqlDatabase :: Insert error", $error);
            }

            return false;
        }

        return true;
    }

    public function getTableColumns($tableName)
    {
        return $this->pdo->query("DESCRIBE {$tableName}")->fetchAll(PDO::FETCH_COLUMN);
    }
}
