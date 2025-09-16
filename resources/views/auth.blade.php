    <!-- ============================================================== -->
    <!-- Start Header  -->
    <!-- ============================================================== -->
    @include('partials/header')
    <!-- ============================================================== -->
    <!-- End Header  -->
    <!-- ============================================================== -->


    <!-- ============================================================== -->
    <!-- Main wrapper - style you can find in pages.scss -->
    <!-- ============================================================== -->

    <section id="wrapper">
        <div class="login-register" style="background-image:url(/assets/lib/images/bk.jpg) !important;">
        <div class="col-lg-5 col-md-6 col-s-8 mx-auto">
            <div class="alert alert-info">
                    <button type="button" class="close" data-dismiss="alert"> </button>

                    <h3 class="h3 mb-0 text-center text-md-start">{{ $CurrentTimeStamp }} </h2>
                </div>
            </div>
            <div class="login-box card">
                <div class="card-body">
                    <form class="form-horizontal form-material" method="post" id="loginform">
                        @csrf
                        <h3 class="box-title m-b-20">Sign In</h3>
                        <div class="form-group ">
                            <div class="col-xs-12">
                                <input class="form-control" name="userlgn" type="text" placeholder="Email">
                            </div>
                            <span class="text-danger" id="userlgn_error"></span>
                        </div>
                        <div class="form-group">
                            <div class="col-xs-12">
                                <input class="form-control showpwd"  type="password" name="userpwd" placeholder="Password">
                                <span class="password-toggle">
                                    <i class="fa fa-eye-slash"></i>
                                </span>
                            </div>
                            <span class="text-danger" id="userpwd_error"></span>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-12 font-14">
                                <div class="checkbox checkbox-primary pull-left p-t-0">
                                    <input id="checkbox-signup" type="checkbox" name="remember">
                                    <label for="checkbox-signup"> Remember me </label>
                                </div> <a href="javascript:void(0)" id="to-recover" class="text-dark pull-right"><i class="fa fa-lock m-r-5"></i>  Forgot pwd?</a> </div>
                        </div>
                        <div class="form-group text-center m-t-20">
                            <div class="col-xs-12">
                                <button class="btn btn-info btn-lg btn-block text-uppercase waves-effect waves-light" type="submit">Log In</button>
                            </div>
                        </div>

                    </form>

                    <form class="form-horizontal" id="recoverform">
                        @csrf
                        <div class="form-group ">
                            <div class="col-xs-12">
                                <h3>Recover Password</h3>
                                <p class="text-muted">Enter your Email and instructions will be sent to you! </p>
                            </div>
                        </div>
                        <div class="form-group ">
                            <div class="col-xs-12">
                                <input class="form-control" type="email" name="email" placeholder="Email">
                            </div>
                            <span class="text-danger" id="email_error"></span>
                        </div>
                        <div class="form-group text-center m-t-20">
                            <div class="col-xs-12">
                                <button class="btn btn-primary btn-lg btn-block text-uppercase waves-effect waves-light" type="submit">Reset Password</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <style>.footer{display:none}</style>

    <!-- ============================================================== -->
    <!-- Start Footer  -->
    <!-- ============================================================== -->
    @include('partials/footer')
    <!-- ============================================================== -->
    <!-- End Footer  -->
    <!-- ============================================================== -->
