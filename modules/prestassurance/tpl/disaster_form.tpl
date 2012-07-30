<script type="text/javascript">
	var no_coment = '{l s='No comment' mod='prestassurance'}';
    var no_disaster = '{l s='No disater for this order' mod='prestassurance'}';
    var disaster_saved = '{l s='Your disaster has been send to insurer' mod='prestassurance'}';
    var comment_added = '{l s='Your comment has been send to insurer' mod='prestassurance'}';
    var your_comment = '{l s='Your comment' mod='prestassurance'}';
    var insurer_comment = '{l s='Insurer comment' mod='prestassurance'}';
    var product_purchased_broken = '{l s='Product purchased Broken' mod='prestassurance'}';
    var product_purchased_stolen = '{l s='Product purchased Stolen' mod='prestassurance'}';
    var product_purchased_not_delivered = '{l s='Product purchased not delivered' mod='prestassurance'}';
    var internet = '{l s='Refund request current subscriptions (Internet)' mod='prestassurance'}';
    var phone_need = 'Merci de saisir un numéro de téléphone';
    var token_psa = '{$token_psa}';
    var base_dir_ssl = '{$base_dir_ssl}';
</script>

    {capture name=path}
    	<a href="{$link->getPageLink('my-account.php', true)}">{l s='My account' mod='prestassurance'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s='My Insurance' mod='prestassurance'}
    {/capture}
    {include file="$tpl_dir./breadcrumb.tpl"}

    <div id="disaster_form_content">
        <form id="disaster_form" class="std">
            <h1>{l s='My Insurance' mod='prestassurance'} :</h1>

            <div id="alert_message"></div>

            <fieldset>
                <p>{l s='Cet espace sert à (i) sélectionner les articles pour lesquels vous souhaitez déclarer un sinistre et à (ii) spécifier la nature du sinistre que vous avez subi.' mod='prestassurance'}</p>  					<p>{l s='Veuillez noter que seuls les dommages prévus au contrat sont couverts et que les autres types de sinistre, comme les pannes ou les défauts de fabrication, n’entrent pas dans le champ des garanties.' mod='prestassurance'}</p>
				<br>
                <div class="psa_disaster_form">
                    <label>{l s='Select an order' mod='prestassurance'} :</label> <select name="id_order" id="order_select" onchange="getOrderDisasterDetails($(this).val());">
                        <option value="0"></option>
                        {foreach from=$orders item=order name=orders}
							<option value="{$order.id_order}">n°{$order.id_order} - {$order.date}</option>
						{foreachelse}
							<option value="0">{l s='There is no valid orders with insurance' mod='prestassurance'}</option>
						{/foreach}
		            </select>
                </div>

                <div class="clear">
                    <br>
                </div>

                <div id="disaster_form_detail" style="display:none">
                    <h3>{l s='Declare new disaster' mod='prestassurance'} :</h3><br>

                    <div class="psa_disaster_form">
                        <label>1) {l s='Select a product' mod='prestassurance'} :</label> <select name="id_product" id="id_product">
                            </select>
                    </div>

                    <div class="clear">
                        <br>
                    </div>

                    <div class="psa_disaster_form">
                        <label>2) {l s='Type de sinistre' mod='prestassurance'} :</label> <select name="disaster_reason" onchange="showStep3($(this).val());">
                            <option value="0">
                                {l s='-------------' mod='prestassurance'}
                            </option>

                            <option value="product_purchased_broken">
                                {l s='Casse' mod='prestassurance'}
                            </option>

                            <option value="product_purchased_stolen">
                                {l s='Vol' mod='prestassurance'}
                            </option>

                            <option value="product_purchased_not_delivered">
                                {l s='Absence de livraison' mod='prestassurance'}
                            </option>

                            <option value="internet">
                                {l s='Abonnement internet' mod='prestassurance'}
                            </option>
                        </select>
                    </div>

                    <div class="clear">
                        <br>
                    </div>

                    <div class="psa_disaster_form">
                        <div id="reason_detail_container" style="display:none;">
                            <label>3) {l s='Détail' mod='prestassurance'} :</label> <select id="reason_detail" name="reason_detail" onchange="showFinalStep($(this).val());">
                                <option value="0">
                                    -------------
                                </option>
                            </select>
                        </div>
                    </div><br>

                    <p id="contact_form_link" class="warning" style="display:none">
                    	{l s='Ce cas n\'est pas pris en charge par l\'assurance, merci de nous contacter directement via le formulaire de' mod='prestassurance'}
                    	<a href="{$link->getPageLink('contact-form.php', true)}">{l s='Contact'}</a>
                    </p>
                    <div class="psa_disaster_form">
                        <div id="documents_list_container" style="display:none">
                            <label>4) {l s='Liste des documents' mod='prestassurance'} :</label>

                            <ul style="margin-left: 50px;" id="documents_list"></ul>
                        </div>
                    </div>

                    <div class="psa_disaster_form">
                        <div id="comment_container" style="display:none;">
                            <p>Une fois le sinistre déclaré, l'assureur entrera en contact avec vous pour vous demander les justificatifs nécessaires au traitement de votre dossier et à votre indemnisation.</p>

                            <p>En conséquence, merci de bien vouloir renseigner vos coordonnées :</p>
                            <br>
                            <label>{l s='Email' mod='prestassurance'} :</label>
                            <input type="text" name="email" id="email" value="{$customer_email}">

                            <p>Voici l’adresse mail dont nous disposons vous concernant. En cas d’erreur ou si vous souhaitez être contacté sur une autre adresse, merci de modifier le champ ci-dessus.</p>
                            <br>
                            <label>{l s='Téléphone' mod='prestassurance'} :</label>
                            <input type="text" name="phone" id="phone"><sup style="color:red">*</sup>

                            <p>Cette information est transmise à l'assureur uniquement dans le cadre de votre indemnisation et ne sera pas utilisée des fins commerciales</p>

                            <div class="clear">
                                <br>
                            </div><label>{l s='Leave a comment' mod='prestassurance'} :</label>

                            <div class="clear">
                                <br>
                            </div>
                            <textarea rows="5" style="width:535px" name="disaster_comment" id="disaster_comment">
