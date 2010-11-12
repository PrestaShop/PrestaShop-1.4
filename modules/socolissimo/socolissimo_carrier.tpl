<script type="text/javascript">
{literal}
	
	function change_action_form() {
			if ($('#id_carrier{/literal}{$id_carrier}{literal}').is(':not(:checked)'))
			{
				$('#form').attr("action", 'order.php');
			}
			else
			{
				$('#form').attr("action", '{/literal}{$urlSo}{literal}');
			}
		}

	$(document).ready(function() 
	{
		$('input[name=id_carrier]').change(function() {
			change_action_form();	
		});
		change_action_form();
	});
{/literal}
</script>
{foreach from=$inputs item=input key=name name=myLoop}
		<input type="hidden" name="{$name|escape:'htmlall':'UTF-8'}" value="{$input|strip_tags|addslashes}"/>
{/foreach}
