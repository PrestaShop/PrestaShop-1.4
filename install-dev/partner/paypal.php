<?php

	if ($_GET['request'] == 'form')
	{

		if (@file_get_contents('http://www.prestashop.com/partner/preactivation-xml.php'))
		{
			$result = simplexml_load_file('https://www.prestashop.com/partner/preactivation/xml.php?version=1.0&partner=paypal&country_iso_code='.addslashes($_GET['country_iso_code']));
			if ($result)
			{
				$varList = "";
				echo '<div id="paypalform_div" style="width: 600px; height: 650px;"><img src="partner/paypal-fancybox.png" /><br /><br /><div id="paypalform_msg" style="padding-left: 50px;">';
				foreach ($result->field as $field)
				{
					echo '<div style="float: left; width: 150px; height: 35px; font-size: 0.8em;">'.$field->label.' : </div><div style="float: left; height: 35px;">';
					if ($field->type == 'text' || $field->type == 'password')
						echo '<input type="'.$field->type.'" class="text required" id="paypalform_'.$field->key.'" name="paypalform_'.$field->key.'" '.(isset($field->size) ? 'size="'.$field->size.'"' : '').' value="'.(isset($_GET[trim($field->key)]) ? $_GET[trim($field->key)] : $field->default).'" /><br />';
					elseif ($field->type == 'radio')
					{
						foreach ($field->values as $key => $value)
							echo $value->label.' <input type="radio" id="paypalform_'.$field->key.'_'.$key.'" name="paypalform_'.$field->key.'" value="'.$value->value.'" '.($value->value == $field->default ? 'checked="checked"' : '').' />';
						echo '<br />';
					}
					elseif ($field->type == 'select')
					{
						echo '<select id="paypalform_'.$field->key.'" name="paypalform_'.$field->key.'" style="width:175px;border:1px solid #D41958">';
						foreach ($field->values as $key => $value)
							echo '<option id="paypalform_'.$field->key.'_'.$key.'" value="'.$value->value.'" '.(trim($value->value) == trim($field->default) ? 'selected="selected"' : '').'>'.$value->label.'</option>';
						echo '</select><br />';
					}
					elseif ($field->type == 'date')
					{
						echo '<select id="paypalform_'.$field->key.'_year" name="paypalform_'.$field->key.'_year" style="border:1px solid #D41958">';
						for ($i = 81; (date('Y') - $i) < date('Y'); $i--)
							echo '<option value="'.(date('Y') - $i).'">'.(date('Y') - $i).'</option>';
						echo '</select>';
						echo '<select id="paypalform_'.$field->key.'_month" name="paypalform_'.$field->key.'_month" style="border:1px solid #D41958">';
						for ($i = 1; $i <= 12; $i++)
							echo '<option value="'.($i < 10 ? '0'.$i : $i).'">'.($i < 10 ? '0'.$i : $i).'</option>';
						echo '</select>';
						echo '<select id="paypalform_'.$field->key.'_day" name="paypalform_'.$field->key.'_day" style="border:1px solid #D41958">';
						for ($i = 1; $i <= 31; $i++)
							echo '<option value="'.($i < 10 ? '0'.$i : $i).'">'.($i < 10 ? '0'.$i : $i).'</option>';
						echo '</select>';
					}
					echo '</div><br clear="left" />';
					if ($field->type == 'date')
						$varList .= "'&".$field->key."='+$('#paypalform_".$field->key."_year').val()+'-'+$('#paypalform_".$field->key."_month').val()+'-'+$('#paypalform_".$field->key."_day').val()+\n";
					else
						$varList .= "'&".$field->key."='+$('#paypalform_".$field->key."').val()+\n";
				}
				echo '<p align="right"><input id="paypalform_button" class="button little" type="button" value="Next" style="font-size: 0.8em;" /></p>
				<div style="color: red; font-size: 0.8em;" id="paypalform_error"></div>
				</div>
				<script>'."
					$('#paypalform_button').click(function() {
						$.ajax({
						  url: 'partner/paypal.php?request=send'+
							".$varList."
							'&country_iso_code=".$_GET['country_iso_code']."',
						  context: document.body,
						  success: function(data) {
							data = data.split('|');
							if (data[0] == 'OK')
								$('#paypalform_div').html('<iframe src=\"' + data[1] + '\" style=\"width: 590px; height: 600px; border: 0px;\" border=\"0\"></iframe>');
							if (data[0] == 'KO')
								$('#paypalform_error').html(data[1]);
							if (data[0] == 'MSG')
								$('#paypalform_msg').html(data[1]);
						  }
						});
					});".'
				</script>
				</div>';
			}
		}

	}


	if ($_GET['request'] == 'send')
	{
		$url = 'https://www.prestashop.com/partner/preactivation/actions.php?version=1.0&partner=paypal';

		// Protect fields
		foreach ($_GET as $key => $value)
			$_GET[$key] = strip_tags(str_replace(array('\'', '"'), '', trim($value)));
		$_GET['state_iso_code'] = 'TX';

		// Get validation method for fields
		require_once('../../classes/Validate.php');
		$result = simplexml_load_file('https://www.prestashop.com/partner/preactivation/xml.php?version=1.0&partner=paypal&request=validate&country_iso_code='.addslashes($_GET['country_iso_code']));
		if (!$result)
		{
			echo 'KO|Could not connect with Prestashop.com';
			exit;
		}

		// Check errors
		$error = '';
		foreach ($result->field as $field)
			if (isset($field->required) && trim($field->required) == 'yes' && empty($error))
			{
				if (empty($_GET[trim($field->key)]))
					$error = 'Field "'.trim($field->label).'" is empty.';
				else if (isset($field->validate) && !call_user_func('ValidateCore::'.trim($field->validate), $_GET[trim($field->key)]))
					$error = 'Field "'.trim($field->label).'" is invalid.';
			}

		if (!empty($error))
		{
			echo 'KO|'.$error;
			exit;
		}


		// Encore Get, Send It and Get Answers
		foreach ($_GET as $key => $val)
			$url .= '&'.$key.'='.urlencode($val);
		$content = @file_get_contents($url);
		if ($content)
			echo $content;
		else
			echo 'KO|Could not connect with Prestashop.com';
	}

?>
