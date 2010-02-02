function getCommentForm()
{
	if (document.forms)
		return (document.forms['comment_form']);
	else
		return (document.comment_form);
}

function acceptComment(id)
{
	var form = getCommentForm();
	form.elements['id_product_comment'].value = id;
	form.elements['action'].value = 'accept';
	form.submit();
}

function deleteComment(id)
{
	var form = getCommentForm();
	form.elements['id_product_comment'].value = id;
	form.elements['action'].value = 'delete';
	form.submit();
}

function getCriterionForm()
{
	if (document.forms)
		return (document.forms['criterion_form']);
	else
		return (document.criterion_form);
}

function editCriterion(id)
{
	var form = getCriterionForm();
	form.elements['id_product_comment_criterion'].value = id;
	form.elements['criterion_name'].value = document.getElementById('criterion_name_' + id).value;
	form.elements['criterion_action'].value = 'edit';
	form.submit();
}

function deleteCriterion(id)
{
	var form = getCriterionForm();
	form.elements['id_product_comment_criterion'].value = id;
	form.elements['criterion_action'].value = 'delete';
	form.submit();
}