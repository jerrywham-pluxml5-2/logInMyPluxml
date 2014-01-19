<?php 
/**
 * Plugin logInMyPluxml
 *
 * @package	PLX
 * @version	1.3
 * @date	19/01/2014
 * @author	Cyril MAGUIRE
 **/
class logInMyPluxml extends plxPlugin {

	/**
	 * Constructeur de la classe logInMyPluxml
	 *
	 * @param	default_lang	langue par défaut utilisée par PluXml
	 * @return	null
	 * @author	Stephane F
	 **/
	public function __construct($default_lang) {

		# Appel du constructeur de la classe plxPlugin (obligatoire)
		parent::__construct($default_lang);

		# droits pour accèder à la page config.php du plugin
		$this->setConfigProfil(PROFIL_ADMIN);

		# Déclarations des hooks		
		$this->addHook('Index', 'Index');
		$this->addHook('plxShowStaticListEnd', 'plxShowStaticListEnd');
	}
	/**
	 * Méthode de traitement de la connexion
	 *
	 * @return	stdio
	 * @author	Cyril MAGUIRE
	 **/
	public function Index() {
		$string = '
			if ((isset($_SESSION[\'timeout\']) && ($_SESSION[\'timeout\'])<time()) OR ($plxMotor->get && preg_match(\'/^logout\/?/\',$plxMotor->get))) {
				$plxMotor->mode=\'loginRequest\';
				if(isset($_SESSION[\'user\']) ) {
					unset($_SESSION[\'user\']);
				}
				$_SESSION = array();
				session_destroy();
				if (defined(\'PLX_LOGINPAGE\')) {
					PLX_LOGINPAGE === false;
				}
				header(\'Location:\'.plxUtils::getRacine());
			}
			$session_domain = dirname(__FILE__);
			# Test sur le domaine et sur l\'identification pour le mode preview
			if((isset($_SESSION[\'domain\']) AND $_SESSION[\'domain\']==$session_domain.\'/core/admin\') AND isset($_SESSION[\'preview\']) AND !empty($_SESSION[\'preview\']) AND (isset($_SESSION[\'user\']) AND $_SESSION[\'user\']!=\'\') AND $plxMotor->get && preg_match(\'/^preview\/?/\',$plxMotor->get)){
				$plxMotor->mode==\'login\';
			} else {
				if(!defined(\'PLX_LOGINPAGE\') OR PLX_LOGINPAGE !== true){ # si on est pas sur la page de login
					# Test sur le domaine et sur l\'identification
					if((isset($_SESSION[\'domain\']) AND $_SESSION[\'domain\']!=$session_domain) OR (!isset($_SESSION[\'user\']) OR $_SESSION[\'user\']==\'\')){
						$plxMotor->mode==\'loginRequest\';
						# Chargement des fichiers de langue en fonction du profil de l\'utilisateur connecté
						$lang = isset($_SESSION[\'lang\']) ? $_SESSION[\'lang\'] : $plxMotor->aConf[\'default_lang\'];
						loadLang(PLX_CORE.\'lang/\'.$lang.\'/admin.php\');
						define(\'L_LOGIN_PUBLIC_PAGE\',\'Connexion\');

						include_once(PLX_CORE.\'lib/class.plx.token.php\');
						include_once(PLX_ROOT.\'plugins/logInMyPluxml/form.login.php\');
						exit;
					}else {
						$_SESSION[\'timeout\'] = '.($this->getParam('timeout') == 0 ? 'time()+(60*60*24*365)' : 'time()+(60*'.$this->getParam('timeout').')').';
						$plxMotor->mode==\'login\';
					}
				} 
			}
		';
		echo "<?php ".$string."?>";
	}
	/**
	 * Méthode de traitement du hook plxShowStaticListEnd
	 *
	 * @return	stdio
	 * @author	Stephane F
	 **/
	public function plxShowStaticListEnd() {

		# ajout du menu pour accèder à la page de contact
		if($this->getParam('mnuDisplay')) {
			echo "<?php \$class = \$this->plxMotor->mode=='login'?'active':'noactive'; ?>";
			echo "<?php array_splice(\$menus, ".($this->getParam('mnuPos')-1).", 0, '<li><a class=\"static '.\$class.'\" href=\"'.\$this->plxMotor->urlRewrite('?logout').'\" title=\"".$this->getParam('mnuName')."\">".$this->getParam('mnuName')."</a></li>'); ?>";
		}
	}
}
 ?>