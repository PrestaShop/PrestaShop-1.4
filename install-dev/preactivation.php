<?php

	if (!isset($_GET['language']))
		$_GET['language'] = 0;
	function getPreinstallXmlLang($object, $field)
	{
		if (property_exists($object, $field.'_'.((int)($_GET['language'])+1)))
			return trim($object->{$field.'_'.((int)($_GET['language'])+1)});
		if (property_exists($object, $field.'_1'))
			return trim($object->{$field.'_1'});
		return '';
	}



	if ($_GET['request'] == 'form')
	{
		$context = stream_context_create(array('http' => array('method'=>"GET", 'timeout' => 5)));
		$content = @file_get_contents('https://www.prestashop.com/partner/preactivation/fields.php?version=1.0&partner='.addslashes($_GET['partner']).'&country_iso_code='.addslashes($_GET['country_iso_code']), false, $context);
		if ($content && $content[0] == '<')
		{
			$result = simplexml_load_string($content);
			if ($result)
			{
				$varList = "";
				echo '<br />';
				foreach ($result->field as $field)
				{
					echo '<div class="field"><label class="aligned">'.getPreinstallXmlLang($field, 'label').' :</label>';
					if ($field->type == 'text' || $field->type == 'password')
						echo '<input type="'.$field->type.'" class="text required" id="paypalform_'.$field->key.'" name="paypalform_'.$field->key.'" '.(isset($field->size) ? 'size="'.$field->size.'"' : '').' value="'.(isset($_GET[trim($field->key)]) ? $_GET[trim($field->key)] : $field->default).'" /><br />';
					elseif ($field->type == 'radio')
					{
						foreach ($field->values as $key => $value)
							echo getPreinstallXmlLang($value, 'label').' <input type="radio" id="paypalform_'.$field->key.'_'.$key.'" name="paypalform_'.$field->key.'" value="'.$value->value.'" '.($value->value == $field->default ? 'checked="checked"' : '').' />';
						echo '<br />';
					}
					elseif ($field->type == 'select')
					{
						echo '<select id="paypalform_'.$field->key.'" name="paypalform_'.$field->key.'" style="width:175px;border:1px solid #D41958">';
						foreach ($field->values as $key => $value)
							echo '<option id="paypalform_'.$field->key.'_'.$key.'" value="'.$value->value.'" '.(trim($value->value) == trim($field->default) ? 'selected="selected"' : '').'>'.getPreinstallXmlLang($value, 'label').'</option>';
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
				echo '
				<script>'."
					$('#btNext').click(function() {
						$.ajax({
						  url: 'preactivation.php?request=send'+
							".$varList."
							'&language_iso_code='+isoCodeLocalLanguage+
							'&country_iso_code='+encodeURIComponent($('select#infosCountry option:selected').attr('rel'))+
							'&activity='+ encodeURIComponent($('select#infosActivity').val())+
							'&timezone='+ encodeURIComponent($('select#infosTimezone').val())+
							'&shop='+ encodeURIComponent($('input#infosShop').val())+
							'&firstName='+ encodeURIComponent($('input#infosFirstname').val())+
							'&lastName='+ encodeURIComponent($('input#infosName').val())+
							'&email='+ encodeURIComponent($('input#infosEmail').val()),
						  context: document.body,
						  success: function(data) {
						  }
						});
					});".'
				</script>';
			}
		}

	}


	if ($_GET['request'] == 'send')
	{
		$url = 'https://www.prestashop.com/partner/preactivation/actions.php?version=1.0&partner=paypal';

		// Protect fields
		foreach ($_GET as $key => $value)
			$_GET[$key] = strip_tags(str_replace(array('\'', '"'), '', trim($value)));

		// Get validation method for fields
		require_once('../classes/Validate.php');
		$result = simplexml_load_file('https://www.prestashop.com/partner/preactivation/fields.php?version=1.0&partner=paypal&request=validate&country_iso_code='.addslashes($_GET['country_iso_code']));
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
					$error = 'Field "'.trim(getPreinstallXmlLang($field, 'label')).'" is empty.';
				else if (isset($field->validate) && !call_user_func('ValidateCore::'.trim($field->validate), $_GET[trim($field->key)]))
					$error = 'Field "'.trim(getPreinstallXmlLang($field, 'label')).'" is invalid.';
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
