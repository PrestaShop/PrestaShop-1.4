<script type="text/javascript">
{literal}
	$(document).ready(function(){ 
{/literal}
{foreach from=$ids item=id}
	{literal}$($('#id_carrier{/literal}{$id}{literal}').parent().parent()).remove();{/literal}
{/foreach}
{literal}
	});
{/literal}
</script>
