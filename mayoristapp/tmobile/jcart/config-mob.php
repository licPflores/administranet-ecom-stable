<?php

// jCart v1.3
// http://conceptlogic.com/jcart/

// Do NOT store any sensitive info in this file!!!
// It's loaded into the browser as plain text via Ajax


////////////////////////////////////////////////////////////////////////////////
// REQUIRED SETTINGS

// Path to your jcart files
$config['jcartPath'] = 'jcart/';
// $config['jcartPath'] = 'tmobile/jcart/';  // tengo que poner un unico jcart por eso no adan

// Path to your checkout page
//$config['checkoutPath']           = 'checkout.php';
//echo print_r($_SESSION["formulario"]);
$formu=null;
if(isset($_SESSION["formulario"])){
	$formu = $_SESSION["formulario"];
}

switch($formu){
    case "pedido":
        $config['checkoutPath'] = 'alta_pedido_confirmado.php';
        break;
    case "devolucion":
        $config['checkoutPath'] = 'alta_devolucion_confirmado.php';
        break;
		
    case "remitoSistema":
         $config['checkoutPath'] = 'alta-remito-sistema-confirmado.php';
        break;
	case "remitoTalonario":
         $config['checkoutPath'] = 'alta-remito-talonario-confirmado.php';
        break;    		
    case "presupuesto":
        $config['checkoutPath'] = 'alta_presupuesto_confirmado.php';
        break;
}



// The HTML name attributes used in your item forms
$config['item']['id']             = 'my-item-id';               // Item id
$config['item']['name']           = 'my-item-name';             // Item name
$config['item']['price']          = 'my-item-price';            // Item price
$config['item']['priceN']         = 'my-item-priceN';           // Item price
$config['item']['qty']            = 'my-item-qty';              // Item quantity
$config['item']['tipoIva']        = 'my-item-tipoIva';          // Articulo Gravado o no
$config['item']['impIva']         = 'my-item-impIva';           // Importe del iva
$config['item']['iva']            = 'my-item-iva';              // Codigo de tipo de iva
$config['item']['alicuota']       = 'my-item-alicuota';         // Alicuota de iva
$config['item']['impInterno']     = 'my-item-impInterno';       // Impuesto interno
$config['item']['neto']           = 'my-item-neto';             // precio neto
$config['item']['netoN']          = 'my-item-netoN';            // precio neto
$config['item']['descPor']        = 'my-item-descPor';          // % descuento por renglon
//$config['item']['descTotal']      = 'my-item-descTotal';      // $ descuento
$config['item']['url']            = 'my-item-url';              // Item URL (optional)
$config['item']['add']            = 'my-add-button';            // Add to cart button
$config['item']['promo']          = 'my-item-promo';            // Si esta en promocion para analizarla
$config['item']['promoCant']      = 'my-item-promoCant';        // Cantidad de articulos sobre los que se hace descuento x promo
$config['item']['promoPorc']      = 'my-item-promoPorc';        // Porcentaje de promocion.
$config['item']['promoTipo']      = 'my-item-promoTipo';        // Tipo de Promocion, anula al porcentaje
// * impuesto interno
$config['item']['impInterno']     = 'my-item-impInterno';       // Impuesto interno
$config['item']['impInternoTasa'] = 'my-item-impInternoTasa';   // Tasa del impuesto interno para poder recalcularlo
$config['item']['impInternoDescripcion'] 	= 'my-item-impInternoDescripcion'; 		//impuesto interno descripcion impuesto interno Nuevo calculo
$config['item']['impInternoTipo'] 			= 'my-item-impInternoTipo'; 		// Impuesto interno tipo de calculo si porcenteja o monto fijo
$config['item']['impInternoPorcentaje'] 	= 'my-item-impInternoPorcentaje'; 	// Impuesto interno porcentaje de calculo %
$config['item']['impInternoMontoFijo'] 		= 'my-item-impInternoMontoFijo'; 	// Impuesto interno monto fijo dec calcul $
$config['item']['impInternoPesoCalculado'] 	= 'my-item-impInternoPesoCalculado'; // Impuesto interno peso calculado
$config['item']['impInternoPagoMinimo'] 	= 'my-item-impInternoPagoMinimo'; 	// Impuesto interno pago minimo
$config['item']['impInternoIdUnimed'] 		= 'my-item-impInternoIdUnimed'; 	// impuesto interno unidad de medidad.
// fin impuesto interno.
$config['item']['lote']           = 'my-item-lote';             // Variable Determina si el articulo Tiene Lote
$config['item']['idLote']         = 'my-item-idLote';           // Id del id de lote seleccionado.
$config['item']['nomLote']        = 'my-item-nomLote';           // nombre del lote para mostrar.
$config['item']['deposito']       = 'my-item-idDeposito';       // Id del Deposito de descarga del articulo.
$config['item']['entregado']      = 'my-item-entregado';
// Cantidad Unidad Display y Bulto
$config['item']['cantidadUnidadDisplay']  		= 'my-item-cantidad-unidad-display'; 	// Cantidad de unidades minimas en display.
$config['item']['cantidadDividir']				= 'my-item-cantidad-dividir';       	// el divisor o cantidad dividir es la cantidad de blto unidad display
$config['item']['tipoUnidadContada']           		= 'my-item-tipo-unidad-contada';            	// como conte si por unidad, display o bulto
$config['item']['cantidadUnidadMinimaContada'] 	= 'my-item-cantidad-minima-contada';	// la cantidad de unidades final entre lo que conte x bulto o unidad



