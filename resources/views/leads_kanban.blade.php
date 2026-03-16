@extends('layout')
@section('title', 'Leads Pipeline (Kanban) - eseCRM')

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
                        <span class="kb-dot ms-2" style="background:#9334e9;"></span> Qualified
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

        </div>

        {{-- Kanban Board --}}
        <div class="kb-board" id="kanbanBoard">

            @php
                $stages = [
                    ['key' => 'New',       'status' => 0, 'label' => 'New Leads',     'cls' => 'new',       'color' => '#1a73e8'],
                    ['key' => 'Contacted', 'status' => 1, 'label' => 'Contacted',     'cls' => 'contacted', 'color' => '#f29900'],
                    ['key' => 'Qualified', 'status' => 2, 'label' => 'Qualified',     'cls' => 'qualified', 'color' => '#9334e9'],
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        const KANBAN_URL = "{{ route('leads.kanban_data') }}";
        const CSRF_TOKEN = "{{ csrf_token() }}";
        const LIMIT      = 15;

        const stageColors = {
            'New':       { border: '#1a73e8', bg: 'rgba(26,115,232,0.08)' },
            'Contacted': { border: '#f29900', bg: 'rgba(242,153,0,0.08)'  },
            'Qualified': { border: '#9334e9', bg: 'rgba(147,52,233,0.08)' },
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
            const initial = (lead.name || 'L').charAt(0).toUpperCase();
            const company = lead.company
                ? `<div class="kb-card-meta"><i class="bx bx-buildings"></i> ${escHtml(lead.company)}</div>` : '';
            const mobile  = lead.mob
                ? `<div class="kb-card-meta"><i class="bx bx-phone"></i> ${escHtml(lead.mob)}</div>` : '';
            const value   = lead.values
                ? `<span class="kb-card-value"><i class="bx bx-rupee"></i>${escHtml(String(lead.values))}</span>` : '';
            const poc     = lead.poc
                ? `<span class="kb-card-poc"><i class="bx bx-user-check"></i> ${escHtml(lead.poc)}</span>` : '';
            const purpose = lead.purpose
                ? `<div class="kb-card-tag">${escHtml(lead.purpose)}</div>` : '';

            return `
                <div class="kb-card" id="lead-${lead.id}" draggable="true"
                     ondragstart="drag(event)" data-id="${lead.id}"
                     style="border-left-color:${color.border};">
                    <div class="kb-card-header">
                        <div class="kb-card-avatar" style="background:${color.bg}; color:${color.border};">${initial}</div>
                        <div class="kb-card-name-block">
                            <div class="kb-card-name">${escHtml(lead.name || '')}</div>
                            ${purpose}
                        </div>
                    </div>
                    ${company}${mobile}
                    <div class="kb-card-footer">${value}${poc}</div>
                </div>`;
        }

        function escHtml(s) {
            return String(s)
                .replace(/&/g,'&amp;').replace(/</g,'&lt;')
                .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
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
                // Show spinner on Load More btn
                $(`#more-label-${stage}`).html('<i class="bx bx-loader-alt bx-spin"></i> Loading...');
            }

            $.get(KANBAN_URL, { stage: statusInt, page: page, limit: LIMIT }, function (res) {
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
            $.get(KANBAN_URL, function (res) {
                if (res.counts) {
                    for (let key in res.counts) {
                        totalAll += res.counts[key];
                    }
                }
                $('#kbTotalBadge').text(totalAll + ' total leads');
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

            $('#kbRefreshBtn').on('click', function () {
                $(this).find('i').addClass('bx-spin');
                setTimeout(() => $(this).find('i').removeClass('bx-spin'), 1200);
                initBoard();
            });
        });

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