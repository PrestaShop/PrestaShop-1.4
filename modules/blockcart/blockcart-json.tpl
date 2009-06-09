{ldelim}
'products': [
{if $products}{foreach from=$products item=product}
{assign var='productId' value=$product.id_product}
{assign var='productAttributeId' value=$product.id_product_attribute}
	{ldelim}
		'id':            {$product.id_product},
		'link':          '{$link->getProductLink($product.id_product, $product.link_rewrite, $product.category)|addslashes}',
		'quantity':      {$product.cart_quantity},
		'priceByLine':   '{displayWtPrice|html_entity_decode:2:'UTF-8' p=$product.real_price}',
		'name':          '{$product.name|addslashes|truncate:16:'...'|escape:'htmlall':'UTF-8'}',
		'price':         '{displayWtPrice|html_entity_decode:2:'UTF-8' p=$product.real_price}',
		'idCombination': {if isset($product.attributes_small)}{$productAttributeId}{else}0{/if},
{if isset($product.attributes_small)}
		'hasAttributes': true,
		'attributes':    '{$product.attributes_small|addslashes}',
{else}
		'hasAttributes': false,
{/if}
		'hasCustomizedDatas': {if isset($customizedDatas.$productId.$productAttributeId)}true{else}false{/if},

		'customizedDatas':[
		{foreach from=$customizedDatas.$productId.$productAttributeId key='id_customization' item='customization'}{ldelim}
{* This empty line was made in purpose (product addition debug), please leave it here *}

			'customizationId':	{$id_customization},
			'quantity':			{$customization.quantity},
			'datas': [
{foreach from=$customization.datas key='type' item='datas'}
				{ldelim}
					'type':	{$type},
					'datas':
					[
{foreach from=$datas key='index' item='data'}

						{ldelim}
						'index':			{$index},
						'value':			'{$data.value|addslashes}',
						'truncatedValue':	'{$data.value|truncate:28:'...'|addslashes}'
						{rdelim},
					{/foreach}]
				{rdelim},
				{/foreach}
			]
		{rdelim},{/foreach}
		]


	{rdelim},
{/foreach}{/if}
],

'discounts': [
{if $discounts}{foreach from=$discounts item=discount name='discounts'}
	{ldelim}
		'id':              '{$discount.id_discount}',
		'name':            '{$discount.name|cat:' : '|cat:$discount.description|truncate:18:'...'|addslashes}',
		'description':     '{$discount.description|addslashes}',
		'nameDescription': '{$discount.name|cat:' : '|cat:$discount.description|truncate:18:'...'}',
		'link':            '{$base_dir_ssl}order.php?deleteDiscount={$discount.id_discount}',
		'price':           '-{if $priceDisplay == 1}{convertPrice|html_entity_decode:2:'UTF-8' price=$discount.value_tax_exc}{else}{convertPrice|html_entity_decode:2:'UTF-8' price=$discount.value_real}{/if}'
	{rdelim}
	{if !$smarty.foreach.discounts.last},{/if}
{/foreach}{/if}
],

'shippingCost': '{$shipping_cost|html_entity_decode:2:'UTF-8'}',
'wrappingCost': '{$wrapping_cost|html_entity_decode:2:'UTF-8'}',
'nbTotalProducts': '{$nb_total_products}',
'total': '{$total|html_entity_decode:2:'UTF-8'}',
'productTotal': '{$product_total|html_entity_decode:2:'UTF-8'}',

{if isset($errors) && $errors}
'hasError' : true,
errors : [
{foreach from=$errors key=k item=error name='errors'}
	'{$error|addslashes|html_entity_decode:2:'UTF-8'}'
	{if !$smarty.foreach.errors.last},{/if}
{/foreach}
]
{else}
'hasError' : false
{/if}

{rdelim}
