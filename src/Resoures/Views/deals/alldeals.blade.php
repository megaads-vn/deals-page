@extends('frontend.layout.master', ['title' => $title])
@section('title', 'HELLO WORLD')
@section('js')
    @parent
    <script defer src="{{ asset('/vendor/deals-page/js/deals-page.js?v=' . time()) }}"></script>
@endsection
@section('style')
    @parent
    <style media="screen">
        #brand-list {
            position: relative;
        }
        #cates_submit {
            display: flex;
            justify-content: flex-start;
            align-items: stretch;
        }
        #list-deals {
            position: absolute;
            top: 100%;
            left: 0;
            transform: translateY(20px);
            width: 260px;
            opacity: 0;
            pointer-events: none;
            background-color: #fff;
            border-radius: 0 0 5px 5px;
            box-shadow: 0 6px 36px rgba(11 11 11 / 11%);
            list-style: none;
            padding: 0;
            margin: 0;
            z-index: 2;
            transition: transform 300ms ease-in-out;
            max-height: 360px;
            overflow-y: auto;
            overflow-x: hidden;
        }
        .tab_button {
            cursor: pointer;
            padding: 10px 20px;
            width: 260px;
            min-width: 260px;
            margin-right: 20px;
            text-align: center;
            font-weight: bold;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(11 11 11 / 11%);
            transition: background-color 300ms ease-in-out, color 300ms ease-in-out;
        }


        .brand-item input {
            position: absolute;
            opacity: 0;
        }
        .brand-item-label {
            position: relative;
            padding: 10px 12px;
            cursor: pointer;
            width: 100%;
            background-color: #fff;
            transition: background-color 300ms ease-in-out;
        }

        .listserach {
            display: flex;
            justify-content: flex-start;
            align-items: stretch;
            width: 100%;
        }

        .serach_input {
            width: 100%;
            padding: 10px 20px;
            border: 1px solid #e9e9e9;
            outline: none;
            border-radius: 3px;
            font-size: 16px;
        }

        .listserach button {
            padding: 10px 20px;
            width: 120px;
            min-width: 120px;
            margin-left: 10px;
            background-color: #2da1e3;
            color: #fff;
            border: none;
            outline: none;
            border-radius: 4px;
        }

        @media (min-width: 1181px) {
            #brand-list:hover #list-deals {
                opacity: 1;
                pointer-events: all;
                transform: translateY(0);
            }

            #brand-list:hover .tab_button {
                background-color: #2da1e3;
                color: #fff;
            }

            .brand-item-label:hover {
                background-color: #f1f1f1;
            }

            .listserach button:hover {
                background-color: #337ab7;
            }
        }
    </style>
@endsection
@section('content')
    <main id="main_coupon" class="deal_main is-store-detail is-deals-detail">
        <div class="container">
            <div class="deal_con">
                <div class="deal_textCon">
                    <h1 class="text_center">All Deals</h1>
                    <p class="deal_mainp"></p>
                </div>
            </div>
            <div class="main_dealbg">
                <div class="deal_content" id="main_inner">
                    @if (count($deals) > 1)
                        <div class="deallist_tab">
                            <form action="/deals/" method="get" id="cates_submit" class="list search-deals-form">
                                <div class="list listbig">
                                    <div id="brand-list" class="dropdown">
                                        <div class="tab_button" data-slug="<?= !empty($brands->slug) ? $brands->slug:''?>">
                                            <span>Brands</span>
                                        </div>
                                        <ul id="list-deals" class="deals_ul">
                                            <?php foreach ($stores as $store) { ?>
                                                <li class="brand-item">
                                                    <label for="<?= $store->id ?>" class="data-tid brand-item-label" data-tid="<?= $store->id ?>" data-slug="<?= $store->slug ?>">
                                                        <input type="radio" id="<?= $store->id ?>" name="select-store" value="<?= $store->id ?>">
                                                        <?= $store->title ?>
                                                    </label>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    </div>
                                </div>
                                <div class="listserach">
                                    <input type="text" class="serach_input" autocomplete="off" name="search" placeholder="Refine By Keyword" value="<?= isset($_GET['search'])?urldecode($_GET['search']):''?>">
                                    <button type="button" name="button">Find</button>
                                </div>
                            </form>
                        </div>
                    @endif
                    @if (count($deals) < 1)
                    <div class="deals_null">
                        Sorry, nothing was searched.
                    </div>
                    @else
                        @include('deals-page::deals.topdeals', ['listDeals' => $deals, 'topDealBoxTitle' => ''])
                    @endif
                    @if ($pagination['page_count'] > 1)
                        <?= pagination(3, $pagination['total_count'], 30, $_GET); ?>
                    @endif
                </div>
            </div>
            <?php if(isset($dataDeal) && !empty($dataDeal)) { ?>
                @include('deals-page::deals.topdeals', ['title' => 'Top Deals'])
            <?php } ?>
        </div>
    </main>
@endsection
