<x-dashboard.default>
    <x-slot name="title">
        Exam Entry Tickets Management
    </x-slot>
    
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Exam Entry Tickets Management') }}
        </h2>
    </x-slot>
    
    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <livewire:admin.exam-tickets />
        </div>
    </div>
</x-dashboard.default>