<x-dashboard.default title="User Dashboard">

    <!--begin::Row-->
    <div class="row">

        <x-partials.dashboard-notifications />


        <!-- Dashboard Counters -->
        <div class="row g-5 g-xl-8 mt-5">
            <x-dashboard.counters />
        </div>
        <!-- End Dashboard Counters -->

        <!-- Dashboard Content Section -->
        <div class="row g-5 gx-xl-10 mt-5">

            <!-- Left Column - Icon Cards -->
            <div class="col-xl-8">
                <div class="row g-5">
                    
                    <x-dashboard.icon-card 
                        title="Exam Center"
                        description="Take Exams, Assignments and Quizes"
                        icon="ki-notepad-edit"
                        :route="route('examcenter')" 
                    />

                    <x-dashboard.icon-card 
                        title="Question Bank"
                        description="Browse and Review Question Sets"
                        icon="ki-questionnaire-tablet"
                        :route="route('questionbank')" 
                    />

                    <x-dashboard.icon-card 
                        title="Staff Mail"
                        description="Access Corporate Email Services"
                        icon="ki-message-text-2"
                        :route="route('staffmail')" 
                    />

                    <x-dashboard.icon-card 
                        title="Support Center"
                        description="Access Technical Support"
                        icon="ki-rescue"
                        :route="route('supportcenter')" 
                    />

                </div>
            </div>
            <!-- End Left Column -->

            <!-- Right Column - Support Widget -->
            <div class="col-xl-4">
                @if (Route::currentRouteName() == 'dashboard')
                    <x-app.support-widget />
                @endif
            </div>
            <!-- End Right Column -->

        </div>
        <!-- End Dashboard Content Section -->
    </div>




</x-dashboard.default>
