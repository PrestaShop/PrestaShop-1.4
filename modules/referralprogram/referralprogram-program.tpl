<script type="text/javascript">
<!--
	var baseDir = '{$base_dir_ssl}';
-->
</script>

<script type="text/javascript">
// <![CDATA[
	ThickboxI18nClose = "{l s='Close' mod='referralprogram'}";
	ThickboxI18nOrEscKey = "{l s='or Esc key' mod='referralprogram'}";
	tb_pathToImage = "{$img_ps_dir}loadingAnimation.gif";
	//]]>
</script>

{capture name=path}<a href="{$base_dir_ssl}my-account.php">{l s='My account' mod='referralprogram'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s='ReferralProgram' mod='referralprogram'}{/capture}
{include file=$tpl_dir./breadcrumb.tpl}

<h2>{l s='Referral program' mod='referralprogram'}</h2>

{if $error}
	<p class="error">
		{if $error == 'conditions not valided'}
			{l s='You need to agree to the conditions of the referral program!' mod='referralprogram'}
		{elseif $error == 'email invalid'}
			{l s='At least one e-mail address is invalid!' mod='referralprogram'}
		{elseif $error == 'name invalid'}
			{l s='At least one first name or last name is invalid!' mod='referralprogram'}
		{elseif $error == 'email exists'}
			{l s='Someone with this e-mail address has already been sponsored!' mod='referralprogram'}: {foreach from=$mails_exists item=mail}{$mail} {/foreach}
		{elseif $error == 'no revive checked'}
			{l s='Please mark at least one checkbox' mod='referralprogram'}
		{elseif $error == 'cannot add friends'}
			{l s='Cannot add friends to database' mod='referralprogram'}
		{/if}
	</p>
{/if}

{if $invitation_sent}
	<p class="success">
	{if $nbInvitation > 1}
		{l s='Emails have been sent to your friends !' mod='referralprogram'}
	{else}
		{l s='Email have been sent to your friend !' mod='referralprogram'}
	{/if}
	</p>
{/if}

{if $revive_sent}
	<p class="success">
	{if $nbRevive > 1}
		{l s='Revive emails have been sent to your friends !' mod='referralprogram'}
	{else}
		{l s='Revive email have been sent to your friend !' mod='referralprogram'}
	{/if}
	</p>
{/if}

<ul class="idTabs">
	<li><a href="#idTab1" {if $activeTab eq 'sponsor'}class="selected"{/if}>{l s='Sponsor my friends' mod='referralprogram'}</a></li>
	<li><a href="#idTab2" {if $activeTab eq 'pending'}class="selected"{/if}>{l s='Pending friends' mod='referralprogram'}</a></li>
	<li><a href="#idTab3" {if $activeTab eq 'subscribed'}class="selected"{/if}>{l s='Friends I sponsored' mod='referralprogram'}</a></li>
