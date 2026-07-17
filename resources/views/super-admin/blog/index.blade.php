@extends('../layout/' . $layout)
@section('subhead')
    <title>{{ __('admin.blog_list') }} - {{ setting('site_name') }}</title>
@endsection
@section('subcontent')
    <style type="text/css">
        .w-50 {
            width: 11rem;
        }
        .no-image-wrap {
            width: 90px;
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
        }
        .no-image-wrap img {
            display: block;
            border-radius: 6px;
        }
        .btn-ai-overlay {
            padding: 4px 10px;
            font-size: 10px;
            line-height: 1.1;
            border-radius: 999px;
            box-shadow: 0 4px 10px rgba(15, 23, 42, 0.15);
        }
        .ai-loader {
            display: none;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #666;
            margin-bottom: 10px;
        }
        .ai-loader .spinner {
            width: 14px;
            height: 14px;
            border: 2px solid #cbd5e1;
            border-top-color: #3b82f6;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .blog-toolbar {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 14px;
            background: transparent;
            border: none;
            border-radius: 12px;
            box-shadow: none;
        }
        .toolbar-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }
        .toolbar-filters {
            display: grid;
            grid-template-columns: 180px 180px 1fr 180px 180px 200px;
            gap: 10px;
            align-items: center;
        }
        .toolbar-filters .input,
        .toolbar-filters select {
            height: 40px;
            border-radius: 8px;
            margin-top: 0;
        }
        .toolbar-actions .button,
        .toolbar-filters .button,
        .toolbar-reset .button {
            height: 40px;
            border-radius: 8px;
        }
        .toolbar-reset-btn {
            width: 100%;
        }
        @media (min-width: 1025px) {
            .toolbar-reset-btn {
                width: 180px;
            }
        }
        @media (max-width: 1024px) {
            .toolbar-filters {
                grid-template-columns: repeat(3, minmax(140px, 1fr));
            }
        }
        @media (max-width: 720px) {
            .toolbar-filters {
                grid-template-columns: repeat(2, 1fr);
            }
            .toolbar-filters .input, 
            .toolbar-filters select, 
            .toolbar-filters .button,
            .toolbar-reset-btn {
                width: 100%;
                min-width: 0;
            }
        }

        .blog-logs-modal__badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 10px;
            border-radius: 999px;
            background: rgba(59, 130, 246, 0.12);
            color: #1d4ed8;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: .02em;
        }
        .blog-logs-modal__subtitle {
            color: #6b7280;
            font-size: 12px;
            margin-top: 6px;
        }
        .blog-logs-panel {
            border: 1px solid rgba(226, 232, 240, 1);
            border-radius: 12px;
            background: #fff;
            overflow: hidden;
        }
        .blog-logs-scroll {
            max-height: 58vh;
            overflow: auto;
        }
        .blog-log-item {
            display: flex;
            gap: 12px;
            padding: 12px 14px;
            border-bottom: 1px solid rgba(226, 232, 240, 1);
        }
        .blog-log-item:last-child {
            border-bottom: none;
        }
        .blog-log-dot {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: #94a3b8;
            margin-top: 6px;
            flex: 0 0 auto;
        }
        .blog-log-main {
            flex: 1 1 auto;
            min-width: 0;
        }
        .blog-log-top {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 10px;
        }
        .blog-log-action {
            font-weight: 600;
            color: #0f172a;
            font-size: 13px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .blog-log-time {
            font-size: 12px;
            color: #64748b;
            white-space: nowrap;
            flex: 0 0 auto;
        }
        .blog-log-meta {
            margin-top: 4px;
            font-size: 12px;
            color: #475569;
            word-break: break-word;
            white-space: normal;
        }
        .blog-log-byline {
            margin-top: 6px;
            font-size: 12px;
            color: #64748b;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .blog-log-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 3px 8px;
            border-radius: 999px;
            background: #f1f5f9;
            color: #334155;
        }
        div#blog-logs-title {
    font-size: 14px;
    font-weight: 700;
}
    </style>
    @include('../layout/components/top-bar')
    <h2 class="intro-y text-lg font-medium mt-10">{{ __('admin.blog_list') }}</h2>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 min-w-0">
            <div class="blog-toolbar">
                <div class="toolbar-filters">
                    <form method="GET" style="display: contents;">
                        <select data-placeholder="" name="status" class="input box pr-10 placeholder-theme-13">
                            <option value="">Select Status</option>
                                <option @if (isset($_GET['status']) && $_GET['status'] != '') @if ($_GET['status'] == 1) selected @endif
                                    @endif value="1">Publish</option>
                                <option @if (isset($_GET['status']) && $_GET['status'] != '') @if ($_GET['status'] == 0) selected @endif
                                    @endif value="0">Unpublish</option>
                                     <option @if (isset($_GET['status']) && $_GET['status'] != '') @if ($_GET['status'] == 2) selected @endif
                                    @endif value="2">Draft </option>
                                <option @if (isset($_GET['status']) && $_GET['status'] != '') @if ($_GET['status'] === 'scheduled') selected @endif
                                    @endif value="scheduled">Scheduled</option>
                        </select>
                        <select data-placeholder="{{ __('admin.select_category') }}" name="category_id"
                            class="input box pr-10 placeholder-theme-13">
                            <option value="">{{ __('admin.select_category') }}</option>
                            @foreach ($category as $cat)
                                <option value="{{ $cat->id }}"
                                    @if (isset($_GET['category_id']) && $_GET['category_id'] != '') @if ($cat->id == $_GET['category_id']) selected @endif
                                    @endif >{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <select data-placeholder="Select Visibility" name="visibility"
                            class="input box pr-10 placeholder-theme-13">
                            <option value="">Select Visibility</option>
                            <option value="featured" @if (isset($_GET['visibility']) && $_GET['visibility'] == 'featured') selected @endif>Featured</option>
                            <option value="slider" @if (isset($_GET['visibility']) && $_GET['visibility'] == 'slider') selected @endif>Slider</option>
                            <option value="editor_picks" @if (isset($_GET['visibility']) && $_GET['visibility'] == 'editor_picks') selected @endif>Editing</option>
                            <option value="weekly_top_picks" @if (isset($_GET['visibility']) && $_GET['visibility'] == 'weekly_top_picks') selected @endif>Final</option>
                        </select>
                        <input type="text" class="input box pr-10 placeholder-theme-13"
                            @if (isset($_GET['name'])) value="{{ $_GET['name'] }}" @endif name="name"
                            placeholder="{{ __('admin.blog_search') }}">
                        <input type="text" class="input box placeholder-theme-13 datepicker blog-date" name="from_date"
                            placeholder="Start date (dd-mm-yyyy)" data-single-mode="true" data-format="DD-MM-YYYY" data-no-default="1"
                            data-has-value="{{ (isset($_GET['from_date']) && $_GET['from_date'] != '') ? '1' : '0' }}"
                            @if (isset($_GET['from_date'])) value="{{ $_GET['from_date'] }}" @endif>
                        <input type="text" class="input box  placeholder-theme-13 datepicker blog-date" name="to_date"
                            placeholder="End date (dd-mm-yyyy)" data-single-mode="true" data-format="DD-MM-YYYY" data-no-default="1"
                            data-has-value="{{ (isset($_GET['to_date']) && $_GET['to_date'] != '') ? '1' : '0' }}"
                            @if (isset($_GET['to_date'])) value="{{ $_GET['to_date'] }}" @endif>
                        <input type="submit" id="search" value="Search"
                            class="button text-white bg-theme-1 shadow-md" name="search">
                        <button class="button border text-gray-700 dark:bg-dark-5 dark:text-gray-300 bg_white toolbar-reset-btn"
                            onclick="resetFilter()" type="button">{{ __('admin.reset') }}</button>
                    </form>
                </div>
                <div class="toolbar-actions">
                    <form id="bulkDeleteForm" method="POST" action="{{ url('/delete-multiple-blog') }}" style="display:inline;">
                        @csrf
                        <input type="hidden" name="_method" value="POST">
                        <button type="button" id="deleteSelected" class="button w-48 bg-theme-6 text-white">{{ __('admin.delete_selected') ?? 'Delete Selected' }}</button>
                    </form>
                     @if (auth()->check() && auth()->user()->type === 'admin')
                    <button type="button" id="bulkEditBtn" data-toggle="modal" data-target="#bulk-edit-modal" class="button w-48 bg-theme-1 text-white">{{ __('admin.bulk_edit_schedule') ?? 'Bulk Edit Schedule' }}</button>
                    @endif
                </div>
            </div>
        </div>
        <div class="intro-y col-span-12 overflow-x-auto w-full min-w-0 lg:overflow-visible table-manage">
            <table class="table table-report -mt-2">
                <thead>
                    <tr>
                        <th class="whitespace-no-wrap"><input type="checkbox" id="select_all" /></th>
                        <th class="whitespace-no-wrap">{{ __('admin.id') }}</th>
                        <th class="whitespace-no-wrap">{{ __('admin.image') }}</th>
                        <th class="whitespace-no-wrap">{{ __('admin.title') }}</th>
                        <th class="whitespace-no-wrap">{{ __('admin.category') }}</th>
                        <th class="whitespace-no-wrap">{{ __('admin.visibility') }}</th>
                        <th class="whitespace-no-wrap">{{ __('admin.views') }}</th>
                        <th class="whitespace-no-wrap">{{ __('admin.schedule_date') }}</th>
                        <th class="whitespace-no-wrap">Source Publish Date</th>
                        @can('blog-status')
                            <th class="text-center whitespace-no-wrap">{{ __('admin.status') }}</th>
                        @endcan
                        @if (Gate::check('blog-edit') ||
                                Gate::check('blog-delete') ||
                                Gate::check('blog-translate') ||
                                Gate::check('blog-send-notification') ||
                                Gate::check('blog-analytics') ||
                                Gate::check('blog-list'))
                            <th class="text-center whitespace-no-wrap">{{ __('admin.action') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    <?php $query = '';
                    $queryString = request()->getQueryString();
                    if (!empty($queryString)) {
                        $query = '?' . $queryString;
                    }
                    ?>
                    @if (count($blog))
                        <?php //  echo "<pre>"; print_r($blog); die;
                        $page = isset($_GET['page']) ? $_GET['page'] : 1;
                        if ($page > 1) {
                            $i = $blog->perPage() * ($blog->currentPage() - 1) + 1;
                        } else {
                            $i = 1;
                        }
                        ?>
                        @foreach ($blog as $row)
                            <?php //echo "<pre>"; print_r($row); die;
                            ?>
                            <tr class="intro-x">
                                <td class="w-10">
                                    <input type="checkbox" class="row_checkbox" name="ids[]" form="bulkDeleteForm" value="{{ $row->id }}" />
                                </td>
                                <td>
                                    {{ $i }}
                                </td>
                                <td>
                                    @if (!empty($row->blog_image))
                                        <a href="{{ $row->blog_image }}" class="image-popup" title="{{ $row->name }}">
                                            <img src="{{ $row->blog_image }}" class="thumb-img-list" alt="{{ $row->name }}"
                                                onerror="this.onerror=null;this.src='<?php echo url('upload/no-image.png'); ?>';">
                                        </a>
                                    @else
                                        <div class="no-image-wrap">
                                            <img src="<?php echo url('upload/no-image.png'); ?>" class="thumb-img-list" alt="{{ $row->name }}">
                                            @if (auth()->check() && auth()->user()->type === 'admin')
                                            <button type="button"
                                                class="button button--sm bg-theme-1 text-white generate-ai-btn btn-ai-overlay"
                                                data-blog-id="{{ $row->id }}"
                                                data-toggle="modal"
                                                data-target="#ai-image-modal">
                                                Generate AI
                                            </button>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td>{{ substr($row->title, 0, 10) }}... ({{ $row->blog_accent_code }})</td>
                                <td>
                                    @if ($row->blog_category_name)
                                        {{ $row->blog_category_name }}
                                    @else
                                        --
                                    @endif
                                </td>
                                <td class="">
                                    @php
                                        $listVisOptions = \App\Models\BlogVisibilityOption::getActive();
                                        $hasAnyVis = false;
                                        foreach ($listVisOptions as $lvo) {
                                            if ($row->{$lvo->field_key} == 1) { $hasAnyVis = true; break; }
                                        }
                                    @endphp
                                    @if($hasAnyVis)
                                        @foreach($listVisOptions as $lvo)
                                            @if($row->{$lvo->field_key} == 1)
                                                <button class="button button--sm mr-1 {{ $lvo->color_class }} text-white featured-btn">{{ $lvo->label }}</button>
                                            @endif
                                        @endforeach
                                    @else
                                        --
                                    @endif
                                </td>
                                <td>{{ $row->viewcount }}</td>
                                <td>
                                    @if (!empty($row->schedule_date))
                                        {{ date(setting('date_format'), strtotime($row->schedule_date)) }}
                                    @else
                                        --
                                    @endif
                                </td>
                                <td>
                                    @if (!empty($row->source_published_at))
                                        @php
                                            $sourceTs = strtotime($row->source_published_at);
                                        @endphp
                                        @if ($sourceTs !== false)
                                            <div>{{ date('d-m-Y', $sourceTs) }}</div>
                                            <div>{{ date('h:i A', $sourceTs) }}</div>
                                        @else
                                            --
                                        @endif
                                    @else
                                        --
                                    @endif
                                </td>
                                @can('blog-status')
                                    <td>
                                        @if ($row->status == 1)
                                            @if (auth()->user()->type === 'admin')
                                            <a href="{{ url('change-status-blog/') }}/{{ $row->id }}/0">
                                            @endif
                                                <div class="flex items-center justify-center text-theme-9">
                                                    <i data-feather="check-square" class="w-4 h-4 mr-2"></i>
                                                    @if(!empty($row->schedule_date) && strtotime($row->schedule_date) > time())
                                                         Scheduled
                                                    @else   
                                                    Publish
                                                    @endif
                                                </div>
                                            @if (auth()->user()->type === 'admin')
                                            </a>
                                            @endif
                                        @elseif ($row->status == 2)
                                            @if (auth()->user()->type === 'admin')
                                            <a href="{{ url('change-status-blog/') }}/{{ $row->id }}/1">
                                            @endif
                                                <div class="flex items-center justify-center text-theme-6">
                                                    <i data-feather="check-square" class="w-4 h-4 mr-2"></i>
                                                    Draft
                                                </div>
                                            @if (auth()->user()->type === 'admin')
                                            </a>
                                            @endif
                                        @else
                                            @if (auth()->user()->type === 'admin')
                                            <a href="{{ url('change-status-blog/') }}/{{ $row->id }}/1">
                                            @endif
                                                <div class="flex items-center justify-center text-theme-6">
                                                    <i data-feather="check-square" class="w-4 h-4 mr-2"></i>
                                                    Unpublish
                                                </div>
                                            @if (auth()->user()->type === 'admin')
                                            </a>
                                            @endif
                                        @endif
                                    </td>
                                @endcan
                                @if (Gate::check('blog-edit') ||
                                        Gate::check('blog-delete') ||
                                        Gate::check('blog-translate') ||
                                        Gate::check('blog-send-notification') ||
                                        Gate::check('blog-analytics') ||
                                        Gate::check('blog-list'))
                                    <td class="table-report__action w-40">
                                        <div class="flex justify-center items-center">
                                            @can('blog-list')
                                                <a class="flex items-center mr-3 text-theme-9 font-size23"
                                                    href="javascript:;"
                                                    data-blog-id="{{ $row->id }}"
                                                    data-blog-title="{{ e($row->title) }}"
                                                    onclick="openBlogLogs(this)"
                                                    title="Logs">
                                                    <i data-feather="list" class="w-4 h-4 mr-1"></i>
                                                </a>
                                            @endcan
                                            @can('blog-translate')
                                                {{-- <a class="flex items-center mr-3 text-theme-3 font-size23"
                                                    href="{{ url('edit-blog-translation') }}/{{ $layout }}/{{ $theme }}/{{ $row->id }}{{ $query }}"
                                                    title="{{ __('admin.translate') }}">
                                                    <i data-feather="edit-3" class="w-4 h-4 mr-1"></i>
                                                </a> --}}
                                            @endcan
                                            @can('blog-send-notification')
                                            @if($row->blog_category_name!=='Personalization')
                                                <a class="flex items-center mr-3 text-theme-9 font-size23"
                                                    href="{{ url('send-blog-notification') }}/{{ $row->id }}{{ $query }}"
                                                    title="{{ __('admin.send_notification') }}">
                                                    <i data-feather="bell" class="w-4 h-4 mr-1"></i>
                                                </a>
                                                @endif
                                            @endcan
                                            @can('blog-analytics')
                                                <a class="flex items-center mr-3 text-theme-9 font-size23"
                                                    href="{{ url('blog') }}/{{ $layout }}/{{ $theme }}/{{ $row->id }}/analytics"
                                                    title="{{ __('admin.analytics ') }}">
                                                    <i data-feather="activity" class="w-4 h-4 mr-1"></i>
                                                </a>
                                            @endcan
                                            @if (setting('instagram_share') == 1)
                                                <a class="flex items-center mr-3" title="{{ __('admin.instagram_image') }}"
                                                    href="{{ asset('upload/social-media-post/instagram/') }}/{{ $row->scial_media_image }}"
                                                    download>
                                                    <i data-feather="download" class="w-4 h-4 mr-1"></i>
                                                </a>
                                            @endif
                                            @if (Auth::user()->type == 'subadmin')
                                                {{-- @if (Auth::user()->id == $row->created_by) --}}
                                                <a class="flex items-center mr-3"
                                                    href="{{ url('edit-blog/') }}/{{ $layout }}/{{ $theme }}/{{ $row->id }}{{ $query }}"
                                                    title="{{ __('admin.edit') }}">
                                                    <i data-feather="check-square" class="w-4 h-4 mr-1"></i>
                                                </a>
                                                {{-- @endif --}}
                                            @else
                                                <a class="flex items-center mr-3"
                                                    href="{{ url('edit-blog/') }}/{{ $layout }}/{{ $theme }}/{{ $row->id }}{{ $query }}"
                                                    title="{{ __('admin.edit') }}">
                                                    <i data-feather="check-square" class="w-4 h-4 mr-1"></i>
                                                </a>
                                            @endif
                                            @can('blog-delete')
                                                <a class="flex items-center text-theme-6" href="javascript:;"
                                                    data-toggle="modal"
                                                    data-target="#delete-confirmation-modal-{{ $row->id }}"
                                                    title="{{ __('admin.delete') }}">
                                                    <i data-feather="trash-2" class="w-4 h-4 mr-1"></i>
                                                </a>
                                            @endcan
                                        </div>
                                        <div class="modal" id="delete-confirmation-modal-{{ $row->id }}">
                                            <div class="modal__content">
                                                <div class="p-5 text-center">
                                                    <i data-feather="x-circle"
                                                        class="w-16 h-16 text-theme-6 mx-auto mt-3"></i>
                                                    <div class="text-3xl mt-5">{{ __('admin.sure_warning') }}</div>
                                                    <div class="text-gray-600 mt-2">{{ __('admin.delete_warning') }}</div>
                                                </div>
                                                <div class="px-5 pb-8 text-center">
                                                    <button type="button" data-dismiss="modal"
                                                        class="button w-24 border text-gray-700 mr-1">{{ __('admin.cancel') }}</button>
                                                    <a href="{{ url('delete-blog') }}/{{ $row->id }}"
                                                        class="button w-24 bg-theme-6 text-white">{{ __('admin.delete') }}</a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                            <?php $i++; ?>
                        @endforeach
                    @else
                        <tr class="intro-x text-center text-danger">
                            <td class="w-40" colspan="11">
                                {{ __('admin.no_record_found') }}
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>

            <div class="modal" id="blog-logs-modal">
                <div class="modal__content" style="max-width: 980px;">
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="blog-logs-modal__badge">
                                    <span>Blog / News</span>
                                    <span style="opacity:.7;">•</span>
                                    <span>Logs</span>
                                    <span style="opacity:.7;">&bull;</span>
                                    <span id="blog-logs-badge-id"></span>
                                </div>
                                
                            </div>
                            <button type="button" class="button w-24 border text-gray-700" onclick="closeBlogLogs()">Close</button>
                        </div>
                        <div class="text-lg font-medium mt-3" id="blog-logs-title">Added by --</div>
                                <div class="blog-logs-modal__subtitle" id="blog-logs-subtitle"></div>
                        <div id="blog-logs-loading" class="mt-4 text-gray-600" style="display:none;">Loading...</div>
                        <div id="blog-logs-error" class="mt-4 text-theme-6" style="display:none;"></div>
                        <div class="mt-4 blog-logs-panel">
                            <div class="blog-logs-scroll" id="blog-logs-body"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="intro-y col-span-12 md:col-span-8 flex flex-wrap sm:flex-row sm:flex-no-wrap items-center min-w-0 overflow-x-auto">
            <ul class="pagination">
                {!! $blog->appends(request()->except('page'))->render() !!}
            </ul>
        </div>
        <div class="intro-y col-span-12 md:col-span-1 flex flex-wrap sm:flex-row sm:flex-no-wrap items-center ml-5 min-w-0" style="color:black">
            <?php $entry_count = isset($_GET['per_page']) ? $_GET['per_page'] : config('constant.paginate.num_per_page'); ?>
            <form>
                <select id="pagination" class="form-select">
                    <option value="5" <?php if (isset($_GET['per_page']) && $_GET['per_page'] == 5) {
                        echo 'selected';
                    } else {
                        if ($entry_count == 5) {
                            echo 'selected';
                        }
                    } ?>>5</option>
                    <option value="10" <?php if (isset($_GET['per_page']) && $_GET['per_page'] == 10) {
                        echo 'selected';
                    } else {
                        if ($entry_count == 10) {
                            echo 'selected';
                        }
                    } ?>>10</option>
                    <option value="25" <?php if (isset($_GET['per_page']) && $_GET['per_page'] == 25) {
                        echo 'selected';
                    } else {
                        if ($entry_count == 25) {
                            echo 'selected';
                        }
                    } ?>>25</option>
                    <option value="50" <?php if (isset($_GET['per_page']) && $_GET['per_page'] == 50) {
                        echo 'selected';
                    } else {
                        if ($entry_count == 50) {
                            echo 'selected';
                        }
                    } ?>>50</option>
                    <option value="100" <?php if (isset($_GET['per_page']) && $_GET['per_page'] == 100) {
                        echo 'selected';
                    } else {
                        if ($entry_count == 100) {
                            echo 'selected';
                        }
                    } ?>>100</option>
                </select>
            </form>
        </div>
        <div class="intro-y col-span-12 md:col-span-3 sm:flex-row sm:flex-no-wrap items-right min-w-0 overflow-hidden text-ellipsis">
            <p class="text-right">
                <?php if ($blog->firstItem() != null) { ?> {{ __('admin.showing') }} {{ $blog->firstItem() }}
                {{ __('admin.to') }} {{ $blog->lastItem() }} {{ __('admin.of') }} {{ $blog->total() }}
                {{ __('admin.entries') }}
                <?php } ?>
            </p>
        </div>
    </div>
    <script>
        document.getElementById('pagination').onchange = function() {
            window.location = "{!! $blog->url(1) !!}&per_page=" + this.value;
        };
    </script>

    <!-- Bulk Edit Modal -->
    <style>
        /* Temporary override to ensure modal is visible despite layout stacking */
        .modal { display: none; }
        .modal.show { display: block !important; visibility: visible !important; opacity: 1 !important; position: fixed !important; left: 0; top: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.45); z-index: 100000 !important; }
        /* Force fixed positioning for modal content to avoid being shifted by parent transforms */
        .modal .modal__content { position: fixed !important; left: 50% !important; top: 50% !important; transform: translate(-50%, -50%) !important; max-width: 90%; width: 640px; background: #fff; border-radius: 8px; z-index:100001 !important; }
    </style>
    <div class="modal modal__overlap" id="bulk-edit-modal" style="z-index:99999;">
        <div class="modal__content">
            <div class="flex items-center px-5 py-5 sm:py-3 border-b border-gray-200 dark:border-dark-5">
                <h2 class="font-medium text-base mr-auto">{{ __('admin.bulk_edit_schedule') ?? 'Bulk Edit Schedule' }}</h2>
            </div>
            <div class="p-5 grid grid-cols-12 gap-4 row-gap-3">
                <div class="col-span-12 sm:col-span-6">
                    <input type="date" id="bulk_schedule_date" class="input w-full border mt-2" name="schedule_date" placeholder="{{ __('admin.schedule_date_placeholder') }}">
                </div>
                <div class="col-span-12 sm:col-span-6">
                    <input type="time" id="bulk_schedule_time" class="input w-full border mt-2" name="schedule_time" placeholder="{{ __('admin.schedule_time_placeholder') }}">
                </div>
                <div class="col-span-12 sm:col-span-4">
                    <select id="bulk_status" class="input w-full border mt-2" name="status">
                        <option value=""> {{ __('Status') }} </option>
                        <option value="1">Publish</option>
                        <option value="0">Unpublish</option>
                        <option value="2">Draft</option>
                    </select>
                </div>

                {{-- Dynamic Visibility Options --}}
                @php
                    $bulkVisOptions = \App\Models\BlogVisibilityOption::getActive();
                @endphp
                @foreach($bulkVisOptions as $bvo)
                <div class="col-span-12 sm:col-span-4">
                    <select id="bulk_vis_{{ $bvo->field_key }}" class="input w-full border mt-2" name="vis_{{ $bvo->field_key }}" data-vis-field="{{ $bvo->field_key }}">
                        <option value="">{{ $bvo->label }}</option>
                        <option value="1">Add {{ $bvo->label }}</option>
                        <option value="0">Remove {{ $bvo->label }}</option>
                    </select>
                </div>
                @endforeach

            </div>
            <div class="px-5 py-3 text-right border-t border-gray-200 dark:border-dark-5">
                <span id="bulk_success_msg" style="display:none; margin-right:10px; color: #155724; background:#d4edda; padding:6px 10px; border-radius:6px; font-size:13px;"></span>
                <button type="button" data-dismiss="modal" class="button w-24 border text-gray-700 mr-1" id="bulk_cancel">{{ __('admin.cancel') }}</button>
                <button type="button" id="bulk_save" class="button w-24 bg-theme-1 text-white">{{ __('admin.save') ?? 'Save' }}</button>
            </div>
        </div>
    </div>

    <!-- AI Image Modal -->
    <div class="modal modal__overlap" id="ai-image-modal" style="z-index:99999;">
        <div class="modal__content" style="max-width: 720px; width: 520px;">
            <div class="flex items-center px-5 py-5 sm:py-3 border-b border-gray-200 dark:border-dark-5">
                <h2 class="font-medium text-base mr-auto">AI Image Preview</h2>
            </div>
            <div class="p-5">
                <input type="hidden" id="ai_blog_id" value="">
                <input type="hidden" id="ai_temp_name" value="">
                <div id="ai_image_status" style="font-size:13px;color:#666;margin-bottom:10px;"></div>
                <div id="ai_image_loader" class="ai-loader">
                    <span class="spinner"></span>
                    <span>Generating image...</span>
                </div>
                <div style="text-align:center;">
                    <img id="ai_preview_img" src="<?php echo url('upload/no-image.png'); ?>" style="max-width:100%;border:1px solid #eee;border-radius:6px;" alt="AI Preview">
                </div>
            </div>
            <div class="px-5 py-3 text-right border-t border-gray-200 dark:border-dark-5">
                <button type="button" data-dismiss="modal" class="button w-24 border text-gray-700 mr-1" id="ai_cancel">Cancel</button>
                <button type="button" id="ai_save" class="button w-24 bg-theme-1 text-white" disabled>Save</button>
            </div>
        </div>
    </div>

    <script>
        // Select / Deselect all rows
        var selectAll = document.getElementById('select_all');
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                var rows = document.querySelectorAll('.row_checkbox');
                for (var i = 0; i < rows.length; i++) {
                    rows[i].checked = this.checked;
                }
            });
        }

        // Delete selected button
        var deleteBtn = document.getElementById('deleteSelected');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function() {
                var rows = document.querySelectorAll('.row_checkbox:checked');
                if (rows.length === 0) {
                    alert('{{ __('admin.select_at_least_one_record') ?? 'Please select at least one record.' }}');
                    return;
                }
                if (confirm('{{ __('admin.sure_warning') ?? 'Are you sure?' }}')) {
                    document.getElementById('bulkDeleteForm').submit();
                }
            });
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                var inputs = document.querySelectorAll('.blog-date[data-no-default="1"]');
                inputs.forEach(function(el) {
                    if (el.getAttribute('data-has-value') === '0') {
                        el.value = '';
                    }
                });
            }, 50);
        });
    </script>

    <script>
        var bulkSaveBtn = document.getElementById('bulk_save');
        if (bulkSaveBtn) {
            bulkSaveBtn.addEventListener('click', function() {
                var checked = document.querySelectorAll('.row_checkbox:checked');
                if (checked.length === 0) {
                    alert('{{ __('admin.select_at_least_one_record') ?? 'Please select at least one record.' }}');
                    return;
                }
                var ids = [];
                checked.forEach(function(ch) { ids.push(ch.value); });

                var schedule_date = document.getElementById('bulk_schedule_date').value;
                var schedule_time = document.getElementById('bulk_schedule_time').value;
                var status = document.getElementById('bulk_status').value;

                // Collect all dynamic visibility selects
                var visFields = {};
                document.querySelectorAll('[data-vis-field]').forEach(function(sel) {
                    var fieldKey = sel.getAttribute('data-vis-field');
                    if (sel.value !== '') {
                        visFields[fieldKey] = sel.value;
                    }
                });

                var hasVisChange = Object.keys(visFields).length > 0;

                if (!schedule_date && !schedule_time && status === '' && !hasVisChange) {
                    alert('Please provide a schedule date, time, status, or a visibility change.');
                    return;
                }

                // Combine date and time
                if (schedule_time) {
                    if (!schedule_date) {
                        var today = new Date();
                        var yyyy = today.getFullYear();
                        var mm = String(today.getMonth()+1).padStart(2,'0');
                        var dd = String(today.getDate()).padStart(2,'0');
                        schedule_date = yyyy + '-' + mm + '-' + dd;
                    }
                    schedule_date = schedule_date + ' ' + schedule_time;
                }

                var data = { ids: ids };
                if (schedule_date) data.schedule_date = schedule_date;
                if (status !== '') data.status = status;

                // Merge dynamic visibility fields into payload
                Object.keys(visFields).forEach(function(k) {
                    data[k] = visFields[k];
                });

                // hide previous message
                var msgEl = document.getElementById('bulk_success_msg');
                if (msgEl) { msgEl.style.display = 'none'; msgEl.innerText = ''; }

                fetch("{{ url('/bulk-update-blog-schedule') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                }).then(function(res) { return res.json(); }).then(function(resp) {
                    if (resp.status) {
                        var msg = resp.message || 'Saved';
                        if (msgEl) {
                            msgEl.innerText = msg + (resp.affected ? (' ('+resp.affected+' updated)') : '');
                            msgEl.style.display = 'inline-block';
                        } else {
                            alert(msg);
                        }
                        document.querySelectorAll('.modal').forEach(function(m) { if (m.classList.contains('show')) m.classList.remove('show'); });
                        setTimeout(function(){ window.location.reload(); }, 900);
                    } else {
                        alert(resp.message || 'Error');
                    }
                }).catch(function(err) {
                    console.error(err);
                    alert('Error');
                });
            });
        }

        // Close modal on cancel
        var bulkCancel = document.getElementById('bulk_cancel');
        if (bulkCancel) {
            bulkCancel.addEventListener('click', function() {
                document.querySelectorAll('.modal').forEach(function(m) { if (m.classList.contains('show')) m.classList.remove('show'); });
            });
        }
    </script>

    <script>
        // Fallback modal toggle: open/close modals for elements using data-toggle="modal"
        (function() {
            function ensureModalOnBody(modal) {
                if (!modal) return modal;
                // move modal to body to avoid parent stacking/overflow issues
                if (modal.parentNode !== document.body) {
                    document.body.appendChild(modal);
                }
                // ensure fixed positioning and high z-index
                modal.style.position = 'fixed';
                modal.style.left = '0';
                modal.style.top = '0';
                modal.style.width = '100vw';
                modal.style.height = '100vh';
                modal.style.zIndex = '99999';
                return modal;
            }

            function openModal(modal) {
                modal = ensureModalOnBody(modal);
                if (!modal) return;
                modal.classList.add('show');
            }

            function closeModal(modal) {
                if (!modal) return;
                modal.classList.remove('show');
            }

            document.addEventListener('click', function(e) {
                var el = e.target.closest('[data-toggle="modal"]');
                if (el) {
                    e.preventDefault();
                     var target = el.getAttribute('data-target') || el.getAttribute('href');
                     if (!target) return;
                     var modal = document.querySelector(target);
                     openModal(modal);
                     if (modal && modal.id === 'bulk-edit-modal') {
                         setTimeout(function() {
                             var d = document.getElementById('bulk_schedule_date');
                             if (d) d.focus();
                         }, 30);
                     }
                 }

                var dismiss = e.target.closest('[data-dismiss="modal"]');
                if (dismiss) {
                    var modal = dismiss.closest('.modal');
                    closeModal(modal);
                }

                // click on overlay to close
                var modalBg = e.target.closest('.modal.show');
                if (modalBg && e.target === modalBg) {
                    closeModal(modalBg);
                }
            });
        })();
    </script>

    <script>
        (function() {
            function setAiStatus(msg) {
                var el = document.getElementById('ai_image_status');
                if (el) el.textContent = msg || '';
            }
            function setAiLoading(isLoading) {
                var loader = document.getElementById('ai_image_loader');
                if (!loader) return;
                loader.style.display = isLoading ? 'flex' : 'none';
            }

            document.addEventListener('click', function(e) {
                var btn = e.target.closest('.generate-ai-btn');
                if (!btn) return;

                var blogId = btn.getAttribute('data-blog-id');
                var imgEl = document.getElementById('ai_preview_img');
                var saveBtn = document.getElementById('ai_save');
                var blogIdEl = document.getElementById('ai_blog_id');
                var tempNameEl = document.getElementById('ai_temp_name');

                if (blogIdEl) blogIdEl.value = blogId || '';
                if (tempNameEl) tempNameEl.value = '';
                if (imgEl) imgEl.src = "<?php echo url('upload/no-image.png'); ?>";
                if (saveBtn) saveBtn.disabled = true;
                setAiStatus('');
                setAiLoading(true);

                fetch("{{ url('/generate-blog-ai-image') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ blog_id: blogId })
                }).then(function(res) { return res.json(); }).then(function(resp) {
                    if (resp.status && resp.data) {
                        if (imgEl) imgEl.src = resp.data.url;
                        if (tempNameEl) tempNameEl.value = resp.data.temp_name || '';
                        if (saveBtn) saveBtn.disabled = false;
                        setAiLoading(false);
                        setAiStatus('Preview ready.');
                    } else {
                        setAiLoading(false);
                        setAiStatus(resp.message || 'Failed to generate image.');
                    }
                }).catch(function() {
                    setAiLoading(false);
                    setAiStatus('Error generating image.');
                });
            });

            var saveBtn = document.getElementById('ai_save');
            if (saveBtn) {
                saveBtn.addEventListener('click', function() {
                    var blogId = document.getElementById('ai_blog_id').value;
                    var tempName = document.getElementById('ai_temp_name').value;
                    if (!blogId || !tempName) {
                        setAiStatus('Missing image data.');
                        return;
                    }
                    setAiStatus('Saving image...');
                    saveBtn.disabled = true;

                    fetch("{{ url('/save-blog-ai-image') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ blog_id: blogId, temp_name: tempName })
                    }).then(function(res) { return res.json(); }).then(function(resp) {
                        if (resp.status) {
                            setAiStatus('Saved. Refreshing...');
                            document.querySelectorAll('.modal').forEach(function(m) {
                                if (m.classList.contains('show')) m.classList.remove('show');
                            });
                            setTimeout(function() { window.location.reload(); }, 600);
                        } else {
                            setAiStatus(resp.message || 'Save failed.');
                            saveBtn.disabled = false;
                        }
                    }).catch(function() {
                        setAiStatus('Error saving image.');
                        saveBtn.disabled = false;
                    });
                });
            }
        })();
    </script>

    <script>
        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/\"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function closeBlogLogs() {
            var modal = document.getElementById('blog-logs-modal');
            if (modal) modal.classList.remove('show');
        }

        function openBlogLogs(el) {
            var blogId = el && el.getAttribute ? el.getAttribute('data-blog-id') : null;
            var blogTitle = el && el.getAttribute ? el.getAttribute('data-blog-title') : '';
            if (!blogId) return;

            var modal = document.getElementById('blog-logs-modal');
            if (!modal) return;
            modal.classList.add('show');

            var badgeIdEl = document.getElementById('blog-logs-badge-id');
            if (badgeIdEl) badgeIdEl.textContent = '#' + blogId;

            var titleEl = document.getElementById('blog-logs-title');
            if (titleEl) titleEl.textContent = 'Added by --';
            var subtitleEl = document.getElementById('blog-logs-subtitle');
            if (subtitleEl) subtitleEl.textContent = '';

            var loading = document.getElementById('blog-logs-loading');
            var error = document.getElementById('blog-logs-error');
            var body = document.getElementById('blog-logs-body');

            if (body) body.innerHTML = '';
            if (error) { error.style.display = 'none'; error.textContent = ''; }
            if (loading) { loading.style.display = 'block'; loading.textContent = 'Loading...'; }

            function statusLabel(v) {
                if (v === 1) return 'Publish';
                if (v === 0) return 'Unpublish';
                if (v === 2) return 'Draft';
                return String(v ?? '--');
            }

            function actionLabel(action, meta) {
                var a = String(action || '').replace(/_/g, ' ').trim();
                if (!a) a = 'action';
                // Friendly overrides
                var map = {
                    'blog created': 'Created',
                    'blog updated': 'Updated',
                    'blog translated': 'Translated',
                    'blog draft created': 'Draft Created',
                    'blog published': 'Published',
                    'blog unpublished': 'Unpublished',
                    'blog drafted': 'Moved to Draft',
                    'blog status changed': 'Status Changed'
                };
                var key = a.toLowerCase();
                if (map[key]) return map[key];

                if (meta && meta.from_status !== undefined && meta.to_status !== undefined) {
                    return 'Status Changed (' + statusLabel(meta.from_status) + ' → ' + statusLabel(meta.to_status) + ')';
                }

                return a.replace(/\b\w/g, function(m) { return m.toUpperCase(); });
            }

            function chip(text) {
                return '<span class="blog-log-chip">' + escapeHtml(text) + '</span>';
            }

            function buildMetaText(meta) {
                if (!meta || typeof meta !== 'object') return '';

                var parts = [];
                if (meta.source) parts.push('source: ' + meta.source);
                if (meta.submittype) parts.push('mode: ' + meta.submittype);
                if (meta.ai_rewrite) parts.push('ai: rewrite');
                if (meta.from_status !== undefined && meta.to_status !== undefined) {
                    parts.push('status: ' + statusLabel(meta.from_status) + ' → ' + statusLabel(meta.to_status));
                }
                if (meta.languages && Array.isArray(meta.languages)) {
                    parts.push('languages: ' + meta.languages.join(', '));
                }
                if (meta.is_featured !== undefined) {
                    parts.push('featured: ' + (String(meta.is_featured) === '1' ? 'yes' : 'no'));
                }
                if (parts.length) return parts.join(' • ');

                try {
                    return JSON.stringify(meta);
                } catch (e) {
                    return '';
                }
            }

            $.ajax({
                type: 'GET',
                url: "{{ url('/blog') }}/" + encodeURIComponent(blogId) + "/logs",
                success: function(resp) {
                    if (loading) loading.style.display = 'none';

                    if (!resp || !Array.isArray(resp.logs)) {
                        if (error) {
                            error.style.display = 'block';
                            error.textContent = 'Failed to load logs.';
                        }
                        return;
                    }

                    if (resp.success !== true) {
                        if (error) {
                            error.style.display = 'block';
                            error.textContent = resp.message ? String(resp.message) : 'Failed to load logs.';
                        }
                        return;
                    }

                    if (titleEl) {
                        var addedBy = resp.added_by_name ? ('Added by ' + resp.added_by_name) : 'Added by --';
                        if (resp.added_by_id) {
                            addedBy += ' (#' + resp.added_by_id + ')';
                        }
                        titleEl.textContent = addedBy;
                    }

                    if (false && resp.blog_title && titleEl) {
                        titleEl.textContent = String(resp.blog_title) + '  (#' + blogId + ')';
                    }
                    if (subtitleEl) {
                        var sub = [];
                        if (false && resp.added_by_name) {
                            sub.push('Added by ' + resp.added_by_name + (resp.added_by_id ? (' (#' + resp.added_by_id + ')') : ''));
                        }
                        if (resp.blog_status !== undefined && resp.blog_status !== null) {
                            sub.push('Current status: ' + statusLabel(resp.blog_status));
                        }
                        subtitleEl.textContent = sub.join(' • ');
                    }

                    if (resp.logs.length === 0) {
                        if (body) body.innerHTML = '<div class="p-6 text-center text-gray-600">No logs found.</div>';
                        return;
                    }

                    var rows = '';
                    resp.logs.forEach(function(item) {
                        var metaObj = item.meta && typeof item.meta === 'object' ? item.meta : null;
                        var userLabel = item.user_name ? (item.user_name + (item.user_id ? (' (#' + item.user_id + ')') : '')) : (item.user_id ? ('#' + item.user_id) : '--');
                        var actionText = actionLabel(item.action, metaObj);
                        var metaText = buildMetaText(metaObj);

                        var chips = [];
                        if (userLabel && userLabel !== '--') chips.push(chip('by ' + userLabel));
                        if (item.ip) chips.push(chip('ip ' + item.ip));
                        if (metaObj && metaObj.source) chips.push(chip(String(metaObj.source)));

                        rows += '<div class="blog-log-item">'
                            + '<div class="blog-log-dot"></div>'
                            + '<div class="blog-log-main">'
                                + '<div class="blog-log-top">'
                                    + '<div class="blog-log-action">' + escapeHtml(actionText) + '</div>'
                                    + '<div class="blog-log-time">' + escapeHtml(item.created_at || '') + '</div>'
                                + '</div>'
                                + (metaText ? ('<div class="blog-log-meta">' + escapeHtml(metaText) + '</div>') : '')
                                + (chips.length ? ('<div class="blog-log-byline">' + chips.join('') + '</div>') : '')
                            + '</div>'
                        + '</div>';
                    });
                    if (body) body.innerHTML = rows;
                },
                error: function() {
                    if (loading) loading.style.display = 'none';
                    if (error) {
                        error.style.display = 'block';
                        error.textContent = 'Error loading logs. (Check permission `blog-list` and that the `dp_blog_action_logs` table exists.)';
                    }
                }
            });
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeBlogLogs();
            }
        });
    </script>

@endsection
