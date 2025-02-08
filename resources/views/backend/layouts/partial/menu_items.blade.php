@php
    $user = Auth::user();
    $currentRole = Request::route()->parameter('role');
    $currentRoute = Request::route()->getName(); // Adjust this based on how you store the user's role
@endphp
{{-- <div class="container">
    <div class="row">
        <div class="col-12">
            <div class="py-3">
                <a class="navbar-brand" href="{{ route('admin.dashboard') }}">
                    <img class="img-fluid logo-img" src="{{ asset('backend/assets/images/Logo.png') }}" />
                </a>
            </div>
        </div>
    </div>
</div>
<div class="bg-dark">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
                    <div class="container">
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                            data-bs-target="#navbarNavDarkDropdown" aria-controls="navbarNavDarkDropdown"
                            aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarNavDarkDropdown">
                            <ul class="navbar-nav">
                                @foreach ($menu as $key => $menu_item)
                                    <?php
                                    $link_active = '';
                                    $menuRoute = $menu_item['route'] ?? null;
                                    $menuRole = $menu_item['role'] ?? null;
                                    $menuDropdown = $menu_item['dropdown'] ?? false;
                                    $menuDropdownItems = $menu_item['dropdown_items'] ?? [];

                                    if ($currentRoute == $menuRoute) {
                                        $link_active = 'active';
                                    }
                                    $params = isset($menuRole) ? ['role' => $menuRole] : [];
                                    ?>
                                    @if (!$menuDropdown && (!$menuRole || $currentRole == $menuRole))
                                        <li class="nav-item">
                                            <a class="nav-link {{ $link_active }}" aria-current="page"
                                                href="{{ route($menuRoute, $params) }}">{{ $menu_item['name'] }}</a>
                                        </li>
                                    @elseif ($menuDropdown && (!$menuRole || $currentRole == $menuRole))
                                        @php
                                            $expanded = '';

                                            foreach ($menuDropdownItems as $submenu) {
                                                if (
                                                    (isset($menuRole) && $currentRole == $menuRole) ||
                                                    (!isset($menuRole) && in_array($currentRoute, $submenu))
                                                ) {
                                                    $expanded = 'is-expanded';
                                                    break;
                                                }
                                            }
                                        @endphp
                                        <li class="nav-item dropdown">
                                            <button class="nav-link dropdown-toggle" data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                                {{ $menu_item['name'] }}
                                            </button>
                                            <ul class="dropdown-menu">
                                                @foreach ($menuDropdownItems as $sub_menu)
                                                    @php
                                                        $subMenuRoute = $sub_menu['route'];
                                                    @endphp
                                                    <li>
                                                        <a class="dropdown-item"
                                                            href="{{ route($subMenuRoute, $params) }}">{{ $sub_menu['name'] }}</a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </li>
                                    @endif
                                @endforeach
                                <li class="nav-item">
                                    <a class="nav-link
              {{ Request::route()->getName() == 'admin.settings.edit_profile' ? 'active' : '' }}
              "
                                        aria-current="page"
                                        href="{{ route('admin.settings.edit_profile') }}">{{ __('Edit Profile') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" aria-current="page" href="{{ route('logout') }}"
                                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}</a>
                                </li>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </ul>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </div>
</div> --}}

<div class="d-flex">
    <div class="bg-dark text-white vh-100" style="width: 250px;">
        <div class="py-4 px-3 text-center">
            <a class="navbar-brand text-white" href="{{ route('admin.dashboard') }}">
                <img class="img-fluid logo-img mb-3 bg-white" src="{{ asset('backend/assets/images/Logo.png') }}" />
            </a>
        </div>
        <ul class="nav flex-column">
            @foreach ($menu as $key => $menu_item)
                @php
                    $link_active = $currentRoute == ($menu_item['route'] ?? '') ? 'active' : '';
                    $menuDropdown = $menu_item['dropdown'] ?? false;
                    $menuDropdownItems = $menu_item['dropdown_items'] ?? [];
                @endphp
                @if (!$menuDropdown)
                    <li class="nav-item">
                        <a class="nav-link text-white {{ $link_active }}"
                            href="{{ route($menu_item['route'], $menu_item['role'] ?? []) }}">
                            {{ $menu_item['name'] }}
                        </a>
                    </li>
                @else
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" id="dropdown{{ $key }}"
                            role="button" data-bs-toggle="dropdown">
                            {{ $menu_item['name'] }}
                        </a>
                        <ul class="dropdown-menu">
                            @foreach ($menuDropdownItems as $sub_menu)
                                <li>
                                    <a class="dropdown-item"
                                        href="{{ route($sub_menu['route'], $sub_menu['role'] ?? []) }}">
                                        {{ $sub_menu['name'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endif
            @endforeach
            <li class="nav-item">
                <a class="nav-link text-white" href="{{ route('admin.settings.edit_profile') }}">
                    {{ __('Edit Profile') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="{{ route('logout') }}"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    {{ __('Logout') }}
                </a>
            </li>
        </ul>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>
    </div>

    <!-- Main Content -->
    <div class="flex-grow-1 main-content mt-3">
        @yield('content')
    </div>
</div>
