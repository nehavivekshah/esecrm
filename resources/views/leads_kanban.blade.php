@extends('layout')
@section('title', 'Leads Kanban Board - eseCRM')

@section('content')
    <style>
        .kanban-board {
            display: flex;
            overflow-x: auto;
            padding: 20px 0;
            min-height: calc(100vh - 150px);
            background: #f4f6f9;
            gap: 20px;
        }

        .kanban-col {
            min-width: 300px;
            background: #e9ecef;
            border-radius: 8px;
            padding: 15px;
            display: flex;
            flex-direction: column;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .kanban-header {
            font-weight: bold;
            padding-bottom: 10px;
            border-bottom: 2px solid #ddd;
            margin-bottom: 15px;
            text-transform: uppercase;
            font-size: 14px;
            color: #495057;
            display: flex;
            justify-content: space-between;
        }

        .kanban-item {
            background: #fff;
            padding: 15px;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 15px;
            cursor: grab;
            border-left: 4px solid #007bff;
            transition: transform 0.2s ease;
        }

        .kanban-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .item-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
            font-size: 15px;
        }

        .item-company {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 10px;
        }

        .item-footer {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: #888;
            border-top: 1px solid #eee;
            padding-top: 8px;
        }

        .badge-score {
            background: #28a745;
            color: #fff;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 11px;
        }

        .kanban-col.drag-over {
            background: #d4d8db;
        }
    </style>

    <section class="task__section">
        <div class="text">
            <i class="bx bx-menu" id="mbtn"></i>
            Leads Pipeline (Kanban)
        </div>

        <div class="container-fluid">
            <div class="d-flex justify-content-between my-3">
                <div>
                    <a href="/manage-lead" class="btn btn-primary btn-sm"><i class="bx bx-plus"></i> Add Lead</a>
                    <a href="/leads" class="btn btn-secondary btn-sm"><i class="bx bx-list-ul"></i> List View</a>
                </div>
            </div>

            <div class="kanban-board" id="kanbanBoard">
                <!-- Stages -->
                <div class="kanban-col" data-stage="New" ondrop="drop(event)" ondragover="allowDrop(event)">
                    <div class="kanban-header">
                        <span>New Leads</span>
                        <span class="badge bg-secondary" id="count-New">0</span>
                    </div>
                    <div class="kanban-items" id="col-New"></div>
                </div>

                <div class="kanban-col" data-stage="Contacted" ondrop="drop(event)" ondragover="allowDrop(event)">
                    <div class="kanban-header">
                        <span>Contacted</span>
                        <span class="badge bg-secondary" id="count-Contacted">0</span>
                    </div>
                    <div class="kanban-items" id="col-Contacted"></div>
                </div>

                <div class="kanban-col" data-stage="Qualified" ondrop="drop(event)" ondragover="allowDrop(event)">
                    <div class="kanban-header">
                        <span>Qualified</span>
                        <span class="badge bg-secondary" id="count-Qualified">0</span>
                    </div>
                    <div class="kanban-items" id="col-Qualified"></div>
                </div>

                <div class="kanban-col" data-stage="Proposal" ondrop="drop(event)" ondragover="allowDrop(event)">
                    <div class="kanban-header">
                        <span>Proposal Sent</span>
                        <span class="badge bg-secondary" id="count-Proposal">0</span>
                    </div>
                    <div class="kanban-items" id="col-Proposal"></div>
                </div>

                <div class="kanban-col" data-stage="Closed" ondrop="drop(event)" ondragover="allowDrop(event)">
                    <div class="kanban-header" style="border-left-color: #28a745">
                        <span>Closed (Won)</span>
                        <span class="badge bg-secondary" id="count-Closed">0</span>
                    </div>
                    <div class="kanban-items" id="col-Closed"></div>
                </div>

                <div class="kanban-col" data-stage="Lost" ondrop="drop(event)" ondragover="allowDrop(event)">
                    <div class="kanban-header" style="border-left-color: #dc3545">
                        <span>Lost</span>
                        <span class="badge bg-secondary" id="count-Lost">0</span>
                    </div>
                    <div class="kanban-items" id="col-Lost"></div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function loadKanban() {
            $.get("{{ route('leads.kanban_data') }}", function (res) {
                $('.kanban-items').empty();

                // Map status integers to our string stages
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
                    let stageStr = stageMap[lead.status] || 'New';
                    counts[stageStr]++;

                    let scoreHtml = lead.score ? `<span class="badge-score">Score: ${lead.score}</span>` : '';
                    let valueHtml = lead.values ? `<span><i class='bx bx-rupee'></i>${lead.values}</span>` : '';

                    let cardHtml = `
                        <div class="kanban-item" id="lead-${lead.id}" draggable="true" ondragstart="drag(event)" data-id="${lead.id}">
                            <div class="item-title">${lead.name}</div>
                            <div class="item-company"><i class='bx bx-building'></i> ${lead.company || 'N/A'}</div>
                            <div class="item-company"><i class='bx bx-phone'></i> ${lead.mob}</div>
                            <div class="item-footer">
                                ${valueHtml}
                                ${scoreHtml}
                            </div>
                        </div>
                    `;
                    $('#col-' + stageStr).append(cardHtml);
                });

                // Update counts
                for (let key in counts) {
                    $('#count-' + key).text(counts[key]);
                }
            });
        }

        $(document).ready(function () {
            loadKanban();
        });

        // Drag and Drop Logic
        function allowDrop(ev) {
            ev.preventDefault();
            $(ev.currentTarget).addClass('drag-over');
        }

        function drag(ev) {
            ev.dataTransfer.setData("text", ev.target.id);
            ev.dataTransfer.setData("leadId", $(ev.target).data('id'));
        }

        // Handles the mouse leave events and drops
        $('.kanban-col').on('dragleave drop', function (e) {
            $(this).removeClass('drag-over');
        });

        function drop(ev) {
            ev.preventDefault();
            $(ev.currentTarget).removeClass('drag-over');

            var data = ev.dataTransfer.getData("text");
            var leadId = ev.dataTransfer.getData("leadId");

            // Find the kanban-items container within the dropped column
            var container = $(ev.currentTarget).find('.kanban-items')[0];
            container.appendChild(document.getElementById(data));

            var newStage = $(ev.currentTarget).data('stage');

            // Map string stage back to integer
            const revStageMap = {
                'New': 0, 'Contacted': 1, 'Qualified': 2, 'Proposal': 3, 'Closed': 5, 'Lost': 9
            };
            var statusInt = revStageMap[newStage];

            updateLeadStage(leadId, statusInt);
        }

        function updateLeadStage(leadId, newStatus) {
            $.post("{{ route('leads.update_status') }}", {
                _token: "{{ csrf_token() }}",
                id: leadId,
                status: newStatus
            }, function (res) {
                console.log('Lead updated successfully');
                // Re-calc counts visually without total reload
                loadKanban();
            }).fail(function () {
                alert('Error updating lead status.');
                loadKanban(); // revert if failed
            });
        }
    </script>
@endsection