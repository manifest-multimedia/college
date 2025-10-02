<div class="container-fluid">
    <h2 class="h4 mb-4">Manage Years</h2>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form wire:submit.prevent="{{ $isEdit ? 'update' : 'save' }}" class="mb-4">
        <div class="mb-3">
            <label for="name" class="form-label">Year Name</label>
            <input type="text" id="name" wire:model.defer="name" class="form-control" placeholder="e.g. Year 1" />
            @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>
        <div class="btn-group">
            <button type="submit" class="btn btn-primary">
                {{ $isEdit ? 'Update' : 'Add' }} Year
            </button>
            @if($isEdit)
                <button type="button" wire:click="resetForm" class="btn btn-secondary">Cancel</button>
            @endif
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Name</th>
                    <th scope="col">Slug</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($years as $year)
                    <tr>
                        <td>{{ $year->id }}</td>
                        <td>{{ $year->name }}</td>
                        <td>{{ $year->slug }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button wire:click="edit({{ $year->id }})" class="btn btn-warning">Edit</button>
                                <button wire:click="delete({{ $year->id }})" class="btn btn-danger" onclick="return confirm('Delete this year?')">Delete</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">No years found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $years->links() }}
    </div>
</div>
