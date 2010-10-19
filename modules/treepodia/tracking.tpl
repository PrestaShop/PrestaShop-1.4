<script type="text/javascript">
<!-- 
{literal}function initTreepodia() {{/literal}
    {foreach from=$products item=product}
		{section name=cpt start=0 loop=$product.product_quantity step=1}
    {literal}Treepodia.getProduct('{/literal}{$account_id}{literal}', '{/literal}{$product.product_id}{literal}').logAddToCart();{/literal}
		{/section}
    {/foreach}
{literal}}{/literal}
// -->
</script>
<script type="text/javascript">
<!-- 
    {literal}document.write(unescape("%3Cscript src='" + ((document.location.protocol == 'https:') ? 'https://' : 'http://') + "api.treepodia.com/video/Treepodia.js' type='text/javascript'%3E%3C/script%3E"));{/literal}
// -->
</script>
