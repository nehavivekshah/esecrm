{{-- Manage Plan — AJAX Form Partial --}}
<style>
.cp-header {
    background: linear-gradient(135deg, #d4af37, #b8860b);
    padding: 24px; border-radius: 16px 16px 0 0; color: #fff; position: relative;
}
.cp-title { font-size: 1.15rem; font-weight: 700; margin: 0; }
.cp-subtitle { font-size: 0.8rem; color: rgba(255,255,255,0.8); }

</style>

<div class="cp-header">
    <button type="button" class="cf-close" data-bs-dismiss="modal"><i class="bx bx-x"></i></button>
    <h3 class="cp-title">{{ isset($plan) ? 'Modify Subscription Tier' : 'Create New Tier' }}</h3>
    <div class="cp-subtitle">Define pricing and feature availability for this package</div>
</div>

<form id="managePlanForm" action="{{ route('managePlan') }}" method="post" class="cf-form-wrap">
    @csrf
    <input type="hidden" name="id" value="{{ $plan->id ?? '' }}">

    <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto; background: #fff;">
        <div class="row g-4">
            {{-- Plan Name --}}
            <div class="col-md-8">
                <label class="cf-label">Tier Name*</label>
                <div class="cf-input-box">
                    <span class="cf-icon"><i class="bx bx-purchase-tag"></i></span>
                    <input type="text" name="name" value="{{ $plan->name ?? '' }}" placeholder="e.g. Platinum Plus" required>
                </div>
            </div>

            {{-- Price --}}
            <div class="col-md-4">
                <label class="cf-label">Monthly Price ($)*</label>
                <div class="cf-input-box">
                    <span class="cf-icon"><i class="bx bx-dollar"></i></span>
                    <input type="number" step="0.01" name="price" value="{{ $plan->price ?? '' }}" placeholder="0.00" required>
                </div>
            </div>

            {{-- Description --}}
            <div class="col-12">
                <label class="cf-label">Marketing Description</label>
                <div class="cf-input-box cf-textarea-box">
                    <textarea name="description" rows="3" placeholder="Explain the value proposition...">{{ $plan->description ?? '' }}</textarea>
                </div>
            </div>

            {{-- Features (Dynamic List) --}}
            <div class="col-12">
                <label class="cf-label">Plan Features (Included Benefits)</label>
                <div id="feature_list">
                    @php $features = $plan->features ?? []; @endphp
                    @forelse($features as $feat)
                        <div class="d-flex gap-2 mb-2 feature-item">
                            <div class="cf-input-box flex-grow-1">
                                <span class="cf-icon"><i class="bx bx-check-circle"></i></span>
                                <input type="text" name="features[]" value="{{ $feat }}" placeholder="e.g. 50 leads per month">
                            </div>
                            <button type="button" class="btn btn-outline-danger btn-sm px-2 remove-feature"><i class="bx bx-trash"></i></button>
                        </div>
                    @empty
                        <div class="d-flex gap-2 mb-2 feature-item">
                            <div class="cf-input-box flex-grow-1">
                                <span class="cf-icon"><i class="bx bx-check-circle"></i></span>
                                <input type="text" name="features[]" value="" placeholder="e.g. 24/7 Priority Support">
                            </div>
                        </div>
                    @endforelse
                </div>
                <button type="button" class="btn btn-sm btn-light border mt-1" id="add_feature_btn">
                    <i class="bx bx-plus me-1"></i> Add Another Benefit
                </button>
            </div>
        </div>
    </div>

    <div class="cf-modal-footer">
        <button type="button" class="cf-btn-cancel" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="cf-btn-save">
            <i class="bx bx-check-double"></i> {{ isset($plan) ? 'Update Tier' : 'Publish Plan' }}
        </button>
    </div>
</form>

<script>
    (function() {
        const list = document.getElementById('feature_list');
        const addBtn = document.getElementById('add_feature_btn');

        if(addBtn) {
            addBtn.addEventListener('click', function() {
                const div = document.createElement('div');
                div.className = 'd-flex gap-2 mb-2 feature-item';
                div.innerHTML = `
                    <div class="cf-input-box flex-grow-1">
                        <span class="cf-icon"><i class="bx bx-check-circle"></i></span>
                        <input type="text" name="features[]" value="" placeholder="Enter feature description...">
                    </div>
                    <button type="button" class="btn btn-outline-danger btn-sm px-2 remove-feature"><i class="bx bx-trash"></i></button>
                `;
                list.appendChild(div);
            });
        }

        list.addEventListener('click', function(e) {
            if(e.target.closest('.remove-feature')) {
                e.target.closest('.feature-item').remove();
            }
        });
    })();
</script>
