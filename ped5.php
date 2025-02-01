<?php
session_start();

// Incluir el archivo wp-load.php de WordPress
define('WP_USE_THEMES', false);
require_once('../wp-load.php');

// Verificar si se ha solicitado cerrar sesión
if (isset($_GET['logout'])) {
    // Cerrar sesión y redirigir al formulario de inicio de sesión de WordPress
    wp_logout();
    wp_redirect('login.php');
    exit;
}

// Verificar si el usuario ha iniciado sesión en WordPress
if (!is_user_logged_in()) {
    // Redirigir al formulario de inicio de sesión de WordPress
    wp_redirect('login.php');
    exit;
} else {

// El usuario ha iniciado sesión, puedes mostrar el contenido aquí
echo '<!DOCTYPE html>
<meta name="viewport" content="width=device-width, initial-scale=1.0">


<html>
<head>

<link rel="stylesheet" type="text/css" href="css_print/pagination.css">
<link rel="stylesheet" type="text/css" href="css_print/styles.css">
<title>Pedidos abiertos</title>';

echo '<script type="text/javascript">
	function imprimirPedido(pedidoID) {
		var pedidoDiv = document.getElementById(\'pedido-\' + pedidoID);
		var tablaHTML = pedidoDiv.querySelector(\'table\').outerHTML;
		var nuevaVentana = window.open(\'\', \'_blank\');
		nuevaVentana.document.write(\'<html><head><title>Pedido #\' + pedidoID + \'</title>\');
		nuevaVentana.document.write(\'<style>\');
		nuevaVentana.document.write(\'@media print { .no-imprimir { display: none; } }\');
		nuevaVentana.document.write(\'table { font-size: 10px; }\'); // Ajusta el tamaño de la fuente para la tabla
		nuevaVentana.document.write(\'th { font-weight: bold; }\'); // Aplica negrita a los títulos de la tabla
		nuevaVentana.document.write(\'#numero-orden { text-decoration: underline; color: black; }\'); // Agrega subrayado al número de orden
		nuevaVentana.document.write(\'button { padding: 10px 20px; background-color: #000; color: #fff; border: none; border-radius: 5px; cursor: pointer; }\'); // Estilos del botón de imprimir
		nuevaVentana.document.write(\'button:hover { background-color: #e4e4e4; color: #000; }\'); // Estilos del botón de imprimir al pasar el mouse
		nuevaVentana.document.write(\'</style></head><body>\');
		nuevaVentana.document.write(tablaHTML);
		nuevaVentana.document.write(\'<br><br>\'); // Agrega espacio adicional
		nuevaVentana.document.write(\'<button class="no-imprimir" onclick="window.print()">Imprimir Recibo</button>\');
		nuevaVentana.document.write(\'</body></html>\');
		nuevaVentana.document.close();
	}
</script>';


echo '<script type="text/javascript">
function confirmarCerrarSesion() {
  if (confirm("¿Estás seguro de que deseas cerrar la sesión?")) {
    return true;
  } else {
    return false;
  }
}
</script>';

//libreria a la impresora bluetooth
echo '<script src="https://cdn.jsdelivr.net/npm/escpos@2.0.6/browser/escpos.browser.min.js"></script>';

echo '<script type="text/javascript">
function refreshPage() {
  location.reload();
}
 setInterval(refreshPage, 15000); // 120000 milisegundos = 2 minutos
</script>

<script src="/jquery-3.6.4.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.5/dist/sweetalert2.all.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.5/dist/sweetalert2.min.css" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/escpos@2.0.6/browser/escpos.browser.min.js"></script>
	
</head>
<body>
<div class="container">
<h1>Administración</h1>
<span style="color:#666; font-size:#14px;">Pedidos abiertos</span></p>
<div>
<select onchange="location = this.value;">
<option value="#">Seleccione</option>
<option value="cancelados_completados.php">Ir a pedidos cerrados</p></option>
<option value="reporte.php">Reporte de ventas</p></option>
</select>
<!-- Agregar el enlace para cerrar sesión -->
<a href="ped5.php?logout" onclick="return confirmarCerrarSesion();" style="float: right; margin-right: 5px; color:#fff; text-decoration:none; background-color:#FF5B00; padding: 8px; border-radius: 5px; border: solid 1px #8b0000;">Cerrar sesión</a></p>
</div></p>
<div>';
}

//Conexión a la API de WordPress y WooCommerce
	require_once('../wp-load.php');
	require_once('../wp-admin/includes/admin.php');
	include_once('../wp-includes/class-wp-list-table.php');
	include_once('../wp-admin/includes/class-wp-screen.php');
	include_once('../wp-content/plugins/woocommerce/includes/class-wc-order.php');

// Obtener los pedidos de WooCommerce
$orders = wc_get_orders(array(
    'orderby' => 'date',
    'order' => 'DESC',
));

// Agregar la paginación
$per_page = 5;
$total_items = count($orders);
$total_pages = intval($total_items / $per_page) + ($total_items % $per_page != 0 ? 1 : 0);
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset = ($current_page - 1) * $per_page;
$orders = array_slice($orders, $offset, $per_page);

// AQUI MUESTRO LOS PEDIDOS
// VARIABLE PARA ALMACENAR EL NUMERO DE ORDEN MAS RECIENTE
$latest_order_number = get_option('latest_order_number', 0);


// Mostrar los pedidos
foreach ($orders as $order) {
    
    $fecha_hora_entrega = ''; 
    
    //var_dump($order->get_meta_data());
    foreach ($order->get_meta_data() as $item_id => $item) {
        if($item->key == 'fdoe_picked_time'){
            $fecha_hora_entrega = $item->value;
        }
    }
    //echo($fecha_hora_entrega);
    
    if ($order->get_status() !== 'completed' && $order->get_status() !== 'cancelled') {
        // Verificar si el número de orden es nuevo
        $current_order_number = $order->get_order_number();

         if ($current_order_number > $latest_order_number) {
            // Actualizar el número de orden más reciente en la opción de la base de datos
            update_option('latest_order_number', $current_order_number);

            // Mostrar el pop-up de "Nuevo pedido recibido" con reproducción de sonido
            echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.5/dist/sweetalert2.all.min.js"></script>';
            echo '<script>
                Swal.fire({
                    icon: "info",
                    title: "Nuevo pedido",
                    text: "Se ha recibido un nuevo pedido.",
                    confirmButtonText: "OK",
                    didOpen: () => {
                        // Reproducir el sonido al abrir el popup
                           var audio = new Audio("cash\\\caja.mp3");
                        audio.play();
                    }
                });
            </script>';
        }

echo '<div id="pedido-' . $order->get_id() . '">';
echo '<table>';
echo '<tr><td colspan="4" align="center" class="order-number">Número #' . $order->get_order_number() . '</td></tr>';
echo '<tbody>';



// Definir la información a mostrar en un array
$order_info = array(
    'Fecha' => $order->get_date_created(),
    'Nombre' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
    'Email' => '<a style="color:#000; text-decoration:none;" href="mailto:' . $order->get_billing_email() . '">' . $order->get_billing_email() . '</a>',
    'Teléfono' => '<a style="color:#000; text-decoration:none;" href="tel:' . $order->get_billing_phone() . '">' . $order->get_billing_phone() . '</a>', // Número de teléfono del cliente
    'Método de pago' => $order->get_payment_method_title(),
    'Método de envío' => $order->get_shipping_method(),
    'Envío' => wc_price($order->get_shipping_total()), // Valor del envío
    'Dirección de envío' => $order->get_shipping_address_1() . ', ' . $order->get_shipping_city(),
    
    'Fecha y Hora Entrega' => $fecha_hora_entrega,
    'Notas del cliente' => $order->get_customer_note(),
);


// Mostrar la información del pedido usando un ciclo foreach
foreach ($order_info as $label => $value) {
    // Verificar si el valor no está vacío y si el campo es "Envío" y el valor es distinto de 0
    if (!empty($value) && !($label === 'Envío' && $value === wc_price(0))) {
       echo '<tr>';
echo '<td colspan="4">';

if($label == "Método de envío"){
    echo '<div class="columna-izquierda"><strong>' . $label . ' : </strong>';
    
    if($order->get_shipping_method() == "Retirar en Local"){
       echo '<img src="img/local.png" style="vertical-align: middle;"  alt="Tipo de Entrega" width="25" height="25">'. $value;
    }else{
        echo '<img src="img/delivery.png" alt="Tipo de Entrega" width="25" height="25">'. $value;
    }
    
    echo '</div>';
    
}else{
    echo '<div class="columna-izquierda"><strong>' . $label . ' :</strong> '. $value .'</div>';
}



echo '</td>';
echo '</tr>';

    }
}

    
// Verificar si se ha enviado el formulario para actualizar el estado del pedido
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_order_status_nonce'])) {
            // Verificar el nonce de seguridad
            if (wp_verify_nonce($_POST['update_order_status_nonce'], 'update_order_status')) {
                // Obtener el ID del pedido y el nuevo estado
                $order_id = absint($_POST['order_id']);
                $new_status = sanitize_text_field($_POST['order_status']);
                // Obtener el objeto del pedido
                $order = wc_get_order($order_id);
                // Actualizar el estado del pedido
                if ($order && $new_status && $new_status != $order->get_status()) {
                    $order->update_status($new_status);
                }
            }
        }

/*// Mostrar la dirección de facturación si hay información disponible
$billing_address = $order->get_billing_address_1();
$billing_city = $order->get_billing_city();

if (!empty($billing_address) || !empty($billing_city) || !empty($billing_postcode) || !empty($billing_country) || !empty($billing_state)) {
    echo '<tr><td>Dirección de facturación:</td><td>';
    if (!empty($billing_address)) {
        echo $billing_address . ', ';}
    if (!empty($billing_city)) {
        echo $billing_city . ', ';
    } echo '</td></tr>';}*/

/*// Mostrar el total de precios si hay información disponible
    $order_total = $order->get_formatted_order_total();
    if (!empty($order_total)) {
        echo '<tr><td style="text-align:right;"><strong>Total:</strong></td><td>' . $order_total . '</td></tr>';}*/
    
    
// Mostrar los títulos de los productos
echo '<tr><th style="width: 15%; border: solid 1px #000; padding: 5px;">Cant</th><th colspan="2" style="width: 65%; border: solid 1px #000; padding: 5px;">Producto</th><th align="center" style="width: 20%; border: solid 1px #000; padding: 5px;">Subtotal</th></tr>';

// Mostrar los productos del pedido
foreach ($order->get_items() as $item_id => $item) {
    $_product = $item->get_product();
    
    // Calcular el subtotal del producto
    $subtotal = $item->get_quantity() * $_product->get_price();

    echo '<tr>';
    echo '<td style="width: 15%; border-left: solid 1px #ddd;" align="center">'. $item->get_quantity() .'</td>';
    echo '<td colspan="2" style="width: 55%;"><span style="font-weight:bold;">' . $_product->get_name() . '</span></td>';
    echo '<td align="center" style="width: 30%; border-right: solid 1px #ddd;">'. wc_price($subtotal) .'</td>';
    echo '</tr>';



// Mostrar las opciones del producto
    $product_options = $item->get_formatted_meta_data();
    $options = array();
    foreach ($product_options as $product_option) {
        $options[] = '<span style="display: inline-block; padding-left: 10px;">' . $product_option->display_value . '</span>';
    }

    echo '<tr><td colspan="4" style="border-left: solid 1px #ddd; border-right: solid 1px #ddd; border-bottom: 1px solid #ddd; padding: 0;">' . implode(', ', $options) . '</td></tr>';
}

// Mostrar el total de precios
echo '<tr><td colspan="4" style="text-align:left; border: 1px solid #ddd; border-bottom-radius: 5px; padding-left: 10px;"><strong>Total:&nbsp;&nbsp;</strong>'. $order->get_formatted_order_total() . '</td></tr>';
echo '</p>';

echo '</table></br>';

// Mostrar botones para imprimir y cambiar estado
echo '<div style="display:flex; align-items:center;">';

// Columna 1: Botón para imprimir el pedido
echo '<div style="flex-grow:1">';
echo '<button class="print-button" onclick="imprimirPedido(' . $order->get_id() . ')">Imprimir pedido</button>';
echo '</div>';

 // Mostrar el estado del pedido
$status = $order->get_status();
if (!empty($status) && $status !== 'completed' && $status !== 'cancelled') {
echo '<div style="background-color:#000; padding:8px; border-top-left-radius:5px; border-bottom-left-radius:5px; border:none; font-family:consolas; color:#fff;"><tr><td colspan="2">Estado: </td><td>' . wc_get_order_status_name($status) . '</td></tr></div>';
}

// Columna 2: Estado del pedido
echo '<div style="flex-grow:1">';
$created_date = $order->get_date_created();
$current_time = current_time('timestamp');
$created_time = strtotime($created_date);
if ($current_time - $created_time <= 11300) {

// AQUI MUESTRO EL BOTON NUEVO EN ROJO
echo '<strong style="color:#fff; float: left; padding-right:30px;">';
echo '<div class="btn-nuevo">Nuevo</div>';
echo '</strong>';


/*//AQUI MUESTRO EL POP UP AVISANDO QUE LLEGO UN NUEVO PEDIDO
// Agregar código JavaScript para mostrar la notificación emergente
    echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.5/dist/sweetalert2.all.min.js
"></script>'; // Cambie "ruta/a/" por la ruta real en su proyecto
    echo '<script>
        // Mostrar notificación emergente
        Swal.fire({
            icon: "info",
            title: "Nuevo pedido",
            text: "Se ha recibido un nuevo pedido.",
            confirmButtonText: "OK"
        });
    </script>';*/
}
echo '</div>';

// Columna 3: Formulario para cambiar el estado del pedido
echo '<div style="flex-grow:1">';
echo '<form method="post">';
echo '<select style="border-radius:5px; padding:5px; color:#666;" name="order_status" onchange="this.form.submit()">';
echo '<option>Cambiar estado</option>';
$statuses = wc_get_order_statuses();
foreach (wc_get_order_statuses() as $value => $label) {
echo '<option value="' . $value . '"' . selected($status, $value, false) . '>' . $label . '</option>';
}
echo '</select>';
echo '<input type="hidden" name="order_id" value="' . $order->get_id() . '">';
echo '<input type="hidden" name="update_order_status_nonce" value="' . wp_create_nonce('update_order_status') . '">';
echo '</form>';
echo '</div>';

echo '</div></br>';

if (isset($_POST['order_status'])) {
// Si se ha enviado el formulario, genera la etiqueta <meta> para actualizar la página cada 2 segundos
    echo '<meta http-equiv="refresh" content="0.2">';}

echo '</div>';

// Terminar la impresión del pedido actual
}}

// Cierra el cuerpo del documento HTML
	echo '</body></html>';

// Mostrar la paginación
	$pagination_args = array(
		'base' => add_query_arg('paged', '%#%'),
		'format' => '',
		'total' => $total_pages,
		'current' => $current_page,
		'show_all' => false,
		'end_size' => 1,
		'mid_size' => 2,
		'prev_next' => true,
		'prev_text' => __('« Atrás'),
		'next_text' => __('Siguiente »'),
		'type' => 'plain',
		'add_args' => false,
		'add_fragment' => ''
	);
	
	echo '<div class="pagination">';
	echo paginate_links($pagination_args);
	echo '</div>';
echo '</head>';
echo '</html>';
?>