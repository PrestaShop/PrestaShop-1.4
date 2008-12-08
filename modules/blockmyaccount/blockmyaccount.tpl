<!-- Block myaccount module -->
<div class="block myaccount">
	<h4><a href="{$base_dir_ssl}my-account.php">{l s='My account' mod='blockmyaccount'}</a></h4>
	<div class="block_content">
		<ul class="bullet">
			<li><a href="{$base_dir_ssl}history.php" title="">{l s='My orders' mod='blockmyaccount'}</a></li>
			{if $returnAllowed}
			<li><a href="{$base_dir_ssl}order-follow.php" title="">{l s='My merchandise returns' mod='blockmyaccount'}</a></li>
			{/if}
			<li><a href="{$base_dir_ssl}order-slip.php" title="">{l s='My credit slips' mod='blockmyaccount'}</a></li>
			<li><a href="{$base_dir_ssl}addresses.php" title="">{l s='My addresses' mod='blockmyaccount'}</a></li>
			<li><a href="{$base_dir_ssl}identity.php" title="">{l s='My personal info' mod='blockmyaccount'}</a></li>
			{if $voucherAllowed}
			<li><a href="{$base_dir_ssl}discount.php" title="">{l s='My vouchers' mod='blockmyaccount'}</a></li>
			{/if}
			{$HOOK_BLOCK_MY_ACCOUNT}
		</ul>
		<p class="logout">
			<a href="{$base_dir}index.php?mylogout" title="{l s='log out' mod='blockmyaccount'}">{l s='Sign out' mod='blockmyaccount'}</a>
		</p>
	</div>
</div>
<!-- /Block myaccount module -->
