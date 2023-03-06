<?php

namespace Megaads\DealsPage\Middlewares;

use Closure;
use Megaads\DealsPage\Models\Keypage;

class KeypageRouting
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
        $routeMatch = \Route::getRoutes()->match($request);
        $getAction = $routeMatch->getAction();
        list($controller, $method) = explode('@', $getAction['controller']);
        $controller = explode('\\', $controller);
        $controller = end($controller);
        if ($controller === 'StoreKeywordController') {
            $params = $routeMatch->parameters();
            if (isset($params['slug'])) {
                $findKeypage = Keypage::query()
                                    ->where('is_deal', 1)
                                    ->where('slug', $params['slug'])
                                    ->first(['id']);
                if (!empty($findKeypage)) {
                    return app()->call('\Megaads\DealsPage\Controllers\KeywordController@index', $params);
                }
            }
        }
        return $next($request);
    }
}
