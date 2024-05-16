<?php

namespace Megaads\DealsPage\Middlewares;

use Closure;
use Megaads\DealsPage\Models\Keypage;
use Megaads\DealsPage\Models\Store;

class WildcardDetector
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $url = $request->url();
        $pageType = $this->checkCurrentRequestType(parse_url($url));
        $routeMatch = \Route::getRoutes()->match($request);
        $getAction = $routeMatch->getAction();
        list($controller, $method) = explode('@', $getAction['controller']);
        return $next($request);
    }

    
    protected function callStoreAction(Store $store, $keypageSlug, $request) {
        $controller = app()->make('App\Http\Controllers\Frontend\StoreKeywordController');
        $response = app()->call([$controller, 'index'], ['slug' => $keypageSlug, 'request' => $request]);   
        return $response;
    }

    /**
     * Check if subdomain is a store
     * 
     * @param string $subdomain
     * @return Store|null
     */
    private function checkSubdomainIsStore($subdomain) {
        $retVal = NULL;
        $store = Store::where('slug', $subdomain)->first(['id', 'title', 'slug']);
        if (!empty($store)) {
            $retVal = $store;
        }
        return $retVal;
    }

    /**
     * Check current request type
     * 
     * @param string $url
     * @return string
     */
    private function checkCurrentRequestType($parsedUrl) {
        $retVal = '';
        $reviewsPathPattern = '/^\/reviews$/';
        $reviewsStorePattern = '/^\/store\/[^\/]+\/reviews$/';
        $dealsPathPattern = '/^\/deals$/';
        $dealsStorePattern = '/^\/store\/[^\/]+\/deals$/';

        if (isset($parsedUrl['path']) && 
        (preg_match($reviewsPathPattern, $parsedUrl['path']) || preg_match($reviewsStorePattern, $parsedUrl['path']))) {
            $retVal = 'review';
        } else if (isset($parsedUrl['path']) && 
        (preg_match($dealsPathPattern, $parsedUrl['path']) || preg_match($dealsStorePattern, $parsedUrl['path']))) {
            $retVal = 'deal';
        } else if (!isset($parsedUrl['path'])) {
            $retVal = 'store';
        }
        return $retVal;
    }

    /**
     * Get subdomain from current host name
     * 
     * @param array $parsedUrl
     * @return string
     */
    protected function detectRedirectUrl($url) {
        // Parse the URL
        $parsedUrl = parse_url($url);
        $retVal = '';
        if (isset($parsedUrl['path']) && !empty($parsedUrl['path'])) {
            $patternKeypage = '~^/[^/]+$~';
            $patternReview = '~^/store/[^/]+/reviews$~';
            $patternDeal = '~^/store/[^/]+/deals$~';
            $patternStore = '~^/store/[^/]+$~';
            $pattern = '/store\/([^\/]+)/';
            $store = '';
            $subdomain = $this->getSubdomainFromCurrentHostNamt($parsedUrl);
            if (empty($subdomain)) {
                // match keypage
                if (preg_match($patternKeypage, $parsedUrl['path'], $matches)) {
                    if (isset($matches[0])) {
                        $slug = str_replace('/', '', $matches[0]);
                        $item = \DB::table('store_n_keyword as snk')
                                    ->leftJoin('store as s', 's.id', '=', 'snk.store_id')
                                    ->where('snk.slug', '=', $slug)
                                    ->first(['s.slug']);
                        if ($item) {
                            $store = $this->getStoreFromDomain($item->slug);
                            $retVal = $parsedUrl['scheme'] . '://' . $store . '.' . $parsedUrl['host'] . $parsedUrl['path'];
                        }
                    }
                }
                
                
                if (empty($retVal) && preg_match($pattern, $parsedUrl['path'], $matches)) {
                    if (isset($matches[1])) {
                        $store = $this->getStoreFromDomain($matches[1]);
                    }
                }
                // match store
                if (!empty($store) && preg_match($patternStore, $parsedUrl['path'])) {
                    $retVal = $parsedUrl['scheme'] . '://' . $store . '.' . $parsedUrl['host'];
                } 
                // match review
                if (!empty($store) && preg_match($patternReview, $parsedUrl['path'])) {
                    $retVal = $parsedUrl['scheme'] . '://' . $store . '.' . $parsedUrl['host'] . '/reviews';
                } 
                // match deal
                if (!empty($store) && preg_match($patternDeal, $parsedUrl['path'])) {
                    $retVal = $parsedUrl['scheme'] . '://' . $store . '.' . $parsedUrl['host'] . '/deals';
                } 
            }

        }
        if (!empty($retVal)) {
            $retVal .= isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '';
        }
        return $retVal;
    }

    /**
     * Get store from domain
     * 
     * @param string $domain
     * @return string
     */
    protected function getStoreFromDomain($domain) {
        return $domain;
        // $names = explode('.', $domain);
        // array_pop($names);
        // return implode('.', $names);
    }

    /**
     * Get subdomain from current host name
     * 
     * @param array $parsedUrl
     * @return string
     */
    protected function getSubdomainFromCurrentHostName($parsedUrl) {
            $host = $parsedUrl['host'];
            $host = preg_replace('/(http|https):\/\//', '', $host);
            $appUrl = preg_replace('/(http|https):\/\//', '', env('APP_URL'));
            $subdomain = preg_replace('/'.$appUrl.'/', '', $host);
            $subdomain = rtrim($subdomain, '.');
            return $subdomain;
    }
}