</ul>
<div class="sheets">

	<div id="idTab1">
		<p class="bold">
			{l s='Get a discount of' mod='referralprogram'} {$discount} {l s='for you and your friends by recommending this Website.' mod='referralprogram'}
		</p>
		{if $canSendInvitations}
			<p>
				{l s='It\'s quick and it\'s easy. Just fill in the first name, last name, and e-mail address(es) of your friend(s) in the fields below.' mod='referralprogram'}
				{l s='When one of them makes at least' mod='referralprogram'} {$orderQuantity} {if $orderQuantity > 1}{l s='orders' mod='referralprogram'}{else}{l s='order' mod='referralprogram'}{/if},
				{l s='he or she will receive a' mod='referralprogram'} {$discount} {l s='voucher and you will receive your own voucher worth' mod='referralprogram'} {$discount}.
			</p>
			<form method="post" action="{$base_dir_ssl}modules/referralprogram/referralprogram-program.php" class="std">
				<table class="std">
				<thead>
					<tr>
						<th class="first_item">&nbsp;</th>
						<th class="item">{l s='Last name' mod='referralprogram'}</th>
						<th class="item">{l s='First name' mod='referralprogram'}</th>
						<th class="last_item">{l s='E-mail' mod='referralprogram'}</th>
					</tr>
				</thead>
				<tbody>
					{section name=friends start=0 loop=$nbFriends step=1}
					<tr class="{if $smarty.section.friends.index % 2}item{else}alternate_item{/if}">
						<td class="align_right">{$smarty.section.friends.iteration}</td>
						<td><input type="text" class="text" name="friendsLastName[{$smarty.section.friends.index}]" size="14" value="{if isset($smarty.post.friendsLastName[$smarty.section.friends.index])}{$smarty.post.friendsLastName[$smarty.section.friends.index]}{/if}" /></td>
						<td><input type="text" class="text" name="friendsFirstName[{$smarty.section.friends.index}]" size="14" value="{if isset($smarty.post.friendsFirstName[$smarty.section.friends.index])}{$smarty.post.friendsFirstName[$smarty.section.friends.index]}{/if}" /></td>
						<td><input type="text" class="text" name="friendsEmail[{$smarty.section.friends.index}]" size="20" value="{if isset($smarty.post.friendsEmail[$smarty.section.friends.index])}{$smarty.post.friendsEmail[$smarty.section.friends.index]}{/if}" /></td>
					</tr>
					{/section}
				</tbody>
				</table>
				<p class="bold">
					{l s='Important: Your friends\' e-mail addresses will only be used in the referral program. They will never be used for other purposes.' mod='referralprogram'}
				</p>
				<p class="checkbox">
					<input type="checkbox" name="conditionsValided" id="conditionsValided" value="1" {if isset($smarty.post.conditionsValided) AND $smarty.post.conditionsValided eq 1}checked="checked"{/if} />
					<label for="conditionsValided">
						{l s='I have read the conditions of the referral program and accept them in their entirety. I also agree to have my friend reminded again in two weeks (if he or she still has not made a purchase on conditions).' mod='referralprogram'}
					</label>
					<a href="{$base_dir}modules/referralprogram/referralprogram-rules.php?height=500&amp;width=400" class="thickbox" title="{l s='Conditions of the referral program' mod='referralprogram'}">{l s='Read conditions.' mod='referralprogram'}</a>
				</p>
				<p>
					{l s='Preview' mod='referralprogram'} <a href="{$base_dir_ssl}modules/referralprogram/preview-email.php?height=500&amp;width=600&amp;mail={$lang_iso}/referralprogram-invitation.html" class="thickbox" title="{l s='Invitation e-mail' mod='referralprogram'}">{l s='the default e-mail' mod='referralprogram'}</a> {l s='that will be sent to your(s) friend(s).' mod='referralprogram'}
				</p>
				<p class="submit">
					<input type="submit" id="submitSponsorFriends" name="submitSponsorFriends" class="button_large" value="{l s='Validate' mod='referralprogram'}" />
				</p>
			</form>
		{else}
			<p class="warning">
				{l s='To become a sponsor, you need to have completed at least' mod='referralprogram'} {$orderQuantity} {if $orderQuantity > 1}{l s='orders' mod='referralprogram'}{else}{l s='order' mod='referralprogram'}{/if}.
			</p>
		{/if}
	</div>

	<div id="idTab2">
	{if $pendingFriends AND $pendingFriends|@count > 0}
		<p>
			{l s='These friends have not yet placed an order on this Website since you sponsored them, but you can try again! To do so, mark the checkboxes of the friend(s) you want to remind, then click on the button "Remind my friend(s)"' mod='referralprogram'}
		</p>
		<form method="post" action="{$base_dir_ssl}modules/referralprogram/referralprogram-program.php" class="std">
			<table class="std">
			<thead>
				<tr>
					<th class="first_item">&nbsp;</th>
					<th class="item">{l s='Last name' mod='referralprogram'}</th>
					<th class="item">{l s='First name' mod='referralprogram'}</th>
					<th class="item">{l s='E-mail' mod='referralprogram'}</th>
					<th class="last_item"><b>{l s='Last invitation' mod='referralprogram'}</b></th>
				</tr>
			</thead>
			<tbody>
			{foreach from=$pendingFriends item=pendingFriend name=myLoop}
				<tr>
					<td>
						<input type="checkbox" name="friendChecked[{$pendingFriend.id_referralprogram}]" id="friendChecked[{$pendingFriend.id_referralprogram}]" value="1" />
					</td>
					<td>
						<label for="friendChecked[{$pendingFriend.id_referralprogram}]">{$pendingFriend.lastname|substr:0:22}</label>
					</td>
					<td>{$pendingFriend.firstname|substr:0:22}</td>
					<td>{$pendingFriend.email}</td>
					<td>{dateFormat date=$pendingFriend.date_upd full=1}</td>
				</tr>
			{/foreach}
			</tbody>
			</table>
			<p class="submit">
				<input type="submit" value="{l s='Remind my friend(s)' mod='referralprogram'}" name="revive" id="revive" class="button_large" />
			</p>
		</form>
		{else}
			<p class="warning">{l s='You have not sponsored any friends.' mod='referralprogram'}</p>
		{/if}
	</div>

	<div id="idTab3">
	{if $subscribeFriends AND $subscribeFriends|@count > 0}
		<p>
			{l s='Here are sponsored friends who have accepted your invitation:' mod='referralprogram'}
		</p>
		<table class="std">
		<thead>
			<tr>
				<th class="first_item">&nbsp;</th>
				<th class="item">{l s='Last name' mod='referralprogram'}</th>
				<th class="item">{l s='First name' mod='referralprogram'}</th>
				<th class="item">{l s='E-mail' mod='referralprogram'}</th>
				<th class="last_item">{l s='Inscription date' mod='referralprogram'}</th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$subscribeFriends item=subscribeFriend name=myLoop}
			<tr>
				<td>{$smarty.foreach.myLoop.iteration}.</td>
				<td>{$subscribeFriend.lastname|substr:0:22}</td>
				<td>{$subscribeFriend.firstname|substr:0:22}</td>
				<td>{$subscribeFriend.email}</td>
				<td>{dateFormat date=$subscribeFriend.date_upd full=1}</td>
			</tr>
			{/foreach}
		</tbody>
		</table>
	{else}
		<p class="warning">
			{l s='No sponsored friends have accepted your invitation yet.' mod='referralprogram'}
		</p>
	{/if}
	</div>
</div>

<ul class="footer_links">
	<li><a href="{$base_dir_ssl}my-account.php"><img src="{$img_dir}icon/my-account.gif" alt="" class="icon" /></a><a href="{$base_dir_ssl}my-account.php">{l s='Back to Your Account' mod='referralprogram'}</a></li>
	<li><a href="{$base_dir}"><img src="{$img_dir}icon/home.gif" alt="" class="icon" /></a><a href="{$base_dir_ssl}">{l s='Home' mod='referralprogram'}</a></li>
</ul>
