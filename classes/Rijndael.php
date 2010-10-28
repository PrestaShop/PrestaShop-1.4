<?php

class RijndaelCore
{
	private $_key;
	private $_iv;
	
	public function __construct($key, $iv)
	{
		$this->_key = $key;
		$this->_iv = base64_decode($iv);
	}
	
	// Base64 is not required, but it is be more compact than urlencode
	public function encrypt($plaintext)
	{
		return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->_key, $plaintext, MCRYPT_MODE_ECB, $this->_iv));
	}

	public function decrypt($ciphertext)
	{
		return mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->_key, base64_decode($ciphertext), MCRYPT_MODE_ECB, $this->_iv);
	}
}

?>