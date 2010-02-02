function fillCombinaison(wholesale_price, price_impact, weight_impact, reference, supplier_reference, ean, quantity, image, old_attr, id_product_attribute, default_attribute, eco_tax, location)
{
	init_elems();
	getE('attribute_quantity').value = quantity;
	getE('attribute_reference').value = reference;
	getE('attribute_supplier_reference').value = supplier_reference;
	getE('attribute_ean13').value = ean;
	getE('submitProductAttribute').value = modifyattributegroup;
	getE('attribute_wholesale_price').value = Math.abs(wholesale_price);
	getE('attribute_price').value = Math.abs(price_impact);
	getE('attribute_weight').value = weight_impact;
	getE('attribute_ecotax').value = eco_tax;
	getE('attribute_location').value = location;
	if (default_attribute == 1)
		getE('attribute_default').checked = true;
	else
		getE('attribute_default').checked = false;

	if (price_impact < 0)
	{
		getE('attribute_price_impact').options[getE('attribute_price_impact').selectedIndex].value = -1;
		getE('attribute_price_impact').selectedIndex = 2;
	}
	else if (!price_impact)
	{
		getE('attribute_price_impact').options[getE('attribute_price_impact').selectedIndex].value = 0;
		getE('attribute_price_impact').selectedIndex = 0;
	}
	else if (price_impact > 0)
	{
		getE('attribute_price_impact').options[getE('attribute_price_impact').selectedIndex].value = 1;
		getE('attribute_price_impact').selectedIndex = 1;
	}
	if (weight_impact < 0)
	{
		getE('attribute_weight_impact').options[getE('attribute_weight_impact').selectedIndex].value = -1;
		getE('attribute_weight_impact').selectedIndex = 2;
	}
	else if (!weight_impact)
	{
		getE('attribute_weight_impact').options[getE('attribute_weight_impact').selectedIndex].value = 0;
		getE('attribute_weight_impact').selectedIndex = 0;
	}
	else if (weight_impact > 0)
	{
		getE('attribute_weight_impact').options[getE('attribute_weight_impact').selectedIndex].value = 1;
		getE('attribute_weight_impact').selectedIndex = 1;
	}

	/* Reset all combination images */
	combinationImages = $('#id_image_attr').find("input[@id^=id_image_attr_]");
	combinationImages.each(function() {
		this.checked = false;
	});

	/* Check combination images */
	if (typeof(combination_images[id_product_attribute]) != 'undefined')
		for (i = 0; i < combination_images[id_product_attribute].length; i++)
			getE('id_image_attr_' + combination_images[id_product_attribute][i]).checked = true;

	check_impact();
	check_weight_impact();

	var elem = getE('product_att_list');
	for (var i = 0; i < old_attr.length; i++)
	{
		var opt = document.createElement('option');
		opt.text = old_attr[i++];
		opt.value = old_attr[i];

		try {
			elem.add(opt, null);
		}
		catch(ex) {
			elem.add(opt);
		}
	}
	getE('id_product_attribute').value = id_product_attribute;
	if (document.getElementById)
		var style = document.getElementById('ResetSpan').style;
	else if (document.all)
		var style = document.all['ResetSpan'].style;
	else if (document.layers)
		var style = document.layers['ResetSpan'].style;
	if (style.display == 'none')
		style.display = 'block';
}

function populate_attrs()
{
	var attr_group = getE('attribute_group');
	var attr_name = getE('attribute');
	var number = attr_group.options.length ? attr_group.options[attr_group.selectedIndex].value : 0;

	if (!number)
	{
		attr_name.options.length = 0;
		attr_name.options[0] = new Option('---', 0);
		return;
	}

	var list = attrs[number];
	attr_name.options.length = 0;

	for(i = 0; i < list.length; i += 2)
		attr_name.options[i / 2] = new Option(list[i + 1], list[i]);
}

function check_impact()
{
	var impact = getE('attribute_price_impact');
	var price = getE('attribute_price');

	if (!impact.selectedIndex)
	{
		getE('span_impact').style.display = 'none';
		price.value = 0;
	}
	else
		getE('span_impact').style.display = 'inline';
}

function check_weight_impact()
{
	var impact = getE('attribute_weight_impact');
	var weight = getE('attribute_weight');

	if (!impact.selectedIndex)
	{
		getE('span_weight_impact').style.display = 'none';
		weight.value = 0;
	}
	else
		getE('span_weight_impact').style.display = 'inline';
}

