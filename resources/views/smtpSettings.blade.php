@extends('layout')
@section('title','SMTP Setup - eseCRM')

@section('content')
<section class="task__section">
    <div class="text">
        <i class="bx bx-menu" id="mbtn"></i> 
        SMTP Email Setup
        <a href="/signout" class="logoutbtn"><i class="bx bx-log-out"></i></a>
    </div>
    <div class="container-fluid">
        <div class="board-title board-title-flex mb-4">
            <!--<a href="/settings" class="btn btn-primary bg-primary text-white btn-sm back-btn"><i class="bx bx-arrow-back"></i></a>-->
            <h1>Configure SMTP Email</h1>
        </div>

        <div class="row">
            <div class="col-md-12 csp-3">
                <form action="/smtp-settings" method="post" class="row g-3 bg-white p-3">
                    @csrf
                    <div class="col-md-6 form-group">
                        <label for="mailer">Mailer</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-envelope'></i></span>
                            <input type="text" class="form-control" id="mailer" name="mailer" placeholder="e.g., smtp" value="{{ $smtpsetup->mailer ?? '' }}" required>
                        </div>
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="host">SMTP Host</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-server'></i></span>
                            <input type="text" class="form-control" id="host" name="host" placeholder="e.g., smtp.mailtrap.io" value="{{ $smtpsetup->host ?? '' }}" required>
                        </div>
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="port">SMTP Port</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-transfer'></i></span>
                            <input type="number" class="form-control" id="port" name="port" placeholder="e.g., 587" value="{{ $smtpsetup->port ?? '' }}" required>
                        </div>
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="username">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-user'></i></span>
                            <input type="text" class="form-control" id="username" name="username" placeholder="SMTP Username" value="{{ $smtpsetup->username ?? '' }}" required>
                        </div>
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="password">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-lock-alt'></i></span>
                            <input type="password" class="form-control" id="password" name="password" placeholder="SMTP Password" value="" required>
                        </div>
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="encryption">Encryption</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-lock'></i></span>
                            <input type="text" class="form-control" id="encryption" name="encryption" placeholder="tls/ssl" value="{{ $smtpsetup->encryption ?? '' }}" required>
                        </div>
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="from_address">From Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-mail-send'></i></span>
                            <input type="email" class="form-control" id="from_address" name="from_address" placeholder="e.g., noreply@yourdomain.com" value="{{ $smtpsetup->from_address ?? '' }}" required>
                        </div>
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="from_name">From Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-user-voice'></i></span>
                            <input type="text" class="form-control" id="from_name" name="from_name" placeholder="e.g., eseCRM" value="{{ $smtpsetup->from_name ?? '' }}" required>
                        </div>
                    </div>

                    <div class="col-md-12 text-center mt-4">
                        <button type="submit" class="btn btn-primary bg-primary text-white px-4">Save Settings</button>
                        <button type="reset" class="btn btn-outline-secondary border px-4">Reset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
