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