@if (isset($storeContact) && isset($store))
    @php
        $storeUrl = route("frontend::store::goStore", ['slug' => $store->slug]);
        $storeImage = isset($store) ? env('APP_URL').'/images/stores/' . $store->coverImage : '';
        $placehoderImage = "/images/blank.gif";
        $rating = !empty($store->crawl_rating) ? $store->crawl_rating : $store->voteUp;
        $ratingCount = !empty($store->crawl_rating_count) ? $store->crawl_rating_count : $store->voteDown;
    @endphp
<div class="contact-info-contain">
    <div class="contact-header">
        <h3 class="contact-title">
            {{ $store->title }}
        </h3>
        <a class="contact-image" href="{{ $store->affiliateUrl }}" rel="nofollow">
            <img class="img-responsive lazy" src="{{ $placehoderImage }}" data-src="{{ reSizeImage("/images/stores/" . $store->coverImage, 122, 0) }}"  alt="<?= $store->title ?> Coupons & Promo codes">
        </a>
    </div>
    <div class="gotostore" style="display: block;">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-link-45deg" viewBox="0 0 16 16">
            <path d="M4.715 6.542L3.343 7.914a3 3 0 1 0 4.243 4.243l1.828-1.829A3 3 0 0 0 8.586 5.5L8 6.086a1.001 1.001 0 0 0-.154.199 2 2 0 0 1 .861 3.337L6.88 11.45a2 2 0 1 1-2.83-2.83l.793-.792a4.018 4.018 0 0 1-.128-1.287z"/>
            <path d="M6.586 4.672A3 3 0 0 0 7.414 9.5l.775-.776a2 2 0 0 1-.896-3.346L9.12 3.55a2 2 0 0 1 2.83 2.83l-.793.792c.112.42.155.855.128 1.287l1.372-1.372a3 3 0 0 0-4.243-4.243L6.586 4.672z"/>
        </svg>
        <a href="{{ $store->affiliateUrl }}" target="_blank" rel="nofollow">
            {{ $store->slug }}
        </a>
        @if (isset($showRating) && $showRating)
        <div class="star-rating storekeyword-rating-wrap">
            <div class="rate storekeyword-rating">
                <input type="radio" id="star5" <?= round($store->voteUp) == 5?'checked':'' ?> name="rate" value="5" />
                <label data-id="{{ $store->id }}" class="js-vote" for="star5" title="5 stars">5 stars</label>
                <input type="radio" id="star4" <?= round($store->voteUp) == 4?'checked':'' ?> name="rate" value="4" />
                <label data-id="{{ $store->id }}" class="js-vote" for="star4" title="4 stars">4 stars</label>
                <input type="radio" id="star3" name="rate" value="3" />
                <label data-id="{{ $store->id }}" class="js-vote" for="star3" title="3 stars">3 stars</label>
                <input type="radio" id="star2" name="rate" value="2" />
                <label data-id="{{ $store->id }}" class="js-vote" for="star2" title="2 stars">2 stars</label>
                <input type="radio" id="star1" name="rate" value="1" />
                <label data-id="{{ $store->id }}" class="js-vote" for="star1" title="1 star">1 star</label>
            </div>
            <span class="count-rating store-count-rating">
                {{ $rating }} from <span>{{ $ratingCount }}</span> users
            </span>
            <div class="js-vote-message"></div>
        </div>
        @endif
    </div>
    <div class="contact-info-desc">
        {!! $storeContact->content !!}
    </div>
    <div class="contact-info-social">
        <div class="contact-title">
            Profiles
        </div>

        <ul class="social-list">
            @if (!empty($storeContact->facebook_url))
            <li>
                <a class="social-icon" rel="noreferrer nofollow" href="{{ $storeContact->facebook_url }}" target="_blank" title="facebook">
                    <svg class="facebook-icon" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
                        <path d="m483.738281 0h-455.5c-15.597656.0078125-28.24218725 12.660156-28.238281 28.261719v455.5c.0078125 15.597656 12.660156 28.242187 28.261719 28.238281h455.476562c15.605469.003906 28.257813-12.644531 28.261719-28.25 0-.003906 0-.007812 0-.011719v-455.5c-.007812-15.597656-12.660156-28.24218725-28.261719-28.238281zm0 0" fill="#4267b2"></path>
                        <path d="m353.5 512v-198h66.75l10-77.5h-76.75v-49.359375c0-22.386719 6.214844-37.640625 38.316406-37.640625h40.683594v-69.128906c-7.078125-.941406-31.363281-3.046875-59.621094-3.046875-59 0-99.378906 36-99.378906 102.140625v57.035156h-66.5v77.5h66.5v198zm0 0" fill="#fff"></path>
                    </svg>
                    <span>Facebook</span>
                </a>
            </li>
            @endif
            @if (!empty($storeContact->twitter_url))
            <li>
                <a class="social-icon" rel="noreferrer nofollow" href="{{ $storeContact->twitter_url }}" target="_blank" title="twitter">
                    <svg class="twitter-icon" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 455.731 455.731" style="enable-background:new 0 0 455.731 455.731;" xml:space="preserve">
                        <rect x="0" y="0" style="fill:#50ABF1;" width="455.731" height="455.731"></rect>
                        <path fill="#FFFFFF" d="M60.377,337.822c30.33,19.236,66.308,30.368,104.875,30.368c108.349,0,196.18-87.841,196.18-196.18 c0-2.705-0.057-5.39-0.161-8.067c3.919-3.084,28.157-22.511,34.098-35c0,0-19.683,8.18-38.947,10.107 c-0.038,0-0.085,0.009-0.123,0.009c0,0,0.038-0.019,0.104-0.066c1.775-1.186,26.591-18.079,29.951-38.207 c0,0-13.922,7.431-33.415,13.932c-3.227,1.072-6.605,2.126-10.088,3.103c-12.565-13.41-30.425-21.78-50.25-21.78 c-38.027,0-68.841,30.805-68.841,68.803c0,5.362,0.617,10.581,1.784,15.592c-5.314-0.218-86.237-4.755-141.289-71.423 c0,0-32.902,44.917,19.607,91.105c0,0-15.962-0.636-29.733-8.864c0,0-5.058,54.416,54.407,68.329c0,0-11.701,4.432-30.368,1.272 c0,0,10.439,43.968,63.271,48.077c0,0-41.777,37.74-101.081,28.885L60.377,337.822z"></path>
                    </svg>
                    <span>Twitter</span>
                </a>
            </li>
            @endif
            @if (!empty($storeContact->youtube_url))
            <li>
                <a class="social-icon" rel="noreferrer nofollow" href="{{ $storeContact->youtube_url }}" target="_blank" title="youtube">
                    <svg  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><defs><style>.cls-1{fill:#dd352e;}.cls-2{fill:#fff;}</style></defs><path class="cls-1" d="M461.73,512H50.27A50.27,50.27,0,0,1,0,461.73V50.27A50.27,50.27,0,0,1,50.27,0H461.73A50.27,50.27,0,0,1,512,50.27V461.73A50.27,50.27,0,0,1,461.73,512Z"/><path class="cls-2" d="M162.31,372.49v-233a15.6,15.6,0,0,1,23.53-13.43L383,242.57a15.6,15.6,0,0,1,0,26.85L185.84,385.91A15.59,15.59,0,0,1,162.31,372.49Z"/></svg>
                    <span>Youtube</span>
                </a>
            </li>
            @endif
        </ul>
    </div>
</div>
@endif
