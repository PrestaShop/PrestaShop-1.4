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
	var priceTE = parseFloat(document.getElementById('priceTE').value);
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
	var priceTI = parseFloat(document.getElementById('priceTI').value);
	var newPrice = priceTI / ((tax / 100) + 1);
	document.getElementById('priceTE').value =	(isNaN(newPrice) == true || newPrice < 0) ? '' :
	 											formatPrice(newPrice).toFixed(6);
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
	var price = document.getElementById('priceTI');
	var newprice = document.getElementById('finalPrice');
	var rprice = document.getElementById('reduction_price');
	document.getElementById('reduction_percent').value = 0;
	if (parseFloat(price.value) <= parseFloat(rprice.value))
		rprice.value = price.value;
	if (parseFloat(rprice.value) < 0 || isNaN(parseFloat(price.value)))
		rprice.value = 0;
	newprice.innerHTML = (price.value - rprice.value).toFixed(2);
}

function reductionPercent()
{
	var price = document.getElementById('priceTI');
	var newprice = document.getElementById('finalPrice');
	var rpercent = document.getElementById('reduction_percent');
	document.getElementById('reduction_price').value = 0;
	if (parseFloat(rpercent.value) >= 100)
		rpercent.value = 100;
	if (parseFloat(rpercent.value) < 0)
		rpercent.value = 0;
	newprice.innerHTML = (price.value * (1 - (rpercent.value / 100))).toFixed(2);
	
}