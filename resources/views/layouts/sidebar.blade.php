<aside class="main-sidebar sidebar-dark-primary elevation-4 dashbroad__sidebar__bg">
    <a href="{{ route('home') }}" class="brand-link">
        <img src="/uploads/hotelio.png"
             alt="Hotelio Logo"
             class="brand-image img-circle elevation-3">
        <span class="brand-text font-weight-light">Hotelio</span>
    </a>

    <div class="sidebar custom-sidebar">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                @include('layouts.menu')
            </ul>
        </nav>
    </div>

</aside>
