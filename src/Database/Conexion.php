
<?php

class Conexion
{
    public static function conectar(): PDO
    {
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $baseDatos = getenv('DB_NAME') ?: 'sistema_reservas';
        $usuario = getenv('DB_USER') ?: 'root';
        $password = getenv('DB_PASSWORD') ?: '';
        $puerto = getenv('DB_PORT') ?: '3306';
        $dsn = "mysql:host={$host};port={$puerto};dbname={$baseDatos};charset=utf8mb4";
        $opciones = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $ca = getenv('DB_SSL_CA');

        if ($ca) {
            $opciones[PDO::MYSQL_ATTR_SSL_CA] = $ca;
            $opciones[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
        }
        return new PDO($dsn, $usuario, $password, $opciones);
    }
}
