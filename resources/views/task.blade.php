@extends('layout')
@section('title','Tasks - eseCRM')

@section('content')
    <section class="task__section">
        <div class="text">
            <i class="bx bx-menu" id="mbtn"></i> 
            Task
            <a href="/signout" class="logoutbtn"><i class="bx bx-log-out"></i></a>
        </div>
        <div class="container-fluid">
            <div class="board-title board-title-flex px-4">
                <h1>Kanban Board</h1>
                <form method="post" class="task-search">
                    @csrf
                    <input type="text" id="taskSearch" name="taskSearch" class="form-size" placeholder="Search Task.." />
                </form>
                <div class="searchTaskResult">
                    <ul id="tsdata"></ul>
                </div>
            </div>
            <div class="flex">
                <input type="hidden" id="userCount" value="{{ count($users) }}" />
                <?php echo html_entity_decode($output); ?>
            </div>
        </div>
    </section>
    
    @if(isset($_GET['id']))
    
    @include('inc.task.popup')
    
    @endif
    
@endsection