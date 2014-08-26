<?php
header('Content-Type: text/html; charset=UTF-8');
if (isset($_POST) && count($_POST) == 3 && isset($_POST['cantidad']) && !empty($_POST['cantidad']) && filter_var($_POST['cantidad'], FILTER_VALIDATE_INT) !== FALSE && isset($_POST['email']) && !empty($_POST['email']) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) && isset($_POST['stripeToken']) && !empty($_POST['stripeToken'])) {
	$cantidad = htmlspecialchars(trim(addslashes(stripslashes(strip_tags($_POST['cantidad'])))));
	$email = htmlspecialchars(trim(addslashes(stripslashes(strip_tags($_POST['email'])))));
	$stripeToken = htmlspecialchars(trim(addslashes(stripslashes(strip_tags($_POST['stripeToken'])))));
	session_start();
	if ($_SESSION['cantidad'] == $cantidad && $_SESSION['email'] == $email) {
	
		session_unset();
		
		if (ini_get("session.use_cookies")) {
			$params = session_get_cookie_params();
			setcookie(session_name(), '', time() - 42000,
			$params["path"], $params["domain"],
			$params["secure"], $params["httponly"]
			);
		}
		
	
		session_destroy();
		session_write_close();

		$cantidad = intval($cantidad);
	
		require_once('./lib/Stripe.php');

	
		Stripe::setApiKey("sk_test_8I6zOlgPiexmGxMyplLiUlRB");

		try {
			$charge = Stripe_Charge::create(array(
				"amount" => $cantidad,
				"currency" => "eur",
				"card" => $stripeToken,
				"description" => $email,
				"receipt_email" => $email)
			);
			if ($charge['paid'] == 1) {
				echo "Pago realizado con éxito. Muchas gracias.";
			}
		} catch(Stripe_CardError $e) {
			$esp = [
				"incorrect_number" => "El número de tarjeta es incorrecto.",
				"invalid_number" => "El número de tarjeta no es válido.",
				"invalid_expiry_month" => "El mes de caducidad no es válido.",
				"invalid_expiry_year" => "El año de caducidad no es válido.",
				"invalid_cvc" => "El código de seguridad no es válido.",
				"expired_card" => "La tarjeta está caducada.",
				"incorrect_cvc" => "El código de seguridad no es correcto.",
				"incorrect_zip" => "El código postal es incorrecto.",
				"card_declined" => "La tarjeta fue rechazada.",
				"missing" => "Su tarjeta no está siendo cargada.",
				"processing_error" => "Ocurrió un error mientras se procesaba el pago.",
				"rate_limit" => "Error inesperado."
			];
			
			// The card has been declined
			// print_r($e);
			$body = $e->getJsonBody(); 
			$err = $body['error'];
			echo $esp[$err['code']];
		} catch (Stripe_InvalidRequestError $e) { 
			// Invalid parameters were supplied to Stripe's API
			echo "Parámetros inválidos";
		} catch (Stripe_AuthenticationError $e) {
			// Authentication with Stripe's API failed // (maybe you changed API keys recently)
			echo "Autentificación falló";
		} catch (Stripe_ApiConnectionError $e) { 
			// Network communication with Stripe failed
			echo "Error"; 
		} catch (Stripe_Error $e) { 
			// Display a very generic error to the user, and maybe send // yourself an email 
			echo "Error";
		} catch (Exception $e) { 
			// Something else happened, completely unrelated to Stripe 
			echo "Error";
		}
	
	}
	
}
?>
