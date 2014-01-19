<?php
/**
 * Plugin logInMyPluxml
 *
 * @package	PLX
 * @version	1.3
 * @date	19/01/2014
 * @author	Cyril MAGUIRE
 **/
# ---------------- FONCTION DE BANNISSEMENT ---------------
# ------------------ BEGIN LICENSE BLOCK ------------------
#
# @update     2013-10-26 Cyril MAGUIRE
# Copyright (c) 2013 SebSauvage
# See http://sebsauvage.net/paste/?36dbd6c6be607e0c#M5uR8ixXo5rXBpXx32gOATLraHPffhBJEeqiDl1dMhs
#
# Instructions d'utilisation:
# • Faites un require_once de ce script.
# • Initialisez une instance de la classe $Ban = new BanYourAss(); en début de script
# • à l'endroit où vous testez la validité du mot de passe:
#     • Si $Ban->ban_canLogin()==false, l'utilisateur est banni. Ne testez même pas le mot de passe: Rejetez l'utilisateur.
#    • Si $Ban->ban_canLogin()==true, vérifiez le mot de passe.
#          • Si le mot de passe est ok, appelez $Ban->ban_loginOk(), sinon appelez $Ban->ban_loginFailed()
# La lib s'occupe de compter le nombre d'échecs et de gérer la durée de bannissement 
# (bannissement/levée de ban).
# Cette lib créé un sous-répertoire "data" qui contient les données de bannissement 
# (ipbans.php) et un log de connexion (log.txt).
#
# Exemple
#        $Ban = new BanYourAss();
#        if (!$Ban->ban_canLogin()) { $pass=false; }
#        if($pass){ $Ban->ban_loginOk(); echo connect("success",array("username"=>$this->username)); }
#        else{ $Ban->ban_loginFailed(); echo connect("error","Incorrect Username or Password"); }
# ------------------- END LICENSE BLOCK -------------------

class BanYourAss {

	private $ipbans;
	private $PLUGINBANDIR;
	private $DATABANDIR;
	private $IPBANS_FILENAME='';
	private $BAN_AFTER = 3;// Ban IP after this many failures.
	private $BAN_DURATION = 1800; // Ban duration for IP address after login failures (in seconds) (1800 sec. = 30 minutes)

	private $consonnes = array('b','c','d','f','g','h','j','k','l','m','n','p','q','r','s','t','v','w','x','z');
	private $voyelles = array('a','e','i','o','u','y');
	private $caracteres = array('@','#','?','!','+','=','-','%','&','*');
	private $nombres = array('0','1','2','3','4','5','6','7','8','9');

	// ------------------------------------------------------------------------------------------
	// Brute force protection system
	// Several consecutive failed logins will ban the IP address for 30 minutes.

	public function __construct() {
		setlocale(LC_TIME, 'fr_FR.utf8','fra');
		date_default_timezone_set('Europe/Paris');
		if (!defined('BAN_DOCTYPE')) {define('BAN_DOCTYPE','<!DOCTYPE html><html lang="fr"><head><meta charset="utf8"></head><body><p>');}
		if (!defined('MSG_COME_BACK_IN')) {define('MSG_COME_BACK_IN','Revenez nous voir dans');}
		if (!defined('MSG_MIN_OR_NOT')) {define('MSG_MIN_OR_NOT','minutes ou pas...</p>');}
		if (!defined('MSG_IF_NOT_SPAMMER')) {define('MSG_IF_NOT_SPAMMER','<p>Si vous n\'êtes pas un robot');}
		if (!defined('CLICK_HERE')) {define('CLICK_HERE','cliquez ici');}
		if (!defined('SECURITY_SALT')) {define('SECURITY_SALT',$this->generateurMot(100));}
		if (!defined('BAN_END')) {define('BAN_END','</p></body></html>');}
		$this->PLUGINBANDIR = PLX_ROOT.'data/configuration/plugins/logInMyPluxml'; // Data subdirectory
		$this->DATABANDIR = $this->PLUGINBANDIR.'/ban'; // Data subdirectory
	
		$this->IPBANS_FILENAME = $this->DATABANDIR.'/ipbans.php'; // File storage for failures and bans.

		if (!is_dir($this->PLUGINBANDIR)) { mkdir($this->PLUGINBANDIR,0705); chmod($this->PLUGINBANDIR,0705); }
		if (!is_dir($this->DATABANDIR)) { mkdir($this->DATABANDIR,0705); chmod($this->DATABANDIR,0705); }
		if (!is_file($this->DATABANDIR.'/.htaccess')) { file_put_contents($this->DATABANDIR.'/.htaccess',"Allow from none\nDeny from all\n"); } // Protect data files.
		if (!is_file($this->IPBANS_FILENAME)) {
			file_put_contents($this->IPBANS_FILENAME, "<?php\n\$IPBANS=".var_export(array('FAILURES'=>array(),'BANS'=>array(),'NOTSPAM'=>array()),true).";\n?>");
		}
		include $this->IPBANS_FILENAME;
		$this->ipbans = $IPBANS;
	}

