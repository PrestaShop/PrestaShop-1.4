<?php

class AdminPerformance extends AdminTab
{
	public function postProcess()
	{
		global $currentIndex;

		if (Tools::isSubmit('submitCiphering') AND Configuration::get('PS_CIPHER_ALGORITHM') != (int)Tools::getValue('PS_CIPHER_ALGORITHM'))
		{
			$algo = (int)Tools::getValue('PS_CIPHER_ALGORITHM');
			$settings = file_get_contents(dirname(__FILE__).'/../../config/settings.inc.php');
			if ($algo)
			{
				if (!function_exists('mcrypt_encrypt'))
					$this->_errors[] = Tools::displayError('mcrypt is not activated on this server');
				else
				{
					if (!strstr($settings, '_RIJNDAEL_KEY_'))
					{
						$key_size = mcrypt_get_key_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
						$key = Tools::passwdGen($key_size);
						$settings = preg_replace('/define\(\'_COOKIE_KEY_\', \'([a-z0-9=\/+-_]+)\'\);/i', 'define(\'_COOKIE_KEY_\', \'\1\');'."\n".'define(\'_RIJNDAEL_KEY_\', \''.$key.'\');', $settings);
					}
					if (!strstr($settings, '_RIJNDAEL_IV_'))
					{
						$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
						$iv = base64_encode(mcrypt_create_iv($iv_size, MCRYPT_RAND));
						$settings = preg_replace('/define\(\'_COOKIE_IV_\', \'([a-z0-9=\/+-_]+)\'\);/i', 'define(\'_COOKIE_IV_\', \'\1\');'."\n".'define(\'_RIJNDAEL_IV_\', \''.$iv.'\');', $settings);
					}
				}
			}
			if (!count($this->_errors))
			{
				if (file_put_contents(dirname(__FILE__).'/../../config/settings.inc.php', $settings))
				{
					Configuration::updateValue('PS_CIPHER_ALGORITHM', $algo);
					Tools::redirectAdmin($currentIndex.'&token='.Tools::getValue('token').'&conf=4');
				}
				else
					$this->_errors[] = Tools::displayError('Cannot overwrite settings file');
			}
		}
		
		return parent::postProcess();
	}

	public function display()
	{
		global $currentIndex;
		
		echo '<form action="'.$currentIndex.'&token='.Tools::getValue('token').'" method="post">
			<fieldset class="width3"><legend><img src="../img/admin/computer_key.png" /> '.$this->l('Ciphering').'</legend>
				<p>'.$this->l('Mcrypt is faster than our custom BlowFish class, but require PHP extension "mcrypt". If you change this configuration, every cookies will be reset.').'</p>
				<label>'.$this->l('Algorithm').' </label>
				<div class="margin-form">
					<input type="radio" value="1" name="PS_CIPHER_ALGORITHM" id="PS_CIPHER_ALGORITHM_1" '.(Configuration::get('PS_CIPHER_ALGORITHM') ? 'checked="checked"' : '').' />
					<label class="t" for="PS_CIPHER_ALGORITHM_1">'.$this->l('Use Rijndael with mcrypt lib.').'</label>
					<br />
					<input type="radio" value="0" name="PS_CIPHER_ALGORITHM" id="PS_CIPHER_ALGORITHM_0" '.(Configuration::get('PS_CIPHER_ALGORITHM') ? '' : 'checked="checked"').' />
					<label class="t" for="PS_CIPHER_ALGORITHM_0">'.$this->l('Keep the custom BlowFish class.').'</label>
				</div>
				<div class="margin-form">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitCiphering" class="button" />
				</div>
			</fieldset>
		</form>';
	}
}

?>
