function getProductCriterionForm()
{
	if (document.forms)
		return (document.forms['product_criterion_form']);
	else
		return (document.product_criterion_form);
}

function getProductCriterion(path, id_product, id_lang)
{
	$.get(path + 'productcommentscriterion.php', { id_product: id_product, id_lang: id_lang },
	function(data){
		document.getElementById('product_criterions').innerHTML = data;
	});
}
