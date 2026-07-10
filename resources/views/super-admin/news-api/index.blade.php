@extends('../layout/' . $layout)

@section('subhead')
    <title>{{__('admin.news_api_list')}} - {{setting('site_name')}}</title>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .filter-box {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            border: 1px solid #edf2f7;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }
        .filter-box:hover {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }
        .dark-mode .filter-box {
            background: #232d45;
            border-color: #2d3748;
        }
        
        /* Select2 Modern Overrides */
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #f1f5f9 !important;
            border: 1px solid #e2e8f0 !important;
            color: #475569 !important;
            border-radius: 8px !important;
            padding: 2px 10px !important;
            font-weight: 500 !important;
            font-size: 0.8rem !important;
            margin: 4px 6px 4px 0 !important;
            cursor: pointer !important;
            transition: all 0.2s ease !important;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice:hover {
            background-color: #f1f5f9 !important;
            border-color: #e2e8f0 !important;
            color: #000 !important;
        }
        .dark-mode .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #2d3748 !important;
            border-color: #4a5568 !important;
            color: #e2e8f0 !important;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            display: none !important;
        }
        .select2-container--default .select2-selection--multiple {
            border: 1px solid #e2e8f0 !important;
            border-radius: 10px !important;
            min-height: 46px !important;
            padding: 4px 12px !important;
            background-color: #fff !important;
            box-shadow: none !important;
        }
        .dark-mode .select2-container--default .select2-selection--multiple {
            background-color: #1a202c !important;
            border-color: #2d3748 !important;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #f1f5f9 !important;
            color: #000 !important;
        }
        .select2-dropdown {
            border-radius: 0.75rem !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1) !important;
            border: 1px solid #e2e8f0 !important;
            z-index: 9999 !important;
            padding: 4px !important;
            background-color: #fff !important;
        }
        .select2-results__options {
            background-color: #fff !important;
            border-radius: 0.5rem !important;
        }
        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #f1f5f9 !important;
        }
        .dark-mode .select2-dropdown {
            background-color: #293145 !important;
            border-color: #1b253b !important;
        }
        
        .form-label {
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            color: #718096;
            margin-bottom: 0.5rem;
            display: block;
        }
        .dark-mode .form-label {
            color: #a0aec0;
        }
        
        .form-input-custom {
            height: 42px !important;
            border-radius: 0.5rem !important;
            border: 1px solid #e2e8f0 !important;
            padding: 0.5rem 0.75rem !important;
            width: 100% !important;
            background-color: #fff !important;
        }
        .dark-mode .form-input-custom {
            background-color: #1b253b !important;
            border-color: #2d3748 !important;
            color: #cbd5e1 !important;
        }
    </style>
@endsection

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.12/datatables.min.css" />

