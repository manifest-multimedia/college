<x-dashboard.default title="Knowledge Base">
    <div class="card">
        <!--begin::Card header-->
        <div class="card-header">
            <div class="card-title">
                <h3 class="card-title">
                    <i class="ki-duotone ki-book-open fs-1 me-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    Knowledge Base
                </h3>
            </div>
            <div class="card-toolbar">
                <div class="d-flex align-items-center position-relative me-2">
                    <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-3">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    <input type="text" class="form-control form-control-solid w-250px ps-10" id="kb_search" placeholder="Search Knowledge Base" />
                </div>
            </div>
        </div>
        <!--end::Card header-->
        
        <!--begin::Card body-->
        <div class="card-body pt-6">
            <div class="mb-15">
                <!--begin::Categories-->
                <div class="mb-15">
                    <h4 class="text-gray-900 fw-bold mb-6">Categories</h4>
                    
                    <div class="row g-10">
                        <!--begin::Col-->
                        <div class="col-md-4">
                            <div class="card-xl-stretch bg-light-primary bg-hover-primary ms-md-n12 mb-10">
                                <div class="card-body d-flex flex-column px-9 py-9">
                                    <div class="mb-5">
                                        <div class="d-flex flex-center h-80px w-80px rounded-circle bg-light-primary mb-6">
                                            <i class="ki-duotone ki-bank fs-3x text-primary">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </div>
                                    </div>
                                    <a href="#" class="fs-4 text-gray-800 fw-bold hover-primary mb-3 category-link" data-category="finance">Finance & Billing</a>
                                    <div class="fw-semibold text-gray-400 mb-6">Fee structure, payments, invoices, and billing inquiries</div>
                                    <div class="d-flex flex-wrap mb-5">
                                        <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                            <div class="fw-semibold fs-6 text-gray-800">15 Articles</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Col-->
                        
                        <!--begin::Col-->
                        <div class="col-md-4">
                            <div class="card-xl-stretch bg-light-success bg-hover-success mx-md-n12 mb-10">
                                <div class="card-body d-flex flex-column px-9 py-9">
                                    <div class="mb-5">
                                        <div class="d-flex flex-center h-80px w-80px rounded-circle bg-light-success mb-6">
                                            <i class="ki-duotone ki-book fs-3x text-success">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </div>
                                    </div>
                                    <a href="#" class="fs-4 text-gray-800 fw-bold hover-primary mb-3 category-link" data-category="academic">Academic Affairs</a>
                                    <div class="fw-semibold text-gray-400 mb-6">Course registration, academic calendar, exams and grading</div>
                                    <div class="d-flex flex-wrap mb-5">
                                        <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                            <div class="fw-semibold fs-6 text-gray-800">21 Articles</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Col-->
                        
                        <!--begin::Col-->
                        <div class="col-md-4">
                            <div class="card-xl-stretch bg-light-warning bg-hover-warning me-md-n12 mb-10">
                                <div class="card-body d-flex flex-column px-9 py-9">
                                    <div class="mb-5">
                                        <div class="d-flex flex-center h-80px w-80px rounded-circle bg-light-warning mb-6">
                                            <i class="ki-duotone ki-abstract-26 fs-3x text-warning">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </div>
                                    </div>
                                    <a href="#" class="fs-4 text-gray-800 fw-bold hover-primary mb-3 category-link" data-category="it">IT Support</a>
                                    <div class="fw-semibold text-gray-400 mb-6">Technical support, login issues, system access, and passwords</div>
                                    <div class="d-flex flex-wrap mb-5">
                                        <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                            <div class="fw-semibold fs-6 text-gray-800">18 Articles</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Col-->

                        <!--begin::Col-->
                        <div class="col-md-4">
                            <div class="card-xl-stretch bg-light-danger bg-hover-danger ms-md-n12 mb-10">
                                <div class="card-body d-flex flex-column px-9 py-9">
                                    <div class="mb-5">
                                        <div class="d-flex flex-center h-80px w-80px rounded-circle bg-light-danger mb-6">
                                            <i class="ki-duotone ki-calendar-8 fs-3x text-danger">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                                <span class="path4"></span>
                                                <span class="path5"></span>
                                                <span class="path6"></span>
                                            </i>
                                        </div>
                                    </div>
                                    <a href="#" class="fs-4 text-gray-800 fw-bold hover-primary mb-3 category-link" data-category="exam">Examinations</a>
                                    <div class="fw-semibold text-gray-400 mb-6">Exam schedules, examination rules, results, and exam clearance</div>
                                    <div class="d-flex flex-wrap mb-5">
                                        <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                            <div class="fw-semibold fs-6 text-gray-800">12 Articles</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Col-->

                        <!--begin::Col-->
                        <div class="col-md-4">
                            <div class="card-xl-stretch bg-light-info bg-hover-info mx-md-n12 mb-10">
                                <div class="card-body d-flex flex-column px-9 py-9">
                                    <div class="mb-5">
                                        <div class="d-flex flex-center h-80px w-80px rounded-circle bg-light-info mb-6">
                                            <i class="ki-duotone ki-office-bag fs-3x text-info">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                                <span class="path4"></span>
                                            </i>
                                        </div>
                                    </div>
                                    <a href="#" class="fs-4 text-gray-800 fw-bold hover-primary mb-3 category-link" data-category="student">Student Services</a>
                                    <div class="fw-semibold text-gray-400 mb-6">Student ID, profile updates, accommodations, and resources</div>
                                    <div class="d-flex flex-wrap mb-5">
                                        <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                            <div class="fw-semibold fs-6 text-gray-800">10 Articles</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Col-->
                        
                        <!--begin::Col-->
                        <div class="col-md-4">
                            <div class="card-xl-stretch bg-light-dark bg-hover-dark me-md-n12 mb-10">
                                <div class="card-body d-flex flex-column px-9 py-9">
                                    <div class="mb-5">
                                        <div class="d-flex flex-center h-80px w-80px rounded-circle bg-light-dark mb-6">
                                            <i class="ki-duotone ki-license fs-3x text-dark">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </div>
                                    </div>
                                    <a href="#" class="fs-4 text-gray-800 fw-bold hover-primary mb-3 category-link" data-category="general">General Information</a>
                                    <div class="fw-semibold text-gray-400 mb-6">Campus information, policies, procedures, and general FAQ</div>
                                    <div class="d-flex flex-wrap mb-5">
                                        <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                            <div class="fw-semibold fs-6 text-gray-800">8 Articles</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Col-->                        
                    </div>
                </div>
                <!--end::Categories-->
                
                <!--begin::Popular Articles-->
                <div class="mb-15" id="popular_articles">
                    <h4 class="text-gray-900 fw-bold mb-6">Popular Articles</h4>
                    
                    <div class="row g-10">
                        <!--begin::Col-->
                        <div class="col-md-4">
                            <div class="card-xl-stretch">
                                <div class="card card-bordered hover-elevate-up">
                                    <div class="card-header">
                                        <h3 class="card-title">How to Reset Your Password</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-5">
                                            <div class="d-flex align-items-center flex-grow-1">
                                                <div class="symbol symbol-circle bg-light-primary me-2">
                                                    <span class="symbol-label fw-bold">IT</span>
                                                </div>
                                                <div class="me-3">
                                                    <a href="#" class="fs-7 text-gray-700 text-hover-primary">IT Support</a>
                                                </div>
                                                <span class="badge badge-light fw-bold my-2">Updated 3 days ago</span>
                                            </div>
                                        </div>
                                        <p class="text-gray-700 fs-6 fw-normal mb-5">Learn how to reset your password if you can't log into your student account or portal.</p>
                                        <a href="#" class="btn btn-sm btn-light-primary" data-article-id="1">Read More</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Col-->

                        <!--begin::Col-->
                        <div class="col-md-4">
                            <div class="card-xl-stretch">
                                <div class="card card-bordered hover-elevate-up">
                                    <div class="card-header">
                                        <h3 class="card-title">Course Registration Process</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-5">
                                            <div class="d-flex align-items-center flex-grow-1">
                                                <div class="symbol symbol-circle bg-light-success me-2">
                                                    <span class="symbol-label fw-bold">AA</span>
                                                </div>
                                                <div class="me-3">
                                                    <a href="#" class="fs-7 text-gray-700 text-hover-primary">Academic Affairs</a>
                                                </div>
                                                <span class="badge badge-light fw-bold my-2">Updated 1 week ago</span>
                                            </div>
                                        </div>
                                        <p class="text-gray-700 fs-6 fw-normal mb-5">Step-by-step guide to register for courses each semester and troubleshoot common issues.</p>
                                        <a href="#" class="btn btn-sm btn-light-success" data-article-id="2">Read More</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Col-->

                        <!--begin::Col-->
                        <div class="col-md-4">
                            <div class="card-xl-stretch">
                                <div class="card card-bordered hover-elevate-up">
                                    <div class="card-header">
                                        <h3 class="card-title">Understanding Your Fee Statement</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-5">
                                            <div class="d-flex align-items-center flex-grow-1">
                                                <div class="symbol symbol-circle bg-light-primary me-2">
                                                    <span class="symbol-label fw-bold">FIN</span>
                                                </div>
                                                <div class="me-3">
                                                    <a href="#" class="fs-7 text-gray-700 text-hover-primary">Finance & Billing</a>
                                                </div>
                                                <span class="badge badge-light fw-bold my-2">Updated 2 weeks ago</span>
                                            </div>
                                        </div>
                                        <p class="text-gray-700 fs-6 fw-normal mb-5">A complete guide to interpreting your fee statement, balance, and payment history.</p>
                                        <a href="#" class="btn btn-sm btn-light-primary" data-article-id="3">Read More</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Col-->
                    </div>
                </div>
                <!--end::Popular Articles-->
                
                <!--begin::Category Articles (Hidden by default)-->
                <div class="mb-15 d-none" id="finance_articles">
                    <div class="d-flex flex-stack mb-5">
                        <h4 class="text-gray-900 fw-bold">Finance & Billing Articles</h4>
                        <a href="#" class="back-to-categories fs-6 fw-semibold">← Back to Categories</a>
                    </div>
                    
                    <div class="separator separator-dashed border-gray-300 mb-8"></div>
                    
                    <div class="row g-10">
                        <!--begin::Col-->
                        <div class="col-md-6 mb-5">
                            <div class="d-flex align-items-center mb-1">
                                <i class="ki-duotone ki-book-open fs-2 text-primary me-3">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                <a href="#" class="fs-5 fw-bold text-gray-900 text-hover-primary" data-article-id="3">Understanding Your Fee Statement</a>
                            </div>
                            <div class="fs-7 text-muted ps-9">A complete guide to interpreting your fee statement, balance, and payment history</div>
                        </div>
                        <!--end::Col-->
                        
                        <!--begin::Col-->
                        <div class="col-md-6 mb-5">
                            <div class="d-flex align-items-center mb-1">
                                <i class="ki-duotone ki-book-open fs-2 text-primary me-3">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                <a href="#" class="fs-5 fw-bold text-gray-900 text-hover-primary">Payment Methods Available</a>
                            </div>
                            <div class="fs-7 text-muted ps-9">All the payment options available for paying tuition and other fees</div>
                        </div>
                        <!--end::Col-->
                        
                        <!--begin::Col-->
                        <div class="col-md-6 mb-5">
                            <div class="d-flex align-items-center mb-1">
                                <i class="ki-duotone ki-book-open fs-2 text-primary me-3">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                <a href="#" class="fs-5 fw-bold text-gray-900 text-hover-primary">Financial Aid and Scholarships</a>
                            </div>
                            <div class="fs-7 text-muted ps-9">Information about financial aid, scholarships and how to apply</div>
                        </div>
                        <!--end::Col-->
                        
                        <!--begin::Col-->
                        <div class="col-md-6 mb-5">
                            <div class="d-flex align-items-center mb-1">
                                <i class="ki-duotone ki-book-open fs-2 text-primary me-3">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                <a href="#" class="fs-5 fw-bold text-gray-900 text-hover-primary">Tuition Refund Policy</a>
                            </div>
                            <div class="fs-7 text-muted ps-9">Understanding the refund policy and timeline for withdrawals</div>
                        </div>
                        <!--end::Col-->
                        
                        <!--begin::Col-->
                        <div class="col-md-6 mb-5">
                            <div class="d-flex align-items-center mb-1">
                                <i class="ki-duotone ki-book-open fs-2 text-primary me-3">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                <a href="#" class="fs-5 fw-bold text-gray-900 text-hover-primary">Late Payment Penalties</a>
                            </div>
                            <div class="fs-7 text-muted ps-9">Information about late payment fees and penalties for missed deadlines</div>
                        </div>
                        <!--end::Col-->
                    </div>
                </div>
                
                <!-- Similar article sections for other categories -->
                <div class="mb-15 d-none" id="it_articles">
                    <div class="d-flex flex-stack mb-5">
                        <h4 class="text-gray-900 fw-bold">IT Support Articles</h4>
                        <a href="#" class="back-to-categories fs-6 fw-semibold">← Back to Categories</a>
                    </div>
                    
                    <div class="separator separator-dashed border-gray-300 mb-8"></div>
                    
                    <div class="row g-10">
                        <!-- Article items -->
                        <div class="col-md-6 mb-5">
                            <div class="d-flex align-items-center mb-1">
                                <i class="ki-duotone ki-book-open fs-2 text-warning me-3">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                <a href="#" class="fs-5 fw-bold text-gray-900 text-hover-primary" data-article-id="1">How to Reset Your Password</a>
                            </div>
                            <div class="fs-7 text-muted ps-9">Learn how to reset your password if you can't log into your student account or portal</div>
                        </div>
                    </div>
                </div>
                
                <!-- Individual article view (hidden by default) -->
                <div class="mb-15 d-none" id="article_view">
                    <div class="d-flex flex-stack mb-5">
                        <h3 class="text-gray-900 fw-bolder" id="article_title">Article Title</h3>
                        <a href="#" class="back-to-articles fs-6 fw-semibold" id="back_to_articles_link">← Back</a>
                    </div>
                    
                    <div class="separator separator-dashed border-gray-300 mb-8"></div>
                    
                    <div class="d-flex align-items-center bg-light-info rounded p-5 mb-7">
                        <div class="d-flex flex-center w-40px h-40px rounded-circle bg-light-info me-3">
                            <i class="ki-duotone ki-information-5 fs-2 text-info">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                        </div>
                        <div class="text-gray-700 fw-semibold fs-6" id="article_meta">Last updated: <span id="article_date">May 1, 2025</span> | Category: <span id="article_category">IT Support</span></div>
                    </div>
                    
                    <div class="fs-5 fw-normal text-gray-700 mb-8" id="article_content">
                        <!-- Article content will be loaded here -->
                        <p>Article content will appear here.</p>
                    </div>
                    
                    <div class="d-flex align-items-center rounded border border-dashed border-gray-300 p-5 mb-7">
                        <div class="d-flex flex-center w-40px h-40px rounded-circle bg-light-success me-3">
                            <i class="ki-duotone ki-questionnaire-tablet fs-2 text-success">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                        </div>
                        <div class="text-gray-700 fw-semibold fs-6">Was this article helpful? <a href="#" class="ms-2 fw-bold link-primary me-2">Yes</a> <a href="#" class="fw-bold link-danger">No</a></div>
                    </div>
                    
                    <div class="d-flex flex-stack">
                        <a href="#" id="create_ticket_btn" class="btn btn-sm btn-primary">Need More Help? Create a Support Ticket</a>
                        <a href="#" class="btn btn-sm btn-light">Share Article</a>
                    </div>
                </div>
                <!--end::Individual article view-->
            </div>
        </div>
        <!--end::Card body-->
    </div>
    <!--end::Card-->

