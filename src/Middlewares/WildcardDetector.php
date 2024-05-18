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
        $parsedUrl = parse_url($url);
        
        $routeMatch = \Route::getRoutes()->match($request);
        $getAction = $routeMatch->getAction();
        list($controller, $method) = explode('@', $getAction['controller']);
        $redirectTo = $this->detectRedirectUrl($parsedUrl);
        if (!empty($redirectTo)) {
            return redirect()->away($redirectTo, 302);
        }
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
    protected function detectRedirectUrl($parsedUrl) {
        // Parse the URL
        $retVal = ""; 
        $pattern = '/store\/([^\/]+)/';
        $subdomain = $this->getSubdomainFromCurrentHostName($parsedUrl);
        $pageType = $this->checkCurrentRequestType($parsedUrl);

        if ($subdomain && isset($parsedUrl['path']) && preg_match($pattern, $parsedUrl['path']) && $pageType == 'deal') {
            $buildUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . '/deals';
            $retVal = preg_replace($pattern, '', $buildUrl);
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

    /**
     * Call DealsController@storeDeal
     * 
     * @param Store $store
     * @param Request $request
     * @return mixed
     */
    private function callDealAction(Store $store, $request) {
        if (class_exists('Megaads\DealsPage\Controllers\DealsController')) {
            $controller = app()->make('Megaads\DealsPage\Controllers\DealsController');
            $response = app()->call([$controller, 'storeDeal'], ['slug' => $store->slug, 'request' => $request]);   
            return $response;
        }
    }
}
