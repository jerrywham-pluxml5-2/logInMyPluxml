<?php if(!defined('PLX_ROOT')) exit; 
/**
 * Plugin logInMyPluxml
 *
 * @package	PLX
 * @version	1.3
 * @date	19/01/2014
 * @author	Cyril MAGUIRE
 **/
# initialisation de la classe de bannissement
include_once(PLX_ROOT.'plugins/logInMyPluxml/ban.php');
$Ban = new BanYourAss();

# récupération d'une instance de plxShow
$plxShow = plxShow::getInstance();
$plxPlugin = $plxShow->plxMotor->plxPlugins->getInstance('logInMyPluxml');

# Variable pour retrouver la page d'authentification
define('PLX_LOGINPAGE', true);

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Initialisation variable erreur
$error = '';
$msg = '';

# Déconnexion
if(!empty($_GET['d']) AND $_GET['d']==1) {

	$_SESSION = array();
	session_destroy();
	header('Location: login');
	exit;

	$formtoken = $_SESSION['formtoken']; # sauvegarde du token du formulaire
	$_SESSION = array();
	session_destroy();
	session_start();
	$msg = L_LOGOUT_SUCCESSFUL;
	$_GET['p']='';
	$_SESSION['formtoken']=$formtoken; # restauration du token du formulaire
	unset($formtoken);
}

# Authentification
if(!empty($_POST['login']) AND !empty($_POST['password'])) {
	$connected = false;
	foreach($plxMotor->aUsers as $userid => $user) {
		if ($_POST['login']==$user['login'] AND sha1($user['salt'].md5($_POST['password']))==$user['password'] AND $user['active'] AND !$user['delete']) {
			$_SESSION['user'] = $userid;
			$_SESSION['profil'] = $user['profil'];
			$_SESSION['hash'] = plxUtils::charAleatoire(10);
			$_SESSION['domain'] = $session_domain;
			$_SESSION['lang'] = $user['lang'];
			$connected = true;
		}
	}
	if($connected) {
		$Ban->ban_loginOk();
		header('Location: '.plxUtils::getRacine().$plxMotor->get);
		exit;
	} else {
		$Ban->ban_loginFailed();
		$msg = L_ERR_WRONG_PASSWORD;
		$error = 'error';
	}
	if (!$Ban->ban_canLogin()) {
		$Ban->ban_loginFailed();
		$msg = L_ERR_WRONG_PASSWORD;
		$error = 'error';
	}
}
plxUtils::cleanHeaders();
?>
<!DOCTYPE html>
<html lang="<?php $plxShow->defaultLang() ?>">
<head>
	<meta charset="<?php $plxShow->charset('min'); ?>">
	<meta name="robots" content="noindex, nofollow" />
	<title><?php echo plxUtils::strCheck($plxMotor->aConf['title']); ?> - <?php echo L_AUTH_PAGE_TITLE ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo strtolower(PLX_CHARSET); ?>" />
	<link rel="icon" href="<?php $plxShow->template(); ?>/img/favicon.png" />
	<style type="text/css">
	body {
		background: #f9f9f9;
		margin: 0;
		padding: 0;
		color: #555;
		font-family: arial, helvetica, sans-serif;
		font-size:0.72em;
	}
	body#auth {
		background-color:Grey;
		margin-top:150px;
	}
	a {
		color:#2175bd;
		font-size:11px;
		font-weight:600;
		text-decoration:none;
		outline:none;
	}
	a:link, a:visited {color:#2175bd;}
	a:hover { text-decoration:none; color:#db2020; }
	#login { margin:0 auto 0 auto; padding:20px; width:275px; background:#fff; border:1px solid #aaa;-webkit-box-shadow: 7px 7px 5px rgba(50, 50, 50, 0.75);-moz-box-shadow: 7px 7px 5px rgba(50, 50, 50, 0.75);box-shadow: 7px 7px 5px rgba(50, 50, 50, 0.75); }
	#login fieldset { border:0; }
	#login .title { text-align:center; margin-bottom: 10px; padding: 0 0 10px 0; font-size:15px; border-bottom:1px solid #dedede; }
	#login label { float:left; display:block; color:#7a7a7a; font-weight:bold; }
	#login input[type=text], #login input[type=password] { margin:5px 0 15px 0; width:247px; padding:2px 3px 2px 3px; background-color:#f8f8f8; border:1px solid #aaa }
	#login input[type=text]:focus, #login input[type=password]:focus { outline:none; color:#222; border:1px solid #77BACE; }
	#login p { text-align:center; margin:0; padding:10px 0 0 0}
	#login p.msg, #login p.error { margin:10px 0 10px 0; padding:10px 5px 10px 5px; }
	#login p.error { background-color: #ffcfcf; border-color: #df8f8f 1px solid; color: #665252; }
	
	</style>
</head>

<body id="auth">
<div id="login">
	<form action="<?php echo plxUtils::getRacine().$plxMotor->get; ?>" method="post" id="form_auth">
	<fieldset>
		<?php echo plxToken::getTokenPostMethod() ?>
		<div class="title"><?php echo L_LOGIN_PUBLIC_PAGE ?></div>
		<?php (!empty($msg))?plxUtils::showMsg($msg, $error):''; ?>
		<label for="id_login"><?php echo L_AUTH_LOGIN_FIELD ?>&nbsp;:</label>
		<?php plxUtils::printInput('login', (!empty($_POST['login']))?plxUtils::strCheck($_POST['login']):'', 'text', '18-255',false,'','" required="required');?>
		<label for="id_password"><?php echo L_AUTH_PASSWORD_FIELD ?>&nbsp;:</label>
		<?php plxUtils::printInput('password', '', 'password','18-255',false,'','" required="required');?>
		<p><input class="button submit" type="submit" value="<?php echo L_SUBMIT_BUTTON ?>" /></p>
	</fieldset>
	</form>
</div>
</body>
</html>
<?php exit(); ?>