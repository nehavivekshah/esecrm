@extends('layout')
@section('title','Role Settings - eseCRM')

@section('content')
<section class="task__section">
    <div class="text">
        <i class="bx bx-menu" id="mbtn"></i> 
        Role Settings
        <a href="/signout" class="logoutbtn"><i class="bx bx-log-out"></i></a>
    </div>
    <div class="container-fluid">
        <div class="board-title board-title-flex mb-2">
            <a href="/role-settings" class="btn btn-primary bg-primary text-white btn-sm back-btn"><i class="bx bx-arrow-back"></i></a>
            @if(!empty(request()->get('id'))) <h1>Edit Role</h1> @else <h1>Add New Role</h1> @endif
        </div>

        <div class="row g-3 px-2">
            <div class="col-md-12 bg-white py-3 px-4 rounded">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <form action="manage-role-setting" method="post" class="row">
                    @csrf
                        
                    <div class="form-group col-md-4">
                        <label for="role">Role*</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-user'></i></span>
                            <input type="text" class="form-control" id="role" name="role" placeholder="Enter Role*" value="{{ $roles->title ?? '' }}" required>
                        </div>
                        <input type="hidden" name="id" value="{{ request()->get('id') ?? '' }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="subrole">Designation</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-user'></i></span>
                            <input type="text" class="form-control" id="subrole" name="subrole" placeholder="Enter Designation" value="{{ $roles->subtitle ?? '' }}">
                        </div>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="status">Status*</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-user'></i></span>
                            <select class="form-control" id="status" name="status" required>
                                <option value="1" @if(($roles->status ?? '') == '1') selected @endif>Active</option>
                                <option value="2" @if(($roles->status ?? '') == '2') selected @endif>Deactive</option>
                            </select>
                        </div>
                    </div>
                    
                    @php
                        $features = explode(',', ($roles->features ?? ''));
                        $permissions = explode(',', ($roles->permissions ?? ''));
                    @endphp

                    <div class="form-group col-md-12">
                        <label for="features">Features & Access Permissions*</label><br>

                        <table class="table table-bordered" style="border-radius: 5px!important;overflow: hidden;">
                            <thead>
                                <tr>
                                    <th>Feature</th>
                                    <th>Assigned</th>
                                    <th>Add</th>
                                    <th>Edit</th>
                                    <th>Delete</th>
                                    <th>Export</th>
                                    <th>Import</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Attendances Management -->
                                <tr>
                                    <td>Attendances Management</td>
                                    <td>
                                        <input type="checkbox" name="permissions[attendances][]" value="assign" @if(in_array('attendances_assign', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[attendances][]" value="add" @if(in_array('attendances_add', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[attendances][]" value="edit" @if(in_array('attendances_edit', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[attendances][]" value="delete" @if(in_array('attendances_delete', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[attendances][]" value="export" @if(in_array('attendances_export', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[attendances][]" value="import" @if(in_array('attendances_import', $permissions)) checked @endif>
                                    </td>
                                </tr>
                                
                                <!-- Users Management -->
                                <tr>
                                    <td>Users Management</td>
                                    <td>
                                        <input type="checkbox" name="permissions[users][]" value="assign" @if(in_array('users_assign', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[users][]" value="add" @if(in_array('users_add', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[users][]" value="edit" @if(in_array('users_edit', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[users][]" value="delete" @if(in_array('users_delete', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[users][]" value="export" @if(in_array('users_export', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[users][]" value="import" @if(in_array('users_import', $permissions)) checked @endif>
                                    </td>
                                </tr>

                                <!-- Tasks Management -->
                                <tr>
                                    <td>Tasks Management</td>
                                    <td>
                                        <input type="checkbox" name="permissions[tasks][]" value="assign" @if(in_array('tasks_assign', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[tasks][]" value="add" @if(in_array('tasks_add', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[tasks][]" value="edit" @if(in_array('tasks_edit', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[tasks][]" value="delete" @if(in_array('tasks_delete', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[tasks][]" value="export" @if(in_array('tasks_export', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[tasks][]" value="import" @if(in_array('tasks_import', $permissions)) checked @endif>
                                    </td>
                                </tr>

                                <!-- Proposals Management -->
                                <tr>
                                    <td>Proposals Management</td>
                                    <td>
                                        <input type="checkbox" name="permissions[proposals][]" value="assign" @if(in_array('proposals_assign', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[proposals][]" value="add" @if(in_array('proposals_add', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[proposals][]" value="edit" @if(in_array('proposals_edit', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[proposals][]" value="delete" @if(in_array('proposals_delete', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[proposals][]" value="export" @if(in_array('proposals_export', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[proposals][]" value="import" @if(in_array('proposals_import', $permissions)) checked @endif disabled>
                                    </td>
                                </tr>

                                <!-- Leads Management -->
                                <tr>
                                    <td>Leads Management</td>
                                    <td>
                                        <input type="checkbox" name="permissions[leads][]" value="assign" @if(in_array('leads_assign', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[leads][]" value="add" @if(in_array('leads_add', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[leads][]" value="edit" @if(in_array('leads_edit', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[leads][]" value="delete" @if(in_array('leads_delete', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[leads][]" value="export" @if(in_array('leads_export', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[leads][]" value="import" @if(in_array('leads_import', $permissions)) checked @endif>
                                    </td>
                                </tr>

                                <!-- Customers Management -->
                                <tr>
                                    <td>Customers Management</td>
                                    <td>
                                        <input type="checkbox" name="permissions[clients][]" value="assign" @if(in_array('clients_assign', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[clients][]" value="add" @if(in_array('clients_add', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[clients][]" value="edit" @if(in_array('clients_edit', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[clients][]" value="delete" @if(in_array('clients_delete', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[clients][]" value="export" @if(in_array('clients_export', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[clients][]" value="import" @if(in_array('clients_import', $permissions)) checked @endif>
                                    </td>
                                </tr>

                                <!-- Projects Management -->
                                <tr>
                                    <td>Projects Management</td>
                                    <td>
                                        <input type="checkbox" name="permissions[projects][]" value="assign" @if(in_array('projects_assign', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[projects][]" value="add" @if(in_array('projects_add', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[projects][]" value="edit" @if(in_array('projects_edit', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[projects][]" value="delete" @if(in_array('projects_delete', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[projects][]" value="export" @if(in_array('projects_export', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[projects][]" value="import" @if(in_array('projects_import', $permissions)) checked @endif>
                                    </td>
                                </tr>

                                <!-- Contracts Management -->
                                <tr>
                                    <td>Contracts Management</td>
                                    <td>
                                        <input type="checkbox" name="permissions[contracts][]" value="assign" @if(in_array('contracts_assign', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[contracts][]" value="add" @if(in_array('contracts_add', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[contracts][]" value="edit" @if(in_array('contracts_edit', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[contracts][]" value="delete" @if(in_array('contracts_delete', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[contracts][]" value="export" @if(in_array('contracts_export', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[contracts][]" value="import" @if(in_array('contracts_import', $permissions)) checked @endif>
                                    </td>
                                </tr>

                                <!-- Recovery Management -->
                                <tr>
                                    <td>Recovery Management</td>
                                    <td>
                                        <input type="checkbox" name="permissions[recoveries][]" value="assign" @if(in_array('recoveries_assign', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[recoveries][]" value="add" @if(in_array('recoveries_add', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[recoveries][]" value="edit" @if(in_array('recoveries_edit', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[recoveries][]" value="delete" @if(in_array('recoveries_delete', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[recoveries][]" value="export" @if(in_array('recoveries_export', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[recoveries][]" value="import" @if(in_array('recoveries_import', $permissions)) checked @endif>
                                    </td>
                                </tr>

                                <!-- Invoices Management -->
                                <tr>
                                    <td>Invoices Management</td>
                                    <td>
                                        <input type="checkbox" name="permissions[invoice][]" value="assign" @if(in_array('invoice_assign', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[invoice][]" value="add" @if(in_array('invoice_add', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[invoice][]" value="edit" @if(in_array('invoice_edit', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[invoice][]" value="delete" @if(in_array('invoice_delete', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[invoice][]" value="export" @if(in_array('invoice_export', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[invoice][]" value="import" @if(in_array('invoice_import', $permissions)) checked @endif>
                                    </td>
                                </tr>

                                <!-- Company Management -->
                                <tr>
                                    <td>Company Management</td>
                                    <td>
                                        <input type="checkbox" name="permissions[company][]" value="assign" @if(in_array('company_assign', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[company][]" value="add" @if(in_array('company_add', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[company][]" value="edit" @if(in_array('company_edit', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[company][]" value="delete" @if(in_array('company_delete', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[company][]" value="export" @if(in_array('company_export', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[company][]" value="import" @if(in_array('company_import', $permissions)) checked @endif>
                                    </td>
                                </tr>

                                <!-- SMTP Settings Management -->
                                <tr>
                                    <td>SMTP Settings Management</td>
                                    <td>
                                        <input type="checkbox" name="permissions[smtp][]" value="assign" @if(in_array('smtp_assign', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[smtp][]" value="add" @if(in_array('smtp_add', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[smtp][]" value="edit" @if(in_array('smtp_edit', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[smtp][]" value="delete" @if(in_array('smtp_delete', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[smtp][]" value="export" @if(in_array('smtp_export', $permissions)) checked @endif>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="permissions[smtp][]" value="import" @if(in_array('smtp_import', $permissions)) checked @endif>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="form-group col-md-12 text-right">
                        <button type="submit" class="btn btn-primary bg-primary text-white px-4">Submit</button>
                        <button type="reset" class="btn btn-light border px-4">Reset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
