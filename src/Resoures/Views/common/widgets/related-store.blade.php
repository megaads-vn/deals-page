<?php
$placehoderImage = "/images/blank.gif";
$result = getRelatedStore([
    'categoryId' => isset($categoryId) ? $categoryId : -1 ,
    'relatedStore' => isset($storeRelated) ? $storeRelated : -1,
    'storeId' => isset($storeId) ? $storeId : -1 ]);
$stores = $result->stores;
$hasNextPage = $result->hasNextPage;
if (isset($items) && count($items) > 0) {
    $stores = $items;
}
$bfTitle = isset($beforeTitle) ? $beforeTitle : '';
$afTitle = isset($afterTitle) ? $afterTitle : '';
$routeName = getCustomRoute(isset($customRouteType) ? $customRouteType : '');
?>
@if(isset($stores) && count($stores) > 0)
<div class="widget">
    <div class="widget-title font-alt">
        {!! $bfTitle . (isset($widgetTitle) ? $widgetTitle : 'Related Stores') . $afTitle !!}
    </div>
    <div class="favorite-related-stores" id="relatedStore">
        @foreach($stores as $store)
            <div class="related-item">
                <a class="related-link" href="<?= route($routeName, [ 'slug' => $store['slug'] ]); ?>" target="_blank" title="<?= $store['title']; ?>">
                    <div class="related-image">
                        <img src="{{ $placehoderImage }}" width="122" height="71" data-src="{{ App\Utils\Utils::reSizeImage("/images/stores/" . $store['coverImage'], 220, 0) }}" class="img-responsive lazy" alt="{{ $store['title'] }} Coupons & Promo codes">
                    </div>
                    <div class="related-title">
                        <?= $store['title']; ?> Coupons
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</div>
<script>
    var ralatedStoreCategoryId = {{ (isset($categoryId) && $categoryId)? $categoryId : 'null' }};
    var storeRelated = {{ (isset($storeRelated) && $storeRelated)? $categoryId : 'null' }};
    var ralatedStoreStoreId = {{ (isset($storeId) && $storeId)? $storeId : 'null' }};
    var ralatedStorePageId = 1;
    var isLoading = false;
    var appUrl = '{{ env('APP_DOMAIN') }}' ;
    var appLang = '{{ env('APP_LANG') }}' ;

    function validURL(str) {
        var pattern = new RegExp('^(https?:\\/\\/)?'+ // protocol
            '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|'+ // domain name
            '((\\d{1,3}\\.){3}\\d{1,3}))'+ // OR ip (v4) address
            '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*'+ // port and path
            '(\\?[;&a-z\\d%_.~+=-]*)?'+ // query string
            '(\\#[-a-z\\d_]*)?$','i'); // fragment locator
        return !!pattern.test(str);
    }

    function getImageCdn($url, $width = 0, $height = 0, $fitIn = true, $webp = false) {
        $originUrl = $url;
        if ($url.substr(0, 4) == 'http') {
            $url = $url.replace('https://', '');
            $url = $url.replace('http://', '');
        }

        if(!validURL($url))
            $url = 'https://couponforless.com/' + $url; //window.location.origin

        $baseCdnUrl = "https://cfl.agoz.me/unsafe/";
        $fitIn = ($fitIn && $width && $height);
        // $fitIn = false;
        if ($fitIn) {
            $baseCdnUrl += "fit-in/";
        }
        if ($width || $height) {
            $baseCdnUrl += $width + "x" + $height + "/";
        }
        if ($fitIn || $webp) {
            $baseCdnUrl += "filters";
        }
        if ($fitIn) {
            $baseCdnUrl += "-fill-fff-";
        }
        if ($webp) {
            $baseCdnUrl += "-format-webp-";
        }
        if ($fitIn || $webp) {
            $baseCdnUrl += "/";
        }
        $baseCdnUrl += $url;
        return $baseCdnUrl;
    }

    function relatedStoreTemplate(store) {
        var storeUrl = `https://${store.slug}.${appUrl}`;
        if (appLang !== '') {
            storeUrl = `https://${store.slug}.${appUrl}/${appLang}`;
        }
        var divEl = document.createElement('div');
        divEl.className = 'related-item';
         
        var aEl = document.createElement('a');
        aEl.className = 'related-link';
        aEl.target = '_blank';
        aEl.href = storeUrl;
        aEl.title = store.title;
        aEl.innerHTML = store.title + ' Coupons';

        divEl.appendChild(aEl);
        return divEl;
    };

    function loadMoreRelatedStore() {
        $('#js-loadmore-store').hide();
        // $('#relatedStore').addClass('loadedmore');

        var dataParams = {
            category_id: ralatedStoreCategoryId,
            store_related: storeRelated,
            store_id: ralatedStoreStoreId,
            page_size: 12,
            page_id: ralatedStorePageId
        };

        var relatedStoreQueryString = Object.keys(dataParams).map((key) => {
            return encodeURIComponent(key) + '=' + encodeURIComponent(dataParams[key])
        }).join('&');

        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                var data = JSON.parse(this.responseText);
                if(data.status == 'successfully' && typeof data.result.data !== 'undefined' && data.result.data.length) {
                    data.result.data.forEach(function(store) {
                        $('#relatedStore').append(relatedStoreTemplate(store));
                    });

                    ralatedStorePageId ++;
                }

                // worst case data.result.length == dataParams.page_size
                // last click to hide button
                if(
                    data.result.data.length == 0 || data.result.data.length < dataParams.page_size
                    || dataParams.page_id  >= data.result.pagesCount - 1
                ) {
                    $('#js-loadmore-store').hide();
                } else {
                    $('#js-loadmore-store').show();
                }

                if(data.result.data.length == 0) {
                    $('.loadmore-favorite').append('<p>No more store</p>');
                }

                $('#relatedStore').scrollTop($("#relatedStore")[0].scrollHeight);

            }
        };
        xmlhttp.open("GET", "/service/related-store?" + relatedStoreQueryString, true);
        xmlhttp.send();
    }
</script>
@endif
