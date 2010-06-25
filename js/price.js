function getTax()
{
	if (noTax)
		return 0;
	var selectedTax = document.getElementById('id_tax');
	var taxId = selectedTax.options[selectedTax.selectedIndex].value;
	return taxesArray[taxId];
}

function formatPrice(price)
{
	var fixedToSix = (Math.round(price * 1000000) / 1000000);
	return (Math.round(fixedToSix) == fixedToSix + 0.000001 ? fixedToSix + 0.000001 : fixedToSix);
}

function calcPriceTI()
{
	var tax = getTax();
	var priceTE = parseFloat(document.getElementById('priceTE').value.replace(/,/g, '.'));
	var newPrice = priceTE *  ((tax / 100) + 1);
	document.getElementById('priceTI').value = (isNaN(newPrice) == true || newPrice < 0) ? '' : 
												formatPrice(newPrice).toFixed(6);
	document.getElementById('finalPrice').innerHTML = (isNaN(newPrice) == true || newPrice < 0) ? '' : 
												formatPrice(newPrice).toFixed(2);
	calcReduction();
}

function calcPriceTE()
{
	var tax = getTax();
	var priceTI = parseFloat(document.getElementById('priceTI').value.replace(/,/g, '.'));
	var newPrice = priceTI / ((tax / 100) + 1);
	document.getElementById('priceTE').value =	(isNaN(newPrice) == true || newPrice < 0) ? '' :
	 											floorf(newPrice, 6);
	document.getElementById('finalPrice').innerHTML = (isNaN(newPrice) == true || newPrice < 0) ? '' : 
												formatPrice(priceTI).toFixed(2);
	calcReduction();
}

function calcReduction()
{
	if (parseFloat(document.getElementById('reduction_price').value) > 0)
		reductionPrice();
	else if (parseFloat(document.getElementById('reduction_percent').value) > 0)
		reductionPercent();
}

function reductionPrice()
{
	var price    = document.getElementById('priceTI');
	var newprice = document.getElementById('finalPrice');
	var curPrice = price.value;

	
	document.getElementById('reduction_percent').value = 0;
	if (isInReductionPeriod())
	{
		var rprice = document.getElementById('reduction_price');
		if (parseFloat(price.value) <= parseFloat(rprice.value))
			rprice.value = price.value;
		if (parseFloat(rprice.value) < 0 || isNaN(parseFloat(price.value)))
			rprice.value = 0;
		curPrice = price.value - rprice.value;
	}
	
	newprice.innerHTML = parseFloat(curPrice).toFixed(2);
	
	
}

function reductionPercent()
{
	var price    = document.getElementById('priceTI');
	var newprice = document.getElementById('finalPrice');
	var curPrice = price.value;
	
	document.getElementById('reduction_price').value = 0;
	if (isInReductionPeriod())
	{
		var newprice = document.getElementById('finalPrice');
		var rpercent = document.getElementById('reduction_percent');

		if (parseFloat(rpercent.value) >= 100)
			rpercent.value = 100;
		if (parseFloat(rpercent.value) < 0)
			rpercent.value = 0;
			
		curPrice = price.value * (1 - (rpercent.value / 100));
	}
	
	newprice.innerHTML = parseFloat(curPrice).toFixed(2);
	
}

function isInReductionPeriod()
{
	var start  = document.getElementById('reduction_from').value;
	var end    = document.getElementById('reduction_to').value;
	
	if (start == end) return true;

	var sdate  = new Date(start.replace(/-/g,'/'));	
	var edate  = new Date(end.replace(/-/g,'/'));
	var today  = new Date();

	return (sdate <= today && edate >= today);
}


