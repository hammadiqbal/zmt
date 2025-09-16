
        @include('partials/header')

        @include('partials/topbar')

        @include('partials/sidebar')

        <div class="page-wrapper">

            <div class="row page-titles">
                <div class="col-md-5 align-self-center">
                    <h3 class="text-themecolor">Profile</h3>
                </div>
                <div class="col-md-12 d-flex justify-content-center">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                        <li class="breadcrumb-item active">Profile</li>
                    </ol>
                </div>
            </div>

            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-4 col-xlg-3 col-md-5">
                        <div class="card">
                            <div class="card-body">
                                <center class="m-t-30">
                                    @if(empty($user->image))
                                        <img src="{{ asset('assets/lib/images/users/avatar.png') }}" width="80">
                                    @else
                                        <img src="{{ asset('assets/users/' . $user->id . '_' . $user->image) }}" width="80">
                                    @endif
                                    <h4 class="card-title m-t-10">{{ ucfirst($user->name) }}</h4>
                                    <h6 class="card-subtitle">{{ ucfirst($roleData->role) }}</h6>
                                    <h6 >
                                        @if($user->status == 1)
                                        <label class="label label-success">Active</label>
                                        @else
                                            <label class="label label-danger">InActive</label>
                                        @endif
                                    </h6>
                                </center>
                            </div>
                        <div>
                            <hr>
                            <div class="card-body">
                                <small class="text-muted">Effective From</small>
                                <h6>{{$effective_timestamp}}</h6>
                                <small class="text-muted p-t-30 db">Added On</small>
                                <h6>{{$timestamp}}</h6>
                                <small class="text-muted p-t-30 db">Last Updated</small>
                                <h6>{{$last_updated}}</h6>
                            </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8 col-xlg-9 col-md-7">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 col-xs-12 b-r"> <strong>Full Name</strong>
                                <br>
                                <p class="text-muted">{{ ucfirst($user->name) }}</p>
                            </div>
                            <div class="col-md-6 col-xs-12 b-r"> <strong>Email</strong>
                                <br>
                                <p class="text-muted">{{ ucfirst($user->email) }}</p>
                            </div>
                            @if(!empty($orgData))
                            <div class="col-md-6 col-xs-12 b-r"> <strong>Organization Name</strong>
                                <br>
                                <p class="text-muted">{{ ucfirst($orgData->organization) }}</p>
                            </div>
                            <div class="col-md-6 col-xs-12 b-r"> <strong>Organization Email</strong>
                                <br>
                                <p class="text-muted">{{ $orgData->email }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">

                        <!-- Nav tabs -->
                        <ul class="nav nav-tabs profile-tab" role="tablist">
                            <li class="nav-item"> <a class="nav-link active" data-toggle="tab" href="#pwd" role="tab">Update Password</a> </li>
                            <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#picture" role="tab">Update Profile Picture</a> </li>
                        </ul>
                        <!-- Tab panes -->
                        <div class="tab-content">
                            <div class="tab-pane active" id="pwd" role="tabpanel">
                                <div class="card-body">
                                    <form id="u_profile" >
                                        @csrf
                                        <div class="form-group mb-1">
                                            <label class="col-md-12">New Password</label>
                                            <div class="col-md-12 mb-1">
                                                <input type="password" name="u_pwd" class="form-control showpwd form-control-line">
                                                <span class="password-toggle1">
                                                    <i class="fa fa-eye-slash"></i>
                                                </span>
                                            </div>

                                            <span class="text-danger" style="padding-left:15px;" id="u_pwd_error"></span>
                                            <br>
                                            <label class="col-md-12">Confirm Password</label>
                                            <div class="col-md-12 mb-1">
                                                <input type="password" name="u_c_pwd" class="form-control showpwd form-control-line">
                                                <span class="password-toggle1">
                                                    <i class="fa fa-eye-slash"></i>
                                                </span>
                                            </div>
                                            <span class="text-danger" style="padding-left:15px;" id="u_c_pwd_error"></span>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-12">
                                                <button type="submit" class="btn btn-primary">Update Password</button>
                                            </div>
                                        </div>
                                    </form>

                                </div>
                            </div>
                            <div class="tab-pane" id="picture" role="tabpanel">
                                <div class="card-body">
                                    <form id="u_img">
                                        @csrf
                                        <div class="col-lg-12 col-md-12">
                                            <div class="card">
                                                <div class="card-body">
                                                    <input type="hidden" id="user-id" value="{{ $user->id }}">
                                                    <label class="control-label">Update Profile Picture</h4></label>
                                                    @if(empty($user->image))
                                                        <input type="file" name="userImg" id="userImg" data-default-file="{{ asset('assets/lib/images/users/avatar.png') }}" class="dropify"/>
                                                    @else
                                                        <input type="file" name="userImg" id="userImg" data-default-file="{{ asset('assets/users/' . $user->id . '_' . $user->image) }}" class="dropify"/>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-sm-12">
                                                    <button type="submit" class="btn btn-primary">Update</button>
                                                </div>
                                            </div>
                                        </div>

                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>



                <!-- ============================================================== -->
    <!-- Start Footer  -->
    <!-- ============================================================== -->
    @include('partials/footer')
    <!-- ============================================================== -->
    <!-- End Footer  -->
    <!-- ============================================================== -->
    <script>
    $(document).ready(function() {
            $('.dropify').dropify();
        });
    </script>
