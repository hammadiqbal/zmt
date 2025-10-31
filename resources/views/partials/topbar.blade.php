@php
// $user = session('user');
// $roles = session('role');
// $rights = session('rights');
// dd($user);
@endphp
        <!-- ============================================================== -->
        <!-- Topbar header - style you can find in pages.scss -->
        <!-- ============================================================== -->
        <header class="topbar">
            <nav class="navbar top-navbar navbar-expand-md navbar-light">
                <!-- ============================================================== -->
                <!-- Logo -->
                <!-- ============================================================== -->
                <div class="navbar-header">
                    <a class="navbar-brand" href="{{ route('dashboard') }}">
                        @if($user->org_id == 0)
                        <h1 class="text-white font-weight-bold display-4">{{ config('app.name') }}</h1>
                        @else
                        <img src="{{ asset('assets/org/'.$user->org_id.'_'.$user->logo) }}" width="120" alt="homepage" class="light-logo img-fluid" /> 
                        @endif
                    </a>
                </div>

                <!-- ============================================================== -->
                <!-- End Logo -->
                <!-- ============================================================== -->
                <div class="navbar-collapse">
                    <!-- ============================================================== -->
                    <!-- toggle and nav items -->
                    <!-- ============================================================== -->
                    <ul class="navbar-nav mr-auto mt-md-0">
                        {{-- <button >Go Back</button> --}}
                        @if($user->org_id == 0)
                            <i class="mdi mdi-home" onclick="window.location.href='{{ route('dashboard') }}'"  title="Click here to view dashboard" style="cursor:pointer;color: white;font-size: 30px;margin-left: 16px;"></i>
                        @else
                            <i class="mdi mdi-home" onclick="window.location.href='{{ route('home') }}'" title="Click here to view home" style="cursor:pointer;color: white;font-size: 30px;margin-left: 16px;"></i>
                        @endif
                     </ul>
                      <ul class="navbar-nav mr-auto mt-md-0">
                        <h2 class="text-white mb-0">{{ ucwords($user->orgName) }}</h2>
                     </ul> 
                    <!-- ============================================================== -->
                    <!-- User profile and search -->
                    <!-- ============================================================== -->
                    <ul class="navbar-nav my-lg-0">
                        <!-- ============================================================== -->
                        <!-- Profile -->
                        <!-- ============================================================== -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                @if(empty($user->image))
                                    <img src="{{ asset('assets/lib/images/users/avatar.png') }}" alt="user" class="profile-pic" />
                                @else
                                    <img src="{{ asset('assets/users/' . $user->id . '_' . $user->image) }}" alt="user" class="profile-pic" >
                                @endif
                            </a>
                            <div class="dropdown-menu dropdown-menu-right scale-up">
                                <ul class="dropdown-user">
                                    <li>
                                        <div class="dw-user-box">
                                            <div class="u-img">
                                                @if(empty($user->image))
                                                    <img src="{{ asset('assets/lib/images/users/avatar.png') }}" width="80">
                                                @else
                                                    <img src="{{ asset('assets/users/' . $user->id . '_' . $user->image) }}" alt="user">
                                                @endif
                                            </div>
                                            <div class="u-text">
                                                <h4>{{ ucwords($user->name) }}</h4>
                                                <h4><p class="text-muted"> {{ ucwords($roles->role) }}</p></h4>
                                                <p class="text-muted">{{ $user->email }}</p>
                                            </div>
                                        </div>
                                    </li>
                                    <li role="separator" class="divider"></li>
                                    <li><a href="{{ route('profile') }}"><i class="ti-user"></i> My Profile</a></li>
                                    <li role="separator" class="divider"></li>
                                    <li><a href="{{ route('logout') }}"><i class="fa fa-power-off"></i> Logout</a></li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>
        </header>
        <!-- ============================================================== -->
        <!-- End Topbar header -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
   
        