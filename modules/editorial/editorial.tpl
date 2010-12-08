{*
* Copyright (C) 2007-2010 PrestaShop 
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author Prestashop SA <contact@prestashop.com>
*  @copyright  Copyright (c) 2007-2010 Prestashop SA : 6 rue lacepede, 75005 PARIS
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registred Trademark & Property of PrestaShop SA
*}

<!-- Module Editorial -->
<div id="editorial_block_center" class="editorial_block">
	{if $editorial->body_home_logo_link}<a href="{$editorial->body_home_logo_link|escape:'htmlall':'UTF-8'}" title="{$editorial->body_title|escape:'htmlall':'UTF-8'|stripslashes}">{/if}
	{if $homepage_logo}<img src="{$link->getMediaLink($image_path)}" alt="{$editorial->body_title|escape:'htmlall':'UTF-8'|stripslashes}" {if $image_width}width="{$image_width}"{/if} {if $image_height}height="{$image_height}" {/if}/>{/if}
	{if $editorial->body_home_logo_link}</a>{/if}
	{if $editorial->body_logo_subheading}{$editorial->body_logo_subheading|stripslashes}
	{elseif $editorial->body_logo_subheading}{$editorial->body_logo_subheading}{/if} 
	{if $editorial->body_title}<h2>{$editorial->body_title|stripslashes}</h2>
	{elseif $editorial->body_title}<h2>{$editorial->body_title|stripslashes}</h2>{/if}
	{if $editorial->body_subheading}<h3>{$editorial->body_subheading|stripslashes}</h3>
	{elseif $editorial->body_subheading}<h3>{$editorial->body_subheading|stripslashes}</h3>{/if}
	{if $editorial->body_paragraph}<div class="rte">{$editorial->body_paragraph|stripslashes}</div>
	{elseif $editorial->body_paragraph}<div class="rte">{$editorial->body_paragraph|stripslashes}</div>{/if}
</div>
<!-- /Module Editorial -->