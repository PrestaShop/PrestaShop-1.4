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
		
		if (Tools::isSubmit('submitCCC'))
		{
			if (
				!Configuration::updateValue('PS_CSS_THEME_CACHE', (int)Tools::getValue('PS_CSS_THEME_CACHE')) OR
				!Configuration::updateValue('PS_JS_THEME_CACHE', (int)Tools::getValue('PS_JS_THEME_CACHE')) OR
				!Configuration::updateValue('PS_HTML_THEME_COMPRESSION', (int)Tools::getValue('PS_HTML_THEME_COMPRESSION')) OR
				!Configuration::updateValue('PS_JS_HTML_THEME_COMPRESSION', (int)Tools::getValue('PS_JS_HTML_THEME_COMPRESSION')) OR
				!Configuration::updateValue('PS_HIGH_HTML_THEME_COMPRESSION', (int)Tools::getValue('PS_HIGH_HTML_THEME_COMPRESSION'))
			)
				$this->_errors[] = Tools::displayError('Unknown error.');
			else
				Tools::redirectAdmin($currentIndex.'&token='.Tools::getValue('token').'&conf=4');
		}
		
		return parent::postProcess();
	}

	public function display()
	{
		global $currentIndex;
		
		echo '<form action="'.$currentIndex.'&token='.Tools::getValue('token').'" method="post">
			<fieldset><legend><img src="../img/admin/computer_key.png" /> '.$this->l('Ciphering').'</legend>
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
		
		echo '<form action="'.$currentIndex.'&token='.Tools::getValue('token').'" method="post" style="margin-top:10px;">
			<fieldset><legend><img src="../img/admin/arrow_in.png" /> '.$this->l('CCC (Combine, Compress and Cache)').'</legend>
				<p>'.$this->l('CCC allows you to reduce the loading time of your page, browser-side. With these settings you will gain performance without touching the code of your theme. Caution, however, that your theme is compatible PrestaShop 1.4+, CCC otherwise cause problems.').'</p>
				<label>'.$this->l('Smart cache for CSS').' </label>
				<div class="margin-form">
					<input type="radio" value="1" name="PS_CSS_THEME_CACHE" id="PS_CSS_THEME_CACHE_1" '.(Configuration::get('PS_CSS_THEME_CACHE') ? 'checked="checked"' : '').' />
					<label class="t" for="PS_CSS_THEME_CACHE_1">'.$this->l('Use CCC for CSS.').'</label>
					<br />
					<input type="radio" value="0" name="PS_CSS_THEME_CACHE" id="PS_CSS_THEME_CACHE_0" '.(Configuration::get('PS_CSS_THEME_CACHE') ? '' : 'checked="checked"').' />
					<label class="t" for="PS_CSS_THEME_CACHE_0">'.$this->l('Keep CSS as original').'</label>
				</div>
				
				<label>'.$this->l('Smart cache for JavaScript').' </label>
				<div class="margin-form">
					<input type="radio" value="1" name="PS_JS_THEME_CACHE" id="PS_JS_THEME_CACHE_1" '.(Configuration::get('PS_JS_THEME_CACHE') ? 'checked="checked"' : '').' />
					<label class="t" for="PS_JS_THEME_CACHE_1">'.$this->l('Use CCC for JavaScript.').'</label>
					<br />
					<input type="radio" value="0" name="PS_JS_THEME_CACHE" id="PS_JS_THEME_CACHE_0" '.(Configuration::get('PS_JS_THEME_CACHE') ? '' : 'checked="checked"').' />
					<label class="t" for="PS_JS_THEME_CACHE_0">'.$this->l('Keep JavaScript as original').'</label>
				</div>
				
				<label>'.$this->l('Minify HTML').' </label>
				<div class="margin-form">
					<input type="radio" value="1" name="PS_HTML_THEME_COMPRESSION" id="PS_HTML_THEME_COMPRESSION_1" '.(Configuration::get('PS_HTML_THEME_COMPRESSION') ? 'checked="checked"' : '').' />
					<label class="t" for="PS_HTML_THEME_COMPRESSION_1">'.$this->l('Minify HTML after "smarty compile" execution.').'</label>
					<br />
					<input type="radio" value="0" name="PS_HTML_THEME_COMPRESSION" id="PS_HTML_THEME_COMPRESSION_0" '.(Configuration::get('PS_HTML_THEME_COMPRESSION') ? '' : 'checked="checked"').' />
					<label class="t" for="PS_HTML_THEME_COMPRESSION_0">'.$this->l('Keep HTML as original').'</label>
				</div>
				
				<label>'.$this->l('Compress inline JavaScript in HTML').' </label>
				<div class="margin-form">
					<input type="radio" value="1" name="PS_JS_HTML_THEME_COMPRESSION" id="PS_JS_HTML_THEME_COMPRESSION_1" '.(Configuration::get('PS_JS_HTML_THEME_COMPRESSION') ? 'checked="checked"' : '').' />
					<label class="t" for="PS_JS_HTML_THEME_COMPRESSION_1">'.$this->l('Compress inline JavaScript in HTML after "smarty compile" execution').'</label>
					<br />
					<input type="radio" value="0" name="PS_JS_HTML_THEME_COMPRESSION" id="PS_JS_HTML_THEME_COMPRESSION_0" '.(Configuration::get('PS_JS_HTML_THEME_COMPRESSION') ? '' : 'checked="checked"').' />
					<label class="t" for="PS_JS_HTML_THEME_COMPRESSION_0">'.$this->l('Keep inline JavaScript in HTML as original').'</label>
				</div>
				
				<label>'.$this->l('High and dangerous HTML compression').' </label>
				<div class="margin-form">
					<input type="radio" value="1" name="PS_HIGH_HTML_THEME_COMPRESSION" id="PS_HIGH_HTML_THEME_COMPRESSION_1" '.(Configuration::get('PS_HIGH_HTML_THEME_COMPRESSION') ? 'checked="checked"' : '').' />
					<label class="t" for="PS_HIGH_HTML_THEME_COMPRESSION_1">'.$this->l('HTML compress up but cancels the W3C validation (only when "Minify HTML" is enabled)').'</label>
					<br />
					<input type="radio" value="0" name="PS_HIGH_HTML_THEME_COMPRESSION" id="PS_HIGH_HTML_THEME_COMPRESSION_0" '.(Configuration::get('PS_HIGH_HTML_THEME_COMPRESSION') ? '' : 'checked="checked"').' />
					<label class="t" for="PS_HIGH_HTML_THEME_COMPRESSION_0">'.$this->l('Keep W3C validation').'</label>
				</div>
				
				<div class="margin-form">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitCCC" class="button" />
				</div>
			</fieldset>
		</form>';
	}
}

?>
