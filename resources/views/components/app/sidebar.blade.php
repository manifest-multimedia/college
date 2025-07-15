<!--begin::Aside-->
<div id="kt_aside" class="py-9 aside" data-kt-drawer="true" data-kt-drawer-name="aside"
data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true"
data-kt-drawer-width="{default:'200px', '300px': '250px'}" data-kt-drawer-direction="start"
data-kt-drawer-toggle="#kt_aside_toggle">
<!--begin::Aside menu-->
<div class="mb-7 aside-menu flex-column-fluid ps-5 pe-3" id="kt_aside_menu">
    <!--begin::Aside Menu-->
    <div class="w-100 hover-scroll-y d-flex pe-2" id="kt_aside_menu_wrapper" data-kt-scroll="true"
        data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-height="auto"
        data-kt-scroll-dependencies="#kt_aside_footer, #kt_header"
        data-kt-scroll-wrappers="#kt_aside, #kt_aside_menu, #kt_aside_menu_wrapper"
        data-kt-scroll-offset="102">
        <!--begin::Menu-->
        <div class="my-auto menu menu-column menu-rounded menu-sub-indention menu-active-bg fw-semibold"
            id="#kt_aside_menu" data-kt-menu="true">
            <div class="pt-5 menu-item">
                <!--begin:Menu content-->
                <div class="menu-content">
                    <span class="menu-heading fw-bold text-uppercase fs-7">Navigation</span>
                </div>
                <!--end:Menu content-->
            </div>
            <!--begin:Menu item-->
            <div data-kt-menu-trigger="click" class="menu-item here show menu-accordion">
                <!--begin:Menu link-->
                @hasrole('Student')
                    <a href="{{ route('student.dashboard') }}" class="menu-link">
                @else
                    <a href="{{ route('dashboard') }}" class="menu-link">
                @endhasrole
                    <span class="menu-icon">
                        <i class="ki-duotone ki-element-11 fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                            <span class="path4"></span>
                        </i>
                    </span>
                    <span class="menu-title">Dashboard</span>
                  
                </a>
                <!--end:Menu link-->
            
            </div>
            <!--end:Menu item-->

            @hasrole('Student')
            <!--begin:Student Menu Items-->
            <!--begin:Menu item-->
            <div class="menu-item">
                <a href="{{ route('student.information') }}" class="menu-link">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-profile-circle fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                    </span>
                    <span class="menu-title">My Information</span>
                </a>
            </div>
            <!--end:Menu item-->

            <!--begin:Menu item-->
            <div class="menu-item">
                <a href="{{ route('courseregistration') }}" class="menu-link">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-book fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </span>
                    <span class="menu-title">Course Registration</span>
                </a>
            </div>
            <!--end:Menu item-->

            <!--begin:Menu item-->
            <div class="menu-item">
                <a href="{{ route('courseregistration.history') }}" class="menu-link">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-calendar fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </span>
                    <span class="menu-title">Registration History</span>
                </a>
            </div>
            <!--end:Menu item-->

            <!--begin:Menu item-->
            <div class="menu-item">
                <a href="{{ route('elections.active') }}" class="menu-link">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-vote fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </span>
                    <span class="menu-title">Elections</span>
                </a>
            </div>
            <!--end:Menu item-->
            <!--end:Student Menu Items-->
            @else
           
            @can('view students')    
            <!--begin:Menu item-->
            <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
            data-kt-menu-placement="right-start"
            class="menu-item menu-lg-down-accordion menu-sub-lg-down-indention">
            <!--begin:Menu link-->
            <a href={{ route('students') }} class="menu-link">
                <span class="menu-icon">
                    <i class="ki-duotone ki-file fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </span>
                <span class="menu-title">Students</span>
               
            </a>
            <!--end:Menu link-->
            </div>
            @endcan
            <!--end:Menu item-->
            
            <!-- Communication Module Navigation -->
            @hasrole(['System', 'Super Admin'])
            <!--begin:Menu item-->
            @php
                $communicationRoutes = [
                    'communication.sms',
                    'communication.contact-groups',
                    'communication.contacts',
                    'communication.sms-logs',
                    'communication.email',
                    'communication.chat',
                    'communication.ai-sensei'
                ];
                $isCommunicationActive = in_array(request()->route()->getName(), $communicationRoutes);
            @endphp
            <div data-kt-menu-trigger="click" class="menu-item menu-accordion {{ $isCommunicationActive ? 'show' : '' }}">
                <!--begin:Menu link-->
                <span class="menu-link">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-message-text-2 fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                    </span>
                    <span class="menu-title">Communication</span>
                    <span class="menu-arrow"></span>
                </span>
                <!--end:Menu link-->
                <!--begin:Menu sub-->
                <div class="menu-sub menu-sub-accordion {{ $isCommunicationActive ? 'show' : '' }}">
                    {{-- @can('send sms') --}}
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('communication.sms') ? 'active' : '' }}" href="{{ route('communication.sms') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Send SMS</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                    {{-- @endcan --}}
                    
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('communication.contact-groups') ? 'active' : '' }}" href="{{ route('communication.contact-groups') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Contact Groups</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                    
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('communication.contacts') ? 'active' : '' }}" href="{{ route('communication.contacts') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Manage Contacts</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                    
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('communication.sms-logs') ? 'active' : '' }}" href="{{ route('communication.sms-logs') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">SMS Logs</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                    
                    {{-- @can('send email') --}}
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('communication.email') ? 'active' : '' }}" href="{{ route('communication.email') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Send Email</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                    {{-- @endcan --}}
                    
                    {{-- @can('use ai chat') --}}
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('communication.chat') ? 'active' : '' }}" href="{{ route('communication.chat') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">AI Assistant</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                    {{-- @endcan --}}
                    
                    <!-- AI Sensei Assistant with File Capabilities -->
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('communication.ai-sensei') ? 'active' : '' }}" href="{{ route('communication.ai-sensei') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">AI Sensei Assistant</span>
                            <span class="badge badge-light-primary">New</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                </div>
                <!--end:Menu sub-->
            </div>
            <!--end:Menu item-->
            @endhasrole
            
            <!--begin:Menu item-->
            @php
                $memoRoutes = ['memos', 'memo.create'];
                $isMemoActive = in_array(request()->route()->getName(), $memoRoutes);
            @endphp
            <div data-kt-menu-trigger="click" class="menu-item menu-accordion {{ $isMemoActive ? 'show' : '' }}">
                <!--begin:Menu link-->
                <span class="menu-link">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-document fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </span>
                    <span class="menu-title">Memo Management</span>
                    <span class="menu-arrow"></span>
                </span>
                <!--end:Menu link-->
                <!--begin:Menu sub-->
                <div class="menu-sub menu-sub-accordion {{ $isMemoActive ? 'show' : '' }}">
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('memos') ? 'active' : '' }}" href="{{ route('memos') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">All Memos</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                    
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('memo.create') ? 'active' : '' }}" href="{{ route('memo.create') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Create Memo</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                </div>
                <!--end:Menu sub-->
            </div>
            <!--end:Menu item-->
            
            @hasrole('Super Admin|Administrator|Academic Officer')
            <!--begin:Menu item-->
            @php
                $academicRoutes = ['academics.dashboard', 'academics.exam-types'];
                $isAcademicActive = in_array(request()->route()->getName(), $academicRoutes);
            @endphp
            <div data-kt-menu-trigger="click" class="menu-item menu-accordion {{ $isAcademicActive ? 'show' : '' }}">
                <!--begin:Menu link-->
                <span class="menu-link">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-book fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </span>
                    <span class="menu-title">Academic Module</span>
                    <span class="menu-arrow"></span>
                </span>
                <!--end:Menu link-->
                <!--begin:Menu sub-->
                <div class="menu-sub menu-sub-accordion {{ $isAcademicActive ? 'show' : '' }}">
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('academics.dashboard') ? 'active' : '' }}" href="{{ route('academics.dashboard') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Manage Academics</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                    
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('academics.exam-types') ? 'active' : '' }}" href="{{ route('academics.exam-types') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Exam Types</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                
                </div>
                <!--end:Menu sub-->
            </div>
            <!--end:Menu item-->
            @endhasrole
            @canany(['view exams', 'create exams', 'edit exams', 'grade exams'])
            <!--begin:Menu item-->
            @php
                $examRoutes = [
                    'examcenter', 'examsessions', 'questionbank', 'exams.results', 
                    'exam.response.tracker', 'admin.exam-extra-time', 'questionbank.with.slug',
                    'exams.create', 'exams.edit', 'admin.exams.offline', 'admin.exams.offline-scores',
                    'admin.transcripts.generation'
                ];
                $isExamActive = in_array(request()->route()->getName(), $examRoutes);
            @endphp
            <div data-kt-menu-trigger="click" class="menu-item menu-accordion {{ $isExamActive ? 'show' : '' }}">
                <!--begin:Menu link-->
                <span class="menu-link">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-address-book fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                    </span>
                    <span class="menu-title">Exam Center</span>
                    <span class="menu-arrow"></span>
                </span>
                <!--end:Menu link-->
                <!--begin:Menu sub-->
                <div class="menu-sub menu-sub-accordion {{ $isExamActive ? 'show' : '' }}">
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('examcenter') ? 'active' : '' }}" href="{{ route('examcenter') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Exam Dashboard</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('examsessions') ? 'active' : '' }}" href="{{ route('examsessions') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Exam Sessions</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('questionbank') || request()->routeIs('questionbank.with.slug') ? 'active' : '' }}" href="{{ route('questionbank') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Question Bank</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('exams.results') ? 'active' : '' }}" href="{{ route('exams.results') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Exam Results</span>
                            <span class="badge badge-light-primary">New</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                    
                    @hasrole('System|Super Admin')
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('exam.response.tracker') ? 'active' : '' }}" href="{{ route('exam.response.tracker') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Exam Audit</span>
                            <span class="badge badge-light-primary">New</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                    @endhasrole
                    
                    @hasrole('System')
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('admin.exam-extra-time') ? 'active' : '' }}" href="{{ route('admin.exam-extra-time') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Extra Time Manager</span>
                            <span class="badge badge-light-primary">New</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                    @endhasrole
                    
                    @can('view exams')
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('admin.exams.offline') ? 'active' : '' }}" href="{{ route('admin.exams.offline') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Offline Exams</span>
                            <span class="badge badge-light-success">New</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                    
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('admin.exams.offline-scores') ? 'active' : '' }}" href="{{ route('admin.exams.offline-scores') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Offline Exam Scores</span>
                            <span class="badge badge-light-success">New</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                    
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('admin.transcripts.generation') ? 'active' : '' }}" href="{{ route('admin.transcripts.generation') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Student Transcripts</span>
                            <span class="badge badge-light-success">New</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                    @endcan
                </div>
                <!--end:Menu sub-->
            </div>
            <!--end:Menu item-->
            @endcanany
            
            @canany(['view finance', 'create invoices', 'process payments', 'generate financial reports'])
            <!--begin:Menu item-->
            @php
                $financeRoutes = [
                    'finance.billing', 'finance.fee.types', 'finance.fee.structure', 
                    'finance.payments', 'finance.exam.clearance', 'finance.exam.tickets.manager', 
                    'finance.exam.scanner', 'finance.course.registration', 'finance.reports'
                ];
                $isFinanceActive = in_array(request()->route()->getName(), $financeRoutes);
            @endphp
            <div data-kt-menu-trigger="click" class="menu-item menu-accordion {{ $isFinanceActive ? 'show' : '' }}">
                <!--begin:Menu link-->
                <span class="menu-link">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-dollar fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                    </span>
                    <span class="menu-title">Finance Module</span>
                    <span class="menu-arrow"></span>
                </span>
                <!--end:Menu link-->
                <!--begin:Menu sub-->
                <div class="menu-sub menu-sub-accordion {{ $isFinanceActive ? 'show' : '' }}">
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('finance.billing') ? 'active' : '' }}" href="{{ route('finance.billing') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Student Billing</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('finance.fee.types') ? 'active' : '' }}" href="{{ route('finance.fee.types') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Fee Types</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('finance.fee.structure') ? 'active' : '' }}" href="{{ route('finance.fee.structure') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Fee Structure</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('finance.payments') ? 'active' : '' }}" href="{{ route('finance.payments') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Fee Payments</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('finance.exam.clearance') ? 'active' : '' }}" href="{{ route('finance.exam.clearance') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Exam Clearance</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('finance.exam.tickets.manager') ? 'active' : '' }}" href="{{ route('finance.exam.tickets.manager') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Exam Tickets</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('finance.exam.scanner') ? 'active' : '' }}" href="{{ route('finance.exam.scanner') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">QR Scanner</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('finance.course.registration') ? 'active' : '' }}" href="{{ route('finance.course.registration') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Course Registration</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('finance.reports') ? 'active' : '' }}" href="{{ route('finance.reports') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Financial Reports</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                </div>
                <!--end:Menu sub-->
            </div>
            <!--end:Menu item-->
            @endcanany
            
            <!--begin:Menu item-->
            @php
                $courseRegRoutes = [
                    'courseregistration', 'courseregistration.history', 'courseregistration.approvals'
                ];
                $isCourseRegActive = in_array(request()->route()->getName(), $courseRegRoutes);
            @endphp
            <div data-kt-menu-trigger="click" class="menu-item menu-accordion {{ $isCourseRegActive ? 'show' : '' }}">
                <!--begin:Menu link-->
                <span class="menu-link">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-book-open fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </span>
                    <span class="menu-title">Course Registration</span>
                    <span class="menu-arrow"></span>
                </span>
                <!--end:Menu link-->
                <!--begin:Menu sub-->
                <div class="menu-sub menu-sub-accordion {{ $isCourseRegActive ? 'show' : '' }}">
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('courseregistration') ? 'active' : '' }}" href="{{ route('courseregistration') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Register Courses</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('courseregistration.history') ? 'active' : '' }}" href="{{ route('courseregistration.history') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Registration History</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('courseregistration.approvals') ? 'active' : '' }}" href="{{ route('courseregistration.approvals') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Registration Approvals</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                </div>
                <!--end:Menu sub-->
            </div>
            <!--end:Menu item-->
            @php
            $electionRoutes = [
                'elections', 'elections.active', 'election.results'
            ];
            $isElectionActive = in_array(request()->route()->getName(), $electionRoutes);
        @endphp
            @hasanyrole('Super Admin|Administrator')
            <!--begin:Menu item-->
           
            <div data-kt-menu-trigger="click" class="menu-item menu-accordion {{ $isElectionActive ? 'show' : '' }}">
                <!--begin:Menu link-->
                <span class="menu-link">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-chart-pie-3 fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                    </span>
                    <span class="menu-title">Election System</span>
                    <span class="menu-arrow"></span>
                </span>
                <!--end:Menu link-->
                <!--begin:Menu sub-->
                <div class="menu-sub menu-sub-accordion {{ $isElectionActive ? 'show' : '' }}">
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('elections') ? 'active' : '' }}" href="{{ route('elections') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Manage Elections</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('elections.active') ? 'active' : '' }}" href="{{ route('elections.active') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Active Elections</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                    
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('election.results') ? 'active' : '' }}" href="{{ route('election.results', ['election' => 1]) }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Election Results</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                </div>
                <!--end:Menu sub-->
            </div>
            <!--end:Menu item-->
            @endhasanyrole

            @can('election management.manage elections')
                <!--begin:Menu item-->
            <div data-kt-menu-trigger="click" class="menu-item menu-accordion {{ $isElectionActive ? 'show' : '' }}">
                <!--begin:Menu link-->
                <span class="menu-link">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-chart-pie-3 fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                    </span>
                    <span class="menu-title">Election System</span>
                    <span class="menu-arrow"></span>
                </span>
                <!--end:Menu link-->
                <!--begin:Menu sub-->
                <div class="menu-sub menu-sub-accordion {{ $isElectionActive ? 'show' : '' }}">
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('elections') ? 'active' : '' }}" href="{{ route('elections') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Manage Elections</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('elections.active') ? 'active' : '' }}" href="{{ route('elections.active') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Active Elections</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                    
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('election.results') ? 'active' : '' }}" href="{{ route('election.results', ['election' => 1]) }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Election Results</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                </div>
                <!--end:Menu sub-->
            </div>
            <!--end:Menu item-->
            @endcan
            
            <!--begin:Menu item-->
            @php
                $supportRoutes = [
                    'support.tickets', 'support.knowledgebase'
                ];
                $isSupportActive = in_array(request()->route()->getName(), $supportRoutes);
            @endphp
            <div data-kt-menu-trigger="click" class="menu-item menu-accordion {{ $isSupportActive ? 'show' : '' }}">
                <!--begin:Menu link-->
                <span class="menu-link">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-question fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </span>
                    <span class="menu-title">Support Center</span>
                    <span class="menu-arrow"></span>
                </span>
                <!--end:Menu link-->
                <!--begin:Menu sub-->
                <div class="menu-sub menu-sub-accordion {{ $isSupportActive ? 'show' : '' }}">
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('support.tickets') ? 'active' : '' }}" href="{{ route('support.tickets') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Support Tickets</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('support.knowledgebase') ? 'active' : '' }}" href="{{ route('support.knowledgebase') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Knowledge Base</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                </div>
                <!--end:Menu sub-->
            </div>
            <!--end:Menu item-->
            
            @hasanyrole('Super Admin|Administrator')
            <!--begin:Menu item-->
            @php
                $settingsRoutes = [
                    'settings.general', 'settings.users', 'settings.roles', 
                    'settings.permissions', 'settings.backup', 'settings.departments',
                    'settings.user-departments'
                ];
                $isSettingsActive = in_array(request()->route()->getName(), $settingsRoutes);
            @endphp
            <div data-kt-menu-trigger="click" class="menu-item menu-accordion {{ $isSettingsActive ? 'show' : '' }}">
                <!--begin:Menu link-->
                <span class="menu-link">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-setting-2 fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </span>
                    <span class="menu-title">System Settings</span>
                    <span class="menu-arrow"></span>
                </span>
                <!--end:Menu link-->
                <!--begin:Menu sub-->
                <div class="menu-sub menu-sub-accordion {{ $isSettingsActive ? 'show' : '' }}">
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('settings.general') ? 'active' : '' }}" href="{{ route('settings.general') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">General Settings</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('settings.users') ? 'active' : '' }}" href="{{ route('settings.users') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">User Management</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('settings.roles') ? 'active' : '' }}" href="{{ route('settings.roles') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Roles & Permissions</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('settings.permissions') ? 'active' : '' }}" href="{{ route('settings.permissions') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Permissions Management</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('settings.backup') ? 'active' : '' }}" href="{{ route('settings.backup') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Backup & Restore</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                    
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('settings.departments') ? 'active' : '' }}" href="{{ route('settings.departments') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Department Management</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                    
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('settings.user-departments') ? 'active' : '' }}" href="{{ route('settings.user-departments') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">User Departments</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                </div>
                <!--end:Menu sub-->
            </div>
            <!--end:Menu item-->
            @endhasanyrole
            @endhasrole
          
            <!--begin:Menu item-->
            <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                <!--begin:Menu link-->
                @hasrole('Student')
                <!-- Students don't need staff mail access -->
                @else
                <a href="{{ url('https://pnmtc.edu.gh/webmail') }}" class="menu-link" target="_blank">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-sms fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </span>
                    <span class="menu-title">Staff Mail</span>
                </a>
                @endhasrole
                <!--end:Menu link-->
                
            </div>
            <!--end:Menu item-->
            
        </div>
        <!--end::Menu-->
    </div>
    <!--end::Aside Menu-->
</div>
<!--end::Aside menu-->
<!--begin::Footer-->
<div class="px-9 aside-footer flex-column-auto" id="kt_aside_menu">
    <!--begin::User panel-->
    <div class="d-flex flex-stack">
        <!--begin::Wrapper-->
        <div class="d-flex align-items-center">
            <!--begin::Avatar-->
            <div class="symbol symbol-circle symbol-40px">
                <img src="{{ Auth::user()->profile_photo_url }}" alt="photo" />
            </div>
            <!--end::Avatar-->
            <!--begin::User info-->
            <div class="ms-2">
                <!--begin::Name-->
                <a href="#" class="text-gray-800 text-hover-primary fs-6 fw-bold lh-1">{{Auth::user()->name}}</a>
                <!--end::Name-->
                <!--begin::Roles-->
                <span class="text-muted fw-semibold d-block fs-7 lh-1">
                    @if(Auth::user()->roles->count() > 0)
                        {{ Auth::user()->roles->pluck('name')->first() }}
                    @else
                        {{ Auth::user()->role }}
                    @endif
                </span>
                <!--end::Roles-->
            </div>
            <!--end::User info-->
        </div>
        <!--end::Wrapper-->
        <!--begin::User menu-->
        <div class="ms-1">
            <div class="btn btn-sm btn-icon btn-active-color-primary position-relative me-n2"
                data-kt-menu-trigger="click" data-kt-menu-overflow="true"
                data-kt-menu-placement="top-end">
                <i class="ki-duotone ki-setting-2 fs-1">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
            </div>
            <!--begin::User account menu-->
            <div class="py-4 menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold fs-6 w-275px"
                data-kt-menu="true">
                <!--begin::Menu item-->
                <div class="px-3 menu-item">
                    <div class="px-3 menu-content d-flex align-items-center">
                        <!--begin::Avatar-->
                        <div class="symbol symbol-50px me-5">
                            <img alt="Avatar" src="{{ Auth::user()->profile_photo_url }}" />
                        </div>
                        <!--end::Avatar-->
                        <!--begin::Username-->
                        <div class="d-flex flex-column">
                            <div class="fw-bold d-flex align-items-center fs-5">{{ Auth::user()->name }}
                                <span class="px-2 py-1 badge badge-light-success fw-bold fs-8 ms-2">
                                    @if(Auth::user()->roles->count() > 0)
                                        {{ Auth::user()->roles->pluck('name')->first() }}
                                    @else
                                        {{ Auth::user()->role }}
                                    @endif
                                </span>
                            </div>
                            <a href="#"
                                class="fw-semibold text-muted text-hover-primary fs-7">{{ Auth::user()->email }}</a>
                        </div>
                        <!--end::Username-->
                    </div>
                </div>
                <!--end::Menu item-->
                <!--begin::Menu separator-->
                <div class="my-2 separator"></div>
                <!--end::Menu separator-->
                <!--begin::Menu item-->
                <div class="px-5 menu-item">
                    {{-- Logout Form --}}
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <buttom type="submit" onclick="event.preventDefault(); this.closest('form').submit();"
                            class="px-5 menu-link">Sign Out</buttom>
                    </form>
                </div>
                <!--end::Menu item-->
            </div>
            <!--end::User account menu-->
        </div>
        <!--end::User menu-->
    </div>
    <!--end::User panel-->
</div>
<!--end::Footer-->
</div>
<!--end::Aside-->