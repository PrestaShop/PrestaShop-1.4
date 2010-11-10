<!-- Module Editorial -->
<div id="editorial_block_center" class="editorial_block">
	{if $editorial->body_home_logo_link}<a href="{$editorial->body_home_logo_link|escape:'htmlall':'UTF-8'}" title="{$xml->body_title[$id_lang]|escape:'htmlall':'UTF-8'|stripslashes}">{/if}
		{if $homepage_logo}<img src="{$link->getMediaLink($image_path)}" alt="{$editorial->body_title[$id_lang]|escape:'htmlall':'UTF-8'|stripslashes}" {if $image_width}width="{$image_width}"{/if} {if $image_height}height="{$image_height}" {/if}/>{/if}
	{if $editorial->body_home_logo_link}</a>{/if}
	{if $editorial->body_logo_subheading[$id_lang]}{$editorial->body_logo_subheading[$id_lang]|stripslashes}
	{elseif $editorial->body_logo_subheading[$default_lang]}{$editorial->body_logo_subheading[$default_lang]}{/if} 
	{if $editorial->body_title[$id_lang]}<h2>{$editorial->body_title[$id_lang]|stripslashes}</h2>
	{elseif $editorial->body_title[$default_lang]}<h2>{$editorial->body_title[$default_lang]|stripslashes}</h2>{/if}
	{if $editorial->body_subheading[$id_lang]}<h3>{$editorial->body_subheading[$id_lang]|stripslashes}</h3>
	{elseif $editorial->body_subheading[$default_lang]}<h3>{$editorial->body_subheading[$default_lang]|stripslashes}</h3>{/if}
	{if $editorial->body_paragraph[$id_lang]}<div class="rte">{$editorial->body_paragraph[$id_lang]|stripslashes}</div>
	{elseif $editorial->body_paragraph[$default_lang]}<div class="rte">{$editorial->body_paragraph[$default_lang]|stripslashes}</div>{/if}
</div>
<!-- /Module Editorial -->
