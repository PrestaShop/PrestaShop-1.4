<!-- Module Editorial -->
<div id="editorial_block_center" class="editorial_block">
	{if $xml->body->home_logo_link}<a href="{$xml->body->home_logo_link|escape:'htmlall':'UTF-8'}" title="{$xml->body->$title|escape:'htmlall':'UTF-8'|stripslashes}">{/if}
		{if $homepage_logo}<img src="{$this_path}homepage_logo.jpg" alt="{$xml->body->$title|escape:'htmlall':'UTF-8'|stripslashes}" />{/if}
	{if $xml->body->home_logo_link}</a>{/if}
	{if $xml->body->$logo_subheading}{$xml->body->$logo_subheading|stripslashes}{/if} 
	{if $xml->body->$title}<h2>{$xml->body->$title|stripslashes}</h2>{/if}
	{if $xml->body->$subheading}<h3>{$xml->body->$subheading|stripslashes}</h3>{/if}
	{if $xml->body->$paragraph}<div class="rte">{$xml->body->$paragraph|stripslashes}</div>{/if}
</div>
<!-- /Module Editorial -->
