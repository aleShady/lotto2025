<?php
class DBM {
    private $con;

    public function __construct() {
        try {
            $this->con = new PDO("mysql:host=localhost;dbname=dsantarella", "root", "");
            $this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Errore connessione DB: " . $e->getMessage());
        }
    }

    public function read(string $sql): array {
        $stmt = $this->con->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function write(string $sql): bool {
        return $this->con->exec($sql) !== false;
    }
}
?>
