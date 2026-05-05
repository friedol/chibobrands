@extends('layouts.admin')

@section('title', 'Record Movement - Gatekeeper')

@section('content')
<div class="container-fluid p-3">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div class="d-flex gap-2">
            <a href="{{ route('gatekeeper.movements.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fas fa-arrow-left me-1"></i> <span data-i18n="back">Back</span>
            </a>
            <button id="langToggle" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold bg-white fs-5" title="Switch Language">
                <span id="langLabel">🇹🇿</span>
            </button>
        </div>
        
        {{-- Type Toggle --}}
        @php $currentType = request('type', 'in'); @endphp
        <div class="d-flex border rounded-pill overflow-hidden p-1 bg-white shadow-sm">
            <a href="{{ route('gatekeeper.movements.create', ['type' => 'in']) }}" 
               class="px-4 py-1 rounded-pill text-decoration-none fw-bold small transition-all {{ $currentType == 'in' ? 'bg-success text-white shadow-sm' : 'text-muted' }}">
                <span data-i18n="incoming">INCOMING</span>
            </a>
            <a href="{{ route('gatekeeper.movements.create', ['type' => 'out']) }}" 
               class="px-4 py-1 rounded-pill text-decoration-none fw-bold small transition-all {{ $currentType == 'out' ? 'bg-warning text-dark shadow-sm' : 'text-muted' }}">
                <span data-i18n="outgoing">OUTGOING</span>
            </a>
        </div>
    </div>

    <form action="{{ route('gatekeeper.movements.store') }}" method="POST" id="movementForm">
        @csrf
        <input type="hidden" name="type" value="{{ $currentType }}">

        <div class="row g-4">
            <!-- Left Column: Who & Where -->
            <div class="col-12 col-lg-6">
                <!-- Who is involved? -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-user mb-1 me-2 text-primary"></i><span data-i18n="person_details">Person Details</span></h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-medium small text-muted"><span data-i18n="person_type">Person Type</span> <span class="text-danger">*</span></label>
                                <select name="handler_type" id="handler_type" class="form-select bg-light border-0 py-2" required>
                                    <option value="" data-i18n="select_type">Select Type...</option>
                                    <option value="Staff" data-i18n="opt_staff">Internal Staff</option>
                                    <option value="Registered Delivery" data-i18n="opt_delivery">Delivery Personnel</option>
                                    <option value="Customer" data-i18n="opt_customer">Customer</option>
                                    <option value="External Person" data-i18n="opt_external">External / Third Party</option>
                                </select>
                            </div>
                            
                            {{-- Quick Select --}}
                            <div class="col-12" id="registered_user_container" style="display: none;">
                                <label class="form-label fw-medium small text-muted"><span data-i18n="quick_select">Quick Select</span></label>
                                <select id="registered_user_select" class="form-select bg-white border-primary shadow-sm py-2">
                                    <option value="" data-i18n="choose_user">-- Choose User --</option>
                                    <!-- Populated via JS -->
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-medium small text-muted"><span data-i18n="full_name">Full Name</span> <span class="text-danger">*</span></label>
                                <input type="text" name="handler_name" id="handler_name" class="form-control bg-light border-0 py-2" required placeholder="Name of person present" data-i18n-placeholder="name_placeholder">
                                <input type="hidden" name="handler_user_id" id="handler_user_id">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-medium small text-muted"><span data-i18n="id_ref">ID / Reference</span> <small>(Optional)</small></label>
                                <input type="text" name="handler_identifier" id="handler_identifier" class="form-control bg-light border-0 py-2" placeholder="Phone or ID number" data-i18n-placeholder="id_placeholder">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Where is it going? -->
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="fas fa-map-marker-alt mb-1 me-2 text-danger"></i>
                            <span data-i18n="{{ $currentType == 'in' ? 'origin_details' : 'dest_details' }}">
                                {{ $currentType == 'in' ? 'Origin Details' : 'Destination Details' }}
                            </span>
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($currentType == 'in')
                            <!-- Incoming Source -->
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-medium small text-muted"><span data-i18n="source_type">Source Type</span> <span class="text-danger">*</span></label>
                                    <select name="source_type" class="form-select bg-light border-0 py-2" required>
                                        <option value="Supplier" data-i18n="opt_supplier">Supplier / Vendor</option>
                                        <option value="Customer return" data-i18n="opt_cust_return">Customer (Return)</option>
                                        <option value="Staff" data-i18n="opt_staff">Staff Member</option>
                                        <option value="External source" data-i18n="opt_ext_source">Other External Source</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-medium small text-muted"><span data-i18n="source_name">Source Name</span> <span class="text-danger">*</span></label>
                                    <input type="text" name="source_name" class="form-control bg-light border-0 py-2" required placeholder="Sender Name" data-i18n-placeholder="sender_placeholder">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-medium small text-muted">Phone / ID Number <small>(Optional)</small></label>
                                    <input type="text" name="source_identifier" class="form-control bg-light border-0 py-2" placeholder="Sender Phone or ID">
                                </div>
                            </div>
                        @else
                            <!-- Outgoing Destination -->
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-medium small text-muted"><span data-i18n="rec_type">Recipient Type</span> <span class="text-danger">*</span></label>
                                    <select name="recipient_type" class="form-select bg-light border-0 py-2" required>
                                        <option value="Customer" {{ request('recipient_name') ? 'selected' : '' }} data-i18n="opt_customer">Customer</option>
                                        <option value="Staff" data-i18n="opt_staff">Staff Member</option>
                                        <option value="External Party" data-i18n="opt_ext_party">External Party</option>
                                    </select>
                                </div>
                                <div class="col-12" id="customer_recipient_search_wrapper" style="display: none;">
                                    <label class="form-label fw-medium small text-muted">Search Customer (name / phone / ID)</label>
                                    <input type="text" id="recipient_customer_search" class="form-control bg-light border-0 py-2" placeholder="Type to search..." autocomplete="off">
                                    <div id="recipient_customer_suggestions" class="list-group mt-1" style="display: none; max-height: 180px; overflow: auto;"></div>
                                </div>

                                <input type="hidden" id="recipient_customer_id" name="recipient_customer_id" value="">

                                <div class="col-12">
                                    <label class="form-label fw-medium small text-muted"><span data-i18n="rec_name">Receiver Name</span> <span class="text-danger">*</span></label>
                                    <input type="text" id="recipient_name" name="recipient_name" class="form-control bg-light border-0 py-2" required placeholder="Receiver Name" data-i18n-placeholder="receiver_placeholder" value="{{ request('recipient_name') }}">
                                </div>

                                <div class="col-12" id="customer_recipient_identifier_wrapper" style="display: none;">
                                    <label class="form-label fw-medium small text-muted">Phone / ID Number <span class="text-danger">*</span></label>
                                    <input type="text" id="recipient_identifier" name="recipient_identifier" class="form-control bg-light border-0 py-2" placeholder="Phone or ID number" value="{{ request('recipient_identifier') }}">
                                </div>
                                <div class="col-12" id="verification_code_wrapper">
                                    <label class="form-label fw-bold text-dark"><i class="fas fa-key me-2 text-warning"></i>Verification Code (Delivery Password)</label>
                                    <input type="text" name="verification_code" class="form-control bg-light border-0 py-2" placeholder="Enter 4-digit code if required" maxlength="10">
                                    <small class="text-muted fs-xs">Required for secure pickups (Design Tasks)</small>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-medium small text-muted"><span data-i18n="transport_method">Transport Method</span> <span class="text-danger">*</span></label>
                                    <select name="delivery_method" class="form-select bg-light border-0 py-2" required>
                                        <option value="Company delivery" data-i18n="opt_comp_veh">Company Vehicle</option>
                                        <option value="External delivery" data-i18n="opt_courier">Courier / 3rd Party</option>
                                        <option value="Self-pickup" data-i18n="opt_pickup">Self Pickup</option>
                                    </select>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column: What & Save -->
            <div class="col-12 col-lg-6">
                <!-- What is the item? -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-box mb-1 me-2 text-success"></i><span data-i18n="item_details">Item Details</span></h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            {{-- Task Search (Pull Data) --}}
                            <div class="col-12">
                                <label class="form-label fw-bold text-primary small"><i class="fas fa-search me-1"></i> <span data-i18n="pull_from_task">Pull from Internal Task / Kazi za Ndani</span></label>
                                <div class="input-group input-group-sm mb-2">
                                    <input type="text" id="task_search_input" class="form-control border-primary" placeholder="Search by Task ID, Title or Customer..." autocomplete="off">
                                    <button type="button" class="btn btn-primary" id="btn_task_search"><i class="fas fa-search"></i></button>
                                </div>
                                <div id="task_suggestions_container" class="list-group shadow-sm" style="display: none; max-height: 250px; overflow-y: auto; z-index: 1000; position: absolute; width: calc(100% - 2rem);"></div>
                                <div class="form-text text-muted x-small">Search and select a task to quickly fill customer and item details.</div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-medium small text-muted"><span data-i18n="item_name">Item Name</span> <span class="text-danger">*</span></label>
                                <input type="text" name="product_name" class="form-control bg-light border-0 py-2" required placeholder="e.g. Mifuko" data-i18n-placeholder="item_placeholder" value="{{ request('product_name') }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-medium small text-muted"><span data-i18n="quantity">Quantity</span> <span class="text-danger">*</span></label>
                                <input type="number" name="quantity" class="form-control bg-light border-0 py-2" required min="1" value="{{ request('quantity', 1) }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-medium small text-muted"><span data-i18n="est_value">Estimated Value</span></label>
                                <div class="input-group">
                                    <span class="input-group-text border-0 bg-light text-muted fw-bold">$</span>
                                    <input type="number" name="unit_price" class="form-control bg-light border-0 py-2" step="0.01" min="0">
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-medium small text-muted"><span data-i18n="reason">Reason / Purpose</span> <span class="text-danger">*</span></label>
                                <select name="purpose" class="form-select bg-light border-0 py-2" required>
                                    <option value="" data-i18n="select_reason">Select Reason...</option>
                                    @if($currentType == 'in')
                                        <option value="Purchase" data-i18n="opt_pur_stock">Stock Purchase</option>
                                        <option value="Return" data-i18n="opt_cust_ret">Customer Return</option>
                                        <option value="Transfer" data-i18n="opt_transfer">Branch Transfer</option>
                                        <option value="Official duty" data-i18n="opt_official">Official Use</option>
                                    @else
                                        <option value="Delivery" {{ request('purpose') == 'Delivery' ? 'selected' : '' }} data-i18n="opt_delivery_act">Delivery</option>
                                        <option value="Transfer" {{ request('purpose') == 'Transfer' ? 'selected' : '' }} data-i18n="opt_stock_trans">Stock Transfer</option>
                                        <option value="Repair" {{ request('purpose') == 'Repair' ? 'selected' : '' }} data-i18n="opt_repair">Sent for Repair</option>
                                        <option value="Official duty" {{ request('purpose') == 'Official duty' ? 'selected' : '' }} data-i18n="opt_official">Official Use</option>
                                    @endif
                                    <option value="Other" data-i18n="opt_other">Other</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-medium small text-muted"><span data-i18n="ref_inv">Reference / Invoice #</span></label>
                                <input type="text" name="authorization_reference" class="form-control bg-light border-0 py-2" placeholder="Optional" data-i18n-placeholder="optional_placeholder" value="{{ request('authorization_reference') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes & Save -->
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark"><span data-i18n="add_notes">Additional Notes</span></label>
                            <textarea name="notes" class="form-control bg-light border-0 py-2" rows="3" placeholder="Any extra details..." data-i18n-placeholder="notes_placeholder"></textarea>
                        </div>
                        
                        <button type="submit" class="btn {{ $currentType == 'in' ? 'btn-success' : 'btn-warning text-dark' }} px-5 py-2 rounded-pill fw-bold shadow-sm" data-no-global-handler>
                            <i class="fas fa-check-circle me-2"></i> 
                            <span data-i18n="{{ $currentType == 'in' ? 'confirm_in' : 'confirm_out' }}">
                                @if($currentType == 'in')
                                    Confirm Incoming Receipt
                                @else
                                    Confirm Outgoing Dispatch
                                @endif
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    const translations = {
        en: {
            back: "Back",
            incoming: "INCOMING",
            outgoing: "OUTGOING",
            person_details: "Person Details",
            person_type: "Person Type",
            select_type: "Select Type...",
            quick_select: "Quick Select",
            choose_user: "-- Choose User --",
            full_name: "Full Name",
            name_placeholder: "Name of person present",
            id_ref: "ID / Reference",
            id_placeholder: "Phone or ID number",
            origin_details: "Origin Details",
            dest_details: "Destination Details",
            source_type: "Source Type",
            source_name: "Source Name",
            sender_placeholder: "Sender Name",
            rec_type: "Recipient Type",
            rec_name: "Receiver Name",
            receiver_placeholder: "Receiver Name",
            transport_method: "Transport Method",
            item_details: "Item Details",
            item_name: "Item Name",
            item_placeholder: "e.g. Mifuko",
            quantity: "Quantity",
            est_value: "Estimated Value",
            reason: "Reason / Purpose",
            select_reason: "Select Reason...",
            ref_inv: "Reference / Invoice #",
            optional_placeholder: "Optional",
            add_notes: "Additional Notes",
            notes_placeholder: "Any extra details...",
            confirm_in: "Confirm Incoming Receipt",
            confirm_out: "Confirm Outgoing Dispatch",
            // Options
            opt_staff: "Internal Staff",
            opt_delivery: "Registered Delivery Personnel",
            opt_customer: "Customer",
            opt_external: "External / Third Party",
            opt_supplier: "Supplier / Vendor",
            opt_cust_return: "Customer (Return)",
            opt_ext_source: "Other External Source",
            opt_ext_party: "External Party",
            opt_comp_veh: "Company Vehicle",
            opt_courier: "Courier / 3rd Party",
            opt_pickup: "Self Pickup",
            opt_pur_stock: "Stock Purchase",
            opt_cust_ret: "Customer Return",
            opt_transfer: "Branch Transfer",
            opt_official: "Official Use",
            opt_delivery_act: "Delivery",
            opt_stock_trans: "Stock Transfer",
            opt_repair: "Sent for Repair",
            opt_other: "Other"
        },
        sw: {
            back: "Rudi",
            incoming: "MAPOKEZI",
            outgoing: "USAFIRISHAJI",
            person_details: "Maelezo ya Mhusika",
            person_type: "Aina ya Mtu",
            select_type: "Chagua Aina...",
            quick_select: "Chagua Haraka",
            choose_user: "-- Chagua Mtu --",
            full_name: "Jina Kamili",
            name_placeholder: "Jina la mhusika aliyepo",
            id_ref: "Kitambulisho / Rejeleo",
            id_placeholder: "Nambari ya Simu au Kitambulisho",
            origin_details: "Maelezo ya Chanzo",
            dest_details: "Maelezo ya Kule Inakwenda",
            source_type: "Aina ya Chanzo",
            source_name: "Jina la Chanzo",
            sender_placeholder: "Jina la Mtumaji",
            rec_type: "Aina ya Mpokeaji",
            rec_name: "Jina la Mpokeaji",
            receiver_placeholder: "Jina la Mpokeaji",
            transport_method: "Njia ya Usafiri",
            item_details: "Maelezo ya Bidhaa",
            item_name: "Jina la Bidhaa",
            item_placeholder: "mfano: Mifuko",
            quantity: "Idadi",
            est_value: "Thamani (Kadirio)",
            reason: "Sababu / Kusudi",
            select_reason: "Chagua Sababu...",
            ref_inv: "Rejeleo / Ankara #",
            optional_placeholder: "Si lazima",
            add_notes: "Maelezo ya Ziada",
            notes_placeholder: "Maelezo yoyote ya nyongeza...",
            confirm_in: "Thibitisha Mapokezi",
            confirm_out: "Thibitisha Usafirishaji",
            // Options
            opt_staff: "Mfanyakazi wa Ndani",
            opt_delivery: "Mhudumu wa Usafirishaji",
            opt_customer: "Mteja",
            opt_external: "Mtu wa Nje",
            opt_supplier: "Msambazaji",
            opt_cust_return: "Mteja (Kurejesha)",
            opt_ext_source: "Chanzo Kingine cha Nje",
            opt_ext_party: "Mtu wa Nje",
            opt_comp_veh: "Gari la Kampuni",
            opt_courier: "Msafirishaji / Mtu wa Tatu",
            opt_pickup: "Kujichukulia",
            opt_pur_stock: "Manunuzi ya Mzigo",
            opt_cust_ret: "Mteja Kurejesha",
            opt_transfer: "Uhamisho wa Twi",
            opt_official: "Matumizi ya Ofisi",
            opt_delivery_act: "Kupeleka Mzigo",
            opt_stock_trans: "Uhamisho wa Mzigo",
            opt_repair: "Kupeleka Matengenezo",
            opt_other: "Nyingine",
            pull_from_task: "Pull from Internal Task / Kazi za Ndani"
        }
    };

    let currentLang = 'en';

    function setLanguage(lang) {
        currentLang = lang;
        document.querySelectorAll('[data-i18n]').forEach(el => {
            const key = el.getAttribute('data-i18n');
            if (translations[lang][key]) {
                el.innerText = translations[lang][key];
            }
        });
        document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
            const key = el.getAttribute('data-i18n-placeholder');
            if (translations[lang][key]) {
                el.placeholder = translations[lang][key];
            }
        });

        // Update Toggle Button Text
        const toggleLabel = document.getElementById('langLabel');
        if (toggleLabel) {
            toggleLabel.innerText = lang === 'en' ? '🇹🇿' : '🇬🇧';
        }
        
        // Save preference (optional, simple local storage)
        localStorage.setItem('gatekeeper_lang', lang);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const langToggle = document.getElementById('langToggle');
        
        // Check saved language
        const savedLang = localStorage.getItem('gatekeeper_lang');
        if (savedLang) {
            setLanguage(savedLang);
        }

        if (langToggle) {
            langToggle.addEventListener('click', function() {
                const newLang = currentLang === 'en' ? 'sw' : 'en';
                setLanguage(newLang);
            });
        }

        // --- Existing Form Logic Below ---
        const handlerTypeSelect = document.getElementById('handler_type');
        const userContainer = document.getElementById('registered_user_container');
        const userSelect = document.getElementById('registered_user_select');
        const nameInput = document.getElementById('handler_name');
        const idInput = document.getElementById('handler_identifier');
        const userIdInput = document.getElementById('handler_user_id');

        // Pass PHP data to JS
        const staffUsers = @json($staffUsers ?? []);
        const deliveryUsers = @json($deliveryPersonnel ?? []);

        if (handlerTypeSelect) {
            handlerTypeSelect.addEventListener('change', function() {
                const type = this.value;
                // Get translation for choose user prompt
                const chooseText = translations[currentLang]['choose_user'] || "-- Choose User --";
                userSelect.innerHTML = `<option value="">${chooseText}</option>`;
                userIdInput.value = ''; // Reset hidden ID

                let activeList = [];

                if (type === 'Staff') {
                    activeList = staffUsers;
                } else if (type === 'Registered Delivery') {
                    activeList = deliveryUsers;
                }

                if (activeList.length > 0) {
                    userContainer.style.display = 'block';
                    activeList.forEach(user => {
                        const option = document.createElement('option');
                        option.value = user.id;
                        option.textContent = user.name;
                        // Store extra data in dataset
                        option.dataset.name = user.name;
                        option.dataset.email = user.email || '';
                        option.dataset.phone = user.phone || '';
                        userSelect.appendChild(option);
                    });
                } else {
                    userContainer.style.display = 'none';
                }
            });
        }

        if (userSelect) {
            userSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption && selectedOption.value) {
                    userIdInput.value = selectedOption.value;
                    nameInput.value = selectedOption.dataset.name;
                    nameInput.readOnly = true; // Lock name if selected from list to prevent mismatch
                    
                    // Construct a useful identifier
                    let ident = [];
                    if (selectedOption.dataset.phone) ident.push(selectedOption.dataset.phone);
                    
                    idInput.value = ident.join(' / ');
                    
                    // Visual feedback
                    nameInput.classList.add('bg-success-subtle');
                    setTimeout(() => nameInput.classList.remove('bg-success-subtle'), 1000);
                } else {
                    // Reset if cleared
                    userIdInput.value = '';
                    nameInput.value = '';
                    nameInput.readOnly = false;
                    idInput.value = '';
                }
            });
        }
        
        // Allow clearing selection by manually editing name? 
        // Better: Add a "Clear Selection" logic or just rely on the dropdown.
        // User said "make sure no global handler make sure is bypassed". 
        // Maybe they want to be ABLE to edit the name even if selected?
        // Let's make it so if they change the dropdown to empty, it clears.
        // If they want to bypass, they just don't select a user. 
        // I will add a manual "Unlock" or just keep it simple: 
        // If they select a user, populate it. If they want to type manually, they should select "Choose User" (empty) first.
        // To be safe, let's ensure we DON'T force the user ID if they type manually.
        
        if (nameInput) {
            nameInput.addEventListener('input', function() {
                // If user types manually, clear the hidden ID so it doesn't link to the wrong user
                // unless we want to allow updating the name of the linked user? 
                // Usually safer to unlink if it's a "Global Handler Bypass" request.
                if (!nameInput.readOnly) {
                     userIdInput.value = ''; 
                }
            });
            
            // Allow unlocking if they double click? No, keep it simple.
            // Just add a small "clear" button or logic? 
            // Actually, simply removing 'readOnly = true' from my previous thought might be better.
            // Let's NOT set readOnly=true, but clear ID on input.
        }

        // --- Customer search (OUTGOING -> Recipient Type = Customer) ---
        const recipientTypeSelect = document.querySelector('select[name="recipient_type"]');
        const customerRecipientSearchWrapper = document.getElementById('customer_recipient_search_wrapper');
        const customerRecipientIdentifierWrapper = document.getElementById('customer_recipient_identifier_wrapper');
        const recipientCustomerSearchInput = document.getElementById('recipient_customer_search');
        const recipientCustomerSuggestions = document.getElementById('recipient_customer_suggestions');
        const recipientCustomerIdInput = document.getElementById('recipient_customer_id');
        const recipientNameInputOut = document.getElementById('recipient_name');
        const recipientIdentifierInput = document.getElementById('recipient_identifier');

        function closeCustomerSuggestions() {
            if (recipientCustomerSuggestions) {
                recipientCustomerSuggestions.style.display = 'none';
                recipientCustomerSuggestions.innerHTML = '';
            }
        }

        function setCustomerMode(isCustomer) {
            if (customerRecipientSearchWrapper) customerRecipientSearchWrapper.style.display = isCustomer ? 'block' : 'none';
            if (customerRecipientIdentifierWrapper) customerRecipientIdentifierWrapper.style.display = isCustomer ? 'block' : 'none';
            if (recipientIdentifierInput) recipientIdentifierInput.required = isCustomer;

            if (!isCustomer) {
                if (recipientCustomerIdInput) recipientCustomerIdInput.value = '';
                if (recipientIdentifierInput) recipientIdentifierInput.value = '';
                closeCustomerSuggestions();
            }
        }

        if (recipientTypeSelect) {
            setCustomerMode(recipientTypeSelect.value === 'Customer');
            recipientTypeSelect.addEventListener('change', function() {
                setCustomerMode(this.value === 'Customer');
            });
        }

        // If user edits manually, unlink from any selected customer
        if (recipientNameInputOut) {
            recipientNameInputOut.addEventListener('input', function() {
                if (recipientCustomerIdInput) recipientCustomerIdInput.value = '';
            });
        }
        if (recipientIdentifierInput) {
            recipientIdentifierInput.addEventListener('input', function() {
                if (recipientCustomerIdInput) recipientCustomerIdInput.value = '';
            });
        }

        if (recipientCustomerSearchInput) {
            const customerSearchUrl = "{{ route('gatekeeper.customers.search') }}";
            let debounceTimer = null;

            recipientCustomerSearchInput.addEventListener('input', function() {
                const q = (this.value || '').trim();

                // Only show suggestions in customer mode.
                if (recipientTypeSelect && recipientTypeSelect.value !== 'Customer') {
                    closeCustomerSuggestions();
                    return;
                }

                if (q.length < 2) {
                    closeCustomerSuggestions();
                    return;
                }

                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(async () => {
                    try {
                        if (recipientCustomerSuggestions) recipientCustomerSuggestions.style.display = 'none';

                        const res = await fetch(customerSearchUrl + '?q=' + encodeURIComponent(q), {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' },
                            credentials: 'same-origin'
                        });
                        if (!res.ok) throw new Error('Search failed');

                        const data = await res.json();
                        const customers = data.customers || [];

                        if (!customers.length) {
                            closeCustomerSuggestions();
                            return;
                        }

                        if (recipientCustomerSuggestions) {
                            recipientCustomerSuggestions.style.display = 'block';
                            recipientCustomerSuggestions.innerHTML = '';

                            customers.forEach(c => {
                                const ident = (c.phone || c.tax_id || '').trim();
                                const label = ident ? (c.name + ' (' + ident + ')') : c.name;

                                const btn = document.createElement('button');
                                btn.type = 'button';
                                btn.className = 'list-group-item list-group-item-action';
                                btn.textContent = label;
                                btn.addEventListener('click', function() {
                                    if (recipientCustomerIdInput) recipientCustomerIdInput.value = c.id;
                                    if (recipientNameInputOut) recipientNameInputOut.value = c.name || '';
                                    if (recipientIdentifierInput) recipientIdentifierInput.value = (c.phone || c.tax_id || '');
                                    recipientCustomerSearchInput.value = c.name || '';
                                    closeCustomerSuggestions();
                                });

                                recipientCustomerSuggestions.appendChild(btn);
                            });
                        }
                    } catch (e) {
                        // Silent fail: keep form usable for manual entry.
                        closeCustomerSuggestions();
                    }
                }, 250);
            });

            // Close dropdown on outside click
            document.addEventListener('click', function(e) {
                if (!recipientCustomerSuggestions || recipientCustomerSuggestions.style.display !== 'block') return;
                const clickedInside = recipientCustomerSuggestions.contains(e.target) || recipientCustomerSearchInput.contains(e.target);
                if (!clickedInside) closeCustomerSuggestions();
            });
        }

        // --- Task Search (Pull from Task) ---
        const taskSearchInput = document.getElementById('task_search_input');
        const taskSuggestions = document.getElementById('task_suggestions_container');
        const taskSearchUrl = "{{ route('gatekeeper.tasks.search') }}";

        function closeTaskSuggestions() {
            if (taskSuggestions) {
                taskSuggestions.style.display = 'none';
                taskSuggestions.innerHTML = '';
            }
        }

        if (taskSearchInput) {
            let taskDebounceTimer = null;

            taskSearchInput.addEventListener('input', function() {
                const q = (this.value || '').trim();
                if (q.length < 2) {
                    closeTaskSuggestions();
                    return;
                }

                clearTimeout(taskDebounceTimer);
                taskDebounceTimer = setTimeout(async () => {
                    try {
                        const res = await fetch(taskSearchUrl + '?q=' + encodeURIComponent(q), {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' },
                            credentials: 'same-origin'
                        });
                        const data = await res.json();
                        const tasks = data.tasks || [];

                        if (!tasks.length) {
                            closeTaskSuggestions();
                            return;
                        }

                        taskSuggestions.style.display = 'block';
                        taskSuggestions.innerHTML = '';

                        tasks.forEach(t => {
                            const btn = document.createElement('button');
                            btn.type = 'button';
                            btn.className = 'list-group-item list-group-item-action p-2';
                            btn.innerHTML = `
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold small">${t.task_code}</span>
                                    <span class="badge bg-light text-dark x-small">${t.department}</span>
                                </div>
                                <div class="text-truncate small">${t.title}</div>
                                <div class="x-small text-muted"><i class="fas fa-user me-1"></i>${t.customer_name}</div>
                            `;
                            btn.addEventListener('click', function() {
                                // Auto-fill fields
                                document.querySelector('input[name="product_name"]').value = t.title;
                                document.querySelector('input[name="authorization_reference"]').value = t.task_code;
                                
                                if (recipientTypeSelect && recipientTypeSelect.value === 'Customer') {
                                    if (recipientNameInputOut) recipientNameInputOut.value = t.customer_name;
                                    if (recipientIdentifierInput) recipientIdentifierInput.value = t.customer_phone;
                                }

                                taskSearchInput.value = t.task_code;
                                closeTaskSuggestions();
                                
                                // Flash feedback
                                taskSearchInput.classList.add('bg-success-subtle');
                                setTimeout(() => taskSearchInput.classList.remove('bg-success-subtle'), 1000);
                            });

                            taskSuggestions.appendChild(btn);
                        });
                    } catch (e) {
                        closeTaskSuggestions();
                    }
                }, 300);
            });

            document.addEventListener('click', function(e) {
                if (!taskSuggestions || taskSuggestions.style.display !== 'block') return;
                if (!taskSuggestions.contains(e.target) && !taskSearchInput.contains(e.target)) {
                    closeTaskSuggestions();
                }
            });
        }

        // --- Processing State ---
        const form = document.getElementById('movementForm');
        if (form) {
            form.addEventListener('submit', function() {
                const btn = this.querySelector('button[type="submit"]');
                if (btn) {
                    const originalText = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Processing...';
                    
                    // Allow re-enable after 5s just in case of backend stall (optional)
                    // setTimeout(() => { btn.disabled = false; btn.innerHTML = originalText; }, 5000);
                }
            });
        }
    });
</script>
<style>
.hover-lift:hover { transform: translateY(-2px); box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15)!important; }
</style>
@endpush
@endsection

