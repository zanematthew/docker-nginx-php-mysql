<?php

namespace App\Acme;

class Connection
{
    /**
     * Something
     * @return array Ha.
     */
    public function connect(): string
    {
        try {
            $dsn = 'mysql:host=foo_mysql;dbname=test;charset=utf8;port=3306';
            $pdo = new \PDO($dsn, 'dev', 'dev');
            return '<strong>Connected!</strong>';
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }
}
