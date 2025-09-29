<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Question Sets Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <div class="mt-8 text-2xl">
                                Question Sets
                            </div>
                            <div class="mt-4 text-gray-500">
                                Manage question sets, create organized question collections, and configure exam assignments.
                            </div>
                        </div>
                        <div class="flex space-x-3">
                            <a href="{{ route('question.sets.create') }}" class="btn btn-sm btn-primary">
                                <i class="ki-duotone ki-plus fs-2"></i>
                                Create Question Set
                            </a>
                            <a href="{{ route('question.import.export') }}" class="btn btn-sm btn-light">
                                <i class="ki-duotone ki-arrow-up-down fs-2"></i>
                                Import/Export
                            </a>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-200 bg-opacity-25 p-6">
                    @livewire('question-bank')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>