</textarea>
                        </div>
                    </div>

                    <div class="clear">
                        <br>
                    </div><input id="submit_disaster_button" style="margin:auto;display:none;" type="submit" class="button" onclick="submitDisaster(); return false;" value="{l s='Déclarer' mod='prestassurance'}">
                </div>
            </fieldset>
        </form>
    </div>

    <div id="disaster_follow_content" style="display:none;">
        <h3>{l s='Followed disaster' mod='prestassurance'} :</h3>

        <table class="std">
			<thead>
				<tr>
					<th style="display:none" class="first_item">{l s='Disaster number' mod='prestassurance'}</th>
					<th class="first_item">{l s='Product' mod='prestassurance'}</th>
					<th class="item">{l s='Type de sinistre' mod='prestassurance'}</th>
					<th class="item" style="width:30px;text-align:center">{l s='Status' mod='prestassurance'}</th>
					<th class="last_item" style="width:30px;text-align:center">{l s='Action' mod='prestassurance'}</th>
				</tr>
			</thead>
			<tfoot>
				<tr id="add_new_comment" style="display:none">
					<td>
						<label>{l s='Comment' mod='prestassurance'} :</label>
					</td>
					<td>
						<input type="hidden" name="id_disaster_new_comment" id="id_disaster_new_comment" value="">
						<input type="hidden" name="id_psa_disaster_new_comment" id="id_psa_disaster_new_comment" value="">
						<textarea rows="3" style="width:90%" name="disaster_new_comment" id="disaster_new_comment"></textarea>
					</td>
					<td colspan="2">
						<input style="margin:auto" type="submit" class="button" onclick="submitAddNewComment(); return false;" value="{l s='Add' mod='prestassurance'}"/><br>
						<input style="margin:auto" type="submit" class="button" onclick="clearNewCommentForm(); return false;" value="{l s='Cancel' mod='prestassurance'}"/>
					</td>
				</tr>
			</tfoot>
			<tbody>
			</tbody>
		</table>

        <div class="clear">
            <br>
        </div>

        <p><b>Légende :</b></p>

        <ul class="legend_status">
            <li id="wait">En cours de traitement</li>

            <li id="more_info">Besoin d’informations complémentaires</li>

            <li id="warn">Problème dans la déclaration</li>

            <li id="canceled">Annulation de la demande d’indemnisation</li>

            <li id="accepted">Dossier accepté</li>
        </ul>
    </div>