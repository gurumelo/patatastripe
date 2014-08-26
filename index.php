<?php
#?fritas=céntimos&correo=juanito@juanito.com 

if (isset($_GET) && count($_GET) == 2 && isset($_GET['fritas']) && !empty($_GET['fritas']) && filter_var($_GET['fritas'], FILTER_VALIDATE_INT) !== FALSE && isset($_GET['correo']) && !empty($_GET['correo']) && filter_var($_GET['correo'], FILTER_VALIDATE_EMAIL)) {
	$fritas = htmlspecialchars(trim(addslashes(stripslashes(strip_tags($_GET['fritas'])))));
	$fritas2 = number_format((float)$fritas/100, 2, '.', '');
	$correo = htmlspecialchars(trim(addslashes(stripslashes(strip_tags($_GET['correo'])))));
	session_start();
	session_regenerate_id();
	$_SESSION['cantidad'] = $fritas;
	$_SESSION['email'] = $correo;
}
?>


<!DOCTYPE html>
<html lang="es">
	<head>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
		<title>Patatas fritastripe</title>
		<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
		<script type="text/javascript">
			esp = {
				"incorrect_number": "El número de tarjeta es incorrecto.",
				"invalid_number": "El número de tarjeta no es válido.",
				"invalid_expiry_month": "El mes de caducidad no es válido.",
				"invalid_expiry_year": "El año de caducidad no es válido.",
				"invalid_cvc": "El código de seguridad no es válido.",
				"expired_card": "La tarjeta está caducada.",
				"incorrect_cvc": "El código de seguridad no es correcto.",
				"incorrect_zip": "El código postal es incorrecto.",
				"card_declined": "La tarjeta fue rechazada.",
				"missing": "Su tarjeta no está siendo cargada.",
				"processing_error": "Ocurrió un error mientras se procesaba el pago.",
				"rate_limit": "Error inesperado."
			};
			
			
			
			// This identifies your website in the createToken call below
			Stripe.setPublishableKey('tu llave pública');
			var stripeResponseHandler = function(status, response) {
				var $form = $('#payment-form');
	 
				if (response.error) {
					//console.log(response.error.code);
					// Show the errors on the form
					//$form.find('.payment-errors').text(response.error.message);
					$form.find('.payment-errors').text(esp[response.error.code]);
					$form.find('button').prop('disabled', false);
				} else {
					// token contains id, last4, and card type
					var token = response.id;
					// Insert the token into the form so it gets submitted to the server
					$form.append($('<input type="hidden" name="stripeToken" />').val(token));
					// and re-submit
					$form.get(0).submit();
				}
			};
 
			jQuery(function($) {
				$('#payment-form').submit(function(e) {
					var $form = $(this);
					// Disable the submit button to prevent repeated clicks
					$form.find('button').prop('disabled', true);
 
					Stripe.card.createToken($form, stripeResponseHandler);
 
					// Prevent the form from submitting with the default action
					return false;
				});
			});
		</script>
	</head>
	<body>
		<h1>patatas fritastripe</h1>
 
		<form action="pago.php" method="POST" id="payment-form">
			<p class="payment-errors"></p>
			
			<input type="hidden" name="cantidad" value="<?= @$fritas ?>"/>
			
			<input type="hidden" size="20" name="email" value="<?= @$correo ?>"/>
 
			<div class="form-row">
				<label>
					<span>Número de tarjeta</span>
					<input type="text" size="20" data-stripe="number" maxlength="20"/>
				</label>
			</div>
 
			<div class="form-row">
				<label>
					<span>CVC</span>
					<input type="text" size="4" data-stripe="cvc" maxlength="4"/>
				</label>
			</div>
 
			<div class="form-row">
				<label>
					<span>Caducidad (MM/AAAA)</span>
					<input type="text" size="2" data-stripe="exp-month" maxlength="2"/>
				</label>
				<span> / </span>
				<input type="text" size="4" data-stripe="exp-year" maxlength="4"/>
			</div>
 

			<button type="submit">Pagar <?= @$fritas2 .' €'?></button>
		</form>
	</body>
</html>
