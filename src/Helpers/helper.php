<?php

if (!function_exists('package_layout_head')) {
    function package_layout_head() {

    }
}

if (!function_exists("sendHttpRequest")) {
    function sendHttpRequest($url, $method = "GET", $params = [], $headers = []) {
        $ch = curl_init();
        $timeout = 30;
        if ($method == 'GET') {
            $strParams = '';
            foreach ($params as $key => $val) {
                $strParams .= $key . '=' . $val . '&';
            }
            $strParams = rtrim($strParams, '&');
            if (!empty($strParams))
                $url .= '?' . $strParams;
        }
        \Log::info('REQUEST_URL: ' . $url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        if ($method != 'GET') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        }

        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        $data = curl_exec($ch);
        curl_close($ch);
        return json_decode($data, true);
    }
}

if (!function_exists('slugify')) {
    function slugify($string) {
        $replacement = '-';
        $map = array();
        $quotedReplacement = preg_quote($replacement, '/');
        $default = array(
            '/à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ|À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ|å/' => 'a',
            '/è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ|È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ|ë/' => 'e',
            '/ì|í|ị|ỉ|ĩ|Ì|Í|Ị|Ỉ|Ĩ|î/' => 'i',
            '/ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ|Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ|ø/' => 'o',
            '/ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ|Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ|ů|û/' => 'u',
            '/ỳ|ý|ỵ|ỷ|ỹ|Ỳ|Ý|Ỵ|Ỷ|Ỹ/' => 'y',
            '/đ|Đ/' => 'd',
            '/ç/' => 'c',
            '/ñ/' => 'n',
            '/ä|æ/' => 'ae',
            '/ö/' => 'oe',
            '/ü/' => 'ue',
            '/Ä/' => 'Ae',
            '/Ü/' => 'Ue',
            '/Ö/' => 'Oe',
            '/ß/' => 'ss',
            '/[^\s\p{Ll}\p{Lm}\p{Lo}\p{Lt}\p{Lu}\p{Nd}]/mu' => ' ',
            '/\\s+/' => $replacement,
            sprintf('/^[%s]+|[%s]+$/', $quotedReplacement, $quotedReplacement) => '',
        );
        //Some URL was encode, decode first
        $string = urldecode($string);
        $map = array_merge($map, $default);
        return strtolower(preg_replace(array_keys($map), array_values($map), $string));
    }
}

if (!function_exists('dealPageSysLog')) {
    function dealPageSysLog($type = 'info', $message, Exception $exception) {
        \Log::$type($message . '' . $exception->getMessage() . '. File: ' . $exception->getFile() . ' Line: ' . $exception->getLine());
    }
}

if (!function_exists('topDeals')) {
    function topDeals($limit = 9, $filters = []) {
        $retVal = 0;
        try {
            $query = \Megaads\DealsPage\Models\Deal::query();
            $query->with(['store' => function($s) {
                $s->select(['id', 'title as name', 'slug']);
            }, 'categories']);
            if (array_key_exists('store_id', $filters)) {
                $query->where('store_id', $filters['store_id']);
            }
            if (array_key_exists('category_id', $filters)) {
                $query->whereHas('categories', function($c) use ($filters) {
                   $c->where('category_id', $filters['category_id']);
                });
            }
            $query->whereNotNull('discount');
            $query->orderBy('discount', 'DESC');
            $query->orderBy('id', 'DESC');
            $query->limit($limit);
            $retVal = $query->get(['id', 'title', 'slug', 'image', 'content', 'price', 'sale_price', 'discount', 'store_id']);
        } catch (Exception $exception) {
            dealPageSysLog('error', 'topDeals_Helper: ', $exception);
        }
        return $retVal;
    }
}
if (!function_exists('pagination')) {
    function pagination($links, $total, $limit, $param) {
        $page = 1;
        if (isset($param['p'])) {
            $page = $param['p'];
            unset($param['p']);
        }
        // $href = http_build_query($param, null, null, PHP_QUERY_RFC3986);
        $href = http_build_query($param);
        if (!empty($href)) {
            $href .= '&';
        }
        $last = ceil($total / $limit);
        $start = ( ( $page - $links ) > 0 ) ? $page - $links : 1;
        $end = ( ( $page + $links ) < $last ) ? $page + $links : $last;
        $html = '<div class="list-pagination"><ul class="pagination">';
        if ($page != 1) {
            $class = ($page == 1) ? "disabled" : "";
            $html .= '<li class="' . $class . '"><a href="?' . $href . 'p=1">&laquo;</a></li>';
        }
        for ($i = $start; $i <= $end; $i++) {
            $class = ($page == $i) ? "p-active" : "";
            $html .= '<li><a class="' . $class . '" href="?' . $href . 'p=' . $i . '">' . $i . '</a></li>';
        }

        if ($page != $last) {
            $class = ($page == $last) ? "disable" : "";
            $html .= '<li class="' . $class . '"><a href="?' . $href . 'p=' . $end . '">&raquo;</a> </li>';
        }
        $html .= '</ul></div>';
        return $html;
    }
}

if (!function_exists('timeOnGoing')) {
    function timeOnGoing($expireTime, $format = 'M d, Y') {
        $expireDate = date($format);
        if ($expireTime && $expireTime != '0000-00-00') {
            $expireDate = date($format, strtotime($expireTime));
        } else {
            $expireDate = 'On going';
        }
        return $expireDate;
    }
}
