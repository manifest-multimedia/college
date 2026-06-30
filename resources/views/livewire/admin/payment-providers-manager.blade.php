<div class="p-6 lg:p-8 bg-white border-b border-gray-200">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Payment Providers & API Keys') }}
        </h2>
    </x-slot>

    {{-- Flash Messages --}}
    @if(session()->has('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif
    
    @if(session()->has('error'))
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Payment Providers</h1>
            <p class="text-sm text-gray-500">Manage payment gateways and generate API secret keys</p>
        </div>
        <button wire:click="toggleForm" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            {{ $showForm ? 'Cancel' : 'Add New Provider' }}
        </button>
    </div>

    {{-- Add Provider Form --}}
    @if($showForm)
        <div class="bg-gray-50 p-6 rounded-lg border border-gray-200 mb-6">
            <h3 class="text-lg font-semibold mb-4">Generate New API Credentials</h3>
            <form wire:submit="generateCredentials">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Provider Name</label>
                        <input type="text" wire:model="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="e.g. Paystack">
                        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Provider Code</label>
                        <input type="text" wire:model="code" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="e.g. paystack_v1">
                        @error('code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="mt-4 flex justify-end">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="generateCredentials">Generate Keys</span>
                        <span wire:loading wire:target="generateCredentials">Generating...</span>
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- Providers Table --}}
    <div class="overflow-x-auto bg-white rounded-lg shadow overflow-y-auto relative">
        <table class="border-collapse table-auto w-full whitespace-no-wrap bg-white table-striped relative">
            <thead>
                <tr class="text-left">
                    <th class="bg-gray-100 sticky top-0 border-b border-gray-200 px-6 py-3 text-gray-600 font-bold tracking-wider uppercase text-xs">Name</th>
                    <th class="bg-gray-100 sticky top-0 border-b border-gray-200 px-6 py-3 text-gray-600 font-bold tracking-wider uppercase text-xs">Code</th>
                    <th class="bg-gray-100 sticky top-0 border-b border-gray-200 px-6 py-3 text-gray-600 font-bold tracking-wider uppercase text-xs">Status</th>
                    <th class="bg-gray-100 sticky top-0 border-b border-gray-200 px-6 py-3 text-gray-600 font-bold tracking-wider uppercase text-xs">Created By</th>
                    <th class="bg-gray-100 sticky top-0 border-b border-gray-200 px-6 py-3 text-gray-600 font-bold tracking-wider uppercase text-xs">Created At</th>
                    <th class="bg-gray-100 sticky top-0 border-b border-gray-200 px-6 py-3 text-gray-600 font-bold tracking-wider uppercase text-xs">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($providers as $provider)
                    <tr>
                        <td class="border-t border-gray-200 px-6 py-4">{{ $provider->name }}</td>
                        <td class="border-t border-gray-200 px-6 py-4"><span class="bg-gray-100 text-gray-800 text-xs font-semibold px-2 py-1 rounded border">{{ $provider->code }}</span></td>
                        <td class="border-t border-gray-200 px-6 py-4">
                            @if($provider->status === 'active')
                                <span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded">Active</span>
                            @else
                                <span class="bg-red-100 text-red-800 text-xs font-semibold px-2 py-1 rounded">Inactive</span>
                            @endif
                        </td>
                        <td class="border-t border-gray-200 px-6 py-4">{{ $provider->creator->name ?? 'System' }}</td>
                        <td class="border-t border-gray-200 px-6 py-4">{{ $provider->created_at->format('M d, Y H:i') }}</td>
                        <td class="border-t border-gray-200 px-6 py-4">
                            <button wire:click="toggleStatus({{ $provider->id }})" class="text-sm {{ $provider->status === 'active' ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900' }}">
                                {{ $provider->status === 'active' ? 'Deactivate' : 'Activate' }}
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="border-t border-gray-200 px-6 py-4 text-center text-gray-500">No payment providers configured.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal for Newly Generated Key --}}
    @if($showGeneratedKey && $newlyGeneratedKey)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg shadow-xl max-w-lg w-full">
                <div class="flex items-center mb-4 text-yellow-600">
                    <svg class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <h3 class="text-lg font-bold">New API Secret Key Generated</h3>
                </div>
                
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                    <p class="text-sm text-yellow-700">
                        <strong>Important:</strong> Copy this secret key now! You will not be able to see it again. This key should be used by the external payment gateway or webhook to authenticate requests.
                    </p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Your API Secret Key:</label>
                    <div class="flex">
                        <input type="text" readonly value="{{ $newlyGeneratedKey }}" id="newApiKey" class="font-mono bg-gray-100 block w-full rounded-l-md border-gray-300 text-sm py-2 px-3 focus:outline-none">
                        <button onclick="copyNewApiKey()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-r-md text-sm font-bold">
                            Copy
                        </button>
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <button wire:click="closeGeneratedKeyModal" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded">
                        I've Copied the Key
                    </button>
                </div>
            </div>
        </div>

        <script>
            function copyNewApiKey() {
                const input = document.getElementById('newApiKey');
                input.select();
                navigator.clipboard.writeText(input.value).then(() => {
                    alert('API secret key copied to clipboard!');
                });
            }
        </script>
    @endif
</div>
