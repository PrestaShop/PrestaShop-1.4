<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<link type="text/css" rel="stylesheet" href="{$content_dir}css/login.css" />
		<title>PrestaShop&trade; - {l s='Affiliation' mod='trackingfront'}</title>
	</head>
	<body>
		{include file=$tpl_dir./errors.tpl}
		<div style="width: 400px; height: 300px; background-color: #8AB50E; margin: 0 auto; color: white; font-family: arial; ">
			<div style="width: 400px; height: 65px; background-color: #567500; margin: 0 auto; color: white; font-family: arial; text-align: center">
				<div style="font-size: 36px;  margin: 5px auto; font-weight: bold; height: 65x; line-height: 65px; vertical-align: middle;">{l s='Affiliation space' mod='trackingfront'}</div>
			</div>
			<form action="{$request_uri|escape:'htmlall':'UTF-8'}" method="post" style="margin: 20px 50px; text-align: left; font-weight: bold;">
				<label>{l s='Login' mod='trackingfront'}</label><br />
				<input type="text" id="email" name="login" value="{$smarty.post.login|escape:'htmlall'|stripslashes}" class="input" />
				<div style="margin: 2.0em 0 0 0;">
					<label>{l s='Password' mod='trackingfront'}</label><br />
					<input type="password" name="passwd" class="input" />
				</div>
				<div style="margin: 2.0em 0 0 0; text-align :right">
					<div id="submit"><input type="submit" name="submitLoginTracking" value="{l s='Log in' mod='trackingfront'}" class="button" style="border: none; background-color: #567500; color: white; font-weight: bold; width: 90px; height: 30px;" /></div>
				</div>
			</form>
		</div>
		<script type="text/javascript">
			if (document.getElementById('email'))
				document.getElementById('email').focus();
		</script>
	</body>
</html>