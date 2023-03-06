@extends('frontend.layout.master', ['title' => $title])
@section('meta')
    @include('frontend.layout.meta', ['data' => ['title' => $title, 'metaDescription' => '']])
    <link rel="stylesheet" href="/frontend/css/slick.css?v=<?= Config::get('app.version'); ?>">
    <link rel="stylesheet" href="/frontend/css/keypage-sitemap.css?v=<?= Config::get('app.version'); ?>">
@endsection
@section('content')
    <div class="container">
        <div id="storeAll">
            <div class="storeAll-top">
                <h1>Search Everything by Keywords on CouponForLess</h1>
                <div class="select-alphabet">
                    <label for="ChooseAlphabe" class="is-mobile click-choose">Choose Alphabet</label>
                    <input type="checkbox" id="ChooseAlphabe" style="opacity: 0; position: absolute">  <!-- input#ChooseAlphabe uncheck , select-alphabet display: none -->
                    <div class="sorter-list">
                        @if (isset($alphabet) && is_array($alphabet))
                            @foreach ($alphabet as $char)
                                <a href="#{{ $char }}" class="char-btn">{{ $char }}</a> <!-- click to a tag , go to taget & input#ChooseAlphabe uncheck -->
                            @endforeach
                        @endif
                    </div>
                    <div class="clear clear-btn"></div>
                </div>
                <div class="is-mobile list-background"></div>
            </div>

            <div class="clr"></div>
            @if (isset($keypageAlphabet) && is_array($keypageAlphabet) && !empty($keypageAlphabet))
                @foreach ($alphabet as $index => $key)
                    <div class="sorter-wrapper">
                        <input class="sorterBoxInput" type="radio" name="Alphabet" id="sorter{{ $key }}" {{ ($index == 0) ? 'checked' : '' }}>
                        <label id="{{ $key }}"class="sorter-label" for="sorter{{ $key }}" style="min-width: 90px">
                            <h2>
                                {{ $key }}
                            </h2>
                        </label>
                        <div class="sorter-box">
                            @php $count = 0; @endphp
                            <input type="checkbox" class="keypageAlphabetInput" id="keypageAlphabet{{ $key }}">
                            <div class="keypage-alphabet-list">
                                @if (isset($keypageAlphabet[$key]))
                                    @foreach ($keypageAlphabet[$key] as $item)
                                        @if ($count > 36)
                                            @break
                                        @endif
                                        <a href="{{ URL::route('frontend::keyword', ['slug' => $item->slug]) }}" class="storeLink" title="{{ $item->keyword }}">
                                            {{ $item->keyword }}
                                        </a>
                                        @php $count++; @endphp
                                    @endforeach
                                @endif
                            </div>
                            @if ($count > 18)
                                <label class="keypageAlphabet" for="keypageAlphabet{{ $key }}">
                                    <span class="more">See more</span>
                                    <span class="less">Less</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"> <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/> </svg>
                                </label>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
@endsection
@section('js')
    @parent
    <script>
        document.addEventListener("DOMContentLoaded", function(event) {
            $('.clear-btn').click(function() {
                if ($('#ChooseAlphabe').is(':checked')) {
                    $('#ChooseAlphabe').prop('checked', false);
                }
            });
            $('.char-btn').on('click', function() {
                var currentChar = $(this).attr('href');
                currentChar = currentChar.replace('#', '');
                $(`#sorter${currentChar}`).prop('checked', true);
                if ($('#ChooseAlphabe').is(':checked')) {
                    $('#ChooseAlphabe').prop('checked', false);
                }
            });
        });

    </script>
@endsection
