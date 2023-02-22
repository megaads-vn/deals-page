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
    public function readCatalogs($pageId = 1)
    {
        $retVal = [];
        $requestConig = Config::get('deals-page.service');
        $baseUrl = $requestConig['domain'];
        $requestPath = "/service/flexoffer/get-product-catalogs";
        $fullRequest = $baseUrl . "" . $requestPath;
        $params = [
            "token" => $requestConig['token'],
            "siteName" => Config::get('deals-page.site_name'),
            "aid" => "113",
            "page" => $pageId,
            "pageSize" => 500,
        ];
        $resResult = sendHttpRequest($fullRequest, "GET", $params);
        if (isset($resResult["status"]) && $resResult["status"] == 'successful') {
            $retVal = $resResult["data"];
        }
        return $retVal;
    }

    public function readCatalogProducts($catalogId, $pageId)
    {
        $retVal = [];
        $requestConig = Config::get('deals-page.service');
        $baseUrl = $requestConig['domain'];
        $requestPath = "/service/flexoffer/get-products";
        $fullRequest = $baseUrl . "" . $requestPath;
        $params = [
            "token" => $requestConig['token'],
            "siteName" => Config::get('deals-page.site_name'),
            "page" => $pageId,
            "pageSize" => 500,
            "cid" => $catalogId,
        ];
        $resResult = sendHttpRequest($fullRequest, "GET", $params);
        if (isset($resResult["status"]) && $resResult["status"] == 'successful') {
            $retVal = $resResult["data"];
        }
        return $retVal;
    }



}