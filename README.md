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
├── App/
│   ├── constrollers/
|   |   ├── AdminController.php
|   |   ├── AnimalController.php
|   |   ├── AreaController.php
|   |   ├── AuthController.php
|   |   ├── CompraController.php
|   |   ├── GuiaController.php
|   |   ├── HomeController.php
|   |   ├── ReservaController.php
|   |   ├── TicketController.php
|   |   └── UsuarioController.php
│   ├── models/
|   |   ├── Administrador.php
│   │   ├── animal.php
│   │   ├── area.php
|   |   ├── Cliente.php
│   │   ├── compra.php
|   |   ├── Guia.php
|   |   ├── recorrido.php
|   |   ├── Reporte.php
|   |   ├── reserva.php
|   |   ├── ticket.php
│   │   └── Usuario.php
│   ├── repositories/
│   │   ├── AnimalRepository.php
|   |   ├── AreaRepository.php
|   |   ├── CompraRepository.php
|   |   ├── GuiaRepository.php
|   |   ├── RecorridoRepository.php
|   |   ├── ReporteRepository.php
|   |   ├── ReservaRepository.php
│   │   ├── TicketRepository.php
│   │   └── UsuarioRepository.php
│   ├── services/
│   │   |── AuthService.php
|   |   ├── CompraService.php
|   |   ├── RegisterService.php
│   │   └── ReservaService.php
│   ├── views/
|   |   ├── admin/
|   |   |   ├── animal_form.php
|   |   |   ├── animales.php
|   |   |   ├── dashboard.php
|   |   |   └── recorridos.php
|   |   ├── animales/
|   |   ├── areas/
|   |   ├── auth/
|   |   |   ├── login.php
|   |   |   ├── perfil.php
|   |   |   └── register.php
|   |   ├── compras/
|   |   |   ├── crear.php
|   |   |   ├── historial.php
|   |   |   ├── listar.php
|   |   |   └── login.php
|   |   ├── errors/
|   |   |   └── 403.php
|   |   ├── guias/
|   |   |   ├── partials/
|   |   |   |   └── tabs.php
|   |   |   ├── dashboard.php
|   |   |   ├── detalle_recorrido.php
|   |   |   ├── horarios.php
|   |   |   ├── reporte_crear.php
|   |   |   ├── reporte_historial.php
|   |   |   └── reporte_seleccionar.php
|   |   ├── reservas/
|   |   |   ├── historial.php
|   |   |   ├── listar.php
|   |   |   ├── pagoqr.php
|   |   |   └── reservar.php
|   |   ├── tickets/
|   |   ├── usuarios/
|   |   |   └── historial.php
|   |   └── home.php
├── config/
│   ├── constants.php
│   └── Database.php
├── core/
│   ├── Authorization.php
│   ├── config.php
│   ├── Database.php
│   ├── Logger.php
│   └── session.php
├── public/
│   ├── img/
│   ├── historial.php
│   ├── index.php
│   ├── logout.php
│   ├── pagoqr_reserva.php
│   └── reservar.php
├── rutas/
│   └── web.php
├── vendor/
├── .gitignore
├── dbzoowonderland.sql
├── composer.json
├── composer.lock
├── Plan_Iteraciones_Sistema.md
├── README.md
└── US-13_IMPLEMENTACION.md

## Módulos

- Primera Iteración: Módulo cliente 
- Segunda Iteración: En desarrollo

## Autor

Antropomorfos - Tecnología Web II

## Login
admin
Usuario: faviopzoo
Contraseña: favio2026

guia
Usuario: charliep
Contraseña: cha2026

cliente
Usuario: juancm
Contraseña: 123