@push('scripts')
<script>
    $(document).ready(function() {
        // Knowledge base articles database (mock data)
        const articles = {
            1: {
                title: "How to Reset Your Password",
                category: "IT Support",
                category_id: "it",
                date: "May 2, 2025",
                content: `
                <h4 class="mb-4">Password Reset Instructions</h4>
                <p>If you're having trouble logging into your student portal or email account, follow these steps to reset your password:</p>
                <ol>
                    <li class="mb-2">Visit the login page for the system you're trying to access (Student Portal, Email, etc.)</li>
                    <li class="mb-2">Click on the "Forgot Password" or "Reset Password" link below the login form</li>
                    <li class="mb-2">Enter your student ID or registered email address</li>
                    <li class="mb-2">Check your email for a password reset link (including spam/junk folders)</li>
                    <li class="mb-2">Click the reset link and follow the instructions to create a new password</li>
                    <li class="mb-2">Use your new password to log in</li>
                </ol>
                
                <h4 class="my-4">Password Requirements</h4>
                <p>When creating a new password, make sure it meets these requirements:</p>
                <ul>
                    <li class="mb-2">At least 8 characters long</li>
                    <li class="mb-2">Contains at least one uppercase letter</li>
                    <li class="mb-2">Contains at least one lowercase letter</li>
                    <li class="mb-2">Contains at least one number</li>
                    <li class="mb-2">Contains at least one special character (!@#$%^&*)</li>
                </ul>
                
                <h4 class="my-4">Common Issues</h4>
                <p><strong>No Reset Email Received:</strong> Check your spam/junk folder. If you still don't see it after 15 minutes, try again or contact IT support.</p>
                <p><strong>Link Expired:</strong> Password reset links expire after 30 minutes. Request a new one if needed.</p>
                <p><strong>Still Can't Log In:</strong> If you've reset your password but still can't log in, contact IT support at helpdesk@college.local or visit the IT help desk in the Administration Building.</p>
                `
            },
            2: {
                title: "Course Registration Process",
                category: "Academic Affairs",
                category_id: "academic",
                date: "April 28, 2025",
                content: `
                <h4 class="mb-4">Course Registration Timeline</h4>
                <p>The course registration process typically opens 4 weeks before the start of each semester. Registration periods are assigned based on your academic level and status.</p>
                
                <h4 class="my-4">Before You Register</h4>
                <p>Before registering for courses, make sure you:</p>
                <ul>
                    <li class="mb-2">Meet with your academic advisor to review your progress and select appropriate courses</li>
                    <li class="mb-2">Clear any financial holds on your account</li>
                    <li class="mb-2">Review the course catalog and create a list of preferred courses and backup options</li>
                    <li class="mb-2">Check your assigned registration window in the Student Portal</li>
                </ul>
                
                <h4 class="my-4">Step-by-Step Registration Guide</h4>
                <ol>
                    <li class="mb-2">Log in to your Student Portal during your assigned registration window</li>
                    <li class="mb-2">Navigate to "Course Registration" in the Academic section</li>
                    <li class="mb-2">Search for courses by subject, course number, or instructor</li>
                    <li class="mb-2">Review course details, prerequisites, and available sections</li>
                    <li class="mb-2">Add desired courses to your shopping cart</li>
                    <li class="mb-2">Review your selections for time conflicts</li>
                    <li class="mb-2">Submit your registration</li>
                    <li class="mb-2">Print or save a copy of your registration confirmation</li>
                </ol>
                
                <h4 class="my-4">Common Registration Issues</h4>
                <p><strong>Course Full:</strong> Join the waitlist if available. Check regularly for openings as students adjust their schedules.</p>
                <p><strong>Time Conflict:</strong> Select different course sections that don't overlap.</p>
                <p><strong>Prerequisite Error:</strong> If you believe you meet the prerequisites but receive an error, contact the academic department.</p>
                <p><strong>Technical Problems:</strong> Clear your browser cache, try a different browser, or contact IT support.</p>
                
                <h4 class="my-4">Add/Drop Period</h4>
                <p>After the initial registration period, you can make changes to your schedule during the add/drop period, which typically lasts through the first week of classes. Be aware of the academic calendar deadlines to avoid academic or financial penalties.</p>
                `
            },
            3: {
                title: "Understanding Your Fee Statement",
                category: "Finance & Billing",
                category_id: "finance",
                date: "April 22, 2025",
                content: `
                <h4 class="mb-4">Accessing Your Fee Statement</h4>
                <p>To view your current fee statement:</p>
                <ol>
                    <li class="mb-2">Log in to your Student Portal</li>
                    <li class="mb-2">Navigate to the "Financial" or "Student Account" section</li>
                    <li class="mb-2">Select "View Fee Statement" or "Account Summary"</li>
                    <li class="mb-2">Choose the relevant semester/term</li>
                </ol>
                
                <h4 class="my-4">Understanding Your Statement</h4>
                <p>Your fee statement is divided into several sections:</p>
                
                <p><strong>Charges:</strong> All costs applied to your account, including:</p>
                <ul>
                    <li class="mb-2">Tuition fees (based on enrolled credit hours)</li>
                    <li class="mb-2">General student fees (technology, library, activity fees, etc.)</li>
                    <li class="mb-2">Course-specific fees (lab fees, materials, etc.)</li>
                    <li class="mb-2">Housing and meal plan charges (if applicable)</li>
                </ul>
                
                <p><strong>Credits:</strong> All payments and reductions applied to your account:</p>
                <ul>
                    <li class="mb-2">Payments you've made</li>
                    <li class="mb-2">Scholarships and grants</li>
                    <li class="mb-2">Financial aid disbursements</li>
                    <li class="mb-2">Tuition waivers or discounts</li>
                </ul>
                
                <p><strong>Balance:</strong> The difference between your total charges and total credits. A positive balance means you owe money; a negative balance (credit) means you may be eligible for a refund.</p>
                
                <h4 class="my-4">Important Fee Statement Terms</h4>
                <p><strong>Due Date:</strong> The deadline by which payment must be received to avoid late fees.</p>
                <p><strong>Pending Aid:</strong> Financial aid that has been awarded but not yet disbursed. This amount is not yet applied to your balance.</p>
                <p><strong>Previous Balance:</strong> Any amount carried over from previous terms.</p>
                
                <h4 class="my-4">Payment Options</h4>
                <p>Your fee statement will include information about payment methods and installment plans. For more details, see our article on "Payment Methods Available."</p>
                
                <h4 class="my-4">Questions About Your Statement</h4>
                <p>If you have questions about specific charges or credits on your statement, contact the Finance Office at finance@college.local or visit in person during business hours (Monday-Friday, 9:00 AM - 4:00 PM).</p>
                `
            }
        };
        
        // Search functionality
        $('#kb_search').on('keyup', function() {
            const searchTerm = $(this).val().toLowerCase();
            
            // If search term is empty, show popular articles
            if (searchTerm === '') {
                $('#popular_articles').removeClass('d-none');
                $('.category-articles').addClass('d-none');
                $('#article_view').addClass('d-none');
                return;
            }
            
            // Hide all sections
            $('#popular_articles').addClass('d-none');
            $('.category-articles').addClass('d-none');
            $('#article_view').addClass('d-none');
            
            // Show search results (would be implemented with proper backend search in a real app)
            // For demo, we'll just display the popular articles with a message
            $('#popular_articles').removeClass('d-none');
            
            // Show success message for search
            toastr.success('Search completed for: ' + searchTerm);
        });
        
        // Category link click handling
        $('.category-link').on('click', function(e) {
            e.preventDefault();
            const category = $(this).data('category');
            
            // Hide all sections
            $('#popular_articles').addClass('d-none');
            $('.category-articles').addClass('d-none');
            $('#article_view').addClass('d-none');
            
            // Show selected category articles
            $('#' + category + '_articles').removeClass('d-none');
        });
        
        // Back to categories link
        $('.back-to-categories').on('click', function(e) {
            e.preventDefault();
            
            // Hide all category articles and article view
            $('.category-articles').addClass('d-none');
            $('#article_view').addClass('d-none');
            
            // Show popular articles
            $('#popular_articles').removeClass('d-none');
        });
        
        // Article link click handling
        $('[data-article-id]').on('click', function(e) {
            e.preventDefault();
            const articleId = $(this).data('article-id');
            
            // If article exists in our mock database
            if (articles[articleId]) {
                const article = articles[articleId];
                
                // Hide all sections
                $('#popular_articles').addClass('d-none');
                $('.category-articles').addClass('d-none');
                
                // Populate article view
                $('#article_title').text(article.title);
                $('#article_date').text(article.date);
                $('#article_category').text(article.category);
                $('#article_content').html(article.content);
                
                // Set back button link
                $('#back_to_articles_link').data('category', article.category_id);
                
                // Show article view
                $('#article_view').removeClass('d-none');
            }
        });
        
        // Back to articles link
        $('.back-to-articles').on('click', function(e) {
            e.preventDefault();
            const category = $(this).data('category');
            
            // Hide article view
            $('#article_view').addClass('d-none');
            
            if (category) {
                // Show category articles
                $('#' + category + '_articles').removeClass('d-none');
            } else {
                // Show popular articles
                $('#popular_articles').removeClass('d-none');
            }
        });
        
        // Create ticket button
        $('#create_ticket_btn').on('click', function(e) {
            e.preventDefault();
            window.location.href = "{{ route('support.tickets') }}";
        });
        
        // Article feedback (Yes/No)
        $('.link-primary, .link-danger').on('click', function(e) {
            e.preventDefault();
            const isHelpful = $(this).hasClass('link-primary');
            
            // Show feedback message
            if (isHelpful) {
                toastr.success('Thank you for your feedback!');
            } else {
                toastr.info('Thank you for your feedback. We\'ll work to improve this article.');
            }
        });
    });
</script>
@endpush

</x-dashboard.default>