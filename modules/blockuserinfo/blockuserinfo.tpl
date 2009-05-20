<!-- Block user information module HEADER -->
<div id="header_user">
	<p id="header_user_info">
		{l s='Welcome' mod='blockuserinfo'},
		{if $logged}
			<span>{$customerName}</span> (<a href="{$base_dir}index.php?mylogout" title="{l s='Log me out' mod='blockuserinfo'}">{l s='Log out' mod='blockuserinfo'}</a>)
		{else}
			<a href="{$base_dir_ssl}my-account.php">{l s='Log in' mod='blockuserinfo'}</a>
		{/if}
	</p>
	<ul id="header_nav">
		<li id="shopping_cart">
			<a href="{$base_dir_ssl}order.php" title="{l s='Your Shopping Cart' mod='blockuserinfo'}">{l s='Cart:' mod='blockuserinfo'}</a>
				<span class="ajax_cart_quantity{if $cart_qties == 0} hidden{/if}">{if $cart_qties > 0}{$cart_qties}{/if}</span><span class="ajax_cart_product_txt{if $cart_qties != 1} hidden{/if}">{l s='product' mod='blockuserinfo'}</span><span class="ajax_cart_product_txt_s{if $cart_qties < 2} hidden{/if}">{l s='products' mod='blockuserinfo'}</span>
				<span class="ajax_cart_total{if $cart_qties == 0} hidden{/if}">{if $cart_qties > 0}{if $priceDisplay == 1}{convertPrice price=$cart->getOrderTotal(false, 4)}{else}{convertPrice price=$cart->getOrderTotal(true, 4)}{/if}{/if}</span>
				<span class="ajax_cart_no_product{if $cart_qties > 0} hidden{/if}">{l s='(empty)' mod='blockuserinfo'}</span>
		</li>
		<li id="your_account"><a href="{$base_dir_ssl}my-account.php" title="{l s='Your Account' mod='blockuserinfo'}">{l s='Your Account' mod='blockuserinfo'}</a></li>
	</ul>
</div>
<!-- /Block user information module HEADER -->
