<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok' => false, 'message' => 'Método no permitido.']);
  exit;
}

function campo($valor, $max = 500) {
  $valor = trim((string)($valor ?? ''));
  $valor = str_replace(["\r", "\n"], ' ', $valor);
  $valor = strip_tags($valor);
  if (mb_strlen($valor, 'UTF-8') > $max) {
    $valor = mb_substr($valor, 0, $max, 'UTF-8');
  }
  return $valor;
}

$nombre   = campo($_POST['nombre'] ?? '', 120);
$correo   = campo($_POST['correo'] ?? '', 160);
$empresa  = campo($_POST['empresa'] ?? '', 160);
$telefono = campo($_POST['telefono'] ?? '', 80);
$interes  = campo($_POST['interes'] ?? '', 120);
$mensaje  = trim(strip_tags((string)($_POST['mensaje'] ?? '')));
$website  = campo($_POST['website'] ?? '', 120); // Honeypot anti-spam

if ($website !== '') {
  echo json_encode(['ok' => true, 'message' => 'Mensaje enviado correctamente.']);
  exit;
}

if ($nombre === '' || $correo === '' || $interes === '' || $mensaje === '') {
  http_response_code(400);
  echo json_encode(['ok' => false, 'message' => 'Por favor completa los campos obligatorios.']);
  exit;
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'message' => 'El correo no parece válido.']);
  exit;
}

if (mb_strlen($mensaje, 'UTF-8') > 2500) {
  $mensaje = mb_substr($mensaje, 0, 2500, 'UTF-8');
}

// CAMBIA ESTE CORREO POR EL CORREO REAL AL QUE QUIERES RECIBIR LOS MENSAJES.
$destinatario = 'TU_CORREO_AQUI@tudominio.com';

$host = $_SERVER['SERVER_NAME'] ?? 'tudominio.com';
$host = preg_replace('/^www\./', '', $host);
$fromEmail = 'no-reply@' . $host;

$asunto = 'Nuevo contacto desde la página de Nómina Muerta';

$cuerpo  = "Nuevo mensaje desde la página web de Nómina Muerta\n\n";
$cuerpo .= "Nombre: {$nombre}\n";
$cuerpo .= "Correo: {$correo}\n";
$cuerpo .= "Empresa / Organización: {$empresa}\n";
$cuerpo .= "Teléfono: {$telefono}\n";
$cuerpo .= "Interés: {$interes}\n\n";
$cuerpo .= "Mensaje:\n{$mensaje}\n\n";
$cuerpo .= "Fecha del servidor: " . date('Y-m-d H:i:s') . "\n";
$cuerpo .= "IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'No disponible') . "\n";

$headers = [];
$headers[] = "From: Sitio Nómina Muerta <{$fromEmail}>";
$headers[] = "Reply-To: {$nombre} <{$correo}>";
$headers[] = "MIME-Version: 1.0";
$headers[] = "Content-Type: text/plain; charset=UTF-8";
$headers[] = "X-Mailer: PHP/" . phpversion();

$enviado = mail($destinatario, $asunto, $cuerpo, implode("\r\n", $headers));

if ($enviado) {
  echo json_encode(['ok' => true, 'message' => 'Mensaje enviado correctamente. Te responderé pronto.']);
} else {
  http_response_code(500);
  echo json_encode(['ok' => false, 'message' => 'No se pudo enviar el mensaje. Revisa la configuración de correo en cPanel.']);
}
?>
