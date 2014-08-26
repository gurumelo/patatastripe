<?php
if (isset($_POST) && count($_POST) == 3 && isset($_POST['cantidad']) && !empty($_POST['cantidad']) && filter_var($_POST['cantidad'], FILTER_VALIDATE_INT) !== FALSE && isset($_POST['email']) && !empty($_POST['email']) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) && isset($_POST['stripeToken']) && !empty($_POST['stripeToken'])) {
	$cantidad = htmlspecialchars(trim(addslashes(stripslashes(strip_tags($_POST['cantidad'])))));
	$email = htmlspecialchars(trim(addslashes(stripslashes(strip_tags($_POST['email'])))));
	$stripeToken = htmlspecialchars(trim(addslashes(stripslashes(strip_tags($_POST['stripeToken'])))));
	session_start();
	if ($_SESSION['cantidad'] == $cantidad && $_SESSION['email'] == $email) {
	
		session_unset();
		session_destroy();
		session_write_close();
		setcookie(session_name(),'',0,'/');
		session_regenerate_id(true);

		$cantidad = intval($cantidad);
	
		require_once('./lib/Stripe.php');

	
		Stripe::setApiKey("sk_test_8I6zOlgPiexmGxMyplLiUlRB");

		try {
			$charge = Stripe_Charge::create(array(
				"amount" => $cantidad,
				"currency" => "eur",
				"card" => $stripeToken,
				"description" => $email)
			);
		} catch(Stripe_CardError $e) {
			// The card has been declined
			echo $e;
		}
	
	}
	
}

?>
