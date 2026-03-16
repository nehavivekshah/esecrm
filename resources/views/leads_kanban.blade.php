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
                </div>
                <div class="leads-toolbar-right">
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

            {{-- New --}}
            <div class="kb-col" data-stage="New" ondrop="drop(event)" ondragover="allowDrop(event)" ondragleave="dragLeave(event)">
                <div class="kb-col-header kb-col-header-new">
                    <div class="kb-col-title">
                        <span class="kb-col-dot" style="background:#1a73e8;"></span>
                        New Leads
                    </div>
                    <span class="kb-count" id="count-New">0</span>
                </div>
                <div class="kb-items" id="col-New"></div>
            </div>

            {{-- Contacted --}}
            <div class="kb-col" data-stage="Contacted" ondrop="drop(event)" ondragover="allowDrop(event)" ondragleave="dragLeave(event)">
                <div class="kb-col-header kb-col-header-contacted">
                    <div class="kb-col-title">
                        <span class="kb-col-dot" style="background:#f29900;"></span>
                        Contacted
                    </div>
                    <span class="kb-count" id="count-Contacted">0</span>
                </div>
                <div class="kb-items" id="col-Contacted"></div>
            </div>

            {{-- Qualified --}}
            <div class="kb-col" data-stage="Qualified" ondrop="drop(event)" ondragover="allowDrop(event)" ondragleave="dragLeave(event)">
                <div class="kb-col-header kb-col-header-qualified">
                    <div class="kb-col-title">
                        <span class="kb-col-dot" style="background:#9334e9;"></span>
                        Qualified
                    </div>
                    <span class="kb-count" id="count-Qualified">0</span>
                </div>
                <div class="kb-items" id="col-Qualified"></div>
            </div>

            {{-- Proposal Sent --}}
            <div class="kb-col" data-stage="Proposal" ondrop="drop(event)" ondragover="allowDrop(event)" ondragleave="dragLeave(event)">
                <div class="kb-col-header kb-col-header-proposal">
                    <div class="kb-col-title">
                        <span class="kb-col-dot" style="background:#006666;"></span>
                        Proposal Sent
                    </div>
                    <span class="kb-count" id="count-Proposal">0</span>
                </div>
                <div class="kb-items" id="col-Proposal"></div>
            </div>

            {{-- Closed Won --}}
            <div class="kb-col" data-stage="Closed" ondrop="drop(event)" ondragover="allowDrop(event)" ondragleave="dragLeave(event)">
                <div class="kb-col-header kb-col-header-closed">
                    <div class="kb-col-title">
                        <span class="kb-col-dot" style="background:#34a853;"></span>
                        Closed (Won)
                    </div>
                    <span class="kb-count" id="count-Closed">0</span>
                </div>
                <div class="kb-items" id="col-Closed"></div>
            </div>

            {{-- Lost --}}
            <div class="kb-col" data-stage="Lost" ondrop="drop(event)" ondragover="allowDrop(event)" ondragleave="dragLeave(event)">
                <div class="kb-col-header kb-col-header-lost">
                    <div class="kb-col-title">
                        <span class="kb-col-dot" style="background:#ea4335;"></span>
                        Lost
                    </div>
                    <span class="kb-count" id="count-Lost">0</span>
                </div>
                <div class="kb-items" id="col-Lost"></div>
            </div>

        </div>

    </section>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        const stageColors = {
            'New':       { border: '#1a73e8', bg: 'rgba(26,115,232,0.06)' },
            'Contacted': { border: '#f29900', bg: 'rgba(242,153,0,0.06)' },
            'Qualified': { border: '#9334e9', bg: 'rgba(147,52,233,0.06)' },
            'Proposal':  { border: '#006666', bg: 'rgba(0,102,102,0.06)' },
            'Closed':    { border: '#34a853', bg: 'rgba(52,168,83,0.06)' },
            'Lost':      { border: '#ea4335', bg: 'rgba(234,67,53,0.06)' },
        };

        function loadKanban() {
            $.get("{{ route('leads.kanban_data') }}", function (res) {
                $('.kb-items').empty();

                const stageMap = {
                    '0': 'New',
                    '1': 'Contacted',
                    '2': 'Qualified',
                    '3': 'Proposal',
                    '5': 'Closed',
                    '9': 'Lost'
                };

                let counts = { 'New': 0, 'Contacted': 0, 'Qualified': 0, 'Proposal': 0, 'Closed': 0, 'Lost': 0 };

                res.data.forEach(function (lead) {
                    let stage    = stageMap[lead.status] || 'New';
                    let color    = stageColors[stage];
                    counts[stage]++;

                    let initial  = (lead.name || 'L').charAt(0).toUpperCase();
                    let company  = lead.company ? `<div class="kb-card-meta"><i class="bx bx-buildings"></i> ${lead.company}</div>` : '';
                    let mobile   = lead.mob    ? `<div class="kb-card-meta"><i class="bx bx-phone"></i> ${lead.mob}</div>` : '';
                    let value    = lead.values ? `<span class="kb-card-value"><i class="bx bx-rupee"></i>${lead.values}</span>` : '';
                    let poc      = lead.poc    ? `<span class="kb-card-poc"><i class="bx bx-user-check"></i> ${lead.poc}</span>` : '';
                    let purpose  = lead.purpose ? `<div class="kb-card-tag">${lead.purpose}</div>` : '';

                    let cardHtml = `
                        <div class="kb-card" id="lead-${lead.id}" draggable="true"
                             ondragstart="drag(event)" data-id="${lead.id}"
                             style="border-left-color:${color.border};">
                            <div class="kb-card-header">
                                <div class="kb-card-avatar" style="background:${color.bg}; color:${color.border};">${initial}</div>
                                <div class="kb-card-name-block">
                                    <div class="kb-card-name">${lead.name || ''}</div>
                                    ${purpose}
                                </div>
                            </div>
                            ${company}
                            ${mobile}
                            <div class="kb-card-footer">
                                ${value}
                                ${poc}
                            </div>
                        </div>
                    `;
                    $('#col-' + stage).append(cardHtml);
                });

                for (let key in counts) {
                    $('#count-' + key).text(counts[key]);
                }
            });
        }

        $(document).ready(function () {
            loadKanban();
        });

        function allowDrop(ev) {
            ev.preventDefault();
            $(ev.currentTarget).addClass('kb-drag-over');
        }

        function dragLeave(ev) {
            $(ev.currentTarget).removeClass('kb-drag-over');
        }

        function drag(ev) {
            ev.dataTransfer.setData("text", ev.target.id);
            ev.dataTransfer.setData("leadId", $(ev.target).data('id'));
        }

        function drop(ev) {
            ev.preventDefault();
            $(ev.currentTarget).removeClass('kb-drag-over');

            var data   = ev.dataTransfer.getData("text");
            var leadId = ev.dataTransfer.getData("leadId");

            var container = $(ev.currentTarget).find('.kb-items')[0];
            container.appendChild(document.getElementById(data));

            var newStage = $(ev.currentTarget).data('stage');

            const revStageMap = {
                'New': 0, 'Contacted': 1, 'Qualified': 2, 'Proposal': 3, 'Closed': 5, 'Lost': 9
            };
            updateLeadStage(leadId, revStageMap[newStage]);
        }

        function updateLeadStage(leadId, newStatus) {
            $.post("{{ route('leads.update_status') }}", {
                _token: "{{ csrf_token() }}",
                id: leadId,
                status: newStatus
            }, function (res) {
                loadKanban();
            }).fail(function () {
                alert('Error updating lead status.');
                loadKanban();
            });
        }
    </script>

@endsection