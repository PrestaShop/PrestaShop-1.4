<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title>PrestaShop&trade; - {l s='Affiliation' mod='trackingfront'}</title>
		<link type="text/css" rel="stylesheet" href="{$base_dir}tools/datepicker/ui.datepicker.css" />
		<link type="text/css" rel="stylesheet" href="{$base_dir}tools/datepicker/ui.datepicker.granularity.css" />
		<script type="text/javascript" src="{$base_dir}js/jquery/jquery-1.2.6.pack.js"></script>
		<script type="text/javascript" src="{$base_dir}js/jquery/jquery.easing.1.3.js"></script>
		<script type="text/javascript" src="{$base_dir}tools/datepicker/ui.datepicker.js"></script>
		<script type="text/javascript" src="{$base_dir}tools/datepicker/ui.datepicker.granularity.js"></script>
	</head>
	<body>
		<div style="width: 800px; height: 75px; background-color: #8AB50E; margin: 0 auto; color: white; font-family: arial; border-bottom: 5px solid #567500;">
			<div style="float: left; font-size: 36px;  margin-left: 20px; font-weight: bold; height: 75px; line-height: 75px; vertical-align: middle;">{l s='Affiliation space' mod='trackingfront'}</div>
			<div style="float: right; font-size: 18px; margin-right: 20px; font-weight: bold; height: 75px; line-height: 75px; vertical-align: middle;"><a href="{$php_self|escape:'htmlall':'UTF-8'}?logout_tracking">{l s='Logout' mod='trackingfront'}</a></div>
		</div>
		<div style="width: 800px; height: 250px; background-color: #EEEEEE; margin: 0 auto; font-family: arial;">
			<div style="float:left; margin: 25px 0 0 50px;">
				<script type="text/javascript">
				{literal}
					jQuery(document).ready(function() {
						$('#date').datepicker({
				{/literal}
							dateInputDay:'#dateInputDay',
							dateInputMonth:'#dateInputMonth',
							dateInputYear:'#dateInputYear',
							dateInputGranularity:'#dateInputGranularity',
							defaultDate:new Date("{$stats_year}", "{$stats_month}", "{$stats_day}"),
							granularity:"{$stats_granularity}",
							prevText:"&#x3c;&#x3c;",
							nextText:"&#x3e;&#x3e;",
				{literal}
							onSelect: function(date) {$("#dateInput").attr("value", date);}
						});
					});
				{/literal}
				</script>
				<div id="date"></div>
				<form action="{$php_self|escape:'htmlall':'UTF-8'}" method="post">
					<input style="width: 100px" id="dateInput" name="dateInput" type="text" readonly="readonly" value="" />
					<input type="hidden" id="dateInputDay" name="dateInputDay" value="{$stats_day}" />
					<input type="hidden" id="dateInputMonth" name="dateInputMonth" value="{$stats_month}" />
					<input type="hidden" id="dateInputYear" name="dateInputYear" value="{$stats_year}" />
					<input type="hidden" id="dateInputGranularity" name="dateInputGranularity" value="{$stats_granularity}" />
					<input type="submit" name="submitTrackingRange" class="button" value="OK" />
				</form>
			</div>
			<div style="float:right; margin: 25px 50px 0 0;">
				<fieldset style="width: 415px; padding: 20px; font-size: 12px; border: 1px solid #88B60E;"><legend style="color: #88B60E; font-size:20px; font-weight: bold;">{$referrer->name}</legend>
					{foreach from=$displayTab key=data item=label}
							<div style="float:left; width: 150px; height: 25px; margin-right: 40px;"><span style="float:left">{$label}</span><span id="{$data}" style="float: right; font-weight: bold;"></span></div>
					{/foreach}			
				</fieldset>
			</div>
		</div>
		<table style="width: 800px; margin: 0 auto; font-family: arial; font-size: 10px; border-bottom: 5px solid #8AB50E;" cellspacing="0" cellpadding="2">
			<tr style="background-color: #8AB50E; font-weight: bold; color: white; font-size: 12px;">
				<th>{l s='ID' mod='trackingfront'}</th>
				<th>{l s='Name' mod='trackingfront'}</th>
				<th>{l s='Visitors' mod='trackingfront'}</th>
				<th>{l s='Visits' mod='trackingfront'}</th>
				<th>{l s='Pages' mod='trackingfront'}</th>
				<th>{l s='Reg.' mod='trackingfront'}</th>
				<th>{l s='Orders' mod='trackingfront'}</th>
				<th>{l s='Sales' mod='trackingfront'}</th>
				<th>{l s='Reg. rate' mod='trackingfront'}</th>
				<th>{l s='Order rate' mod='trackingfront'}</th>
				<th>{l s='Base fee' mod='trackingfront'}</th>
				<th>{l s='Percent fee' mod='trackingfront'}</th>
			</tr>
			<tr id="trid_dummy"><td colspan="12" style="background: #567500;" /></tr>
		</table>
	</body>
</html>