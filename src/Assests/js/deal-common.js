var viewMoreDeal = document.getElementById('js-view-more-deals');
var currentPage = document.getElementById('deal-current-id');
var filterType = document.getElementById('deal-filter-type');

function validateElements() {
    var continueHandle = true;
    if (typeof isLoading === 'undefined') {
        console.log('Loading state is undefine.');
        continueHandle = false;
    } else if (typeof viewMoreDeal === 'undefined' || !viewMoreDeal) {
        console.log('Loadmore element is undefine.');
        continueHandle = false;
    } else if (typeof currentPage === 'undefined' || !currentPage) {
        console.log('Unknow current page.');
        continueHandle = false;
    } else if (typeof  filterType === 'undefined' || !filterType) {
        console.log('Unknow deal type will be filtered.');
        continueHandle = false;
    }
    return continueHandle;
}

function buildFilter(e) {
    var retVal = {
        page_size: 5,
        current_page: parseInt(currentPage.value) + 1,
        dealType: filterType.value
    };

    var target = e.target.getAttribute('data-target');
    var targetId = e.target.getAttribute('data-target-id');
    if (target == 'category') {
        retVal.category_id = parseInt(targetId);
    } else if (target === 'store') {
        retVal.store_id = parseInt(targetId);
    }

    return retVal;
}

document.addEventListener("DOMContentLoaded", function(event) {
    if (typeof viewMoreDeal !== "undefined" && viewMoreDeal) {
        viewMoreDeal.addEventListener("click", function(e) {
            e = e || window.event;
            e.preventDefault();
            var continueHandle = validateElements();

            if (isLoading) return;
            if (!continueHandle) return;
            isLoading = true;
            var dealRequestFilter = buildFilter(e);

            if (filterType === 'price') {
                var minPrice = document.getElementById('input-min').value;
                var maxPrice = document.getElementById('input-max').value;
                dealRequestFilter.minPrice = minPrice;
                dealRequestFilter.maxPrice = maxPrice;
            }

            var queryString = Object.keys(dealRequestFilter).map((key) => {
                return encodeURIComponent(key) + '=' + encodeURIComponent(dealRequestFilter[key])
            }).join('&');

            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
                if (this.readyState === 4 && this.status === 200) {
                    var response = JSON.parse(this.responseText);
                    if (response.status === 'successful') {
                        if (!response.has_next) {
                            viewMoreDeal.style.display = "none";
                        }
                        currentPage.value = dealRequestFilter.current_page;
                        document.getElementById("deal-items-wrapper").insertAdjacentHTML("afterend", response.data);
                    }

                    setTimeout(() => {
                        isLoading = false;
                    }, 300);

                }
            };
            xmlhttp.open("GET", "/category-store/deals/load-more?" + queryString, true);
            xmlhttp.setRequestHeader("Authorization", "Basic YXBpOjEyM0AxMjNh")
            xmlhttp.send();
        });
    }
});

document.addEventListener("click", function(e) {
    var elClass = e.target.getAttribute("class");
    if (elClass.indexOf("js-go-deals") !== -1) {
        var itemId = e.target.getAttribute('data-id');
        var originUrl = window.location.origin;
        var fullUrl = `${originUrl}/go-deal/${itemId}`;
        window.open(`${originUrl}/go-deal/${itemId}`);
    }
});