@extends('../layout/' . $layout)

@section('subhead')
    <title>Blog Visibility Options - {{ setting('site_name') }}</title>
@endsection

@section('subcontent')
    @include('../layout/components/top-bar')

    <style>
        /* ── Visibility Options Manager Styles ── */
        .vis-manager {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 16px rgba(0,0,0,.07);
            padding: 28px;
            margin-top: 24px;
        }
        .vis-manager__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 22px;
            padding-bottom: 16px;
            border-bottom: 1px solid #eef0f3;
        }
        .vis-manager__title {
            font-size: 1.18rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 9px;
        }
        .vis-manager__title .badge {
            font-size: 11px;
            font-weight: 600;
            background: #e0e7ff;
            color: #3730a3;
            padding: 3px 8px;
            border-radius: 20px;
        }
        /* ── Row cards ── */
        .vis-row {
            background: #f8fafd;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            padding: 14px 16px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: box-shadow .15s, border-color .15s;
        }
        .vis-row:hover { border-color: #6366f1; box-shadow: 0 0 0 2px rgba(99,102,241,.10); }
        .vis-row.vis-row--inactive { opacity: .55; background: #f1f3f6; }
        .vis-row__drag {
            cursor: grab;
            color: #9ca3af;
            font-size: 18px;
            flex-shrink: 0;
        }
        .vis-row__label-input {
            flex: 1;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 7px 12px;
            font-size: 14px;
            color: #1e293b;
            outline: none;
            transition: border-color .15s;
            min-width: 0;
        }
        .vis-row__label-input:focus { border-color: #6366f1; box-shadow: 0 0 0 2px rgba(99,102,241,.14); }
        .vis-row__select {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 7px 10px;
            font-size: 13px;
            color: #374151;
            background: #fff;
            outline: none;
            min-width: 160px;
            flex-shrink: 0;
        }
        .vis-row__color-select {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 7px 10px;
            font-size: 13px;
            color: #374151;
            background: #fff;
            outline: none;
            min-width: 130px;
            flex-shrink: 0;
        }
        /* Toggle switch */
        .vis-toggle { position: relative; display: inline-block; width: 40px; height: 22px; flex-shrink: 0; }
        .vis-toggle input { opacity: 0; width: 0; height: 0; }
        .vis-toggle__slider {
            position: absolute; inset: 0; background: #cbd5e1; border-radius: 22px; cursor: pointer; transition: .2s;
        }
        .vis-toggle__slider:before {
            content: ''; position: absolute; height: 16px; width: 16px; left: 3px; bottom: 3px;
            background: #fff; border-radius: 50%; transition: .2s;
        }
        .vis-toggle input:checked + .vis-toggle__slider { background: #6366f1; }
        .vis-toggle input:checked + .vis-toggle__slider:before { transform: translateX(18px); }
        /* Delete button */
        .vis-row__delete {
            background: none; border: none; padding: 4px 6px; cursor: pointer;
            color: #ef4444; font-size: 18px; border-radius: 5px; transition: background .15s;
            flex-shrink: 0;
        }
        .vis-row__delete:hover { background: #fee2e2; }
        /* Color dot preview */
        .color-dot {
            width: 14px; height: 14px; border-radius: 50%; display: inline-block; vertical-align: middle; margin-right: 4px;
        }
        /* Save button */
        .vis-save-btn {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: #fff;
            border: none;
            padding: 10px 28px;
            border-radius: 7px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            letter-spacing: .3px;
            transition: opacity .15s, transform .1s;
            display: flex; align-items: center; gap: 8px;
        }
        .vis-save-btn:hover { opacity: .92; transform: translateY(-1px); }
        .vis-save-btn:disabled { opacity: .55; cursor: default; transform: none; }
        /* Add row button */
        .vis-add-btn {
            background: #e0e7ff;
            color: #3730a3;
            border: none;
            padding: 9px 18px;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: flex; align-items: center; gap: 6px;
            transition: background .15s;
        }
        .vis-add-btn:hover { background: #c7d2fe; }
        /* Status message */
        .vis-status {
            font-size: 13px;
            padding: 8px 14px;
            border-radius: 6px;
            display: none;
            margin-top: 12px;
        }
        .vis-status--success { background: #d1fae5; color: #065f46; display: block; }
        .vis-status--error { background: #fee2e2; color: #991b1b; display: block; }
        /* Field key tag */
        .field-key-tag {
            font-size: 11px;
            font-weight: 600;
            background: #f1f5f9;
            color: #64748b;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 2px 7px;
            white-space: nowrap;
        }
        /* Info helper */
        .vis-help {
            background: #f0f9ff;
            border-left: 3px solid #38bdf8;
            padding: 10px 14px;
            border-radius: 0 6px 6px 0;
            font-size: 12.5px;
            color: #0369a1;
            margin-bottom: 20px;
        }
        /* Empty state */
        .vis-empty { text-align: center; padding: 36px 20px; color: #94a3b8; font-size: 14px; }
    </style>

    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Blog Visibility Options</h2>
        <a href="{{ url('blog/side-menu/light') }}" class="button border text-gray-700 mr-2">
            ← Back to Blogs
        </a>
    </div>

    <div class="vis-manager">
        <div class="vis-manager__header">
            <div class="vis-manager__title">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                Manage Visibility Options
                <span class="badge" id="active-count-badge">{{ $options->where('is_active', 1)->count() }} active</span>
            </div>
            <div style="display:flex;gap:10px;align-items:center;">
                <button class="vis-save-btn" id="saveAllBtn" type="button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Save All
                </button>
            </div>
        </div>

        <div class="vis-help">
            <strong>How it works:</strong> Each visibility option maps to a blog database field. Active options appear as checkboxes in the Blog Create &amp; Edit forms, and as dropdowns in the Bulk Edit modal. You can add up to 4 options (one per available field).
        </div>

        <div id="vis-rows-container">
            {{-- Rows will be rendered here --}}
            @if($options->isEmpty())
                <div class="vis-empty" id="vis-empty-msg">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <p class="mt-2">No visibility options yet. Click <strong>Add Option</strong> to get started.</p>
                </div>
            @endif

            @foreach($options as $option)
            <div class="vis-row {{ $option->is_active ? '' : 'vis-row--inactive' }}" data-id="{{ $option->id }}" data-sort="{{ $option->sort_order }}">
                <span class="vis-row__drag" title="Drag to reorder">⠿</span>
                <input type="text" class="vis-row__label-input" placeholder="Option label (e.g. Featured App)" value="{{ $option->label }}" data-field="label" required>
                <input type="hidden" class="vis-row__select" data-field="field_key" value="{{ $option->field_key }}">
                <select class="vis-row__color-select" data-field="color_class" title="Badge color in blog list">
                    <option value="bg-theme-1"  {{ $option->color_class === 'bg-theme-1'  ? 'selected' : '' }}>🔵 Blue</option>
                    <option value="bg-theme-9"  {{ $option->color_class === 'bg-theme-9'  ? 'selected' : '' }}>🟢 Green</option>
                    <option value="bg-theme-6"  {{ $option->color_class === 'bg-theme-6'  ? 'selected' : '' }}>🔴 Red</option>
                    <option value="bg-theme-12" {{ $option->color_class === 'bg-theme-12' ? 'selected' : '' }}>🟡 Yellow</option>
                    <option value="bg-theme-11" {{ $option->color_class === 'bg-theme-11' ? 'selected' : '' }}>🟣 Purple</option>
                </select>
                <label class="vis-toggle" title="{{ $option->is_active ? 'Active' : 'Inactive' }}">
                    <input type="checkbox" class="vis-active-toggle" {{ $option->is_active ? 'checked' : '' }}>
                    <span class="vis-toggle__slider"></span>
                </label>
                <span class="field-key-tag">{{ $option->field_key }}</span>
            </div>
            @endforeach
        </div>

        <div id="vis-status-msg" class="vis-status"></div>
    </div>

    {{-- Hidden template for new rows --}}
    <template id="vis-row-template">
        <div class="vis-row" data-id="" data-sort="999">
            <span class="vis-row__drag" title="Drag to reorder">⠿</span>
            <input type="text" class="vis-row__label-input" placeholder="Option label (e.g. Featured App)" value="" data-field="label" required>
            <input type="hidden" class="vis-row__select" data-field="field_key" value="">
            <select class="vis-row__color-select" data-field="color_class" title="Badge color in blog list">
                <option value="bg-theme-1">🔵 Blue</option>
                <option value="bg-theme-9">🟢 Green</option>
                <option value="bg-theme-6">🔴 Red</option>
                <option value="bg-theme-12">🟡 Yellow</option>
                <option value="bg-theme-11">🟣 Purple</option>
            </select>
            <label class="vis-toggle" title="Active">
                <input type="checkbox" class="vis-active-toggle" checked>
                <span class="vis-toggle__slider"></span>
            </label>
            <span class="field-key-tag" style="display:none;"></span>
        </div>
    </template>

    <script>
    (function() {
        var container = document.getElementById('vis-rows-container');
        var saveBtn   = document.getElementById('saveAllBtn');
        var addBtn    = document.getElementById('addRowBtn');
        var statusEl  = document.getElementById('vis-status-msg');
        var emptyMsg  = document.getElementById('vis-empty-msg');
        var countBadge = document.getElementById('active-count-badge');
        var maxOptions = {{ count($allowedKeys) }}; // 4

        function showStatus(msg, isError) {
            statusEl.textContent = msg;
            statusEl.className = 'vis-status ' + (isError ? 'vis-status--error' : 'vis-status--success');
            setTimeout(function() {
                statusEl.className = 'vis-status';
            }, 4000);
        }

        function updateActiveCount() {
            var active = container.querySelectorAll('.vis-active-toggle:checked').length;
            if (countBadge) countBadge.textContent = active + ' active';
        }

        function updateEmptyMsg() {
            var rows = container.querySelectorAll('.vis-row');
            if (emptyMsg) emptyMsg.style.display = rows.length === 0 ? 'block' : 'none';
        }

        function updateAddBtn() {
            var rows = container.querySelectorAll('.vis-row').length;
            if (addBtn) addBtn.disabled = rows >= maxOptions;
        }

        // Add new row from template
        if (addBtn) {
            addBtn.addEventListener('click', function() {
                var rows = container.querySelectorAll('.vis-row');
                if (rows.length >= maxOptions) {
                    showStatus('Maximum ' + maxOptions + ' visibility options allowed (one per DB field).', true);
                    return;
                }
                var tpl = document.getElementById('vis-row-template');
                var clone = tpl.content.cloneNode(true);
                var row = clone.querySelector('.vis-row');
                
                // Assign an available field key
                var existingKeys = Array.from(container.querySelectorAll('[data-field="field_key"]')).map(function(el) { return el.value; });
                var allowedKeys = {!! json_encode(array_keys($allowedKeys)) !!};
                var newKey = allowedKeys.find(function(k) { return existingKeys.indexOf(k) === -1; });
                row.querySelector('[data-field="field_key"]').value = newKey || '';
                row.querySelector('.field-key-tag').textContent = newKey || '';
                row.querySelector('.field-key-tag').style.display = 'inline-block';
                
                container.appendChild(row);
                bindRowEvents(row);
                updateEmptyMsg();
                updateActiveCount();
                updateAddBtn();
                row.querySelector('.vis-row__label-input').focus();
            });
        }

        // Delete row
        function bindRowEvents(row) {
            var del = row.querySelector('.vis-row__delete');
            if (del) {
                del.addEventListener('click', function() {
                    row.remove();
                    updateEmptyMsg();
                    updateActiveCount();
                    updateAddBtn();
                });
            }
            var toggle = row.querySelector('.vis-active-toggle');
            if (toggle) {
                toggle.addEventListener('change', function() {
                    row.classList.toggle('vis-row--inactive', !toggle.checked);
                    updateActiveCount();
                });
            }
        }

        // Bind existing rows
        container.querySelectorAll('.vis-row').forEach(function(row) { bindRowEvents(row); });

        updateEmptyMsg();
        updateAddBtn();

        // Save all
        if (saveBtn) {
            saveBtn.addEventListener('click', function() {
                saveBtn.disabled = true;
                saveBtn.textContent = 'Saving…';

                var rows = container.querySelectorAll('.vis-row');
                var options = [];
                var sortOrder = 1;
                var valid = true;

                rows.forEach(function(row) {
                    var label = row.querySelector('[data-field="label"]').value.trim();
                    var fieldKey = row.querySelector('[data-field="field_key"]').value;
                    var colorClass = row.querySelector('[data-field="color_class"]').value;
                    var isActive = row.querySelector('.vis-active-toggle').checked ? 1 : 0;
                    var id = row.getAttribute('data-id') || null;

                    if (!label) {
                        row.querySelector('[data-field="label"]').style.borderColor = '#ef4444';
                        valid = false;
                    } else {
                        row.querySelector('[data-field="label"]').style.borderColor = '';
                    }

                    options.push({
                        id: id ? parseInt(id) : null,
                        label: label,
                        field_key: fieldKey,
                        color_class: colorClass,
                        is_active: isActive,
                        sort_order: sortOrder++,
                    });
                });

                if (!valid) {
                    showStatus('Please fill in all option labels.', true);
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Save All';
                    return;
                }

                // Check for duplicate field_key
                var usedKeys = {};
                var dupFound = false;
                options.forEach(function(o) {
                    if (usedKeys[o.field_key]) { dupFound = true; }
                    usedKeys[o.field_key] = true;
                });
                if (dupFound) {
                    showStatus('Each option must map to a unique DB field. Please fix duplicates.', true);
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Save All';
                    return;
                }

                fetch('{{ route("blog.visibility.save") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ options: options })
                })
                .then(function(r) { return r.json(); })
                .then(function(resp) {
                    if (resp.status) {
                        showStatus('✓ Visibility options saved successfully!', false);
                        // Re-render rows with returned data (fresh IDs)
                        if (resp.options && Array.isArray(resp.options)) {
                            container.querySelectorAll('.vis-row').forEach(function(r) { r.remove(); });
                            resp.options.forEach(function(opt) {
                                var tpl = document.getElementById('vis-row-template');
                                var clone = tpl.content.cloneNode(true);
                                var row = clone.querySelector('.vis-row');
                                row.setAttribute('data-id', opt.id);
                                row.setAttribute('data-sort', opt.sort_order);
                                if (!opt.is_active) row.classList.add('vis-row--inactive');
                                row.querySelector('[data-field="label"]').value = opt.label;
                                row.querySelector('[data-field="field_key"]').value = opt.field_key;
                                row.querySelector('[data-field="color_class"]').value = opt.color_class;
                                row.querySelector('.vis-active-toggle').checked = !!opt.is_active;
                                container.appendChild(row);
                                bindRowEvents(row);
                            });
                        }
                        updateEmptyMsg();
                        updateActiveCount();
                        updateAddBtn();
                    } else {
                        showStatus('Error: ' + (resp.message || 'Unknown error'), true);
                    }
                })
                .catch(function(err) {
                    showStatus('Network error: ' + err.message, true);
                })
                .finally(function() {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Save All';
                });
            });
        }
    })();
    </script>
@endsection
