
<?php

class Conexion
{
    private static string $host = '127.0.0.1';
    private static string $baseDatos = 'sistema_reservas';
    private static string $usuario = 'root';
    private static string $password = '';

    public static function conectar(): PDO
    {
        $dsn = 'mysql:host=' . self::$host
             . ';dbname=' . self::$baseDatos
             . ';charset=utf8mb4';

        return new PDO($dsn, self::$usuario, self::$password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
}
