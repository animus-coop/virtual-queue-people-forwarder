# Virtual Queue Token Verifier SDK

SDK para redireccionar personas a la fila virtual en caso de sobrecarga del sistema.

## Instalación

```bash
composer require virtual-queue/people-forwarder
```

## Recomenedaciones
Inicializá el SDK en el punto más alto posible del flujo de ejecución, antes de cualquier lógica, consulta o procesamiento. La intención es que el redireccionamiento ocurra como la primera —o una de las primeras— acciones del sistema para evitar lo máximo posible cualquier carga. Por ej. en Laravel podes insertarlo en `bootstrap/autoload.php`

```php
#index.php
require_once './vendor/autoload.php';
use VirtualQueue\PeopleForwarder\PeopleForwarder;
$forwarder = new PeopleForwarder('tuticketera', '123456');
$forwarder->forward();
```

## Activación / Desactivación (API del lado de ticketera)
Crear un API endpoint del lado de la ticketera con una logica similar a la siguiente:

```php
<?php
require_once './vendor/autoload.php';

use VirtualQueue\PeopleForwarder\PeopleForwarder;
use VirtualQueue\PeopleForwarder\Exception\SdkException;

$action = $_GET['action']; //activate|deactivate
$timestamp = $_GET['timestamp'];
$signature = $_GET['signature'];

$forwarder = new PeopleForwarder('tuticketera', 'private_key');
// 'tuticketera' es el subdominio dentro de virtual-queue. Ej. https://tuticketera.virtual-queue.com

try {
    $forwarder->dispatchAction($action, $signature, $timestamp);
}   catch (SdkException $e) {
    echo $e->getMessage();
}
```

## Docker
Podes verlo implementado y corriendo en esta imagen de docker:

`docker run -p 80:80 juliananimus/virtual-queue-people-forwarder`

## Licencia

MIT

