@extends('layout')
@section('title','Reset Password - Easy Doctor')

@section('content')
    @php
    
        $roles = session('roles');
        $roleArray = explode(',',($roles->permissions ?? ''));
    
    @endphp
    <section class="task__section">
        <div class="text">
            <i class="bx bx-menu" id="mbtn"></i> 
            My Account
            <a href="/admin/signout" class="logoutbtn"><i class="bx bx-log-out"></i></a>
        </div>
        <div class="container-fluid">
            <div class="board-title board-title-flex">
                <h1>Reset Password</h1>
            </div>
            <div class="row">
                <div class="col-md-4 offset-md-4 rounded bg-white mt-4">
                    <form action="{{ route('resetPassword') }}" method="POST" class="card-body py-3 px-3">
                        @csrf
                        <div class="form-group">
                            <label class="small">New Password*</label><br />
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-key"></i></span>
                                <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Password" required />
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="small">Re-enter New Password*</label><br />
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-key"></i></span>
                                <input type="password" name="cn_password" id="cn_password" class="form-control" placeholder="Confirm Password" required />
                            </div>
                            <div id="passwordError" class="text-danger newPassword small" style="display:none;">Passwords do not match.</div>
                        </div>
                        
                        <div class="form-group text-right mt-3 mb-0">
                            <button type="submit" id="submitButtonv" class="btn btn-primary bg-primary text-white">Submit</button>
                            <button type="reset" class="btn btn-light border">Reset</button>
                        </div>
                    </form>
                <div>
            </div>
        </div>
    </section>
    
    <!-- Scripts -->
    <script>
        
        document.getElementById('new_password').addEventListener('keyup', validatePasswordMatch);
        document.getElementById('cn_password').addEventListener('keyup', validatePasswordMatch);
        
        function validatePasswordMatch() {
            var newPassword = document.getElementById('new_password').value;
            var confirmPassword = document.getElementById('cn_password').value;
            var passwordError = document.getElementById('passwordError');
        
            if (newPassword !== confirmPassword) {
                document.getElementById('submitButtonv').disabled = true;
                passwordError.style.display = 'block';
            } else {
                document.getElementById('submitButtonv').disabled = false;
                passwordError.style.display = 'none';
            }
        }
        
    </script>
@endsection