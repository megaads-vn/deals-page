<?php

namespace Megaads\DealsPage\Middlewares;

use Closure;
use Megaads\DealsPage\Models\Keypage;
use Megaads\DealsPage\Models\Store;
use Megaads\DealsPage\Models\Deal;
use Illuminate\Support\Facades\Redis;
use App\Utils\Utils;    

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
        $cacheExists = $this->isInterceptorCached($request->url());
        if ($cacheExists) {
            header('X-Cache-State: HIT');
            return $next($request);
        }
        $url = $request->url();
        
        $parsedUrl = parse_url($url);
        $routeMatch = \Route::getRoutes()->match($request);
        $getAction = $routeMatch->getAction();
        list($controller, $method) = explode('@', $getAction['controller']);
        $redirectTo = $this->detectRedirectUrl($parsedUrl);
        if (!empty($redirectTo)) {
            return redirect()->away($redirectTo, 301);
        }
        $isGoToOrigin = false;
        $url = $this->replaceToOriginDomain($url, $isGoToOrigin);
        if ($isGoToOrigin) {
            return redirect()->away($url, 301);
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
        $allDealsPattern = '/^\/alldeals$/';
        
        if (isset($parsedUrl['path']) && 
        (preg_match($reviewsPathPattern, $parsedUrl['path']) || preg_match($reviewsStorePattern, $parsedUrl['path']))) {
            $retVal = 'review';
        } else if (isset($parsedUrl['path']) && 
        (preg_match($dealsPathPattern, $parsedUrl['path']) || preg_match($dealsStorePattern, $parsedUrl['path']))) {
            $retVal = 'deal';
        } else if (isset($parsedUrl['path']) && preg_match($allDealsPattern, $parsedUrl['path'])) {
            $retVal = 'all_deals';
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
        $detailDealPattern = '/^\/deals\/([^\/]+)/';
        $subdomain = $this->getSubdomainFromCurrentHostName($parsedUrl);
        $pageType = $this->checkCurrentRequestType($parsedUrl);
        
        if ($subdomain && isset($parsedUrl['path']) && preg_match($pattern, $parsedUrl['path']) && $pageType == 'deal') {
            $buildUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . '/deals';
            $retVal = preg_replace($pattern, '', $buildUrl);
        } else if ($subdomain && $pageType == 'all_deals') {
            $buildUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . '/alldeals';
            $retVal = preg_replace("/{$subdomain}./", '', $buildUrl);
        } else if (!$subdomain && isset($parsedUrl['path']) && preg_match($pattern, $parsedUrl['path']) && $pageType == 'deal') {
            preg_match($pattern, $parsedUrl['path'], $matches);
            if (!empty($matches[1]))  {
                $store = Store::where('slug', $matches[1])->first(['slug']);
                if (!empty($store)) {  
                    $retVal = $parsedUrl['scheme'] . '://' . $store->slug . '.' . $parsedUrl['host'] . '/deals';
                }
            }
        } else if (!$subdomain && isset($parsedUrl['path']) && preg_match($detailDealPattern, $parsedUrl['path'])) {
            preg_match($detailDealPattern, $parsedUrl['path'], $matches);
            if (!empty($matches[1]))  {
                $deal = Deal::where('slug', $matches[1])->first(['store_id']);
                if (!empty($deal)) {  
                    $store = Store::where('id', $deal->store_id)->first(['slug']);
                    if (!empty($store)) {
                        $retVal = $parsedUrl['scheme'] . '://' . $store->slug . '.' . $parsedUrl['host'] . '/deals/' . $matches[1];
                    }
                }
            }
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

    private function isInterceptorCached($url)
    {
        $interceptorConfig = config('interceptor.enable', false);  
        if (!$interceptorConfig) return false;
        $interceptorConnection = config('interceptor.cacheConnection', 'cache');
        $interceptorAppName = config('interceptor.appName', 'interceptor');
        $saveToFile = config('interceptor.saveToFile', false);
        $deviceDetected = $this->deviceDetected();
        $key = $interceptorAppName . '::' . $deviceDetected . '::' . $url;
        $retVal = false;
        
        if ($saveToFile === false) {
            try {
                $redis = Redis::connection($interceptorConnection);
                $keyExists = $redis->exists($key);
                if ($keyExists) {
                    $retVal = true;
                }
            } catch (\Exception $ex) {}
        } else {
            $directory = storage_path('cache/interceptor/' . $interceptorAppName);
            $filename = md5($key);
            $filePath = $directory . '/' . $filename;
            if (file_exists($filePath)) {
                $retVal = true;
            }
        }
        return $retVal; 
    }

    private function deviceDetected() {
        $retVal = 'desktop';
        if (Utils::isMobile()) {
            $retVal = 'mobile';
        } else if (Utils::isTablet()) {
            $retVal = 'tablet';
        }
        return $retVal;
    }
    
    private function replaceToOriginDomain($currentUrl, &$isRedirect) {
        $retVal = $currentUrl;
        $parseCurrentUrl = parse_url($currentUrl);
        $baseUrl = env('APP_URL');
        $parseUrl = parse_url($baseUrl);
        $host = $parseUrl['host'];
        preg_match("/https?:\/\/(.*?)\.{$host}/i", $currentUrl, $matched);
        if (preg_match('/coupon-category/', $currentUrl) && isset($matched[1])) {
            $retVal = env('APP_URL') . '' . $parseCurrentUrl['path'];
            $isRedirect = true;
        }
        return $retVal;
    }
}
