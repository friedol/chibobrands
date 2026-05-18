@extends('layouts.admin')

@section('title', 'Message Templates - CHIBO BRAND')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Message Templates</h4>
            <p class="text-muted small mb-0">Manage reusable message templates for customer communication</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#broadcastLeadsModal">
                <i class="fas fa-broadcast-tower me-2"></i>Broadcast to Leads
            </button>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTemplateModal">
                <i class="fas fa-plus me-2"></i>Create New Template
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle me-3 fs-4"></i>
                <div>
                    <div class="fw-bold">{{ session('success') }}</div>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error') || $errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-circle me-3 fs-4"></i>
                <div>
                    <div class="fw-bold">{{ session('error') ?? 'There was a problem sending the message.' }}</div>
                    @if(session('error_list'))
                        <ul class="mb-0 mt-2 small">
                            @foreach(session('error_list') as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif
                    @if($errors->any())
                        <ul class="mb-0 mt-2 small">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 15px;">
                <div class="card-header border-0 d-flex justify-content-between align-items-center py-2" style="background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%);">
                    <h6 class="mb-0 fw-bold text-white"><i class="fas fa-list me-2"></i>Available Templates</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted small text-uppercase" style="letter-spacing: 0.05rem;">
                                <tr>
                                    <th class="ps-4 py-3">Template Title</th>
                                    <th class="py-3">Category</th>
                                    <th class="py-3">Content Preview</th>
                                    <th class="py-3">Created By</th>
                                    <th class="text-end pe-4 py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($templates as $template)
                                    <tr>
                                        <td class="ps-4 py-3">
                                            <div class="fw-bold text-dark">{{ $template->title }}</div>
                                            <div class="text-muted x-small">Updated {{ $template->updated_at->diffForHumans() }}</div>
                                        </td>
                                        <td class="py-3">
                                            <span class="badge bg-soft-primary text-primary px-3 py-2 rounded-pill fw-medium">{{ $template->category ?? 'General' }}</span>
                                        </td>
                                        <td class="py-3">
                                            <div class="text-truncate text-muted small" style="max-width: 300px;">
                                                {{ $template->content }}
                                            </div>
                                        </td>
                                        <td class="py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-primary-soft rounded-circle text-center me-2 text-primary fw-bold" style="width: 30px; height: 30px; line-height: 30px; font-size: 11px; background-color: rgba(13, 110, 253, 0.1);">
                                                    {{ strtoupper(substr($template->creator->name ?? 'U', 0, 1)) }}
                                                </div>
                                                <span class="small fw-medium text-dark">{{ $template->creator->name ?? 'Unknown' }}</span>
                                            </div>
                                        </td>
                                        <td class="text-end pe-4 py-3">
                                            <div class="btn-group shadow-sm rounded-pill bg-white p-1">
                                                <button type="button" class="btn btn-sm btn-icon btn-light rounded-circle me-1" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#sendTemplateModal{{ $template->id }}"
                                                        title="Send to Customer">
                                                    <i class="fas fa-paper-plane text-info"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-icon btn-light rounded-circle me-1" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editTemplateModal{{ $template->id }}"
                                                        title="Edit">
                                                    <i class="fas fa-edit text-primary"></i>
                                                </button>
                                                <form action="{{ route('admin.message-templates.destroy', $template->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-sm btn-icon btn-light rounded-circle" 
                                                            onclick="modernConfirm('Delete this template?', () => this.closest('form').submit())"
                                                            title="Delete">
                                                        <i class="fas fa-trash text-danger"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <div class="py-4">
                                                <i class="fas fa-comment-slash text-muted mb-3" style="font-size: 4rem; opacity: 0.2;"></i>
                                                <h6 class="text-muted fw-bold">No message templates found.</h6>
                                                <p class="small text-muted mb-0">Create your first template to start communicating faster.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     BROADCAST TO LEADS MODAL
═══════════════════════════════════════════════════════════ -->
<div class="modal fade" id="broadcastLeadsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header border-0 text-white py-3 px-4" style="background: linear-gradient(135deg, #198754 0%, #146c43 100%);">
                <div class="d-flex align-items-center">
                    <div class="bg-white bg-opacity-25 rounded-circle p-2 me-3">
                        <i class="fas fa-broadcast-tower fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-white">Broadcast SMS to Leads</h5>
                        <p class="mb-0 small text-white-50">Send a message directly to lead phone numbers</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.message-templates.broadcast-leads') }}" method="POST" id="broadcastLeadsForm">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <!-- Left: Message & Filters -->
                        <div class="col-md-7">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted text-uppercase" style="letter-spacing:0.05rem;">Message Template</label>
                                <select name="template_id" class="form-select border-2" id="leadsBroadcastTemplate">
                                    <option value="">— Custom message below —</option>
                                    @foreach($templates->where('is_active', true) as $t)
                                        <option value="{{ $t->id }}" data-content="{{ $t->content }}">{{ $t->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted text-uppercase" style="letter-spacing:0.05rem;">Message <span class="text-muted fw-normal">(overrides template)</span></label>
                                <textarea name="custom_content" class="form-control border-2" rows="4" id="leadsBroadcastContent"
                                          placeholder="Type a custom message, or pick a template above. Use {name} for lead name."></textarea>
                                <div class="form-text x-small text-end fw-bold mt-1 leads-char-count text-muted">0 characters</div>
                            </div>

                            <hr class="opacity-25 my-3">
                            <p class="small fw-bold text-muted text-uppercase mb-2" style="letter-spacing:0.05rem;"><i class="fas fa-filter me-1"></i>Filter Recipients</p>
                            <div class="row g-2">
                                @if(in_array(auth()->user()->role, ['admin','super_admin']))
                                <div class="col-12">
                                    <label class="form-label small text-muted">Seller</label>
                                    <select name="seller_id" class="form-select form-select-sm border-2">
                                        <option value="">All Sellers</option>
                                        @foreach($sellers as $seller)
                                            <option value="{{ $seller->id }}">{{ $seller->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif
                                <div class="col-6">
                                    <label class="form-label small text-muted">Lead Status</label>
                                    <select name="lead_status" class="form-select form-select-sm border-2">
                                        <option value="all">All Statuses</option>
                                        <option value="pending" selected>Pending</option>
                                        <option value="converted">Converted</option>
                                        <option value="not_interested">Not Interested</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small text-muted">Priority</label>
                                    <select name="lead_priority" class="form-select form-select-sm border-2">
                                        <option value="all">All Priorities</option>
                                        <option value="urgent">Urgent</option>
                                        <option value="high">High</option>
                                        <option value="normal">Normal</option>
                                        <option value="low">Low</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small text-muted">Source (optional)</label>
                                    <input type="text" name="lead_source" class="form-control form-control-sm border-2" placeholder="e.g. WhatsApp, Referral...">
                                </div>
                            </div>
                        </div>

                        <!-- Right: Preview -->
                        <div class="col-md-5">
                            <div class="preview-card p-4 rounded-4 bg-light h-100 border border-success border-opacity-10">
                                <h6 class="fw-bold text-success mb-3"><i class="fas fa-eye me-2"></i>Message Preview</h6>
                                <div class="message-bubble bg-white p-3 rounded-4 shadow-sm mb-3 border">
                                    <div class="small text-dark lh-base" id="leadsPreviewText" style="white-space: pre-wrap;">Pick a template or type a message...</div>
                                    <div class="text-end mt-2">
                                        <span class="x-small text-muted" id="leadsPreviewMeta">0 chars · 1 unit</span>
                                    </div>
                                </div>
                                <div class="p-3 rounded-4 bg-white shadow-sm border">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="small text-muted"><i class="fas fa-info-circle me-1"></i>Placeholders:</span>
                                    </div>
                                    <p class="x-small text-muted mb-0">
                                        <code>{name}</code> — lead's name<br>
                                        SMS is sent per lead individually.
                                    </p>
                                    <hr class="my-2 opacity-10">
                                    <p class="x-small text-warning mb-0"><i class="fas fa-exclamation-triangle me-1"></i>Only leads with phone numbers are messaged.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-pill px-5 fw-bold shadow-sm" data-no-global-handler>
                        <i class="fas fa-paper-plane me-2"></i>Send to Leads
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Send and Edit Modals for each template -->
@foreach($templates as $template)
    <!-- Send Modal -->
    <div class="modal fade" id="sendTemplateModal{{ $template->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                <div class="modal-header border-0 text-white py-3 px-4" style="background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%);">
                    <div class="d-flex align-items-center">
                        <div class="bg-white bg-opacity-25 rounded-circle p-2 me-3">
                            <i class="fas fa-paper-plane fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0 text-white">Send "{{ $template->title }}"</h5>
                            <p class="mb-0 small text-white-50">Select customers and broadcast this message</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.message-templates.send') }}" method="POST" class="broadcast-form">
                    @csrf
                    <input type="hidden" name="template_id" value="{{ $template->id }}">
                    <input type="hidden" name="recipient_type" value="selected">
                    <div class="modal-body p-4">
                        <div class="row g-4">
                            <!-- Left Side: Selection -->
                            <div class="col-md-7">
                                <div class="mb-3">
                                    <div class="position-relative mb-3">
                                        <span class="position-absolute top-50 start-0 translate-middle-y ps-3 text-muted">
                                            <i class="fas fa-search"></i>
                                        </span>
                                        <input type="text" class="form-control ps-5 rounded-pill border-light bg-light customer-search" 
                                               placeholder="Search by name or phone...">
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                                        <div class="form-check">
                                            <input class="form-check-input select-all-customers" type="checkbox" id="selectAll{{ $template->id }}">
                                            <label class="form-check-label small fw-bold text-muted cursor-pointer" for="selectAll{{ $template->id }}">
                                                Select All Customers
                                            </label>
                                        </div>
                                        <span class="badge bg-soft-info text-info rounded-pill px-3 selected-count">0 Selected</span>
                                    </div>

                                    <div class="customer-list-scroll shadow-sm rounded-4 bg-white border" style="max-height: 350px; overflow-y: auto;">
                                        <div class="list-group list-group-flush customers-container">
                                            @foreach($customers as $customer)
                                                <label class="list-group-item list-group-item-action border-0 px-3 py-2 cursor-pointer customer-item" 
                                                       data-name="{{ strtolower($customer->name) }}" 
                                                       data-phone="{{ strtolower($customer->phone ?? '') }}">
                                                    <div class="d-flex align-items-center">
                                                        <div class="form-check mb-0">
                                                            <input class="form-check-input customer-checkbox" type="checkbox" 
                                                                   name="customer_ids[]" value="{{ $customer->id }}" 
                                                                   id="cust{{ $template->id }}_{{ $customer->id }}">
                                                        </div>
                                                        <div class="ms-3 flex-grow-1">
                                                            <div class="fw-bold text-dark small mb-0">{{ $customer->name }}</div>
                                                            <div class="text-muted" style="font-size: 0.7rem;">
                                                                <i class="fas fa-phone-alt me-1"></i>{{ $customer->phone ?? 'No phone' }}
                                                            </div>
                                                        </div>
                                                        @if(!$customer->phone)
                                                            <span class="badge bg-soft-danger text-danger x-small rounded-pill">No Phone</span>
                                                        @endif
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Side: Preview & Info -->
                            <div class="col-md-5">
                                <div class="preview-card p-4 rounded-4 bg-light h-100 border border-info border-opacity-10">
                                    <h6 class="fw-bold text-info mb-3"><i class="fas fa-eye me-2"></i>Message Preview</h6>
                                    <div class="message-bubble bg-white p-3 rounded-bottom-4 rounded-end-4 shadow-sm mb-4 position-relative border">
                                        <div class="small text-dark lh-base">{{ $template->content }}</div>
                                        <div class="text-end mt-2">
                                            @php
                                                $charCount = strlen($template->content);
                                                $smsUnits = ceil($charCount / 160) ?: 1;
                                            @endphp
                                            <span class="x-small text-muted">{{ $charCount }} characters ({{ $smsUnits }} unit{{ $smsUnits > 1 ? 's' : '' }})</span>
                                        </div>
                                    </div>
                                    
                                    <div class="broadcast-summary p-3 rounded-4 bg-white shadow-sm border">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="small text-muted">Status:</span>
                                            <span class="small fw-bold text-success">Ready to send</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-0">
                                            <span class="small text-muted">Cost estimate:</span>
                                            <span class="small fw-bold text-dark">{{ $smsUnits }} unit{{ $smsUnits > 1 ? 's' : '' }} per customer</span>
                                        </div>
                                        <hr class="my-2 opacity-10">
                                        <p class="x-small text-muted mb-0 italic">Messages will be sent individually to each selected customer.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info text-white rounded-pill px-5 fw-bold shadow-sm" data-no-global-handler>
                            <i class="fas fa-broadcast-tower me-2"></i>Broadcast Now
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editTemplateModal{{ $template->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 text-white py-3 px-4" style="background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%);">
                    <h5 class="modal-title fw-bold mb-0"><i class="fas fa-edit me-2"></i>Edit Template</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.message-templates.update', $template->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-4 text-start">
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted text-uppercase" style="letter-spacing: 0.05rem;">Title</label>
                            <input type="text" name="title" class="form-control form-control-lg border-2" value="{{ $template->title }}" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted text-uppercase" style="letter-spacing: 0.05rem;">Category</label>
                            <input type="text" name="category" class="form-control border-2" value="{{ $template->category }}" placeholder="e.g. Welcome, Ready, Follow-up">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase" style="letter-spacing: 0.05rem;">Content</label>
                            <textarea name="content" class="form-control border-2" rows="6" required>{{ $template->content }}</textarea>
                            <div class="form-text x-small text-end fw-bold mt-2 char-count">0 characters</div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0 text-center justify-content-center">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold" data-no-global-handler>Update Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

<!-- Create Modal -->
<div class="modal fade" id="createTemplateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 text-white py-3 px-4" style="background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%);">
                <h5 class="modal-title fw-bold mb-0"><i class="fas fa-plus me-2"></i>Create New Template</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.message-templates.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted text-uppercase" style="letter-spacing: 0.05rem;">Template Title</label>
                        <input type="text" name="title" class="form-control form-control-lg border-2" placeholder="e.g. Order Ready Notification" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted text-uppercase" style="letter-spacing: 0.05rem;">Category</label>
                        <input type="text" name="category" class="form-control border-2" placeholder="e.g. Production, Marketing">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase" style="letter-spacing: 0.05rem;">Content</label>
                        <textarea name="content" class="form-control border-2" rows="6" placeholder="Type your message here..." required></textarea>
                        <div class="form-text x-small text-end fw-bold mt-2 char-count">0 characters</div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0 text-center justify-content-center">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm" data-no-global-handler>Create Template</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    .bg-soft-primary { background-color: rgba(13, 110, 253, 0.1); }
    .bg-soft-info { background-color: rgba(13, 202, 240, 0.1); }
    .bg-soft-danger { background-color: rgba(220, 53, 69, 0.1); }
    .x-small { font-size: 0.75rem; }
    .cursor-pointer { cursor: pointer; }
    
    .btn-icon {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }
    
    .customer-list-scroll::-webkit-scrollbar {
        width: 6px;
    }
    .customer-list-scroll::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    .customer-list-scroll::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 10px;
    }
    .customer-list-scroll::-webkit-scrollbar-thumb:hover {
        background: #aaa;
    }
    
    .customer-item:hover {
        background-color: #f8f9fa !important;
    }
    
    .customer-item.selected {
        background-color: #f0f7ff !important;
    }

    .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.05);
    }
    
    .message-bubble::after {
        content: '';
        position: absolute;
        bottom: 0px;
        right: -10px;
        width: 0;
        height: 0;
        border: 10px solid transparent;
        border-left-color: #fff;
        border-bottom-color: #fff;
        filter: drop-shadow(2px 2px 2px rgba(0,0,0,0.05));
        display: none; /* simplified design */
    }
    
    .broadcast-form .form-check-input {
        width: 1.2rem;
        height: 1.2rem;
        cursor: pointer;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Character count for textareas
    const textareas = document.querySelectorAll('textarea[name="content"]');
    textareas.forEach(textarea => {
        const charCount = textarea.parentElement.querySelector('.char-count');
        
        const updateCount = () => {
            const count = textarea.value.length;
            const units = Math.ceil(count / 160) || 1;
            charCount.textContent = `${count} characters (${units} unit${units > 1 ? 's' : ''})`;
            charCount.classList.toggle('text-warning', count > 160);
            
            // Update SMS units in broadcast cards if applicable
            const modal = textarea.closest('.modal');
            if (modal) {
                const unitsSpan = modal.querySelector('.sms-count');
                if (unitsSpan) unitsSpan.textContent = units;
            }
        };

        textarea.addEventListener('input', updateCount);
        updateCount();
    });

    // Multi-select & Search Functionality
    const broadcastModals = document.querySelectorAll('[id^="sendTemplateModal"]');
    broadcastModals.forEach(modal => {
        const searchInput = modal.querySelector('.customer-search');
        const selectAllCheckbox = modal.querySelector('.select-all-customers');
        const checkboxes = modal.querySelectorAll('.customer-checkbox');
        const selectedCountBadge = modal.querySelector('.selected-count');
        const customerItems = modal.querySelectorAll('.customer-item');

        const updateCount = () => {
            const checkedCount = modal.querySelectorAll('.customer-checkbox:checked').length;
            selectedCountBadge.textContent = `${checkedCount} Selected`;
            selectedCountBadge.classList.toggle('bg-info', checkedCount > 0);
            selectedCountBadge.classList.toggle('text-white', checkedCount > 0);
            
            // Highlight selected items
            checkboxes.forEach(cb => {
                cb.closest('.customer-item').classList.toggle('selected', cb.checked);
            });
        };

        // Search logic
        searchInput.addEventListener('input', function() {
            const term = this.value.toLowerCase();
            customerItems.forEach(item => {
                const name = item.getAttribute('data-name');
                const phone = item.getAttribute('data-phone');
                if (name.includes(term) || phone.includes(term)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });

        // Select All logic
        selectAllCheckbox.addEventListener('change', function() {
            // Only select visible ones
            customerItems.forEach(item => {
                if (item.style.display !== 'none') {
                    const cb = item.querySelector('.customer-checkbox');
                    cb.checked = this.checked;
                }
            });
            updateCount();
        });

        // Individual checkbox change
        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateCount);
        });

        // Click anywhere on item to toggle
        customerItems.forEach(item => {
            item.addEventListener('click', function(e) {
                if (e.target.tagName !== 'INPUT') {
                    const cb = this.querySelector('.customer-checkbox');
                    cb.checked = !cb.checked;
                    updateCount();
                }
            });
        });
    });

    // ── Broadcast to Leads modal ─────────────────────────────
    const templateSelect  = document.getElementById('leadsBroadcastTemplate');
    const contentArea     = document.getElementById('leadsBroadcastContent');
    const previewText     = document.getElementById('leadsPreviewText');
    const previewMeta     = document.getElementById('leadsPreviewMeta');
    const charCountLabel  = document.querySelector('.leads-char-count');

    function updateLeadsPreview() {
        const text = contentArea.value.trim() || (templateSelect.selectedOptions[0]?.dataset.content ?? '');
        previewText.textContent = text || 'Pick a template or type a message...';
        const len   = text.length;
        const units = Math.ceil(len / 160) || 1;
        previewMeta.textContent    = `${len} chars · ${units} unit${units > 1 ? 's' : ''}`;
        charCountLabel.textContent = `${len} characters (${units} unit${units > 1 ? 's' : ''})`;
        charCountLabel.classList.toggle('text-warning', len > 160);
    }

    templateSelect.addEventListener('change', function() {
        const selected = this.selectedOptions[0];
        if (selected && selected.dataset.content) {
            contentArea.value = '';       // clear custom so template shows in preview
        }
        updateLeadsPreview();
    });

    contentArea.addEventListener('input', updateLeadsPreview);
    updateLeadsPreview();

    // Reset broadcast leads modal on close
    document.getElementById('broadcastLeadsModal').addEventListener('hidden.bs.modal', function() {
        document.getElementById('broadcastLeadsForm').reset();
        contentArea.value = '';
        updateLeadsPreview();
    });

    // Reset all modals when they are hidden
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('hidden.bs.modal', function () {
            const forms = this.querySelectorAll('form');
            forms.forEach(form => {
                // If it's a broadcast form, we might not want to reset the search
                if (!form.classList.contains('broadcast-form')) {
                    form.reset();
                }
                
                // Reset character counts for standard forms
                const charCounts = form.querySelectorAll('.char-count');
                charCounts.forEach(cc => {
                    const ta = cc.previousElementSibling;
                    if (ta && ta.tagName === 'TEXTAREA') {
                        const count = ta.value.length;
                        const units = Math.ceil(count / 160) || 1;
                        cc.textContent = `${count} characters (${units} unit${units > 1 ? 's' : ''})`;
                    }
                });
            });
        });
    });
});
</script>
@endpush
@endsection