//las promociones que vamos a calcular en esta seccion del carrito de compras
//son aquellas que en las que se aplica un descuento por cantidad comprada.
//Y el control se hace en el carrito porque es donde vamos a sumar y actualizar
//la cantidad realmente comprada.
// Your PayPal secure merchant ID
// Found here: https://www.paypal.com/webapps/customerprofile/summary.view
$config['paypal']['id']           = 'seller_1282188508_biz@conceptlogic.com';

////////////////////////////////////////////////////////////////////////////////
// OPTIONAL SETTINGS

// Three-letter currency code, defaults to USD if empty
// See available options here: http://j.mp/agNsTx
$config['currencyCode']           = '';

// Add a unique token to form posts to prevent CSRF exploits
// Learn more: http://conceptlogic.com/jcart/security.php
$config['csrfToken']              = false;

if(isset($formu)){
    switch($formu){
        // default cart for DEVOLUCION
        //================================================================================
        case "devolucion":
		$config['text']['cartTitle']      = 'Devolución';    // Shopping Cart
		$config['text']['singleItem']     = '';    // Item
		$config['text']['multipleItems']  = '';    // Items
		$config['text']['subtotal']       = 'Total';    // Subtotal
		$config['text']['update']         = 'actualizar';    // update
		$config['text']['checkout']       = 'Confirmar Devolución';    // checkout
		$config['text']['checkoutPaypal'] = '';    // Checkout with PayPal
		$config['text']['removeLink']     = '';    // remove
		$config['text']['emptyButton']    = '';    // empty
		$config['text']['emptyMessage']   = 'Devolución esta vacía';    // Your cart is empty!
		$config['text']['itemAdded']      = 'Item agregado';    // Item added!
		$config['text']['priceError']     = '';    // Invalid price format!
		$config['text']['limiteError']  = 'Se ha excedido del limite de Items por perdido';
		$config['text']['quantityError']  = 'Las Cantidades deben ser números enteros';    // Item quantities must be whole numbers!
		$config['text']['checkoutError']  = '';    // Your order could not be processed!
		$config['text']['percepError']    = 'Debe parametrizar el tipo de percepción para el cliente ';  
	break;
    
        case "pedido":
	// Override default cart text PEDIDO
	//==============================================================================
		$config['text']['cartTitle']      = 'Pedido';    // Shopping Cart
		$config['text']['singleItem']     = '';    // Item
		$config['text']['multipleItems']  = '';    // Items
		$config['text']['subtotal']       = 'Total';    // Subtotal
		$config['text']['update']         = 'actualizar';    // update
		$config['text']['checkout']       = 'Confirmar Pedido';    // checkout
		$config['text']['checkoutPaypal'] = '';    // Checkout with PayPal
		$config['text']['removeLink']     = '';    // remove
		$config['text']['emptyButton']    = '';    // empty
		$config['text']['emptyMessage']   = 'El pedido esta vacío';    // Your cart is empty!
		$config['text']['itemAdded']      = 'Item agregado';    // Item added!
		$config['text']['priceError']     = '';    // Invalid price format!
		$config['text']['limiteError']  = 'Se ha excedido del limite de Items por perdido';
		$config['text']['quantityError']  = 'Las Cantidades deben ser números enteros';    // Item quantities must be whole numbers!
		$config['text']['checkoutError']  = '';    // Your order could not be processed!
		$config['text']['percepError']    = 'Debe parametrizar el tipo de percepción para el cliente ';  
	break;
        case "presupuesto":
            // Override default cart text PEDIDO
	//==============================================================================
		$config['text']['cartTitle']      = 'Presupuesto';    // Shopping Cart
		$config['text']['singleItem']     = '';    // Item
		$config['text']['multipleItems']  = '';    // Items
		$config['text']['subtotal']       = 'Total';    // Subtotal
		$config['text']['update']         = 'actualizar';    // update
		$config['text']['checkout']       = 'Confirmar Presupuesto';    // checkout
		$config['text']['checkoutPaypal'] = '';    // Checkout with PayPal
		$config['text']['removeLink']     = '';    // remove
		$config['text']['emptyButton']    = '';    // empty
		$config['text']['emptyMessage']   = 'El presupuesto esta vacío';    // Your cart is empty!
		$config['text']['itemAdded']      = 'Item agregado';    // Item added!
		$config['text']['priceError']     = '';    // Invalid price format!
		$config['text']['limiteError']  = 'Se ha excedido del limite de Items por presupuesto';
		$config['text']['quantityError']  = 'Las Cantidades deben ser números enteros';    // Item quantities must be whole numbers!
		$config['text']['checkoutError']  = '';    // Your order could not be processed!
		$config['text']['percepError']    = 'Debe parametrizar el tipo de percepción para el cliente ';  
            break;
         case "remitoSistema":
            // Override default cart text REMITO SISTEMA
	//==============================================================================
		$config['text']['cartTitle']      = 'Remito Sistema';    // Shopping Cart
		$config['text']['singleItem']     = '';    // Item
		$config['text']['multipleItems']  = '';    // Items
		$config['text']['subtotal']       = 'Total';    // Subtotal
		$config['text']['update']         = 'actualizar';    // update
		$config['text']['checkout']       = 'Confirmar Remito';    // checkout
		$config['text']['checkoutPaypal'] = '';    // Checkout with PayPal
		$config['text']['removeLink']     = '';    // remove
		$config['text']['emptyButton']    = '';    // empty
		$config['text']['emptyMessage']   = 'El remito esta vacío';    // Your cart is empty!
		$config['text']['itemAdded']      = 'Item agregado';    // Item added!
		$config['text']['priceError']     = '';    // Invalid price format!
		$config['text']['limiteError']  = 'Se ha excedido del limite de Items por remito';
		$config['text']['quantityError']  = 'Las Cantidades deben ser números enteros';    // Item quantities must be whole numbers!
		$config['text']['checkoutError']  = '';    // Your order could not be processed!
		$config['text']['percepError']    = 'Debe parametrizar el tipo de percepción para el cliente ';  
            break;
		case "remitoTalonario":
		// Override default cart text REMITO TALOMARIO
	//==============================================================================
		$config['text']['cartTitle']      = 'Remito Talonario';    // Shopping Cart
		$config['text']['singleItem']     = '';    // Item
		$config['text']['multipleItems']  = '';    // Items
		$config['text']['subtotal']       = 'Total';    // Subtotal
		$config['text']['update']         = 'actualizar';    // update
		$config['text']['checkout']       = 'Confirmar Remito';    // checkout
		$config['text']['checkoutPaypal'] = '';    // Checkout with PayPal
		$config['text']['removeLink']     = '';    // remove
		$config['text']['emptyButton']    = '';    // empty
		$config['text']['emptyMessage']   = 'El remito esta vacío';    // Your cart is empty!
		$config['text']['itemAdded']      = 'Item agregado';    // Item added!
		$config['text']['priceError']     = '';    // Invalid price format!
		$config['text']['limiteError']  = 'Se ha excedido del limite de Items por remito';
		$config['text']['quantityError']  = 'Las Cantidades deben ser números enteros';    // Item quantities must be whole numbers!
		$config['text']['checkoutError']  = '';    // Your order could not be processed!
		$config['text']['percepError']    = 'Debe parametrizar el tipo de percepción para el cliente ';  
		break;
	}   
        
        
}else{
		$config['text']['cartTitle']      = 'PresupuestoP';    // Shopping Cart
		$config['text']['singleItem']     = '';    // Item
		$config['text']['multipleItems']  = '';    // Items
		$config['text']['subtotal']       = 'Total';    // Subtotal
		$config['text']['update']         = 'actualizar';    // update
		$config['text']['checkout']       = 'Confirmar Presupuesto';    // checkout
		$config['text']['checkoutPaypal'] = '';    // Checkout with PayPal
		$config['text']['removeLink']     = '';    // remove
		$config['text']['emptyButton']    = '';    // empty
		$config['text']['emptyMessage']   = 'El presupuesto esta vacío';    // Your cart is empty!
		$config['text']['itemAdded']      = 'Item agregado';    // Item added!
		$config['text']['priceError']     = '';    // Invalid price format!
		$config['text']['limiteError']  = 'Se ha excedido del limite de Items por presupuesto';
		$config['text']['quantityError']  = 'Las Cantidades deben ser números enteros';    // Item quantities must be whole numbers!
		$config['text']['checkoutError']  = '';    // Your order could not be processed!
		$config['text']['percepError']    = 'Debe parametrizar el tipo de percepción para el cliente ';  
}
// Override the default buttons by entering paths to your button images
$config['button']['checkout']     = '';
$config['button']['paypal']       = '';
$config['button']['update']       = '';
$config['button']['empty']        = '';

////////////////////////////////////////////////////////////////////////////////
// Talonarios
$config['text']['fechaT'] ='';
$config['text']['pvT'] ='';
$config['text']['numeroT'] ='';


////////////////////////////////////////////////////////////////////////////////
// ADVANCED SETTINGS

// Display tooltip after the visitor adds an item to their cart?
$config['tooltip']                = true;

// Allow decimals in item quantities?
$config['decimalQtys']            = true;

// How many decimal places are allowed?
$config['decimalPlaces']          = 2;

// Number format for prices, see: http://php.net/manual/en/function.number-format.php
$config['priceFormat']            = array('decimals' => 2, 'dec_point' => ',', 'thousands_sep' => '.');

// Send visitor to PayPal via HTTPS?
$config['paypal']['https']        = true;

// Use PayPal sandbox?
$config['paypal']['sandbox']      = false;

// The URL a visitor is returned to after completing their PayPal transaction
$config['paypal']['returnUrl']    = '';

// The URL of your PayPal IPN script
$config['paypal']['notifyUrl']    = '';
$config['limiteRenglon']=10;
if(isset($_SESSION['limite_renglon'])){
    $config['limiteRenglon'] = $_SESSION['limite_renglon'];
}