	public function generateurMot($longueur = 8,$nbCaracteres = 4,$caracteresSup = array(),$nombresSup = array(),$voyellesSup = array(),$consonnesSupp = array()) {
		$mot = '';
		$consonnes = $this->consonnes;
		$voyelles = $this->voyelles;
		$nombres = $this->nombres;
		$caracteres = $this->caracteres;
		$caracteresDejaChoisis = array();
		
		if (!empty($consonnesSupp)) {
			$consonnes = array_diff($this->consonnes,$consonnesSupp);
		}
		if (!empty($voyellesSup)) {
			$voyelles = array_diff($this->voyelles,$voyellesSup);
		}
		if (!empty($caracteresSup)) {
			$caracteres = array_diff($this->caracteres,$caracteresSup);
		}
		if (!empty($nombresSup)) {
			$nombres = array_diff($this->nombres,$nombresSup);
		}
		
		if (empty($consonnes)) {
			$consonnes = array('b');
		}
		if (empty($voyelles)) {
			$voyelles = $this->consonnes;
		}
		if (empty($nombres)) {
			$nombres = $this->consonnes;
		}
		
		if ($nbCaracteres == 0) {
			$caracteres = $this->consonnes;
		}
		$choix = array('0'=>$consonnes,'1'=>$voyelles,'2'=>$caracteres,'3'=>$nombres);
		$j = 0;
		for($i=0;$i<$longueur;$i++) {
			if (count($caracteresDejaChoisis) == $nbCaracteres) {
				$caracteres = $caracteresDejaChoisis;
			}
			//choix aléatoire entre consonnes et voyelles
			$rand = array_rand($choix,1);
			$tab = $choix[$rand];
			//on recherche l'index d'une lettre, au hasard dans le tableau choisi
			$lettre = array_rand($tab,1);
			if (in_array($lettre, $caracteresDejaChoisis)) {
				$lettre = array_rand($consonnes,1);
				$tab = $consonnes;
			}
			//On ajoute le caractère au tableau des caractères déjà choisis
			if ($tab == $caracteres) {
				$caracteresDejaChoisis[] = $lettre;
			}
			//on recherche la dernière lettre du mot généré
			if (strlen($mot) > 0) {
				$derniereLettre = $mot[strlen($mot)-1];
			} else {
				$derniereLettre = '';
			}
			
			//si la lettre choisie est déjà à la fin du mot généré, on relance la boucle
			if ($tab[$lettre] == $derniereLettre || in_array($derniereLettre,$tab)) {
				$i--;
			} else {//sinon on l'ajoute au mot généré
				$maj = mt_rand(0,10);
				if ($maj<2) {
					$mot .= strtoupper($tab[$lettre]);	
				} else {
					$mot .= $tab[$lettre];	
				}
			}
		}
		
		return $mot;
	}

