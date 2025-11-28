<x-frontend.exams title="Online Examination">
    @if(app()->environment('local'))
        {{-- V2 Optimized Version (Local Environment) --}}
        <livewire:online-examination-v2 :exam-password="$examPassword" :student_id="$student_id" />
    @else
        {{-- V1 Production Version (Production Environment) --}}
        <livewire:online-examination :exam-password="$examPassword" :student_id="$student_id" />
    @endif
</x-frontend.exams>