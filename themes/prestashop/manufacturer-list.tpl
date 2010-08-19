{capture name=path}{l s='Manufacturers'}{/capture}
{include file=$tpl_dir./breadcrumb.tpl}

<h2>{l s='Manufacturers'}</h2>

{if isset($errors) AND $errors}
	{include file=$tpl_dir./errors.tpl}
{else}
	<p>{strip}
		<span class="bold">
			{if $nbManufacturers == 0}{l s='There are no manufacturers.'}
			{else}
				{if $nbManufacturers == 1}{l s='There is'}{else}{l s='There are'}{/if}&#160;
				{$nbManufacturers}&#160;
				{if $nbManufacturers == 1}{l s='manufacturer.'}{else}{l s='manufacturers.'}{/if}
			{/if}
		</span>{/strip}
	</p>

	{if $nbManufacturers > 0}
		<ul id="manufacturers_list">
		{foreach from=$manufacturers item=manufacturer name=manufacturers}
			<li class="{if $smarty.foreach.manufacturers.first}first_item{elseif $smarty.foreach.manufacturers.last}last_item{else}item{/if}"> 
				<div class="left_side">
					<!-- logo -->
					<div class="logo">
					{if $manufacturer.nb_products > 0}<a href="{$link->getmanufacturerLink($manufacturer.id_manufacturer, $manufacturer.link_rewrite)|escape:'htmlall':'UTF-8'}" title="{$manufacturer.name|escape:'htmlall':'UTF-8'}">{/if}
						<img src="{$img_manu_dir}{$manufacturer.image|escape:'htmlall':'UTF-8'}-medium.jpg" alt="" width="{$mediumSize.width}" height="{$mediumSize.height}" />
					{if $manufacturer.nb_products > 0}</a>{/if}
					</div>
					<!-- name -->
					<h3>
						{if $manufacturer.nb_products > 0}<a href="{$link->getmanufacturerLink($manufacturer.id_manufacturer, $manufacturer.link_rewrite)|escape:'htmlall':'UTF-8'}">{/if}
						{$manufacturer.name|truncate:60:'...'|escape:'htmlall':'UTF-8'}
						{if $manufacturer.nb_products > 0}</a>{/if}
					</h3>
					<p class="description rte">
					{if $manufacturer.nb_products > 0}<a href="{$link->getmanufacturerLink($manufacturer.id_manufacturer, $manufacturer.link_rewrite)|escape:'htmlall':'UTF-8'}">{/if}
					{$manufacturer.description}
					{if $manufacturer.nb_products > 0}</a>{/if}
					</p>
				</div>

				<div class="right_side">
				{if $manufacturer.nb_products > 0}<a href="{$link->getmanufacturerLink($manufacturer.id_manufacturer, $manufacturer.link_rewrite)|escape:'htmlall':'UTF-8'}">{/if}
					<span>{$manufacturer.nb_products|intval} {if $manufacturer.nb_products == 1}{l s='product'}{else}{l s='products'}{/if}</span>
				{if $manufacturer.nb_products > 0}</a>{/if}

				{if $manufacturer.nb_products > 0}
					<a class="button" href="{$link->getmanufacturerLink($manufacturer.id_manufacturer, $manufacturer.link_rewrite)|escape:'htmlall':'UTF-8'}">{l s='view products'}</a>
				{/if}
				</div>
				<br class="clear"/>
			</li>
		{/foreach}
		</ul>
		{include file=$tpl_dir./pagination.tpl}
	{/if}
{/if}
