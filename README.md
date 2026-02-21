# ZooWonderland - Sistema de Gestión de Zoologico

Sistema desarrollado como proyecto del curso Tecnología Web II

## Requisitos

- PHP 8.2 o superior
- MySQL 8.0 o superior
- Composer

## Instalación

1. Clonar el repositorio
2. Ejecutar `composer install`
3. Configurar archivo `.env`
4. Importar base de datos
5. Acceder a `http://localhost/zoowonderland/public`

## Estructura del Proyecto
/
├── config/
├── public/
│   ├── index.php
│   └── login.php
├── src/
│   ├── enums/
│   ├── models/
│   │   ├── Login.php
│   │   ├── Registro.php
│   │   ├── Comprar.php
│   │   └── Historial.php
│   ├── Repositories/
│   │   ├── Users.php
│   │   ├── Compras.php
│   │   └── Historial.php
│   ├── Services/
│   └── Utils/
├── vendor/
├── .gitignore
├── composer.json
├── composer.lock
└── README.md

## Módulos

- Primera Iteración: Módulo cliente 
- Segunda Iteración: En desarrollo

## Autor

Antropomorfos - Tecnología Web II
