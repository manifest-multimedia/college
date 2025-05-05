<x-dashboard.default title="Support Tickets">
    <!--begin::Card-->
    <div class="card">
        <!--begin::Card header-->
        <div class="card-header">
            <div class="card-title">
                <h3 class="card-title">
                    <i class="ki-duotone ki-ticket fs-1 me-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                    </i>
                    Support Tickets
                </h3>
            </div>
            <div class="card-toolbar">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#create_ticket_modal">
                    <i class="ki-duotone ki-plus-square fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                    </i>
                    Create New Ticket
                </button>
            </div>
        </div>
        <!--end::Card header-->
        <!--begin::Card body-->
        <div class="card-body py-4">
            <!-- Tabs for filtering tickets -->
            <ul class="nav nav-tabs nav-line-tabs mb-5 fs-6">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#all_tickets">All Tickets</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#open_tickets">Open</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#in_progress_tickets">In Progress</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#closed_tickets">Closed</a>
                </li>
            </ul>

            <!-- Tab content -->
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="all_tickets" role="tabpanel">
                    <!--begin::Table -->
                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="tickets_table">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th class="min-w-125px">Ticket ID</th>
                                <th class="min-w-175px">Subject</th>
                                <th class="min-w-125px">Category</th>
                                <th class="min-w-125px">Status</th>
                                <th class="min-w-125px">Last Updated</th>
                                <th class="min-w-125px">Created</th>
                                <th class="text-end min-w-100px">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold">
                            <!-- Example ticket rows for demonstration -->
                            <tr>
                                <td>
                                    <a href="#" class="text-gray-800 text-hover-primary mb-1">#TKT-0001</a>
                                </td>
                                <td>Login issues with student portal</td>
                                <td>IT Support</td>
                                <td>
                                    <span class="badge badge-light-success">Open</span>
                                </td>
                                <td>2 days ago</td>
                                <td>May 2, 2025</td>
                                <td class="text-end">
                                    <a href="#" class="btn btn-sm btn-light btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                        Actions
                                        <i class="ki-duotone ki-down fs-5 ms-1"></i>
                                    </a>
                                    <!--begin::Menu-->
                                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                                        <div class="menu-item px-3">
                                            <a href="#" class="menu-link px-3" data-bs-toggle="modal" data-bs-target="#view_ticket_modal">
                                                View
                                            </a>
                                        </div>
                                        <div class="menu-item px-3">
                                            <a href="#" class="menu-link px-3" data-bs-toggle="modal" data-bs-target="#reply_ticket_modal">
                                                Reply
                                            </a>
                                        </div>
                                        <div class="menu-item px-3">
                                            <a href="#" class="menu-link px-3" data-kt-tickets-table-filter="close_row">
                                                Close
                                            </a>
                                        </div>
                                    </div>
                                    <!--end::Menu-->
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <a href="#" class="text-gray-800 text-hover-primary mb-1">#TKT-0002</a>
                                </td>
                                <td>Fee structure inquiry</td>
                                <td>Finance</td>
                                <td>
                                    <span class="badge badge-light-warning">In Progress</span>
                                </td>
                                <td>5 hours ago</td>
                                <td>May 3, 2025</td>
                                <td class="text-end">
                                    <a href="#" class="btn btn-sm btn-light btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                        Actions
                                        <i class="ki-duotone ki-down fs-5 ms-1"></i>
                                    </a>
                                    <!--begin::Menu-->
                                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                                        <div class="menu-item px-3">
                                            <a href="#" class="menu-link px-3" data-bs-toggle="modal" data-bs-target="#view_ticket_modal">
                                                View
                                            </a>
                                        </div>
                                        <div class="menu-item px-3">
                                            <a href="#" class="menu-link px-3" data-bs-toggle="modal" data-bs-target="#reply_ticket_modal">
                                                Reply
                                            </a>
                                        </div>
                                        <div class="menu-item px-3">
                                            <a href="#" class="menu-link px-3" data-kt-tickets-table-filter="close_row">
                                                Close
                                            </a>
                                        </div>
                                    </div>
                                    <!--end::Menu-->
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <a href="#" class="text-gray-800 text-hover-primary mb-1">#TKT-0003</a>
                                </td>
                                <td>Course registration assistance</td>
                                <td>Academic Affairs</td>
                                <td>
                                    <span class="badge badge-light-danger">Closed</span>
                                </td>
                                <td>2 days ago</td>
                                <td>Apr 28, 2025</td>
                                <td class="text-end">
                                    <a href="#" class="btn btn-sm btn-light btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                        Actions
                                        <i class="ki-duotone ki-down fs-5 ms-1"></i>
                                    </a>
                                    <!--begin::Menu-->
                                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                                        <div class="menu-item px-3">
                                            <a href="#" class="menu-link px-3" data-bs-toggle="modal" data-bs-target="#view_ticket_modal">
                                                View
                                            </a>
                                        </div>
                                        <div class="menu-item px-3">
                                            <a href="#" class="menu-link px-3" data-bs-toggle="modal" data-bs-target="#reply_ticket_modal">
                                                Reply
                                            </a>
                                        </div>
                                        <div class="menu-item px-3">
                                            <a href="#" class="menu-link px-3" data-kt-tickets-table-filter="reopen_row">
                                                Reopen
                                            </a>
                                        </div>
                                    </div>
                                    <!--end::Menu-->
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <!--end::Table-->
                </div>
                
                <div class="tab-pane fade" id="open_tickets" role="tabpanel">
                    <!-- Open tickets will be filtered here -->
                    <div class="py-5 text-center fs-4 text-gray-500">
                        No open tickets found. Click "Create New Ticket" to submit a support request.
                    </div>
                </div>
                
                <div class="tab-pane fade" id="in_progress_tickets" role="tabpanel">
                    <!-- In progress tickets will be filtered here -->
                    <div class="py-5 text-center fs-4 text-gray-500">
                        No tickets currently in progress.
                    </div>
                </div>
                
                <div class="tab-pane fade" id="closed_tickets" role="tabpanel">
                    <!-- Closed tickets will be filtered here -->
                    <div class="py-5 text-center fs-4 text-gray-500">
                        No closed tickets found.
                    </div>
                </div>
            </div>
        </div>
        <!--end::Card body-->
    </div>
    <!--end::Card-->

    <!-- Create Ticket Modal -->
    <div class="modal fade" id="create_ticket_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content">
                <form class="form" action="#" id="create_ticket_form">
                    <div class="modal-header">
                        <h2 class="fw-bold">Create New Support Ticket</h2>
                        <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                            <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                        </div>
                    </div>
                    <div class="modal-body py-10 px-lg-17">
                        <!-- Subject Field -->
                        <div class="fv-row mb-7">
                            <label class="required fs-6 fw-semibold mb-2">Subject</label>
                            <input type="text" class="form-control form-control-solid" placeholder="Enter ticket subject" name="ticket_subject" required />
                        </div>
                        
                        <!-- Category Field -->
                        <div class="fv-row mb-7">
                            <label class="required fs-6 fw-semibold mb-2">Category</label>
                            <select class="form-select form-select-solid" name="ticket_category" required>
                                <option value="">Select a category...</option>
                                <option value="IT Support">IT Support</option>
                                <option value="Finance">Finance</option>
                                <option value="Academic Affairs">Academic Affairs</option>
                                <option value="Examination">Examination</option>
                                <option value="Course Registration">Course Registration</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        
                        <!-- Priority Field -->
                        <div class="fv-row mb-7">
                            <label class="required fs-6 fw-semibold mb-2">Priority</label>
                            <select class="form-select form-select-solid" name="ticket_priority" required>
                                <option value="Low">Low</option>
                                <option value="Medium" selected>Medium</option>
                                <option value="High">High</option>
                                <option value="Urgent">Urgent</option>
                            </select>
                        </div>
                        
                        <!-- Message Field -->
                        <div class="fv-row mb-7">
                            <label class="required fs-6 fw-semibold mb-2">Message</label>
                            <textarea class="form-control form-control-solid" rows="6" name="ticket_message" placeholder="Describe your issue in detail" required></textarea>
                        </div>
                        
                        <!-- Attachments Field -->
                        <div class="fv-row mb-7">
                            <label class="fs-6 fw-semibold mb-2">Attachments (Optional)</label>
                            <input type="file" class="form-control form-control-solid" name="ticket_attachments" multiple />
                            <div class="form-text">You can upload up to 5 files (Max 2MB each).</div>
                        </div>
                    </div>
                    <div class="modal-footer flex-center">
                        <button type="reset" data-bs-dismiss="modal" class="btn btn-light me-3">Cancel</button>
                        <button type="submit" id="create_ticket_submit" class="btn btn-primary">
                            <span class="indicator-label">Submit</span>
                            <span class="indicator-progress">Please wait... 
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- View Ticket Modal -->
    <div class="modal fade" id="view_ticket_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-900px">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bold">Ticket #TKT-0001: Login issues with student portal</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <div class="modal-body py-10 px-lg-17">
                    <!-- Ticket Info -->
                    <div class="d-flex flex-wrap mb-10">
                        <div class="border border-dashed border-gray-300 rounded py-3 px-4 me-6 mb-3">
                            <div class="fw-semibold text-gray-500">Category:</div>
                            <div class="fs-5 fw-bold text-gray-800">IT Support</div>
                        </div>
                        <div class="border border-dashed border-gray-300 rounded py-3 px-4 me-6 mb-3">
                            <div class="fw-semibold text-gray-500">Status:</div>
                            <div class="fs-5 fw-bold text-gray-800">Open</div>
                        </div>
                        <div class="border border-dashed border-gray-300 rounded py-3 px-4 me-6 mb-3">
                            <div class="fw-semibold text-gray-500">Priority:</div>
                            <div class="fs-5 fw-bold text-gray-800">Medium</div>
                        </div>
                        <div class="border border-dashed border-gray-300 rounded py-3 px-4 me-6 mb-3">
                            <div class="fw-semibold text-gray-500">Created On:</div>
                            <div class="fs-5 fw-bold text-gray-800">May 2, 2025</div>
                        </div>
                    </div>
                    
                    <!-- Conversation Thread -->
                    <div class="mb-0">
                        <h3 class="fw-bold mb-5">Conversation</h3>
                        
                        <!-- Original Message -->
                        <div class="border border-dashed border-gray-300 rounded p-7 mb-10">
                            <div class="d-flex flex-stack mb-5">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-circle symbol-40px me-2">
                                        <img src="{{ Auth::user()->profile_photo_url }}" alt="Profile" />
                                    </div>
                                    <div class="ms-3">
                                        <a href="#" class="fs-5 fw-bold text-gray-900 text-hover-primary me-1">{{ Auth::user()->name }}</a>
                                        <span class="text-muted fs-7 mb-1">2 days ago</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="fs-6 text-gray-800 mb-5">
                                I'm having trouble logging into the student portal. Every time I try to log in, it shows "Invalid credentials" even though I'm sure my password is correct. I've tried resetting my password twice but still facing the same issue.
                            </div>
                            
                            <div class="fs-7 text-muted">Device: MacBook Pro | Browser: Chrome 123.0 | OS: macOS 16.2</div>
                        </div>
                        
                        <!-- Support Response -->
                        <div class="border border-dashed border-gray-300 rounded p-7 mb-10 bg-light">
                            <div class="d-flex flex-stack mb-5">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-circle symbol-40px me-2">
                                        <div class="symbol-label bg-light-primary">
                                            <i class="ki-duotone ki-abstract-26 fs-2 text-primary"><span class="path1"></span><span class="path2"></span></i>
                                        </div>
                                    </div>
                                    <div class="ms-3">
                                        <a href="#" class="fs-5 fw-bold text-gray-900 text-hover-primary me-1">Support Team</a>
                                        <span class="text-muted fs-7 mb-1">1 day ago</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="fs-6 text-gray-800 mb-5">
                                Hello {{ Auth::user()->name }},<br><br>
                                Thank you for reaching out to us. I understand you're having trouble accessing your student portal. Let me help you resolve this issue.<br><br>
                                Could you please verify the following:<br>
                                1. Are you using your student ID (not your email) as the username?<br>
                                2. Have you cleared your browser cache/cookies?<br>
                                3. Have you tried using a different browser?<br><br>
                                Let me know if any of these steps help. If not, we can reset your access from our end.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer flex-center">
                    <button type="button" data-bs-dismiss="modal" class="btn btn-light me-3">Close</button>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#reply_ticket_modal" data-bs-dismiss="modal">
                        Reply
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Reply Ticket Modal -->
    <div class="modal fade" id="reply_ticket_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content">
                <form class="form" action="#" id="reply_ticket_form">
                    <div class="modal-header">
                        <h2 class="fw-bold">Reply to Ticket #TKT-0001</h2>
                        <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                            <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                        </div>
                    </div>
                    <div class="modal-body py-10 px-lg-17">
                        <!-- Message Field -->
                        <div class="fv-row mb-7">
                            <label class="required fs-6 fw-semibold mb-2">Your Reply</label>
                            <textarea class="form-control form-control-solid" rows="6" name="reply_message" placeholder="Type your reply here" required></textarea>
                        </div>
                        
                        <!-- Attachments Field -->
                        <div class="fv-row mb-7">
                            <label class="fs-6 fw-semibold mb-2">Attachments (Optional)</label>
                            <input type="file" class="form-control form-control-solid" name="reply_attachments" multiple />
                            <div class="form-text">You can upload up to 5 files (Max 2MB each).</div>
                        </div>
                    </div>
                    <div class="modal-footer flex-center">
                        <button type="reset" data-bs-dismiss="modal" class="btn btn-light me-3">Cancel</button>
                        <button type="submit" id="reply_ticket_submit" class="btn btn-primary">
                            <span class="indicator-label">Submit Reply</span>
                            <span class="indicator-progress">Please wait... 
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable for tickets
    const ticketsTable = $('#tickets_table').DataTable({
        info: false,
        order: [],
        columnDefs: [
            { orderable: false, targets: 6 } // Actions column
        ],
    });
    
    // Form submission handling
    $('#create_ticket_form').on('submit', function(e) {
        e.preventDefault();
        
        // Show loading indicator
        const submitButton = document.getElementById('create_ticket_submit');
        submitButton.setAttribute('data-kt-indicator', 'on');
        submitButton.disabled = true;
        
        // Simulate form submission (replace with actual AJAX call to your backend)
        setTimeout(function() {
            // Hide loading indicator
            submitButton.removeAttribute('data-kt-indicator');
            submitButton.disabled = false;
            
            // Close modal and show success message
            $('#create_ticket_modal').modal('hide');
            
            // Show success message
            Swal.fire({
                text: "Your support ticket has been created successfully!",
                icon: "success",
                buttonsStyling: false,
                confirmButtonText: "OK",
                customClass: {
                    confirmButton: "btn btn-primary"
                }
            });
            
            // Reset form
            $('#create_ticket_form')[0].reset();
        }, 1500);
    });
    
    // Reply form submission handling
    $('#reply_ticket_form').on('submit', function(e) {
        e.preventDefault();
        
        // Show loading indicator
        const submitButton = document.getElementById('reply_ticket_submit');
        submitButton.setAttribute('data-kt-indicator', 'on');
        submitButton.disabled = true;
        
        // Simulate form submission (replace with actual AJAX call to your backend)
        setTimeout(function() {
            // Hide loading indicator
            submitButton.removeAttribute('data-kt-indicator');
            submitButton.disabled = false;
            
            // Close modal and show success message
            $('#reply_ticket_modal').modal('hide');
            
            // Show success message
            Swal.fire({
                text: "Your reply has been submitted successfully!",
                icon: "success",
                buttonsStyling: false,
                confirmButtonText: "OK",
                customClass: {
                    confirmButton: "btn btn-primary"
                }
            });
            
            // Reset form
            $('#reply_ticket_form')[0].reset();
        }, 1500);
    });
    
    // Handle status changes
    document.querySelectorAll('[data-kt-tickets-table-filter="close_row"]').forEach(element => {
        element.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Show confirmation dialog
            Swal.fire({
                text: "Are you sure you want to close this ticket?",
                icon: "warning",
                showCancelButton: true,
                buttonsStyling: false,
                confirmButtonText: "Yes, close it!",
                cancelButtonText: "No, cancel",
                customClass: {
                    confirmButton: "btn fw-bold btn-danger",
                    cancelButton: "btn fw-bold btn-active-light-primary"
                }
            }).then(function(result) {
                if (result.isConfirmed) {
                    // Simulate status change
                    Swal.fire({
                        text: "Ticket has been closed!",
                        icon: "success",
                        buttonsStyling: false,
                        confirmButtonText: "OK",
                        customClass: {
                            confirmButton: "btn fw-bold btn-primary",
                        }
                    });
                }
            });
        });
    });
    
    // Handle reopen ticket
    document.querySelectorAll('[data-kt-tickets-table-filter="reopen_row"]').forEach(element => {
        element.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Show confirmation dialog
            Swal.fire({
                text: "Are you sure you want to reopen this ticket?",
                icon: "question",
                showCancelButton: true,
                buttonsStyling: false,
                confirmButtonText: "Yes, reopen it!",
                cancelButtonText: "No, cancel",
                customClass: {
                    confirmButton: "btn fw-bold btn-success",
                    cancelButton: "btn fw-bold btn-active-light-primary"
                }
            }).then(function(result) {
                if (result.isConfirmed) {
                    // Simulate status change
                    Swal.fire({
                        text: "Ticket has been reopened!",
                        icon: "success",
                        buttonsStyling: false,
                        confirmButtonText: "OK",
                        customClass: {
                            confirmButton: "btn fw-bold btn-primary",
                        }
                    });
                }
            });
        });
    });
});
</script>
@endpush

</x-dashboard.default>