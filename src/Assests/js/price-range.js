const rangeInput = document.querySelectorAll(".range-input input"),
    priceInput = document.querySelectorAll(".price-input input"),
    range = document.querySelector(".slider .progress");
let priceGap = 1000;
priceInput.forEach(input =>{
    input.addEventListener("input", e =>{
        let minPrice = parseInt(priceInput[0].value),
            maxPrice = parseInt(priceInput[1].value);

        if((maxPrice - minPrice >= priceGap) && maxPrice <= rangeInput[1].max){
            if(e.target.className === "input-min"){
                rangeInput[0].value = minPrice;
                range.style.left = ((minPrice / rangeInput[0].max) * 100) + "%";
            }else{
                rangeInput[1].value = maxPrice;
                range.style.right = 100 - (maxPrice / rangeInput[1].max) * 100 + "%";
            }
        }
    });
});
rangeInput.forEach(input =>{
    input.addEventListener("input", e =>{
        let minVal = parseInt(rangeInput[0].value),
            maxVal = parseInt(rangeInput[1].value);
        if((maxVal - minVal) < priceGap){
            if(e.target.className === "range-min"){
                rangeInput[0].value = maxVal - priceGap
            }else{
                rangeInput[1].value = minVal + priceGap;
            }
        }else{
            priceInput[0].value = minVal;
            priceInput[1].value = maxVal;
            range.style.left = ((minVal / rangeInput[0].max) * 100) + "%";
            range.style.right = 100 - (maxVal / rangeInput[1].max) * 100 + "%";
        }
    });
});

var dealInput = document.getElementsByName('deal-filter');
var filterForm = document.getElementById('deal-filter-form');
var priceRangeInput = document.getElementById('price-range-input');
var dealFilterBtn = document.getElementById('deal-filter-btn');
if (dealInput.length > 0) {
    for (var d of dealInput) {
        d.addEventListener('click', filterClickEventHanler);
    }
}

dealFilterBtn.addEventListener('click', filterClickEventHanler);

function filterClickEventHanler(e) {
    var inputType = e.target.id;
    var filterType = 'all';
    if (inputType == 'deal-code') {
        filterType = 'code';
    } else if (inputType == 'deal-offer') {
        filterType = 'offer';
    } else if (inputType == 'deal-price' || inputType == 'deal-filter-btn') {
        filterType = 'price';
    } else if (inputType == 'deal-newest') {
        filterType = 'newest';
    }
    if (filterType == 'price') {
        priceRangeInput.classList.remove('price-range-hide');
    } else {
        priceRangeInput.classList.add('price-range-hide');
    }
    var hideInput = document.createElement('input');
    hideInput.setAttribute('class', 'hidden-deal-type');
    hideInput.setAttribute('name', 'dealType');
    hideInput.setAttribute('value', filterType);
    filterForm.append(hideInput);
    if (filterType === 'price') {
        var minPriceVal = parseInt(priceInput[0].value),
            maxPriceVal = parseInt(priceInput[1].value);
        var minPriceInput = document.createElement('input');
        minPriceInput.setAttribute('class', 'hidden-deal-type');
        minPriceInput.setAttribute('name', 'minPrice');
        minPriceInput.setAttribute('value', minPriceVal);

        var maxPriceInput = document.createElement('input');
        maxPriceInput.setAttribute('class', 'hidden-deal-type');
        maxPriceInput.setAttribute('name', 'maxPrice');
        maxPriceInput.setAttribute('value', maxPriceVal);
        filterForm.append(minPriceInput);
        filterForm.append(maxPriceInput);
    }
    filterForm.submit();
}