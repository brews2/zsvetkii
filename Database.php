<?php

class Database {
    public $pdo;

    public function __construct($config) {
        $dsn = "mysql:" . http_build_query($config, "", ";");
        $this->pdo = new PDO($dsn);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function query($sql) {
        $statement = $this->pdo->prepare($sql);
        $statement->execute();
        return $statement;
    }
}