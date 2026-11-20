<?php

class Database {
    private static $host = 'sql101.infinityfree.com';
    private static $db_name = 'if0_43458494_habla_sin_voz';
    private static $username = 'if0_43458494';
    private static $password = '5wEDg6P7rD';
    private static $port = 3306;
    private static $charset = 'utf8mb4';
    private static $instance = null;
    private static $lastError = null;

    /**
     * Obtiene la conexión activa a la base de datos
     * @return PDO|null
     */
    public static function getConnection() {
        if (self::$instance === null) {
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            // Probar base de datos local y variantes de hosting automáticamente
            $dbCandidates = array_unique([self::$db_name, 'habla_sin_voz', 'if0_43458494_HablaSinVoz']);
            $hosts = array_unique([self::$host, 'localhost']);

            foreach ($dbCandidates as $candidateDb) {
                foreach ($hosts as $h) {
                    try {
                        $dsn = "mysql:host=" . $h . ";port=" . self::$port . ";dbname=" . $candidateDb . ";charset=" . self::$charset;
                        self::$instance = new PDO($dsn, self::$username, self::$password, $options);
                        self::$lastError = null;
                        return self::$instance;
                    } catch (PDOException $e) {
                        self::$lastError = $e->getMessage();
                    }
                }
            }
            return null;
        }
        return self::$instance;
    }

    /**
     * Devuelve el último error ocurrido al conectar
     */
    public static function getLastError() {
        return self::$lastError;
    }

    /**
     * Verifica si la conexión está disponible
     */
    public static function isConnected() {
        return self::getConnection() !== null;
    }
}