@section('subcontent')
    @include('../layout/components/top-bar')
    <h2 class="intro-y text-lg font-medium mt-10">{{__('admin.search_news_api_post')}}</h2>
    
    <div class="intro-y filter-box mt-5">
        <form method="GET">
            <div class="grid grid-cols-12 gap-x-6 gap-y-4">
                <!-- Row 1: Concept, Language, DataType, Sort -->
                <div class="col-span-12 md:col-span-3">
                    <label class="form-label">Concepts/Keywords</label>
                    <select class="input w-full" name="conceptUri[]" id="conceptSuggest" multiple="multiple" data-placeholder="Select Concepts...">
                        @php 
                            $selectedC = is_array(request('conceptUri')) ? request('conceptUri') : [];
                            $renderedC = [];
                        @endphp
                        @foreach($suggestedConcepts ?? [] as $conceptOption)
                            @if(in_array($conceptOption['id'], $selectedC))
                                <option value="{{$conceptOption['id']}}" selected>{{$conceptOption['name']}}</option>
                                @php $renderedC[] = $conceptOption['id']; @endphp
                            @endif
                        @endforeach
                        @foreach($selectedC as $sUri)
                            @if(!in_array($sUri, $renderedC))
                                @php 
                                    $n = $sUri;
                                    if(strpos($sUri, 'wikipedia.org/wiki/') !== false){
                                        $parts = explode('/', $sUri); $n = urldecode(str_replace('_', ' ', end($parts)));
                                    }
                                @endphp
                                <option value="{{$sUri}}" selected>{{$n}}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="col-span-12 md:col-span-3">
                    <label class="form-label">{{__('admin.language') ?? 'Language'}}</label>
                    <select class="input form-input-custom" name="language">
                        @php $selectedLang = request('language', 'en'); @endphp
                        <option value="">{{__('admin.all_language')}}</option>
                        @foreach($news_api_language as $key => $value)
                            <option value="{{$key}}" @if($selectedLang == $key) selected @endif>{{$value}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-12 md:col-span-3">
                    <label class="form-label">Data Type</label>
                    <select class="input form-input-custom" name="dataType">
                        <option value="news" @if(request('dataType') == 'news') selected @endif>News Only</option>
                        <option value="blog" @if(request('dataType') == 'blog') selected @endif>Blogs Only</option>
                        <option value="pr" @if(request('dataType') == 'pr') selected @endif>PR Only</option>
                        <option value="all" @if(request('dataType') == 'all') selected @endif>All Types</option>
                    </select>
                </div>
                <div class="col-span-12 md:col-span-3">
                    <label class="form-label">Sort Order</label>
                    <select class="input form-input-custom" name="articlesSortBy">
                        <option value="date" @if(request('articlesSortBy', 'date') == 'date') selected @endif>Sort by Date/Time</option>
                        <option value="id" @if(request('articlesSortBy') == 'id') selected @endif>Recently Added</option>
                        <option value="rel" @if(request('articlesSortBy') == 'rel') selected @endif>Sort by Relevance</option>
                        <option value="sourceImportance" @if(request('articlesSortBy') == 'sourceImportance') selected @endif>Source Importance</option>
                        <option value="socialScore" @if(request('articlesSortBy') == 'socialScore') selected @endif>Social Score</option>
                    </select>
                </div>

                <!-- Row 2: Location, Source -->
                <div class="col-span-12 md:col-span-6">
                    <label class="form-label">Location Suggestion</label>
                    <select class="input w-full" name="locationUri[]" id="locationSuggest" multiple="multiple" data-placeholder="Select Locations...">
                        @php 
                            $selectedL = is_array(request('locationUri')) ? request('locationUri') : [];
                            $renderedL = [];
                        @endphp
                        @foreach($suggestedLocations ?? [] as $locOption)
                            @if(in_array($locOption['id'], $selectedL))
                                <option value="{{$locOption['id']}}" selected>{{$locOption['name']}}</option>
                                @php $renderedL[] = $locOption['id']; @endphp
                            @endif
                        @endforeach
                        @foreach($selectedL as $sUri)
                            @if(!in_array($sUri, $renderedL))
                                @php 
                                    $n = $sUri;
                                    if(strpos($sUri, 'wikipedia.org/wiki/') !== false){
                                        $parts = explode('/', $sUri); $n = urldecode(str_replace('_', ' ', end($parts)));
                                    }
                                @endphp
                                <option value="{{$sUri}}" selected>{{$n}}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="col-span-12 md:col-span-6">
                    <label class="form-label">Source Suggestion</label>
                    <select class="input w-full" name="sourceUri[]" id="sourceSuggest" multiple="multiple" data-placeholder="Select Sources...">
                        @php 
                            $selectedS = is_array(request('sourceUri')) ? request('sourceUri') : [];
                            $renderedS = [];
                        @endphp
                        @foreach($suggestedSources ?? [] as $srcOption)
                            @if(in_array($srcOption['id'], $selectedS))
                                <option value="{{$srcOption['id']}}" selected>{{$srcOption['name']}}</option>
                                @php $renderedS[] = $srcOption['id']; @endphp
                            @endif
                        @endforeach
                        @foreach($selectedS as $sUri)
                            @if(!in_array($sUri, $renderedS))
                                @php 
                                    $n = $sUri;
                                    if(strpos($sUri, '/') !== false){
                                        $parts = explode('/', $sUri); $n = end($parts);
                                    }
                                @endphp
                                <option value="{{$sUri}}" selected>{{$n}}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <!-- Row 3: Category -->
                <div class="col-span-12">
                    <label class="form-label">Category Suggestion</label>
                    <select class="input w-full" name="categoryUri[]" id="categorySuggest" multiple="multiple" data-placeholder="Select Categories...">
                        @php 
                            $selectedCat = is_array(request('categoryUri')) ? request('categoryUri') : [];
                            $renderedCat = [];
                        @endphp
                        @foreach($suggestedCategories ?? [] as $catOption)
                            @if(in_array($catOption['id'], $selectedCat))
                                <option value="{{$catOption['id']}}" selected>{{$catOption['name']}}</option>
                                @php $renderedCat[] = $catOption['id']; @endphp
                            @endif
                        @endforeach
                        @foreach($selectedCat as $sUri)
                            @if(!in_array($sUri, $renderedCat))
                                @php 
                                    $n = $sUri;
                                    if(strpos($sUri, 'news/') !== false){
                                        $parts = explode('/', $sUri); $n = ucfirst(end($parts));
                                    }
                                @endphp
                                <option value="{{$sUri}}" selected>{{$n}}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <!-- Row 4: Date Range -->
                <div class="col-span-12 md:col-span-3">
                    <label class="form-label">From Date</label>
                    <input type="text" class="input form-input-custom datepicker" name="from" placeholder="YYYY-MM-DD" @if(isset($_GET['from'])) value="{{$_GET['from']}}" @else value="{{ date('Y-m-d') }}" @endif>
                </div>
                <div class="col-span-12 md:col-span-3">
                    <label class="form-label">To Date</label>
                    <input type="text" class="input form-input-custom datepicker" name="to" placeholder="YYYY-MM-DD" @if(isset($_GET['to'])) value="{{$_GET['to']}}" @else value="{{ date('Y-m-d') }}" @endif>
                </div>
                <div class="col-span-12 md:col-span-3">
                    <label class="form-label">From Time</label>
                    <input type="time" class="input form-input-custom" name="from_time" @if(isset($_GET['from_time'])) value="{{$_GET['from_time']}}" @else value="00:00" @endif>
                </div>
                <div class="col-span-12 md:col-span-3">
                    <label class="form-label">To Time</label>
                    <input type="time" class="input form-input-custom" name="to_time" @if(isset($_GET['to_time'])) value="{{$_GET['to_time']}}" @else value="23:59" @endif>
                </div>
                
                <!-- Row 5: Actions -->
                <div class="col-span-12 flex items-end justify-end gap-2 mt-2">
                    <button type="button" class="button border text-gray-700 dark:bg-dark-5 dark:text-gray-300 flex items-center h-10 px-6" onclick="resetFilter()">
                        <i data-feather="rotate-ccw" class="w-4 h-4 mr-2"></i> {{__('admin.reset')}}
                    </button>
                    <button type="submit" class="button text-white bg-theme-1 shadow-md flex items-center h-10 px-8">
                        <i data-feather="search" class="w-4 h-4 mr-2"></i> {{__('admin.search_news')}}
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="intro-y flex items-center justify-between mt-8">
        <h2 class="text-lg font-medium">{{__('admin.search_news_api_post')}} - Results</h2>
        @can('feed-item-save-post')
        <button id="bulk-save-btn" type="button" class="button text-white bg-theme-1 flex items-center gap-2 px-4 shadow-md">
            <i data-feather="save" class="w-4 h-4"></i>
            <span>{{__('admin.save_selected_as_posts') ?? 'Bulk Save'}}</span>
        </button>
        @endcan
    </div>

    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible table-manage">
            <table class="table table-report -mt-2">
                <thead>
                    <tr>
                        <th class="whitespace-no-wrap">&nbsp;</th>
                        <th class="whitespace-no-wrap">{{__('admin.image')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.title_desc')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.published_time')}}</th>
                        @can('feed-item-save-post')
                        <th class="text-center whitespace-no-wrap">{{__('admin.action')}}</th>
                        @endcan
                    </tr>
                </thead>
               
                <tbody>
                    @if(count($data))
                        <?php $i=1; ?>
                        @foreach ($data as $row)

                            <tr class="intro-x row1" data-id="{{ $i }}">
                                <td class="w-8 text-center">
                                    <input type="checkbox" class="news-select" data-idx="{{ $i }}" data-source="{{ $row['source']['name'] }}" data-author="{{ $row['author'] }}" data-title="{{ htmlspecialchars($row['title'], ENT_QUOTES) }}" data-description="{{ htmlspecialchars($row['description'], ENT_QUOTES) }}" data-url="{{ $row['url'] }}" data-urlToImage="{{ $row['urlToImage'] }}" data-publishedAt="{{ $row['publishedAt'] }}" data-content="{{ htmlspecialchars($row['content'], ENT_QUOTES) }}">
                                </td>
                                <td class="w-40">
                                    @if(isset($row['urlToImage']) && $row['urlToImage']!=null && $row['urlToImage']!='')
                                        <img onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';"  src="{{$row['urlToImage']}}" width="150" onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';">
                                    @else
                                        <img onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';"  src="{{url('upload/author/default.png')}}" width="150" onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';">
                                    @endif
                                </td> 
                                <td >
                                    <a target="_blank" href="{{$row['url']}}" class="font-medium whitespace-no-wrap"><?php echo substr($row['title'], 0,90);?></a> 
                                    <div class="text-gray-600 text-xs"><?php echo substr($row['description'], 0,150)."........";?></div>
                                </td>
                                 <td>
                                    @if(isset($row['publishedAt']) && $row['publishedAt'] != '')
                                        @php
                                            try {
                                                $dt = \Carbon\Carbon::parse($row['publishedAt']);
                                                $pub = $dt->format('Y-m-d H:i');
                                                $diff = $dt->diffForHumans();
                                            } catch (\Exception $e) {
                                                $pub = $row['publishedAt'];
                                                $diff = '';
                                            }
                                        @endphp
                                        <div class="text-gray-600 text-xs font-bold text-theme-1">{{$diff}}</div>
                                        <div class="text-gray-600 text-xs">{{$pub}}</div>
                                    @else
                                        <div class="text-gray-600 text-xs">--</div>
                                    @endif
                                </td>
                                @can('feed-item-save-post')
                                <td class="table-report__action w-56">
                                    <div class="flex justify-center items-center">
                                        <form method="post" action="{{url('save-news-api-post')}}">
                                            @csrf
                                            <input type="hidden" name="source" value="{{$row['source']['name']}}">
                                            <input type="hidden" name="author" value="{{$row['author']}}">
                                            <input type="hidden" name="title" value="{{$row['title']}}">
                                            <input type="hidden" name="description" value="{{$row['description']}}">
                                            <input type="hidden" name="url" value="{{$row['url']}}">
                                            <input type="hidden" name="urlToImage" value="{{$row['urlToImage']}}">
                                            <input type="hidden" name="publishedAt" value="{{$row['publishedAt']}}">
                                            <input type="hidden" name="content" value="{{$row['content']}}">
                                            <button trpe="submit" class="font-medium whitespace-no-wrap button text-white bg-theme-1"> {{__('admin.save_as_post')}}</button>
                                        </form>

                                        <!-- <a href="{{url('save-news-api-post')}}?source={{$row['source']['name']}}&author={{$row['author']}}&title={{$row['title']}}&description={{$row['description']}}&url={{$row['url']}}&urlToImage={{$row['urlToImage']}}&publishedAt={{$row['publishedAt']}}&content={{$row['content']}}" class="font-medium whitespace-no-wrap button text-white bg-theme-1"> {{__('admin.save_as_post')}}</a> -->

                                        <div class="text-gray-600 text-xs whitespace-no-wrap"></div>
                                    </div>
                                   
                                </td>
                                @endcan
                            </tr>
                            <?php $i++; ?>
                        @endforeach
                    @else
                        <tr class="intro-x text-center text-danger">
                            <td class="w-40" colspan="5">
                                {{__('admin.no_record_found')}}
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="intro-y col-span-8 flex flex-wrap sm:flex-row sm:flex-no-wrap items-center">
            <ul class="pagination">
                @if($data instanceof \Illuminate\Pagination\LengthAwarePaginator)
                {!! $data->appends(request()->except('page'))->render() !!}
                @endif
            </ul>
        </div>
        <div class="intro-y col-span-1 flex flex-wrap sm:flex-row sm:flex-no-wrap items-center">
            @if($data instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <select id="pagination-per-page" class="form-select box mt-3 sm:mt-0">
                @foreach([10, 20, 50, 100] as $val)
                    <option value="{{$val}}" @if($data->perPage() == $val) selected @endif>{{$val}}</option>
                @endforeach
            </select>
            @endif
        </div>
        <div class="intro-y col-span-3 flex flex-wrap sm:flex-row sm:flex-no-wrap items-center justify-end">
            @if($data instanceof \Illuminate\Pagination\LengthAwarePaginator && $data->total() > 0)
                <p class="text-gray-600 text-xs">
                    {{ __('admin.showing') }} {{ $data->firstItem() }}
                    {{ __('admin.to') }} {{ $data->lastItem() }} {{ __('admin.of') }} {{ $data->total() }}
                    {{ __('admin.entries') }}
                </p>
            @endif
        </div>
        
    </div>

    
@endsection

@section('script')
<script>
$(document).ready(function(){
    function collectAndSend() {
        const selected = document.querySelectorAll('.news-select:checked');
        if(selected.length === 0){ alert('Please select at least one article'); return; }
        const posts = [];
        selected.forEach((chk) => {
            posts.push({
                idx: chk.getAttribute('data-idx') || '',
                source: chk.getAttribute('data-source') || '',
                author: chk.getAttribute('data-author') || '',
                title: chk.getAttribute('data-title') || '',
                description: chk.getAttribute('data-description') || '',
                url: chk.getAttribute('data-url') || '',
                urlToImage: chk.getAttribute('data-urlToImage') || chk.getAttribute('data-urltoimage') || '',
                publishedAt: chk.getAttribute('data-publishedAt') || chk.getAttribute('data-publishedat') || '',
                content: chk.getAttribute('data-content') || ''
            });
        });

        const token = '{{ csrf_token() }}';
        fetch('{{ url('save-news-api-post') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ posts: posts })
        }).then(r=>r.json()).then(res=>{
            if(!res){ alert('No response'); return; }
            const results = res.results || [];
            let created = 0, skipped = 0, failed = 0;
            results.forEach(item => {
                const idx = item.idx;
                const tr = document.querySelector('tr[data-id="' + idx + '"]');
                if(tr){
                    let statusSpan = tr.querySelector('.bulk-status');
                    if(!statusSpan){
                        statusSpan = document.createElement('div');
                        statusSpan.className = 'bulk-status text-xs ml-2';
                        tr.querySelector('td').appendChild(statusSpan);
                    }
                    if(item.status === 'created'){
                        statusSpan.textContent = 'Saved';
                        statusSpan.style.color = 'green';
                        created++;
                    } else if(item.status === 'skipped'){
                        statusSpan.textContent = 'Already exists';
                        statusSpan.style.color = '#888';
                        skipped++;
                    } else {
                        statusSpan.textContent = 'Failed';
                        statusSpan.style.color = 'red';
                        failed++;
                    }
                }
            });
            // Summary
            if(created === 0 && skipped > 0 && failed === 0){
                alert('All selected items already exist');
            } else {
                let messages = [];
                if(created) messages.push(created + ' added');
                if(skipped) messages.push(skipped + ' skipped');
                if(failed) messages.push(failed + ' failed');
                alert(messages.join(', '));
            }
        }).catch(err=>{ console.error(err); alert('Request failed'); });
    }

    const bulkBtn = document.getElementById('bulk-save-btn');
    if (bulkBtn) bulkBtn.addEventListener('click', collectAndSend);
});

