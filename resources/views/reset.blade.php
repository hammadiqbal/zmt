@include('partials/header')

    <section id="wrapper">
        <div class="login-register" style="background-image:url(/assets/lib/images/bk.jpg);">
            <div class="login-box card" style="width: 500px;">
                <div class="card-body">
                  <form id="u_profile" class="form-horizontal form-material">
                    @csrf
                    <div class="form-group">
                        <div class="col-xs-12 text-center">
                          <div class="user-thumb text-center">
                            <h3><b>{{ucwords($user->name)}}</b></h3>
                          </div>
                        </div>
                      </div>
                    <div class="form-group mb-1">
                        <label class="col-md-12">New Password</label>
                        <input type="hidden" name="userId" value="{{$user->id}}">
                        <input type="hidden" name="userEmail" value="{{$user->email}}">
                        <input type="hidden" name="userName" value="{{$user->name}}">

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
        </div>

    </section>
    <style>.footer{display:none}</style>
    @include('partials/footer')

</body>
</html>
