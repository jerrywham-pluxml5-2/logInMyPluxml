<?php if(!defined('PLX_ROOT')) exit; 
/**
 * Plugin logInMyPluxml
 *
 * @package	PLX
 * @version	1.3
 * @date	19/01/2014
 * @author	Cyril MAGUIRE
 **/
# Control du token du formulaire
plxToken::validateFormToken($_POST);

if(!empty($_POST)) {
	$plxPlugin->setParam('mnuDisplay', $_POST['mnuDisplay'], 'numeric');
	$plxPlugin->setParam('mnuName', $_POST['mnuName'], 'string');
	$plxPlugin->setParam('mnuPos', $_POST['mnuPos'], 'numeric');
	$plxPlugin->setParam('timeout', $_POST['timeout'], 'numeric');
	$plxPlugin->saveParams();
	header('Location: parametres_plugin.php?p=logInMyPluxml');
	exit;
}
$mnuDisplay =  $plxPlugin->getParam('mnuDisplay')=='' ? 1 : $plxPlugin->getParam('mnuDisplay');
$mnuName =  $plxPlugin->getParam('mnuName')=='' ? $plxPlugin->getLang('L_DEFAULT_MENU_NAME') : $plxPlugin->getParam('mnuName');
$mnuPos =  $plxPlugin->getParam('mnuPos')=='' ? 2 : $plxPlugin->getParam('mnuPos');
$timeout =  $plxPlugin->getParam('timeout')=='' ? 60 : $plxPlugin->getParam('timeout');

?>

<h2><?php echo $plxPlugin->getInfo('title') ?></h2>

<form id="form" action="parametres_plugin.php?p=logInMyPluxml" method="post">
	<fieldset>
		<p class="field"><label for="id_mnuDisplay"><?php echo $plxPlugin->lang('L_MENU_DISPLAY') ?>&nbsp;:</label></p>
		<?php plxUtils::printSelect('mnuDisplay',array('1'=>L_YES,'0'=>L_NO),$mnuDisplay); ?>
		<p class="field"><label for="id_mnuName"><?php $plxPlugin->lang('L_MENU_TITLE') ?>&nbsp;:</label></p>
		<?php plxUtils::printInput('mnuName',$mnuName,'text','20-20') ?>
		<p class="field"><label for="id_mnuPos"><?php $plxPlugin->lang('L_MENU_POS') ?>&nbsp;:</label></p>
		<?php plxUtils::printInput('mnuPos',$mnuPos,'text','2-5') ?>
		<p class="field"><label for="id_timeout"><?php $plxPlugin->lang('L_TIMEOUT') ?>&nbsp;:</label></p>
		<?php plxUtils::printInput('timeout',$timeout,'text','2-5') ?>
		<p>
			<?php echo plxToken::getTokenPostMethod() ?>
			<input type="submit" name="submit" value="<?php $plxPlugin->lang('L_SAVE') ?>" />
		</p>
	</fieldset>
</form>
