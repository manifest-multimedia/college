<x-dashboard.default>
    <x-slot name="title">
        Student Transcript Generation
    </x-slot>
    
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Student Transcript Generation') }}
        </h2>
    </x-slot>
    
    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <livewire:admin.transcript-generation />
        </div>
    </div>
</x-dashboard.default>
