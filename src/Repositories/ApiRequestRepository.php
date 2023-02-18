<?php

namespace Megaads\DealsPage\Repositories;

use Illuminate\Support\Facades\Config;

class ApiRequestRepository
{
    protected $serviceDomain = "";
    protected $serviceToken = "";

    public function __construct()
    {
        $serviceConfig = Config::get("deals-page.service");
        $this->serviceDomain = $serviceConfig["domain"];
        $this->serviceToken = $serviceConfig["token"];
    }

    /**
     * @return array|mixed
     */
    public function readCatalogs()
    {
        $retVal = [];
        $resResult = sendHttpRequest('https://couponforless.test/catalogs.json');
        if (isset($resResult["status"]) && $resResult["status"] == 'successful') {
            $retVal = $resResult["data"];
        }
        return $retVal;
    }

    public function readCatalogProducts($catalogId)
    {
        $retVal = [];
        $resResult = sendHttpRequest('https://couponforless.test/products.json');
        if (isset($resResult["status"]) && $resResult["status"] == 'successful') {
            $retVal = $resResult["data"];
        }
        return $retVal;
    }



}