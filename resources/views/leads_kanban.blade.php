@extends('layout')
@section('title', 'Leads Pipeline (Kanban) - eseCRM')

<style>
/* Kanban Filter Bar */
.kb-filter-bar{background:#fff;border:1px solid #e8eaed;border-radius:12px;padding:12px 16px;margin:0 0px 12px;box-shadow:0 1px 4px rgba(0,0,0,.06)}
.kb-filter-row{display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end}
.kb-filter-field{display:flex;flex-direction:column;gap:4px;flex:1 1 150px;min-width:120px}
.kb-filter-label{font-size:.72rem;font-weight:600;color:#5f6368;text-transform:uppercase;letter-spacing:.3px;display:flex;align-items:center;gap:4px}
.kb-filter-input,.kb-filter-select{height:36px;border:1.5px solid #dadce0;border-radius:8px;padding:0 10px;font-size:.82rem;color:#202124;background:#f8f9fa;outline:none;transition:border-color .2s,box-shadow .2s;width:100%}
.kb-filter-input:focus,.kb-filter-select:focus{border-color:#006666;box-shadow:0 0 0 3px rgba(0,102,102,.10);background:#fff}
.kb-filter-actions{flex:0 0 auto;flex-direction:row!important;align-items:flex-end;gap:6px;min-width:auto}
.kb-filter-active-dot{display:inline-block;width:8px;height:8px;background:#f29900;border-radius:50%;margin-left:4px;vertical-align:middle;animation:kbDotPulse 1.5s ease-in-out infinite}
@keyframes kbDotPulse{0%,100%{transform:scale(1);opacity:1}50%{transform:scale(1.4);opacity:.7}}
@media(max-width:576px){.kb-filter-bar{margin:0 8px 10px;padding:10px 12px}.kb-filter-field{flex:1 1 100%}.kb-filter-actions{width:100%}}
</style>

@section('content')

    <section class="task__section">
        @include('inc.header', ['title' => 'Leads Pipeline'])

        <div class="dash-container pb-0">

            {{-- Kanban Toolbar --}}
            <div class="leads-toolbar mb-3">
                <div class="leads-toolbar-left">
                    <div class="kb-stage-legend">
                        <span class="kb-dot" style="background:#1a73e8;"></span> New
                        <span class="kb-dot ms-2" style="background:#f29900;"></span> Contacted
                        <span class="kb-dot ms-2" style="background:#7c3aed;"></span> Qualified
                        <span class="kb-dot ms-2" style="background:#006666;"></span> Proposal
                        <span class="kb-dot ms-2" style="background:#34a853;"></span> Won
                        <span class="kb-dot ms-2" style="background:#ea4335;"></span> Lost
                    </div>
                    <span class="kb-total-badge" id="kbTotalBadge">Loading...</span>
                </div>
                <div class="leads-toolbar-right">
                    <button class="lb-icon-btn" id="kbRefreshBtn" title="Refresh Board">
                        <i class="bx bx-refresh"></i>
                    </button>
                    <a href="/leads" class="lb-btn lb-btn-ghost">
                        <i class="bx bx-list-ul"></i>
                        <span class="d-none d-sm-inline">List View</span>
                    </a>
                    <a href="/manage-lead" class="lb-btn lb-btn-primary">
                        <i class="bx bx-plus"></i>
                        <span class="d-none d-sm-inline">Add Lead</span>
                    </a>
                </div>
            </div>

            {{-- Filter Bar --}}
            <div class="kb-filter-bar" id="kbFilterBar">
                <div class="kb-filter-row">
                    {{-- Search --}}
                    <div class="kb-filter-field">
                        <label class="kb-filter-label"><i class="bx bx-search"></i> Search</label>
                        <input type="text" id="kbSearch" class="kb-filter-input" placeholder="Name, company, mobile…">
                    </div>
                    {{-- Assigned To --}}
                    <div class="kb-filter-field">
                        <label class="kb-filter-label"><i class="bx bx-user"></i> Assigned To</label>
                        <select id="kbAssigned" class="kb-filter-select">
                            <option value="">— All Salespersons —</option>
                            @foreach($getUsers as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Date From --}}
                    <div class="kb-filter-field">
                        <label class="kb-filter-label"><i class="bx bx-calendar"></i> Date From</label>
                        <input type="date" id="kbDateFrom" class="kb-filter-input">
                    </div>
                    {{-- Date To --}}
                    <div class="kb-filter-field">
                        <label class="kb-filter-label"><i class="bx bx-calendar-check"></i> Date To</label>
                        <input type="date" id="kbDateTo" class="kb-filter-input">
                    </div>
                    {{-- Actions --}}
                    <div class="kb-filter-field kb-filter-actions">
                        <button class="lb-btn lb-btn-primary" id="kbApplyFilter">
                            <i class="bx bx-filter-alt"></i> Apply
                        </button>
                        <button class="lb-btn lb-btn-ghost" id="kbResetFilter">
                            <i class="bx bx-x"></i> Reset
                        </button>
                    </div>
                </div>
            </div>

        </div>

        {{-- Kanban Board --}}
        <div class="kb-board" id="kanbanBoard">

            @php
                $stages = [
                    ['key' => 'New',       'status' => 0, 'label' => 'New Leads',     'cls' => 'new',       'color' => '#1a73e8'],
                    ['key' => 'Contacted', 'status' => 1, 'label' => 'Contacted',     'cls' => 'contacted', 'color' => '#f29900'],
                    ['key' => 'Qualified', 'status' => 2, 'label' => 'Qualified',     'cls' => 'qualified', 'color' => '#7c3aed'],
                    ['key' => 'Proposal',  'status' => 3, 'label' => 'Proposal Sent', 'cls' => 'proposal',  'color' => '#006666'],
                    ['key' => 'Closed',    'status' => 5, 'label' => 'Closed (Won)',  'cls' => 'closed',    'color' => '#34a853'],
                    ['key' => 'Lost',      'status' => 9, 'label' => 'Lost',          'cls' => 'lost',      'color' => '#ea4335'],
                ]
            @endphp

            @foreach($stages as $stage)
            <div class="kb-col"
                 data-stage="{{ $stage['key'] }}"
                 data-status="{{ $stage['status'] }}"
                 data-page="1"
                 ondrop="drop(event)"
                 ondragover="allowDrop(event)"
                 ondragleave="dragLeave(event)">

                <div class="kb-col-header kb-col-header-{{ $stage['cls'] }}">
                    <div class="kb-col-title">
                        <span class="kb-col-dot" style="background:{{ $stage['color'] }};"></span>
                        {{ $stage['label'] }}
                    </div>
                    <span class="kb-count" id="count-{{ $stage['key'] }}">
                        <span class="kb-spinner-sm"></span>
                    </span>
                </div>

                {{-- skeleton placeholder --}}
                <div class="kb-items" id="col-{{ $stage['key'] }}">
                    <div class="kb-skeleton"></div>
                    <div class="kb-skeleton" style="height:60px;"></div>
                    <div class="kb-skeleton" style="height:70px;"></div>
                </div>

                <div class="kb-load-more-wrap" id="more-{{ $stage['key'] }}" style="display:none;">
                    <button class="kb-load-more-btn"
                            onclick="loadStage('{{ $stage['key'] }}', {{ $stage['status'] }}, true)">
                        <i class="bx bx-chevron-down"></i>
                        <span class="kb-load-more-label" id="more-label-{{ $stage['key'] }}">Load more</span>
                    </button>
                </div>

            </div>
            @endforeach

        </div>

    </section>

    {{-- Lead Details Popup Modal --}}
    <div class="modal fade" id="kbLeadModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable" style="max-width:540px;">
            <div class="modal-content" style="border-radius:16px; overflow:hidden; border:none;">

                {{-- Header Banner --}}
                <div class="ld-header">
                    <div class="ld-header-content">
                        <div class="ld-avatar" id="kb_leadAvatar">L</div>
                        <div style="flex:1; min-width:0;">
                            <div class="ld-name" id="kb_leadName">—</div>
                            <span class="ld-company" id="kb_leadCompany">—</span>
                            <div class="mt-1">
                                <span class="ld-status-chip" id="kb_leadStatus">—</span>
                            </div>
                        </div>
                        <div class="d-flex gap-1 align-items-center">
                            <a href="#" class="ld-quick-btn" id="kb_btnCall" title="Call"><i class="bx bx-phone"></i></a>
                            <a href="#" class="ld-quick-btn ld-quick-wa" id="kb_btnWa" title="WhatsApp"><i class="bx bxl-whatsapp"></i></a>
                            <a href="#" class="ld-quick-btn ld-quick-mail" id="kb_btnMail" title="Email"><i class="bx bx-envelope"></i></a>
                            <button type="button" class="ld-close-btn" data-bs-dismiss="modal"><i class="bx bx-x"></i></button>
                        </div>
                    </div>
                </div>

                {{-- Modal Body --}}
                <div class="modal-body p-0">

                    {{-- Tabs --}}
                    <div class="ld-tab-nav">
                        <button class="ld-tab active" onclick="kbTab(this,'kb-tab-info')">
                            <i class="bx bx-user"></i> Info
                        </button>
                        <button class="ld-tab" onclick="kbTab(this,'kb-tab-conv')">
                            <i class="bx bx-message-dots"></i> Conversations
                        </button>
                    </div>

                    {{-- Info Tab --}}
                    <div id="kb-tab-info" style="padding:16px;">
                        <div class="ld-info-grid" id="kb_infoGrid">
                            {{-- Contact Card --}}
                            <div class="ld-info-card">
                                <div class="ld-info-card-header"><i class="bx bx-phone-call"></i> Contact</div>
                                <div class="ld-info-row"><span class="ld-info-label">Mobile</span><span class="ld-info-val" id="kb_mob">—</span></div>
                                <div class="ld-info-row"><span class="ld-info-label">WhatsApp</span><span class="ld-info-val" id="kb_wa">—</span></div>
                                <div class="ld-info-row"><span class="ld-info-label">Email</span><span class="ld-info-val" id="kb_email">—</span></div>
                            </div>
                            {{-- Business Card --}}
                            <div class="ld-info-card">
                                <div class="ld-info-card-header"><i class="bx bx-buildings"></i> Business</div>
                                <div class="ld-info-row"><span class="ld-info-label">Company</span><span class="ld-info-val" id="kb_company">—</span></div>
                                <div class="ld-info-row"><span class="ld-info-label">Industry</span><span class="ld-info-val" id="kb_industry">—</span></div>
                                <div class="ld-info-row"><span class="ld-info-label">Website</span><span class="ld-info-val" id="kb_website">—</span></div>
                            </div>
                            {{-- CRM Card (full width) --}}
                            <div class="ld-info-card" style="grid-column:1/-1;">
                                <div class="ld-info-card-header"><i class="bx bx-brain"></i> CRM Intelligence</div>
                                <div class="ld-info-row"><span class="ld-info-label">Purpose</span><span class="ld-info-val" id="kb_purpose">—</span></div>
                                <div class="ld-info-row"><span class="ld-info-label">Lead Value</span><span class="ld-info-val" id="kb_value">—</span></div>
                                <div class="ld-info-row"><span class="ld-info-label">Assigned</span><span class="ld-info-val" id="kb_assigned">—</span></div>
                                <div class="ld-info-row"><span class="ld-info-label">Tags</span><span class="ld-info-val" id="kb_tags">—</span></div>
                            </div>
                        </div>
                        <div class="ld-action-bar">
                            <a href="#" id="kb_editBtn" class="ld-btn ld-btn-primary"><i class="bx bx-edit-alt"></i> Edit Lead</a>
                        </div>
                    </div>

                    {{-- Conversations Tab --}}
                    <div id="kb-tab-conv" style="display:none; padding:16px;">
                        <div id="kb_timeline" style="padding-left:8px;">
                            <p class="text-muted text-center" style="font-size:0.82rem;">Loading…</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        const KANBAN_URL = "{{ route('leads.kanban_data') }}";
        const CSRF_TOKEN = "{{ csrf_token() }}";
        const LIMIT      = 15;

        const stageColors = {
            'New':       { border: '#1a73e8', bg: 'rgba(26,115,232,0.08)' },
            'Contacted': { border: '#f29900', bg: 'rgba(242,153,0,0.08)'  },
            'Qualified': { border: '#7c3aed', bg: 'rgba(124,58,237,0.10)' },
            'Proposal':  { border: '#006666', bg: 'rgba(0,102,102,0.08)'  },
            'Closed':    { border: '#34a853', bg: 'rgba(52,168,83,0.08)'  },
            'Lost':      { border: '#ea4335', bg: 'rgba(234,67,53,0.08)'  },
        };

        // State: pages loaded per stage
        const colPages = {
            'New': 1, 'Contacted': 1, 'Qualified': 1,
            'Proposal': 1, 'Closed': 1, 'Lost': 1
        };

        const stageStatusMap = {
            'New': 0, 'Contacted': 1, 'Qualified': 2,
            'Proposal': 3, 'Closed': 5, 'Lost': 9
        };

        // Build a card's HTML
        function buildCard(lead, stage) {
            const color   = stageColors[stage];
            const initial = (lead.name || lead.company || 'L').charAt(0).toUpperCase();

            // Header: name + company — always shown separately
            const nameHtml    = `<div class="kb-card-name">${escHtml(lead.name || '—')}</div>`;
            const companyHtml = lead.company
                ? `<div class="kb-card-company"><i class="bx bx-buildings"></i> ${escHtml(lead.company)}</div>` : '';

            // Source badge
            const sourceHtml = lead.source
                ? `<span class="kb-card-source"><i class="bx bx-user-plus"></i> ${escHtml(lead.source)}</span>` : '';

            // Lead value chip
            const valueHtml = lead.values
                ? `<span class="kb-card-value-chip"><i class="bx bx-rupee"></i>${escHtml(String(lead.values))}</span>` : '';

            // Contact action buttons — always visible
            const waBtn    = lead.whatsapp
                ? `<a href="https://api.whatsapp.com/send/?phone=${encodeURIComponent(lead.whatsapp)}&text=Hi&type=phone_number&app_absent=0" target="_blank" class="kb-action-btn kb-action-wa" title="WhatsApp" onclick="event.stopPropagation();"><i class="bx bxl-whatsapp"></i></a>` : '';
            const callBtn  = lead.mob
                ? `<a href="tel:+${encodeURIComponent(lead.mob)}" class="kb-action-btn kb-action-call" title="Call ${escHtml(lead.mob)}" onclick="event.stopPropagation();"><i class="bx bx-phone"></i></a>` : '';
            const emailBtn = lead.email
                ? `<a href="mailto:${escHtml(lead.email)}" class="kb-action-btn kb-action-email" title="Email ${escHtml(lead.email)}" onclick="event.stopPropagation();"><i class="bx bx-envelope"></i></a>` : '';
            const editBtn  = `<a href="/manage-lead?id=${lead.id}&from=kanban" class="kb-action-btn kb-action-edit" title="Edit Lead" onclick="event.stopPropagation();"><i class="bx bx-edit-alt"></i></a>`;

            return `
                <div class="kb-card" id="lead-${lead.id}" draggable="true"
                     ondragstart="drag(event)" data-id="${lead.id}"
                     style="border-left-color:${color.border};">

                    {{-- Top: avatar + name + company --}}
                    <div class="kb-card-header">
                        <div class="kb-card-avatar" style="background:${color.bg}; color:${color.border};">${initial}</div>
                        <div class="kb-card-name-block">
                            ${nameHtml}
                            ${companyHtml}
                        </div>
                    </div>

                    {{-- Meta row: source + value --}}
                    <div class="kb-card-meta-row">
                        ${sourceHtml}
                        ${valueHtml}
                    </div>

                    {{-- Action buttons — always visible --}}
                    <div class="kb-card-actions kb-card-actions-visible">
                        ${waBtn}${callBtn}${emailBtn}
                        <span class="kb-action-spacer"></span>
                        ${editBtn}
                    </div>
                </div>`;
        }


        function escHtml(s) {
            return String(s)
                .replace(/&/g,'&amp;').replace(/</g,'&lt;')
                .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }

        // ── Get current filter values ──
        function getFilters() {
            return {
                search:    $('#kbSearch').val().trim(),
                assigned:  $('#kbAssigned').val(),
                date_from: $('#kbDateFrom').val(),
                date_to:   $('#kbDateTo').val(),
            };
        }

        // Load (or append) cards for one stage
        function loadStage(stage, statusInt, append) {
            const page   = append ? (colPages[stage] + 1) : 1;
            const colEl  = $('#col-' + stage);
            const moreEl = $('#more-' + stage);
            const cntEl  = $('#count-' + stage);

            if (!append) {
                colEl.html('<div class="kb-skeleton"></div><div class="kb-skeleton" style="height:60px;"></div>');
                moreEl.hide();
            } else {
                $(`#more-label-${stage}`).html('<i class="bx bx-loader-alt bx-spin"></i> Loading...');
            }

            var params = Object.assign({ stage: statusInt, page: page, limit: LIMIT }, getFilters());

            $.get(KANBAN_URL, params, function (res) {
                if (!append) colEl.empty();

                res.data.forEach(function (lead) {
                    colEl.append(buildCard(lead, stage));
                });

                colPages[stage] = page;

                // Update count badge
                cntEl.text(res.total);

                // Load More button
                if (res.has_more) {
                    const remaining = res.total - (page * LIMIT);
                    $(`#more-label-${stage}`).html(
                        `<i class="bx bx-chevron-down"></i> Load ${Math.min(remaining, LIMIT)} more of ${remaining}`
                    );
                    moreEl.show();
                } else {
                    moreEl.hide();
                }
            }).fail(function () {
                if (!append) colEl.html('<div class="kb-empty-col"><i class="bx bx-error"></i> Failed to load</div>');
            });
        }

        // Load SUMMARY counts first (cheap), then load each column
        function initBoard() {
            let totalAll = 0;
            var filters  = getFilters();

            $.get(KANBAN_URL, filters, function (res) {
                if (res.counts) {
                    for (let key in res.counts) {
                        totalAll += res.counts[key];
                    }
                }
                // Show active filter indicator if any filter is set
                var hasFilter = filters.search || filters.assigned || filters.date_from || filters.date_to;
                var badge = totalAll + ' lead' + (totalAll === 1 ? '' : 's');
                if (hasFilter) badge += ' <span class="kb-filter-active-dot" title="Filters active"></span>';
                $('#kbTotalBadge').html(badge);
            });

            // Load each column independently (parallel)
            const stages = [
                { key: 'New',       status: 0 },
                { key: 'Contacted', status: 1 },
                { key: 'Qualified', status: 2 },
                { key: 'Proposal',  status: 3 },
                { key: 'Closed',    status: 5 },
                { key: 'Lost',      status: 9 },
            ];
            stages.forEach(function (s) {
                colPages[s.key] = 1;
                loadStage(s.key, s.status, false);
            });
        }

        $(document).ready(function () {
            initBoard();

            // Refresh button
            $('#kbRefreshBtn').on('click', function () {
                $(this).find('i').addClass('bx-spin');
                setTimeout(() => $(this).find('i').removeClass('bx-spin'), 1200);
                initBoard();
            });

            // Apply filters
            $('#kbApplyFilter').on('click', function () { initBoard(); });
            $('#kbSearch').on('keydown', function (e) {
                if (e.key === 'Enter') initBoard();
            });

            // Reset filters
            $('#kbResetFilter').on('click', function () {
                $('#kbSearch').val('');
                $('#kbAssigned').val('');
                $('#kbDateFrom').val('');
                $('#kbDateTo').val('');
                initBoard();
            });
        });

        /* ── Kanban Card Double-Click → Lead Details Popup ── */
        var kbUserMap = {!! json_encode($getUsers->pluck('name','id')) !!};

        var kbStatusLabels = {0:'New',1:'Contacted',2:'Qualified',3:'Proposal Sent',5:'Closed (Won)',9:'Lost'};
        var kbStatusColors = {0:'#5f6368',1:'#f29900',2:'#7c3aed',3:'#006666',5:'#34a853',9:'#ea4335'};

        $(document).on('dblclick', '.kb-card', function (e) {
            e.stopPropagation();
            var id = $(this).data('id');
            if (!id) return;

            // Reset to Info tab
            kbTab($('.ld-tab').first()[0], 'kb-tab-info');
            $('#kb_timeline').html('<p class="text-muted text-center" style="font-size:0.82rem;">Loading…</p>');

            // Open modal immediately
            var modal = new bootstrap.Modal(document.getElementById('kbLeadModal'));
            modal.show();

            $.get('/get-lead-details/' + id, function (data) {
                var l = data.lead;
                var loc = {};
                try { loc = JSON.parse(l.location) || {}; } catch(e) {}

                // Header
                $('#kb_leadAvatar').text((l.name || 'L').charAt(0).toUpperCase());
                $('#kb_leadName').text(l.name || '—');
                $('#kb_leadCompany').text(l.company || '—');

                var sl = kbStatusLabels[l.status] || 'New';
                var sc = kbStatusColors[l.status] || '#5f6368';
                $('#kb_leadStatus').text(sl).css({'background': sc+'18','color': sc,'border-color': sc+'40'});

                $('#kb_btnCall').attr('href', l.mob   ? 'tel:+'+l.mob                    : '#');
                $('#kb_btnWa').attr('href',   l.whatsapp ? 'https://wa.me/'+l.whatsapp  : '#');
                $('#kb_btnMail').attr('href', l.email ? 'mailto:'+l.email               : '#');

                // Info cards
                $('#kb_mob').text(l.mob ? '+'+l.mob : '—');
                $('#kb_wa').text(l.whatsapp ? '+'+l.whatsapp : '—');
                $('#kb_email').text(l.email || '—');
                $('#kb_company').text(l.company || '—');
                $('#kb_industry').text(l.industry || '—');
                $('#kb_website').html(l.website ? '<a href="'+l.website+'" target="_blank">'+l.website+'</a>' : '—');
                $('#kb_purpose').text(l.purpose || '—');
                $('#kb_value').text(l.values ? '₹'+Number(l.values).toLocaleString('en-IN') : '—');
                $('#kb_assigned').text(kbUserMap[l.assigned] || l.assigned || '—');
                $('#kb_tags').text(l.tags || '—');

                // Edit button
                $('#kb_editBtn').attr('href', '/manage-lead?id='+id+'&from=kanban');

                // Conversations timeline
                var html = '';
                (data.comments || []).forEach(function (c) {
                    html += '<div class="ld-timeline-item">'
                          + '<div class="ld-tl-dot"></div>'
                          + '<div class="ld-tl-body">'
                          + '<div class="ld-tl-meta">'+(c.next_date || c.created_at)+'</div>'
                          + '<p class="ld-tl-msg">'+c.msg+'</p>'
                          + '</div></div>';
                });
                $('#kb_timeline').html(html || '<p class="text-muted text-center py-3" style="font-size:0.82rem;">No conversations yet.</p>');
            });
        });

        function kbTab(btn, tabId) {
            $('.ld-tab').removeClass('active');
            $(btn).addClass('active');
            $('#kb-tab-info, #kb-tab-conv').hide();
            $('#' + tabId).show();
        }

        /* ── Drag & Drop ─────────────────────────────────────────── */
        function allowDrop(ev) {
            ev.preventDefault();
            $(ev.currentTarget).addClass('kb-drag-over');
        }
        function dragLeave(ev) {
            $(ev.currentTarget).removeClass('kb-drag-over');
        }
        function drag(ev) {
            ev.dataTransfer.setData("text",   ev.target.id);
            ev.dataTransfer.setData("leadId", $(ev.target).data('id'));
        }
        function drop(ev) {
            ev.preventDefault();
            $(ev.currentTarget).removeClass('kb-drag-over');

            const elemId  = ev.dataTransfer.getData("text");
            const leadId  = ev.dataTransfer.getData("leadId");
            const newStage = $(ev.currentTarget).data('stage');

            const container = $(ev.currentTarget).find('.kb-items')[0];
            const elem = document.getElementById(elemId);
            if (!elem || !container) return;
            container.appendChild(elem);

            const revMap = { 'New':0,'Contacted':1,'Qualified':2,'Proposal':3,'Closed':5,'Lost':9 };
            updateLeadStage(leadId, revMap[newStage], newStage, elem);
        }
        function updateLeadStage(leadId, newStatus, newStage, elem) {
            // Optimistic: update card border color immediately
            const color = stageColors[newStage];
            if (color) $(elem).css('border-left-color', color.border);

            $.post("{{ route('leads.update_status') }}", {
                _token: CSRF_TOKEN,
                id: leadId,
                status: newStatus
            }).fail(function () {
                alert('Error updating lead status. Refreshing...');
                initBoard();
            });
        }
    </script>

@endsection