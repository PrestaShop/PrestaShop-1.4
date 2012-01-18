<ul id="menuTab">
	<li id="menuTab1" class="menuTabButton selected">{l s='Info Service Buyster' mod='buyster'}</li>
	<li id="menuTab2" class="menuTabButton">{l s='Account settings' mod='buyster'}</li>
	<li id="menuTab3" class="menuTabButton">{l s='Option payment' mod='buyster'}</li>
	<li id="menuTab5" class="menuTabButton">{l s='Logo settings' mod='buyster'}</li>
	<li id="menuTab4" class="menuTabButton">{l s='Manage Buyster transactions' mod='buyster'}</li>
	<!--<li id="menuTab3" class="menuTabButton">{l s='Diagnostic'}</li>-->
</ul>
<div id="tabList">
	<div id="menuTab1Sheet" class="tabItem selected">{$varMain.info}</div>
	<div id="menuTab2Sheet" class="tabItem">{$varMain.account}</div>
	<div id="menuTab3Sheet" class="tabItem"><div>{$varMain.parameter}</div></div>
	<div id="menuTab5Sheet" class="tabItem"><div>{$varMain.logo}</div></div>
	<div id="menuTab4Sheet" class="tabItem"><div>{$varMain.manage}</div></div>
	<!--<div id="menuTab3Sheet" class="tabItem"><div>{$varMain.diagnostic}</div></div>-->
</div>
<br clear="left" />
<br />
<style>
	#menuTab { float: left; padding: 0; margin: 0; text-align: left; }
	#menuTab li { text-align: left; float: left; display: inline; padding: 5px; padding-right: 10px; background: #EFEFEF; font-weight: bold; cursor: pointer; border-left: 1px solid #CCCCCC; border-right: 1px solid #CCCCCC; border-top: 1px solid #CCCCCC; }
	#menuTab li.menuTabButton.selected { background: #FFF6D3; border-left: 1px solid #CCCCCC; border-right: 1px solid #CCCCCC; border-top: 1px solid #CCCCCC; }
	#tabList { clear: left; }
	.tabItem { display: none; }
	.tabItem.selected { display: block; background: #FFFFF0; border: 1px solid #CCCCCC; padding: 10px; padding-top: 20px; }
</style>
<script type="text/javascript">
	$(".menuTabButton").click(function () {
		$(".menuTabButton.selected").removeClass("selected");
		$(this).addClass("selected");
		$(".tabItem.selected").removeClass("selected");
		$("#" + this.id + "Sheet").addClass("selected");
	});
</script>