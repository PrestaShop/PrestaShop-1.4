var	selects = new Array;
var	combinaisons = new Array;
var quantities = new Array;
var prices = new Array;
var ecotaxes = new Array;
var images = new Array;
var attr_price = new Number(0);
var product_price = new Number;
var attr_ecotax = new Number(0);
var img_prod_dir = new String;
var id_product = new Number;
var	oosp = new Number;
var last_qties = new Number;
var total_qties = new Number;

function	addSelect(id_attribute_group, group_name)
{
	selects[id_attribute_group] = group_name;
}

function writeQuantity()
{
	if (typeof(nbpiece) != 'undefined' && (nbpiece > 0 || oosp == 1))
	{
    	getE('add_to_cart').style.display = 'block';
   		getE('last_qties').style.display = ((nbpiece < last_qties && (nbpiece > 0 && oosp != 1)) ? 'block' : 'none');
		checkQty();
	}
	else
	{
	    getE('add_to_cart').style.display = 'none';
	}
}

function writeAvailability()
{
	if (getE('qtyav'))
	{
		getE('qtyav').innerHTML = (typeof(availabilityMessage) != 'undefined' ? availabilityMessage : '');
		if (typeof(nbpiece) != 'undefined' && (nbpiece > 0 || oosp == 1))
		{
			if (displayQties)
				getE('qtyav').innerHTML += (getE('qtyav').innerHTML != '' ? '<br />' : '') + nbpiece + ' ' + (nbpiece > 1 ? pieces : piece);
		}
		else if (typeof(nbpiece) != 'undefined' && (nbpiece == 0 && oosp == 0))
		{
	        getE('qtyav').innerHTML = (typeof(availabilityMessage) != 'undefined' ? availabilityMessage : outofstock);
		}
		else if (noAttribut == 0)
		{
			getE('qtyav').innerHTML = ' ' + doesntexist + (total_qties ? ' ' + doesntexist_but : '');
		}
		toggle(getE('divqtyav'), (getE('qtyav').innerHTML != ''));
 	}
 	writeQuantity();
}

function	checkQty()
{
	if (getE('qty').value != '' && getE('qty').value <= 0)
   		getE('qty').value = 1;
	else if (getE('qty').value > nbpiece && oosp == 0)
		getE('qty').value = nbpiece;
}

function    updatePrice()
{
	var tax = (taxRate / 100) + 1;
	var attribut_price = attr_price;
	if (no_tax > 0)
		attribut_price /= tax;
    var priceProductWR = (attribut_price + product_price_without_reduct) * currency_rate;
    var priceReduct = priceProductWR / 100 * parseInt(reduction_percent) + reduction_price;
    var priceProduct = priceProductWR - priceReduct;
    var priceProductHT = (priceProductWR - priceReduct) / tax;

    getE('price').innerHTML = (typeof(priceText) != 'undefined' ? priceText + ' ' : '') + (currency_format == 1 ? currency_sign + ' ' : '') + (priceProduct).toFixed(priceDisplayPrecision) + (currency_format == 2 ? ' ' + currency_sign : '');
    if (getE('pretax-price'))
		getE('pretax-price').innerHTML = (typeof(priceText) != 'undefined' ? priceText + ' ' : '') + (currency_format == 1 ? currency_sign + ' ' : '') + (priceProductHT).toFixed(priceDisplayPrecision) + (currency_format == 2 ? ' ' + currency_sign : '');
    if (getE('price_without_reduct'))
        getE('price_without_reduct').innerHTML = (currency_format == 1 ? currency_sign + ' ' : '') + (priceProductWR).toFixed(priceDisplayPrecision) + (currency_format == 2 ? ' ' + currency_sign : '');
}

function	updateEcotax()
{
	if (attr_ecotax != 0)
		getE('ecotax').innerHTML = '&eacute;co-participation : ' + (attr_ecotax * currency_rate) + ' ' + currency_sign;
	else
		getE('ecotax').innerHTML = '';
}

function	addCombinaison(id_product_attribute, attributes, quantity, price, ecotax, id_image)
{
	quantities[id_product_attribute] = quantity;
	total_qties += quantity;
	combinaisons[id_product_attribute] = attributes;
	prices[id_product_attribute] = price;
	ecotaxes[id_product_attribute] = ecotax;
	images[id_product_attribute] = id_image;
}

function	in_array(val, arr)
{
	for (var i in arr)
		if (arr[i] == val)
    		return true;
	return false;    	
}

function	findCombinaison()
{
	var currentComb;
	var nbOk = new Number(0);
	var choices = new Array;
	var link = new String();
	
	/* first is true when called for first time */
	
	for (keyVar in selects)
	{
	   current = selects[keyVar];
	   choices.push(eval(getE(current).value));
	}

	for (keyComb in combinaisons)
	{
		for (id_attribute in combinaisons[keyComb])
		{
			if (in_array(combinaisons[keyComb][id_attribute], choices))
				nbOk++;
		}

		if (nbOk == combinaisons[keyComb].length)
		{
			getE('id_product_attribute').value = keyComb;
			nbpiece = quantities[keyComb];
		    writeAvailability();
			attr_price = prices[keyComb];
			updatePrice();
			attr_ecotax = ecotaxes[keyComb];
			updateEcotax();

			if (images[keyComb] && images[keyComb] != -1)
			{
			 	link = img_prod_dir + id_product + '-' + images[keyComb] + '-large.jpg';
				getE('bigpic').src = link;
				onClickImage(images[keyComb], '', link);
			}
			
			return;
		}
		
		nbOk = 0;
	}
	nbpiece = -1;
	writeAvailability();
	return;
}

function getE(name)
{
	if (document.getElementById)
		var elem = document.getElementById(name);
	else if (document.all)
		var elem = document.all[name];
	else if (document.layers)
		var elem = document.layers[name];
	return elem;
}
