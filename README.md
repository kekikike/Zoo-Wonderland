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
|   └── constants.php
├── public/
|   ├── img/
│   ├── index.php
|   ├── comprar.php
|   ├── historial.php
|   ├── reservar.php
|   ├── registrar.php 
|   ├── logout.php
│   └── login.php
├── src/
│   ├── enums/
│   ├── models/
|   |   ├── Administrador.php
│   │   ├── animal.php
│   │   ├── area.php
|   |   ├── Cliente.php
|   |   ├── Guia.php
│   │   ├── compra.php
|   |   ├── recorrido.php
|   |   ├── reserva.php
|   |   ├── ticket.php
│   │   └── usuario.php
│   ├── Repositories/
│   │   ├── AnimalRepository.php
|   |   ├── AreaRepository.php
|   |   ├── CompraRepository.php
|   |   ├── RecorridoRepository.php
|   |   ├── ReservaRepository.php
│   │   ├── TicketRepository.php
│   │   └── UsuarioRepository.php
│   ├── Services/
│   │   |── Auth.php
|   |   ├── autoload_session.php
│   │   └── Register.php
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
