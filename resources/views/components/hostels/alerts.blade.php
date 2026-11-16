@if(session('message'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
        <i class="fas fa-circle-check me-2"></i><div>{{ session('message') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger" role="alert">
        <div class="fw-bold mb-1"><i class="fas fa-triangle-exclamation me-2"></i>Please fix the following</div>
        <ul class="mb-0 ps-4">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif
