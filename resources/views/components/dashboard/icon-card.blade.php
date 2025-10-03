@props(['title', 'description', 'icon', 'route'])

<div class="col-12 col-sm-6">
    <a class="dashboard-icon-card card h-100 text-gray-800 text-hover-primary border-hover-primary" 
       href="{{ $route }}">
        <div class="card-body d-flex flex-column align-items-center justify-content-center text-center p-10">
            <div class="icon-wrapper mb-6">
                <i class="ki-duotone {{ $icon }} fs-3tx text-primary">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                    <span class="path4"></span>
                </i>
            </div>
            <span class="fs-3 fw-bold mb-2">{{ $title }}</span>
            <span class="text-gray-600 fs-6 fw-normal">{{ $description }}</span>
        </div>
    </a>
</div>

<style>
    .dashboard-icon-card {
        transition: all 0.3s ease;
        min-height: 200px;
        border: 1px solid #e4e6ef;
        background: #ffffff;
    }
    
    .dashboard-icon-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        border-color: var(--bs-primary);
        text-decoration: none;
    }
    
    .dashboard-icon-card .icon-wrapper {
        width: 70px;
        height: 70px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: transparent;
        border-radius: 12px;
        transition: all 0.3s ease;
    }
    
    .dashboard-icon-card:hover .icon-wrapper {
        transform: scale(1.1);
    }
    
    .dashboard-icon-card .text-primary {
        color: #009ef7 !important;
        transition: all 0.3s ease;
    }
    
    .dashboard-icon-card:hover .text-primary {
        color: #006bb3 !important;
    }
    
    .dashboard-icon-card .fs-3 {
        transition: color 0.3s ease;
    }
    
    .dashboard-icon-card:hover .fs-3 {
        color: var(--bs-primary);
    }
</style>
