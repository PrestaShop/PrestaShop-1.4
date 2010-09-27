{if $cms}
	{if !$content_only}
		{capture name=path}{l s=$cms->meta_title}{/capture}
		{include file=$tpl_dir./breadcrumb.tpl}
	{/if}
	<div class="rte{if $content_only} content_only{/if}">
		{$cms->content}
	</div>
{elseif $category}
	<div class="rte{if $content_only} content_only{/if}">
		<h2>{$category->name}</h2>
		{if isset($sub_category) & !empty($sub_category)}	
			<h4>{l s='List of sub categories in '}{$category->name} : </h4>
			<ul class="bullet">
				{foreach from=$sub_category item=subcategory}
					<li>
						<a href="{$link->getCMSCategoryLink($subcategory.id_cms_category, $subcategory.link_rewrite)|escape:'htmlall':'UTF-8'}">{$subcategory.name|escape:'htmlall':'UTF-8'}</a>
					</li>
				{/foreach}
			</ul>
		{/if}
		{if isset($cms_pages) & !empty($cms_pages)}
		<h4>{l s='List of pages in '}{$category->name} : </h4>
			<ul class="bullet">
				{foreach from=$cms_pages item=cmspages}
				{if $cmspages.id_cms_category == $category->id_cms_category}
					<li>
						<a href="{$link->getCMSLink($cmspages.id_cms, $cmspages.link_rewrite)|escape:'htmlall':'UTF-8'}">{$cmspages.meta_title|escape:'htmlall':'UTF-8'}</a>
					</li>
				{/if}
				{/foreach}
			</ul>
		{/if}
	</div>
{else}
	{l s='This page does not exist.'}
{/if}
<br />
{if !$content_only}
	<p><a href="{$link->getCMSCategoryLink(1, '')|escape:'htmlall':'UTF-8'}" title="{l s='Home'}">
			<img src="{$img_dir}icon/home.gif" alt="{l s='Home'}" class="icon" />
		</a>
		<a href="{$link->getCMSCategoryLink(1, '')|escape:'htmlall':'UTF-8'}" title="{l s='Home'}">{l s='Home'}</a>
	</p>
{/if}