<script type="text/javascript" src="../modules/prestassurance/fancybox/jquery.mousewheel-3.0.4.pack.js"></script>
<script type="text/javascript" src="../modules/prestassurance/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
<link type="text/css" rel="stylesheet" href="../modules/prestassurance/fancybox/jquery.fancybox-1.3.4.css" />

<script>
{literal}
	$(document).ready(function()
	{
		$('a#limited_country').fancybox();
		$('a#limited_country').trigger('click');		
	});
	

{/literal}
</script>
<a href="#alert_limited_country" id="limited_country"></a>
<div style="display:none">
	<div id="alert_limited_country">
		<p class="warn" style="width:300px">Désolé mais nous ne sommes pas en mesure de vous proposer d’assurance sur les articles de votre panier, notre contrat ne couvrant que les acheteurs situés dans le même pays que nous.</p>
	</div>
</div>