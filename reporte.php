<?php
session_start();

// Incluir el archivo wp-load.php de WordPress
define('WP_USE_THEMES', false);
require_once('../wp-load.php');

// Verificar si se ha solicitado cerrar sesión
if (isset($_GET['logout'])) {
    wp_logout();
    wp_redirect('login.php');
    exit;
}

// Verificar si el usuario ha iniciado sesión en WordPress
if (!is_user_logged_in()) {
    wp_redirect('login.php');
    exit;
} else {
    // algo
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="css_print/reporte.css">

    <script type="text/javascript">
        function confirmarCerrarSesion() {
            return confirm("¿Estás seguro de que deseas cerrar la sesión?");
        }

        function mostrarFechasSeleccionadas() {
            var fechaDesde = document.getElementById('fecha_desde').value;
            var fechaHasta = document.getElementById('fecha_hasta').value;
            document.getElementById('fechas-seleccionadas').innerText = "Desde: " + fechaDesde + " Hasta: " + fechaHasta;
        }

        function imprimirReporte(fechaDesde, fechaHasta) {
            var nuevaVentana = window.open('', '_blank');
            var contenidoTabla = document.getElementById('reporte-tabla').innerHTML;

            nuevaVentana.document.open();
            nuevaVentana.document.write('<html><head><title>Reporte de ventas</title>');
            nuevaVentana.document.write('<style>');
            nuevaVentana.document.write('table { border-collapse: collapse; width: 100%; }');
            nuevaVentana.document.write('th, td { text-align: left; padding: 8px; }');
            nuevaVentana.document.write('tr:nth-child(even) { background-color: #f2f2f2; }');
            nuevaVentana.document.write('</style>');
            nuevaVentana.document.write('</head><body>');
            nuevaVentana.document.write('<h2>Fechas seleccionadas: Desde ' + fechaDesde + ' Hasta ' + fechaHasta + '</h2>');
            nuevaVentana.document.write('<table>' + contenidoTabla + '</table></body></html>');
            nuevaVentana.document.close();

            nuevaVentana.print();
        }
    </script>
</head>
<body>

<div class="container">
    <h1>Administración</h1>
    <span style="color:#666; font-size:14px;">Reporte de ventas</span>
    <div>
        <select onchange="location = this.value;">
            <option value="#">Seleccione</option>
            <option value="ped5.php">Ir a pedidos abiertos</option>
            <option value="cancelados_completados.php">Ir a pedidos cerrados</option>
        </select>

        <a href="ped5.php?logout" onclick="return confirmarCerrarSesion();" style="float: right; margin-right: 5px; color:#fff; text-decoration:none; background-color:#FF5B00; padding: 8px; border-radius:5px; border: solid 1px #8b0000;">Cerrar sesión</a>
    </div>

    <?php
require_once('../wp-load.php');
require_once('../wp-admin/includes/admin.php');
include_once('../wp-includes/class-wp-list-table.php');
include_once('../wp-admin/includes/class-wp-screen.php');
include_once('../wp-content/plugins/woocommerce/includes/class-wc-order.php');

// Obtener las fechas seleccionadas
$fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : date('Y-m-d');
$fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : $fecha_desde; // Si no se selecciona 'hasta', usar 'desde'

// Ajuste para permitir que se busque en el mismo día
if ($fecha_desde > $fecha_hasta) {
    $fecha_hasta = $fecha_desde; // Si 'desde' es mayor que 'hasta', corregir la fecha
}

$args = array(
    'status' => 'completed',
    'date_completed' => $fecha_desde . '...' . $fecha_hasta, // Buscar en el rango de fechas
    'limit' => -1
);

$orders = new WC_Order_Query($args);
$orders_data = $orders->get_orders();

echo "<form method='get'>";
echo "<label style='font-size:16px; font-weight:bold;' for='fecha_desde'>Fecha desde:&nbsp</label>";
echo "<input class='container' type='date' id='fecha_desde' name='fecha_desde' value='$fecha_desde'>&nbsp";
echo "<label style='font-size:16px; font-weight:bold;' for='fecha_hasta'>Fecha hasta:&nbsp</label>";
echo "<input class='container' type='date' id='fecha_hasta' name='fecha_hasta' value='$fecha_hasta'>&nbsp";
echo "<input class='print-button' type='submit' value='Mostrar'>";
echo "</form></p>";

$totales_por_tipo_pago = array();
$total_general = 0;

foreach ($orders_data as $order) {
    $fecha_order = $order->get_date_completed()->format('Y-m-d');

    if ($fecha_order >= $fecha_desde && $fecha_order <= $fecha_hasta) {
        $tipo_pago = $order->get_payment_method_title();
        $total = $order->get_total();
        if (isset($totales_por_tipo_pago[$tipo_pago])) {
            $totales_por_tipo_pago[$tipo_pago] += $total;
        } else {
            $totales_por_tipo_pago[$tipo_pago] = $total;
        }

        // Añadir el total al tipo de pago "efectivo"
        if ($tipo_pago === 'Efectivo') {
            if (isset($totales_por_tipo_pago['Efectivo'])) {
                $totales_por_tipo_pago['Efectivo'] += $total;
            } else {
                $totales_por_tipo_pago['Efectivo'] = $total;
            }
        }

        $total_general += $total;
    }
}

echo "<table id='reporte-tabla'>";
echo "<tr><th>Tipo de pago</th><th>Total</th></tr>";

foreach ($totales_por_tipo_pago as $tipo_pago => $total) {
    echo "<tr><td>" . $tipo_pago . "</td><td>" . $total . "</td></tr>";
}

echo "<tr><td><strong>Total general</strong></td><td><strong>" . $total_general . "</strong></td></tr>";
echo "</table>";
?>


    <div id="fechas-seleccionadas"></div>
    <button class="print-button2" onclick="imprimirReporte(document.getElementById('fecha_desde').value, document.getElementById('fecha_hasta').value);">Imprimir reporte</button>
</div>
</body>
</html>
