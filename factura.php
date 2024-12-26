<?php

$dato=array(
    'telefono'=>'931563393',
    'pago'=>$_POST['cliente_pago'],
    'correo'=>'ivanronyci@gmail.com',
    'cajero'=>'Ivan Rony',
);

$emisor = array(
    'tipodoc'                   =>  '6',
    'nrodoc'                    =>  '10601803033',
    'razon_social'              =>  'CETI ORG',
    'nombre_comercial'          =>  'CETI',
    'direccion'                 =>  'VIRTUAL',
    'ubigeo'                    =>  '200101',
    'departamento'              =>  'PUNO',
    'provincia'                 =>  'PUNO',
    'distrito'                  =>  'PUNO',
    'pais'                      =>  'PE',
    'usuario_secundario'        =>  'MODDATOS',
    'clave_usuario_secundario'  =>  'MODDATOS'
);

$cliente = array(
    'tipodoc'                   =>  '6',
    'nrodoc'                    =>  $_POST['cliente_nrodoc'],
    'razon_social'              =>  $_POST['cliente_razon_social'],
    'direccion'                 =>  $_POST['cliente_direccion'],
    'pais'                      =>  $_POST['cliente_pais'],
);

$comprobante = array(
    'tipodoc'                   =>  '01', //FACTURA: 01, BOLETA: 03, NC: 07, ND: 08
    'serie'                     =>  'FACT',
    'correlativo'               =>  1,
    'fecha_emision'             =>  date('Y-m-d'),
    'hora'                      =>  '00:00:00',
    'fecha_vencimiento'         =>  date('Y-m-d'),
    'moneda'                    =>  'PEN',
    'total_opgravadas'          =>  0.00,
    'total_opexoneradas'        =>  0.00,
    'total_opinafectas'         =>  0.00,
    'total_impbolsas'           =>  0.00,
    'total_opgratuitas1'        =>  0.00,
    'total_opgratuitas2'        =>  0.00,
    'igv'                       =>  0.00,
    'total'                     =>  0.00,
    'total_texto'               =>  '',
    'forma_pago'                =>  'Credito', //Contado o Credito, si hay cuotas cuando es credito
    'monto_pendiente'           =>  200.00 //Contado: 0 y no hay cuotas
);

$cuotas = array(
    array(
        'cuota'                 =>  'Cuota001',
        'monto'                 =>  100.00,
        'fecha'                 =>  '2025-12-20'
    ),
    array(
        'cuota'                 =>  'Cuota002',
        'monto'                 =>  100.00,
        'fecha'                 =>  '2025-12-24'
    )
);

$detalle = array();

// Recorre los ítems enviados por el formulario
$contador = 1;
while (isset($_POST["cantidad_$contador"])) {
    $detalle[] = array(
        'item'                      =>  $contador,
        'codigo'                    =>  "PRO00$contador", // Puedes adaptar esto a tu necesidad
        'descripcion'               =>  $_POST["descripcion_$contador"], // Aquí también puedes personalizar la descripción
        'cantidad'                  =>  $_POST["cantidad_$contador"],
        'precio_unitario'           =>  $_POST["precio_unitario_$contador"], // Debes incluir la lógica adecuada para obtener el precio
        'valor_unitario'            =>  $_POST["precio_unitario_$contador"], // Igual, lógica de valor sin IGV
        'igv'                       =>  0.00, // Lógica para calcular el IGV
        'tipo_precio'               =>  '01', // Tipo de precio
        'porcentaje_igv'            =>  0.00,
        'importe_total'             =>  $_POST["importe_total_$contador"],
        'valor_total'               =>  $_POST["importe_total_$contador"], // Lógica para calcular el valor total
        'unidad'                    =>  'NIU',
        'bolsa_plastica'            =>  'NO',
        'total_impuesto_bolsas'     =>  0.00,
        'tipo_afectacion_igv'       =>  '20', // Tipo de afectación (gravada, exonerada, etc.)
        'codigo_tipo_tributo'       =>  '9997', // Código de tributo
        'tipo_tributo'              =>  'VAT',
        'nombre_tributo'            =>  'EXO'
    );
    $contador++;
}


