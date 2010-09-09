{if $display_widget}
<div style="text-align: center">
	<a target="_blank" href="https://www.trustedshops.com/buyerrating/info_{$ts_id}.html" title="See customer ratings of {$shop_name}"><img alt="Customer ratings of {$shop_name}" border="0"  src="{$filename}"/></a>
</div>
<br />
{/if}
	
{if $display_rating_link}
<div style="text-align: center">
	<a target="_blank" href="{$rating_url}" title="Rate this shop"><img alt="Rate this shop" border="0" src="{$module_dir}/img/apply_{$language}.gif" /></a>
</div>
<br />
{/if}