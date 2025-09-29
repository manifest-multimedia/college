<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Question Set Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
                    <nav class="flex mb-4" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-3">
                            <li class="inline-flex items-center">
                                <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                                    Dashboard
                                </a>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                                    <a href="{{ route('question.sets') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2">Question Sets</a>
                                </div>
                            </li>
                            <li aria-current="page">
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                                    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Question Set Details</span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                    
                    <div class="flex justify-between items-center mt-8">
                        <div>
                            <div class="text-2xl">
                                Question Set Details
                            </div>
                            <div class="mt-4 text-gray-500">
                                View and manage question set information and questions.
                            </div>
                        </div>
                        <div class="flex space-x-3">
                            <a href="{{ route('question.sets.edit', $id) }}" class="btn btn-sm btn-primary">
                                <i class="ki-duotone ki-pencil fs-2"></i>
                                Edit Set
                            </a>
                            <a href="{{ route('question.sets.questions', $id) }}" class="btn btn-sm btn-light">
                                <i class="ki-duotone ki-questionnaire-tablet fs-2"></i>
                                Manage Questions
                            </a>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-200 bg-opacity-25 p-6">
                    @livewire('question-bank', ['mode' => 'show_set', 'questionSetId' => $id])
                </div>
            </div>
        </div>
    </div>
</x-app-layout>