//inicializar varibles totales
$total_opgravadas = 0.00;
$total_opexoneradas = 0.00;
$total_opinafectas = 0.00;
$total_opimpbolsas = 0.00;
$total = 0.00;
$igv = 0.00;
$op_gratuitas1 = 0.00;
$op_gratuitas2 = 0.00;

foreach ($detalle as $key => $value) {
    
    if ($value['tipo_afectacion_igv'] == 10) { //op gravadas
        $total_opgravadas += $value['valor_total'];
    }

    if ($value['tipo_afectacion_igv'] == 20) { //op exoneradas
        $total_opexoneradas += $value['valor_total'];
    }

    if ($value['tipo_afectacion_igv'] == 30) { //op inafectas
        $total_opinafectas += $value['valor_total'];
    }

    $igv += $value['igv'];
    $total_opimpbolsas = $value['total_impuesto_bolsas'];
    $total += $value['importe_total'] + $total_opimpbolsas;
}

$comprobante['total_opgravadas'] = $total_opgravadas;
$comprobante['total_opexoneradas'] = $total_opexoneradas;
$comprobante['total_opinafectas'] = $total_opinafectas;
$comprobante['total_impbolsas'] = $total_opimpbolsas;
$comprobante['total_opgratuitas_1'] = $op_gratuitas1;
$comprobante['total_opgratuitas_2'] = $op_gratuitas2;
$comprobante['igv'] = $igv;
$comprobante['total'] = $total;

require_once('cantidad_en_letras.php');
$comprobante['total_texto'] = CantidadEnLetra($total);

//PARTE 1: CREAR EL XML DE FACTURA
require_once('./api/api_genera_xml.php');
$obj_xml = new api_genera_xml();

//nombre del XML segun SUNAT
$nombreXML = $emisor['nrodoc'] . '-' . $comprobante['tipodoc'] . '-' . $comprobante['serie'] . '-' . $comprobante['correlativo'];
$rutaXML = 'xml/';

$obj_xml->crea_xml_invoice($rutaXML . $nombreXML, $emisor, $cliente, $comprobante, $detalle, $cuotas);

require_once('./api/api_cpe.php');
$objEnvio = new api_cpe();
$estado_envio = $objEnvio->enviar_invoice($emisor, $nombreXML, 'certificado_digital/', 'xml/', 'cdr/');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados de Envío</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .result-container {
            max-width: 600px;
            margin: auto;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #4CAF50;
        }
        .result-item {
            margin: 10px 0;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .result-item:last-child {
            border-bottom: none;
        }
        .result-label {
            font-weight: bold;
            color: #333;
        }
        .result-value {
            color: #555;
        }
        .status-success {
            color: green;
        }
        .status-fail {
            color: red;
        }
    </style>
</head>
<body>
    <div class="result-container">
        <h1>Resultados del Envío</h1>
        <div class="result-item">
            <span class="result-label">Estado de Envío:</span>
            <span class="result-value <?php echo ($estado_envio['estado'] === 'Exitoso') ? 'status-success' : 'status-fail'; ?>">
                <?php echo $estado_envio['estado']; ?>
            </span>
        </div>
        <div class="result-item">
            <span class="result-label">Mensaje:</span>
            <span class="result-value"><?php echo $estado_envio['estado_mensaje']; ?></span>
        </div>
        <div class="result-item">
            <span class="result-label">Descripción:</span>
            <span class="result-value"><?php echo $estado_envio['descripcion']; ?></span>
        </div>
        <div class="result-item">
            <span class="result-value"><?php echo '<form action="genera_pdf2.php" method="POST" target="_blank">';
echo '<input type="hidden" name="emisor" value="' . htmlspecialchars(json_encode($emisor)) . '">';
echo '<input type="hidden" name="cliente" value="' . htmlspecialchars(json_encode($cliente)) . '">';
echo '<input type="hidden" name="comprobante" value="' . htmlspecialchars(json_encode($comprobante)) . '">';
echo '<input type="hidden" name="detalle" value="' . htmlspecialchars(json_encode($detalle)) . '">';
echo '<input type="hidden" name="dato" value="' . htmlspecialchars(json_encode($dato)) . '">';
echo '<button type="submit">Imprimir Factura en PDF</button>';
echo '</form>' ?></span>
        </div>
        
    </div>
</body>
</html>