function init_elems()
{
	var elem = getE('product_att_list');

	if (elem.length)
		for (i = elem.length - 1; i >= 0; i--)
			if (elem[i])
				elem.remove(i);

	getE('attribute_price_impact').selectedIndex = 0;
	getE('attribute_weight_impact').selectedIndex = 0;
	try
	{
		if (impact.options[impact.selectedIndex].value == 0)
			getE('span_impact').style.display = 'none';
		if (impact2.options[impact.selectedIndex].value == 0)
			getE('span_weight_impact').style.display = 'none';
	}
	catch (e)
	{
		getE('span_impact').style.display = 'none';
		getE('span_weight_impact').style.display = 'none';
	}
}

function attr_selectall()
{
	var elem = getE('product_att_list');
	var i;

	for (i = 0; i < elem.length; i++)
		elem.options[i].selected = true;
}

function del_attr_multiple()
{
	var attr = getE('attribute_group');

	if (!attr)
		return ;
	var length = attr.length;
	var target;

	for (var i = 0; i < length; ++i)
	{
		elem = attr.options[i];
		if (elem.selected)
		{
			target = getE('table_' + elem.parentNode.getAttribute('name'));
			if (target && getE('result_' + elem.getAttribute('name')))
			{
				target.removeChild(getE('result_' + elem.getAttribute('name')));
				if (!target.lastChild || !target.lastChild.id)
					toggle(target.parentNode, false);
			}
		}
	}
}

function create_attribute_row(id, id_group, name, price, weight)
{
	var row = document.createElement('tr');
	var col1 = document.createElement('td');
	var col2 = document.createElement('td');
	var col3 = document.createElement('td');
	var col1_content = document.createElement('input');
	var col2_content = document.createElement('input');
	var col3_content = document.createElement('input');

	col1.innerHTML = name;


	col1_content.name = 'options['+id_group+']['+id+']';
	col1_content.value = id;
	col1_content.type = 'hidden';

	col2_content.name = 'price_impact['+id+']';
	col2_content.value = price;
	col2_content.style.width = '50px';

	col3_content.value = weight;
	col3_content.name = 'weight_impact['+id+']';
	col3_content.style.width = '50px';

	col1.appendChild(col1_content);
	col2.appendChild(col2_content);
	col3.appendChild(col3_content);
	row.appendChild(col1);
	row.appendChild(col2);
	row.appendChild(col3);
	row.id = 'result_' + id;
	return row;
}

function add_attr_multiple()
{
	var attr = getE('attribute_group');
	if (!attr)
		return ;
	var length = attr.length;
	var target;
	var new_elem;

	for (var i = 0; i < length; ++i)
	{
		elem = attr.options[i];
		if (elem.selected)
		{
			name = elem.parentNode.getAttribute('name');
			target = getE('table_' + name);
			if (target && !getE('result_' + elem.getAttribute('name')))
			{
				new_elem = create_attribute_row(elem.getAttribute('name'), elem.parentNode.getAttribute('name'), elem.value, '0.00', '0.00');
				target.appendChild(new_elem);
				toggle(target.parentNode, true);
			}
		}
	}
}

function del_attr()
{
	var elem = getE('product_att_list');
	var i;

	for (i = elem.length - 1; i >= 0; i--)
	{
		if (elem.options[i].selected)
			elem.remove(i);
	}
}

function add_attr()
{
	var attr_group = getE('attribute_group');
	var attr_name = getE('attribute');
	var opt = document.createElement('option');
	var elem = getE('product_att_list');
	var gs = attr_group.options.length ? attr_group.options[attr_group.selectedIndex] : 0;
	var as = attr_name.options.length ? attr_name.options[attr_name.selectedIndex] : 0;
	var list = getE('product_att_list');
	var i;

	if (gs.value == 0) alert('Please choose a group');
	else if (as.value == 0) alert('Please choose an attribute');
	else
	{
		opt.text = gs.text + ' : ' + as.text;
		opt.value = as.value;

		for (i = list.length - 1; i >= 0; i--)
			if (list.options[i].value == as.value)
			{
				alert('You cannot add same attribute twice');
				return;
			}

		try {
			elem.add(opt, null);
		}
		catch(ex) {
			elem.add(opt);
		}
	}
}

function openCloseLayer(whichLayer)
{
	if (document.getElementById)
		var style = document.getElementById(whichLayer).style;
	else if (document.all)
		var style = document.all[whichLayer].style;
	else if (document.layers)
		var style = document.layers[whichLayer].style;
	style.display = style.display == 'none' ? 'block' : 'none';
}