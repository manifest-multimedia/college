<x-frontend.exams title="Online Examination">
    @switch(app()->environment())
        @case('staging')
            {{-- V1 Production Version (Test Environment) --}}
            <livewire:online-examination :exam-password="$examPassword" :student_id="$student_id" />
            @break
        @default
            {{-- V2 Optimized Version (Default) --}}
            <livewire:online-examination-v2 :exam-password="$examPassword" :student_id="$student_id" />
    @endswitch
</x-frontend.exams>