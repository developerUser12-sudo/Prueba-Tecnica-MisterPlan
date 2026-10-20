# Prueba técnica para Misterplan

Aplicación web de gestión de reservas de alojamiento desarrollada como parte de una prueba técnica para MisterPlan. El proyecto incluye una API desarrollada en PHP nativo, una base de datos MySQL, vistas renderizadas con Twig e interacción mediante JavaScript.

## URL de la web

* https://prueba-tecnica-misterplan.onrender.com/index.php?ruta=reservas

## Tecnologías utilizadas

### Backend

* PHP

### Gestiónd de dependencias

* Composer

### Frontend

* JavaScript
* HTML5
* Twig

### Base de datos

* MySQL

### Control de versiones

* Git

## Funcionalidades

* Gestión de reservas.
* Creación y consumo de API propia.
* Filtrar reservas.

## Estado del proyecto

🟢 **En desarrollo**

## Instalación y ejecución

### 1.Clonar repositorio

* git clone https://github.com/developerUser12-sudo/Prueba-Tecnica-MisterPlan.git
* Tras clonar el repositorio, copia la carpeta en el directorio htdocs de XAMPP

### 2.Instalar dependencias

* Desde la raíz del proyecto, ejecuta: composer install

### 3.Crear la base de datos

* Inicia Apache y MySQL desde el panel de control de XAMPP
* Accede a phpMyAdmin
* Crea una base de datos llamada sistema_reservas
* Importa el archivo SQL del proyecto

### 4.Configura la conexión

* La configuración local utiliza valores predeterminados. Si utilizas otra configuración en MySQL, adapta los valores correspondientes en la clase Conexion

### 5.Ejecutar la aplicación

*  Abre en el navegador: http://localhost/prueba-tecnica-reservas-misterplan/public/index.php?ruta=reservas