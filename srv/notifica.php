<?php

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../lib/php/ejecutaServicio.php";
require_once  __DIR__ . "/../lib/php/select.php";
require_once  __DIR__ . "/../lib/php/devuelveJson.php";
require_once  __DIR__ . "/Bd.php";
require_once __DIR__ . "/TABLA_SUSCRIPCION.php";
require_once __DIR__ . "/Suscripcion.php";
require_once __DIR__ . "/suscripcionElimina.php";

use Minishlink\WebPush\WebPush;

const AUTH = [
  "VAPID" => [
    "subject" => "https://notificacionesphp.gilbertopachec2.repl.co/",
    "publicKey" => "BMBlr6YznhYMX3NgcWIDRxZXs0sh7tCv7_YCsWcww0ZCv9WGg-tRCXfMEHTiBPCksSqeve1twlbmVAZFv7GSuj0",
    "privateKey" => "vplfkITvu0cwHqzK9Kj-DYStbCH_9AhGx9LqMyaeI6w"
  ]
];

ejecutaServicio(function () {
  $webPush = new WebPush(AUTH);

  // ✅ Lee el mensaje enviado por el cliente
  $datos = json_decode(file_get_contents("php://input"), true);
  $mensaje = trim($datos["mensaje"] ?? "") ?: "Hola desde el equipo ProRam 👋";

  $pdo = Bd::pdo();
  $suscripciones = select(
    pdo: $pdo,
    from: SUSCRIPCION,
    mode: PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE,
    opcional: Suscripcion::class
  );

  foreach ($suscripciones as $suscripcion) {
    $webPush->queueNotification($suscripcion, $mensaje);
  }

  $reportes = $webPush->flush();

  $reporteDeEnvios = "";
  foreach ($reportes as $reporte) {
    $endpoint = $reporte->getRequest()->getUri();
    $htmlEndpoint = htmlentities($endpoint);
    if ($reporte->isSuccess()) {
      $reporteDeEnvios .= "<dt>$htmlEndpoint</dt><dd>Éxito</dd>";
    } else {
      if ($reporte->isSubscriptionExpired()) {
        suscripcionElimina($pdo, $endpoint);
      }
      $explicacion = htmlentities($reporte->getReason());
      $reporteDeEnvios .= "<dt>$endpoint</dt><dd>Fallo: $explicacion</dd>";
    }
  }

  devuelveJson(["reporte" => ["innerHTML" => $reporteDeEnvios]]);
});
