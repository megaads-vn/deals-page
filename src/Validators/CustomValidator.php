<?php
namespace Megaads\DealsPage\Validators;

use Illuminate\Http\Request;
use Illuminate\Routing\Matching\UriValidator;
use Illuminate\Routing\Route;

class CustomValidator extends UriValidator
{

    public function matches(Route $route, Request $request)
    {
        $retVal = parent::matches($route, $request);
        $action = $route->getAction();
        if (isset($action['useCustomValidator']) && $action['useCustomValidator']) {
            $path = $request->url();
            if (preg_match('/^((?!\/(deals|deal)).)*$/mi', $path, $matches)) {
                $retVal = true;
            } else {
                $retVal = false;
            }
        }
        return $retVal;
    }
}