	private function logm($message) {
	    $t = strval(date('Y/m/d_H:i:s')).' - '.$_SERVER["REMOTE_ADDR"].' - '.strval($message)."\n";
	    file_put_contents($this->DATABANDIR.'/log.txt',$t,FILE_APPEND);
	}
	public function notSpamCode() {
		if (is_file($this->IPBANS_FILENAME)) {include $this->IPBANS_FILENAME;}
		return (isset($this->ipbans['NOTSPAM'][$_SERVER['REMOTE_ADDR']]) && !empty($this->ipbans['NOTSPAM'][$_SERVER['REMOTE_ADDR']])) ? $this->ipbans['NOTSPAM'][$_SERVER['REMOTE_ADDR']]:false;
	}
	// Signal a failed login. Will ban the IP if too many failures:
	public function ban_loginFailed() {
	    $ip=$_SERVER["REMOTE_ADDR"]; 
	    $gb=$this->ipbans;
	    if (!isset($gb['FAILURES'][$ip])) {$gb['FAILURES'][$ip]=0;}
	    $gb['FAILURES'][$ip]++;
	    if ($gb['FAILURES'][$ip]>($this->BAN_AFTER-1))
	    {
	    	$notSpamCode = base64_encode($ip.time().SECURITY_SALT);
	        $gb['BANS'][$ip]=time()+$this->BAN_DURATION;
	    	if (!isset($gb['NOTSPAM'][$ip])) {$gb['NOTSPAM'][$ip]=$notSpamCode;}
	    	if (empty($gb['NOTSPAM'][$ip])) {$gb['NOTSPAM'][$ip]=$notSpamCode;}
	        $this->logm('IP address banned from login');
	    	file_put_contents($this->IPBANS_FILENAME, "<?php\n\$IPBANS=".var_export($gb,true).";\n?>");
	        echo BAN_DOCTYPE.MSG_COME_BACK_IN.'&nbsp;'.($this->BAN_DURATION/60).'&nbsp;'.MSG_MIN_OR_NOT;
			echo MSG_IF_NOT_SPAMMER.'<a href=index.php?notspam='.$notSpamCode.'>&nbsp;'.CLICK_HERE.'</a>'.BAN_END;
			exit();
	    }
	    file_put_contents($this->IPBANS_FILENAME, "<?php\n\$IPBANS=".var_export($gb,true).";\n?>");
	}

	// Signals a successful login. Resets failed login counter.
	public function ban_loginOk() {
	    $ip=$_SERVER["REMOTE_ADDR"]; 
	    $gb=$this->ipbans;
	    unset($gb['FAILURES'][$ip]); unset($gb['BANS'][$ip]);unset($gb['NOTSPAM'][$ip]);
	    file_put_contents($this->IPBANS_FILENAME, "<?php\n\$IPBANS=".var_export($gb,true).";\n?>");
	    $this->logm('Login ok.');
	}

	// Checks if the user CAN login. If 'true', the user can try to login.
	public function ban_canLogin() {
	    $ip=$_SERVER["REMOTE_ADDR"]; 
	    $gb=$this->ipbans;
	    if (isset($gb['BANS'][$ip]))
	    {
	        // User is banned. Check if the ban has expired:
	        if ($gb['BANS'][$ip]<=time())
	        { // Ban expired, user can try to login again.
	            $this->logm('Ban lifted.');
	            unset($gb['FAILURES'][$ip]); unset($gb['BANS'][$ip]);unset($gb['NOTSPAM'][$ip]);
	            file_put_contents($this->IPBANS_FILENAME, "<?php\n\$IPBANS=".var_export($gb,true).";\n?>");
	            return true; // Ban has expired, user can login.
	        }
	        return false; // User is banned.
	    }
	    return true; // User is not banned.
	}

	public function liftBan($ip) {
		$gb = $this->ipbans;
		if (isset($gb['BANS'][$ip]))
   		{
            $this->logm('Ban lifted.');
            unset($gb['FAILURES'][$ip]); unset($gb['BANS'][$ip]);unset($gb['NOTSPAM'][$ip]);
            file_put_contents($this->IPBANS_FILENAME, "<?php\n\$IPBANS=".var_export($gb,true).";\n?>");
            return true; // Ban has expired, user can login.
	   	}
	}
}
?>