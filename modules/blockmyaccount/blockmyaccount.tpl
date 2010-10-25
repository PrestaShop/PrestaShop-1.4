<!-- Block myaccount module -->
<div class="block myaccount">
	<h4><a href="{$link->getPageLink('my-account.php', true)}">{l s='My account' mod='blockmyaccount'}</a></h4>
	<div class="block_content">
		<ul class="bullet">
			<li><a href="{$link->getPageLink('history.php', true)}" title="">{l s='My orders' mod='blockmyaccount'}</a></li>
			{if $returnAllowed}
			<li><a href="{$link->getPageLink('order-follow.php', true)}" title="">{l s='My merchandise returns' mod='blockmyaccount'}</a></li>
			{/if}
			<li><a href="{$link->getPageLink('order-slip.php', true)}" title="">{l s='My credit slips' mod='blockmyaccount'}</a></li>
			<li><a href="{$link->getPageLink('addresses.php', true)}" title="">{l s='My addresses' mod='blockmyaccount'}</a></li>
			<li><a href="{$link->getPageLink('identity.php', true)}" title="">{l s='My personal info' mod='blockmyaccount'}</a></li>
			{if $voucherAllowed}
			<li><a href="{$link->getPageLink('discount.php', true)}" title="">{l s='My vouchers' mod='blockmyaccount'}</a></li>
			{/if}
			{$HOOK_BLOCK_MY_ACCOUNT}
		</ul>
		<p class="logout">
			<a href="{$link->getPageLink('index.php')}?mylogout" title="{l s='log out' mod='blockmyaccount'}">{l s='Sign out' mod='blockmyaccount'}</a>
		</p>
	</div>
</div>
<!-- /Block myaccount module -->
