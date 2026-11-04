@canany(['manage-canteen-meals', 'manage-canteen-qualifications'])
    @php($isCanteenActive = request()->routeIs('canteen.*'))
    <div data-kt-menu-trigger="click" class="menu-item menu-accordion {{ $isCanteenActive ? 'show' : '' }}">
        <span class="menu-link">
            <span class="menu-icon"><i class="ki-duotone ki-shop fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i></span>
            <span class="menu-title">Canteen Module</span>
            <span class="menu-arrow"></span>
        </span>
        <div class="menu-sub menu-sub-accordion {{ $isCanteenActive ? 'show' : '' }}">
            @can('manage-canteen-meals')
                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('canteen.meals.settings') ? 'active' : '' }}" href="{{ route('canteen.meals.settings') }}">
                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                        <span class="menu-title">Meal Windows</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('canteen.meals.activity') ? 'active' : '' }}" href="{{ route('canteen.meals.activity') }}">
                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                        <span class="menu-title">Meal Activity</span>
                    </a>
                </div>
            @endcan
            @can('manage-canteen-qualifications')
                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('canteen.lists.*') ? 'active' : '' }}" href="{{ route('canteen.lists.index') }}">
                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                        <span class="menu-title">Qualification Lists</span>
                    </a>
                </div>
            @endcan
        </div>
    </div>
@endcanany
