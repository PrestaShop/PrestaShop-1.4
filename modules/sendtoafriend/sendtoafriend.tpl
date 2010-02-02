{capture name=path}{l s='Send to a friend' mod='sendtoafriend'}{/capture}
{include file=$tpl_dir./breadcrumb.tpl}

<h2>{l s='Send to a friend' mod='sendtoafriend'}</h2>

<p class="bold">{l s='Send this page to a friend who might be interested in the item below.' mod='sendtoafriend'}.</p>
{include file=$tpl_dir./errors.tpl}

{if $confirm}
	<p class="success">{$confirm}</p>
{else}
	<form method="post" action="{$request_uri}" class="std">
		<fieldset>
			<h3>{l s='Send a message' mod='sendtoafriend'}</h3>
		
			<p class="align_center">
				<a href="{$productLink}"><img src="{$link->getImageLink($product->link_rewrite, $cover.id_image, 'small')}" alt="" title="{$cover.legend}" /></a><br/>
				<a href="{$productLink}">{$product->name}</a>
			</p>
			
			<p>
				<label for="friend-name">{l s='Friend\'s name:' mod='sendtoafriend'}</label>
				<input type="text" id="friend-name" name="name" value="{if isset($smarty.post.name)}{$smarty.post.name|escape:'htmlall'|stripslashes}{/if}" />
			</p>
			<p>
				<label for="friend-address">{l s='Friend\'s email:' mod='sendtoafriend'}</label>
				<input type="text" id="friend-address" name="email" value="{if isset($smarty.post.name)}{$smarty.post.email|escape:'htmlall'|stripslashes}{/if}" />
			</p>
			
			<p class="submit">
				<input type="submit" name="submitAddtoafriend" value="{l s='send' mod='sendtoafriend'}" class="button" />
			</p>
		</fieldset>
	</form>
{/if}

<ul class="footer_links">
	<li><a href="{$productLink}" class="button_large">{l s='Back to' mod='sendtoafriend'} {$product->name}</a></li>
</ul>
