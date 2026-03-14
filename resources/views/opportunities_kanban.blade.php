@extends('layout')
@section('title', 'Sales Pipeline - eseCRM')

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
            min-width: 320px;
            background: #f8f9fa; /* Google background */
            border-radius: 8px;
            padding: 15px;
            display: flex;
            flex-direction: column;
            border: 1px solid #dadce0; /* Google border */
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
            border-radius: 8px;
            border: 1px solid #dadce0; /* Google flat card */
            margin-bottom: 15px;
            cursor: grab;
            border-left: 4px solid var(--accent-primary); /* Use theme color */
            transition: box-shadow 0.2s ease, transform 0.2s ease;
        }

        .kanban-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 1px 2px 0 rgba(60,64,67,0.3), 0 1px 3px 1px rgba(60,64,67,0.15); /* Google hover shadow */
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
            margin-bottom: 5px;
        }

        .item-footer {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            color: #444;
            border-top: 1px solid #eee;
            padding-top: 8px;
            font-weight: 600;
        }

        .kanban-col.drag-over {
            background: #d4d8db;
        }

        .total-val {
            font-size: 12px;
            color: #28a745;
        }
    </style>

    <section class="task__section">
        <div class="text">
            <i class="bx bx-menu" id="mbtn"></i>
            Sales Pipeline (Opportunities)
        </div>

        <div class="container-fluid">
            <div class="d-flex justify-content-between my-3">
                <div>
                    <button class="btn btn-indigo rounded-pill" data-bs-toggle="modal" data-bs-target="#addOpportunityModal">
                        <i class="bx bx-plus"></i> New Deal
                    </button>
                </div>
            </div>

            <div class="kanban-board" id="kanbanBoard">
                <!-- Stages -->
                <div class="kanban-col" data-stage="New" ondrop="drop(event)" ondragover="allowDrop(event)">
                    <div class="kanban-header">
                        <span>New <span class="badge bg-secondary" id="count-New">0</span></span>
                        <span class="total-val" id="val-New">₹0</span>
                    </div>
                    <div class="kanban-items" id="col-New"></div>
                </div>

                <div class="kanban-col" data-stage="Qualified" ondrop="drop(event)" ondragover="allowDrop(event)">
                    <div class="kanban-header">
                        <span>Qualified <span class="badge bg-secondary" id="count-Qualified">0</span></span>
                        <span class="total-val" id="val-Qualified">₹0</span>
                    </div>
                    <div class="kanban-items" id="col-Qualified"></div>
                </div>

                <div class="kanban-col" data-stage="Proposal" ondrop="drop(event)" ondragover="allowDrop(event)">
                    <div class="kanban-header">
                        <span>Proposal <span class="badge bg-secondary" id="count-Proposal">0</span></span>
                        <span class="total-val" id="val-Proposal">₹0</span>
                    </div>
                    <div class="kanban-items" id="col-Proposal"></div>
                </div>

                <div class="kanban-col" data-stage="Negotiation" ondrop="drop(event)" ondragover="allowDrop(event)">
                    <div class="kanban-header">
                        <span>Negotiation <span class="badge bg-secondary" id="count-Negotiation">0</span></span>
                        <span class="total-val" id="val-Negotiation">₹0</span>
                    </div>
                    <div class="kanban-items" id="col-Negotiation"></div>
                </div>

                <div class="kanban-col" data-stage="Closed Won" ondrop="drop(event)" ondragover="allowDrop(event)">
                    <div class="kanban-header" style="border-left-color: #28a745">
                        <span>Closed Won <span class="badge bg-secondary" id="count-Closed-Won">0</span></span>
                        <span class="total-val" id="val-Closed-Won">₹0</span>
                    </div>
                    <div class="kanban-items" id="col-Closed-Won"></div>
                </div>

                <div class="kanban-col" data-stage="Closed Lost" ondrop="drop(event)" ondragover="allowDrop(event)">
                    <div class="kanban-header" style="border-left-color: #dc3545">
                        <span>Closed Lost <span class="badge bg-secondary" id="count-Closed-Lost">0</span></span>
                        <span class="total-val text-danger" id="val-Closed-Lost">₹0</span>
                    </div>
                    <div class="kanban-items" id="col-Closed-Lost"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Add Opportunity Modal -->
    <div class="modal fade" id="addOpportunityModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('opportunities.store') }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Create New Deal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Deal Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Website Redesign">
                    </div>
                    <div class="mb-3">
                        <label>Customer</label>
                        <select name="customer_id" class="form-select" required>
                            <option value="">Select Customer</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}">{{ $c->company ?? $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Deal Value (₹)</label>
                        <input type="number" name="amount" class="form-control" placeholder="50000">
                    </div>
                    <div class="mb-3">
                        <label>Expected Close Date</label>
                        <input type="date" name="expected_close_date" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Stage</label>
                        <select name="stage" class="form-select" required>
                            <option value="New">New</option>
                            <option value="Qualified">Qualified</option>
                            <option value="Proposal">Proposal</option>
                            <option value="Negotiation">Negotiation</option>
                            <option value="Closed Won">Closed Won</option>
                            <option value="Closed Lost">Closed Lost</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-indigo rounded-pill px-4">Save Deal</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function formatCurrency(val) {
            return '₹' + Number(val).toLocaleString('en-IN');
        }

        function loadKanban() {
            $.get("{{ route('opportunities.kanban_data') }}", function (res) {
                $('.kanban-items').empty();

                let counts = { 'New': 0, 'Qualified': 0, 'Proposal': 0, 'Negotiation': 0, 'Closed Won': 0, 'Closed Lost': 0 };
                let values = { 'New': 0, 'Qualified': 0, 'Proposal': 0, 'Negotiation': 0, 'Closed Won': 0, 'Closed Lost': 0 };

                res.data.forEach(function (opp) {
                    let stageStr = opp.stage || 'New';
                    if (!counts.hasOwnProperty(stageStr)) stageStr = 'New';

                    counts[stageStr]++;
                    values[stageStr] += Number(opp.amount || 0);

                    // CSS ID friendly stage name
                    let stageId = stageStr.replace(/\s+/g, '-');
                    let amountHtml = opp.amount ? `<span><i class='bx bx-rupee'></i>${Number(opp.amount).toLocaleString('en-IN')}</span>` : '<span>-</span>';
                    let dateHtml = opp.expected_close_date ? `<span class="text-muted"><i class='bx bx-calendar'></i> ${opp.expected_close_date}</span>` : '';

                    let cardHtml = `
                        <div class="kanban-item" id="opp-${opp.id}" draggable="true" ondragstart="drag(event)" data-id="${opp.id}">
                            <div class="item-title">${opp.name}</div>
                            <div class="item-company"><i class='bx bx-building'></i> ${opp.company_name || opp.client_name || 'Unknown'}</div>
                            <div class="item-footer">
                                ${amountHtml}
                                ${dateHtml}
                            </div>
                        </div>
                    `;
                    $('#col-' + stageId).append(cardHtml);
                });

                // Update stats
                Object.keys(counts).forEach(key => {
                    let stageId = key.replace(/\s+/g, '-');
                    $('#count-' + stageId).text(counts[key]);
                    $('#val-' + stageId).text(formatCurrency(values[key]));
                });
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
            ev.dataTransfer.setData("oppId", $(ev.target).data('id'));
        }

        $('.kanban-col').on('dragleave drop', function (e) {
            $(this).removeClass('drag-over');
        });

        function drop(ev) {
            ev.preventDefault();
            $(ev.currentTarget).removeClass('drag-over');

            var data = ev.dataTransfer.getData("text");
            var oppId = ev.dataTransfer.getData("oppId");

            var container = $(ev.currentTarget).find('.kanban-items')[0];
            container.appendChild(document.getElementById(data));

            var newStage = $(ev.currentTarget).data('stage');

            // Optional prompt if closed lost
            var reason = '';
            if (newStage === 'Closed Lost') {
                reason = prompt("Please provide a reason for losing this deal:");
            }

            updateOppStage(oppId, newStage, reason);
        }

        function updateOppStage(oppId, newStage, reason) {
            $.post("{{ route('opportunities.update_stage') }}", {
                _token: "{{ csrf_token() }}",
                id: oppId,
                stage: newStage,
                reason: reason
            }, function (res) {
                loadKanban();
            }).fail(function () {
                alert('Error updating deal stage.');
                loadKanban();
            });
        }
    </script>
@endsection