(function() {
    var jq = document.createElement('script');
    jq.src = "https://code.jquery.com/jquery-3.6.0.min.js";
    jq.onload = function() {
        var s2 = document.createElement('script');
        s2.src = "https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js";
        s2.onload = function() {
            var $isolatedJq = window.jQuery.noConflict(true);

            var initSelect2Autosuggest = function(selector, type, placeholder) {
                $isolatedJq(selector).select2({
                    placeholder: placeholder,
                    allowClear: true,
                    width: 'resolve',
                    ajax: {
                        url: "{{ route('eventRegistry.fetchFilters') }}",
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                type: type,
                                prefix: params.term || '',
                                lang: 'eng'
                            };
                        },
                        processResults: function (data) {
                            if(!data || !Array.isArray(data)) return { results: [] };
                            return {
                                results: $isolatedJq.map(data, function (item) {
                                    var text = item.label || item.title || item.eng || item.uri || "";
                                    if (typeof text === "object") {
                                        text = text.eng || Object.values(text)[0] || "";
                                    }
                                    return {
                                        text: text,
                                        id: item.wikiUri || item.uri,
                                        parentUri: item.parentUri || ""
                                    };
                                })
                            };
                        },
                        cache: true
                    },
                    templateResult: function(item) {
                        if (!item.id) { return item.text; }
                        // Clean up the display name for categories if needed
                        var cleanText = item.text;
                        if (type === 'categories') {
                            cleanText = item.text.replace('news/', '').replace('dmoz/', '');
                        }
                        return $isolatedJq('<span>' + cleanText + '</span>');
                    },
                    templateSelection: function(item) {
                        var cleanText = item.text;
                        if (type === 'categories') {
                            cleanText = item.text.replace('news/', '').replace('dmoz/', '');
                        }
                        // Use a span with data-id to ensure we can always identify the value
                        return $isolatedJq('<span class="select2-chip-text" data-val-id="' + (item.id || item.text) + '">' + cleanText + '</span>');
                    }
                });
            };

            initSelect2Autosuggest('#categorySuggest', 'categories', "Select Categories (Type to Search...)");
            initSelect2Autosuggest('#sourceSuggest', 'sources', "Select Sources (Type to Search...)");
            initSelect2Autosuggest('#locationSuggest', 'locations', "Select Locations (Type to Search...)");
            initSelect2Autosuggest('#conceptSuggest', 'concepts', "Select Concepts/Keywords (Type to Search...)");
            // Global handler to remove tag on click
            $isolatedJq(document).on('click', '.select2-selection__choice', function(e) {
                var $choice = $isolatedJq(this);
                var $select = $choice.closest('.select2-container').prev('select');
                
                // Get the value ID from our custom data attribute
                var valId = $choice.find('.select2-chip-text').attr('data-val-id');
                
                if (valId) {
                    var currentValues = $select.val() || [];
                    var newValues = currentValues.filter(function(v) {
                        return v !== valId;
                    });
                    $select.val(newValues).trigger('change');
                }
                
                // Prevent opening the dropdown on remove
                e.preventDefault();
                e.stopPropagation();
            });
        };
        document.body.appendChild(s2);
    };
    document.body.appendChild(jq);
})();

function resetFilter() {
    window.location.href = "{{ url()->current() }}";
}

$(document).on('change', '#pagination-per-page', function() {
    let perPage = $(this).val();
    let url = new URL(window.location.href);
    url.searchParams.set('per_page', perPage);
    url.searchParams.set('page', 1); // reset to first page
    window.location.href = url.toString();
});
</script>
@endsection