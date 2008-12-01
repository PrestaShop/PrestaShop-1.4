<!-- Block languages module -->
<div id="languages_block_top">
	<ul id="first-languages">
		{foreach from=$languages key=k item=language name="languages"}
			<li {if $language.iso_code == $lang_iso}class="selected_language"{/if}>
				{if $language.iso_code != $lang_iso}<a href="{$link->getLanguageLink($language.id_lang, $language.name)}" title="{$language.name}">{/if}
					<img src="{$img_lang_dir}{$language.id_lang}.jpg" alt="{$language.name}" />
				{if $language.iso_code != $lang_iso}</a>{/if}
			</li>
		{/foreach}
	</ul>
</div>
<script type="text/javascript">
	$('ul#first-languages li:not(.selected_language)').css('opacity', 0.3);
	$('ul#first-languages li:not(.selected_language)').hover(function(){ldelim}
		$(this).css('opacity', 1);
	{rdelim}, function(){ldelim}
		$(this).css('opacity', 0.3);
	{rdelim});
</script>
<!-- /Block languages module -->

