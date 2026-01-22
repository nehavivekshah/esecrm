function setCookie(name, value, days) {
    let expires = "";
    if (days) {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "")  + expires + "; path=/";
}

function getCookie(name) {
    const nameEQ = name + "=";
    const ca = document.cookie.split(';');
    for(let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

window.onload = function() {
    const sidebar = document.querySelector(".sidebar");
    const closeBtn = document.querySelector("#btn");
    const closemBtn = document.querySelector("#mbtn");

    // Check if the sidebar state is saved in cookies
    const sidebarState = getCookie("sidebarOpen");

    let screenWidth = window.innerWidth;

    // Apply the saved state (open or closed) from cookies
    if (sidebarState === "open" && screenWidth > 768) {
        sidebar.classList.add("open");
        closeBtn.classList.replace("bx-menu", "bx-menu-alt-right");
    } else {
        sidebar.classList.remove("open");
        closeBtn.classList.replace("bx-menu-alt-right", "bx-menu");
    }
    
    closeBtn.addEventListener("click", function() {
        sidebar.classList.toggle("open");
        menuBtnChange();
        saveSidebarState();
    });

    closemBtn.addEventListener("click", function() {
        sidebar.classList.toggle("open");
        menuBtnChange();
        saveSidebarState();
    });

    function menuBtnChange() {
        if (sidebar.classList.contains("open")) {
            closeBtn.classList.replace("bx-menu", "bx-menu-alt-right");
        } else {
            closeBtn.classList.replace("bx-menu-alt-right", "bx-menu");
        }
    }

    function saveSidebarState() {
        if (sidebar.classList.contains("open")) {
            setCookie("sidebarOpen", "open", 1); // Save the 'open' state for 7 days
        } else {
            setCookie("sidebarOpen", "closed", 1); // Save the 'closed' state for 7 days
        }
    }
}

function addtask(id){
    document.querySelectorAll('.task-form').forEach(function(el) {
       el.style.display = 'none';
    });
    document.querySelector("#tf"+id).style = 'display:block!important;';
}

$(document).ready(function(){
    
    $('#tasktitle').keyup(function(e){
        e.preventDefault();
        var tasktitle = $('#tasktitle').val();
        var taskid = $('#taskid').val();
        //var data = $('#form-data').serialize();
        //alert(tasktitle);
        /*headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },*/
        $.ajax({
            type: 'get',
            url: "/tasksubmit",
            data: {taskid:taskid,tasktitle:tasktitle},
            
            beforeSend: function(){
                //alert('....Please wait');
            },
            success: function(response){
                //alert(response);
                //console.log(response);
            },
            complete: function(response){
                //alert(response);
                //console.log(response);
            }
        });
    });
    
    $('#edttaskdetails').on('submit', function (e) {
        
        e.preventDefault();
        var formData = new FormData(this);
        
        $.ajax({
            url: 'tasksubmit',
            type: 'POST',
            data: formData,
            beforeSend: function(){
                //alert('....Please wait');
                $('#res').html('....Please wait');
                $('#res').css('color','#ff7b00');
            },
            success: function (response) {
                // handle success response
                //console.log(response.data);
                $('#res').html(response.success);
                $('#res').css('color','#008000');
            },
            error: function (response) {
                // handle error response
                //console.log(response.data);
                $('#res').html(response.success);
                $('#res').css('color','#f44336');
            },
            contentType: false,
            processData: false
        });
        
    });
    
    $('#taskComments').on('submit', function (e) {
        
        e.preventDefault();
        var formData = new FormData(this);
        
        $.ajax({
            url: '/tasksubmit',
            type: 'POST',
            data: formData,
            beforeSend: function(){
                //alert('....Please wait');
                $('#res1').html('....Please wait');
                $('#res1').css('color','#ff7b00');
            },
            success: function (response) {
                // handle success response
                //console.log(response.data);
                $('#res1').html(response.success);
                $('#commentInputs').val('');
                $('#reloadMsg').html(response.message);
                $('#res1').css('color','#008000');
            },
            error: function (response) {
                // handle error response
                //console.log(response.data);
                $('#res1').html(response.success);
                $('#reloadMsg').html(response.message);
                $('#res1').css('color','#f44336');
            },
            contentType: false,
            processData: false
        });
        
    });
    
    $(function(){
    	$("#colorpalet").change(function(){
    	    var label = $(this).val();
    	    var tskId = $("#taskid").val();
    	    
    	    $.ajax({
                type: 'get',
                url: "/tasksubmit",
                data: {tskId:tskId,label:label},
                
                beforeSend: function(){
                    //alert('....Please wait');
                },
                success: function(response){
                    $("#labelicon").attr("style", "color:"+label+"");
                },
                complete: function(response){
                    $("#labelicon").attr("style", "color:"+label+"");
                }
            });
    	});
    });
    
	$(".taskstart").click(function(e){
	    let ele = $(this);
	    var tskstartId = ele.attr('id');
	    var tskhr = ele.attr('data-taskhr');
	    //alert(tskhr);
	    
	    $.ajax({
            type: 'get',
            url: "/tasksubmit",
            data: {tskstartId:tskstartId,tskhr:tskhr},
            
            beforeSend: function(){
                ele.html('<i class="bx bx-loader"></i> <span>Loading..</span>');
            },
            success: function(response){
                //alert("Success");
                //ele.html('<i class="bx bx-pause"></i> <span>Stop</span>');
                location.reload();
                //console.log(response);
            }
        });
	});
    
    $(".taskdeleted").click(function(e){
	    const ele = $(this);
	    const deltaskid = ele.attr("id");
	    //alert(deltaskid);
	    
	    $.ajax({
            type: 'get',
            url: "/tasksubmit",
            data: {deltaskid:deltaskid},
            
            beforeSend: function(){
                ele.html('<i class="bx bx-loader"></i> <span>Loading..</span>');
            },
            success: function(response){
                //alert("Success");
                //ele.html('<i class="bx bx-pause"></i> <span>Stop</span>');
                window.location.href="/task";
            },
            complete: function(response){
                //alert("Complete");
                //ele.html('<i class="bx bx-play"></i> <span class="p-0">Start</span>');
            }
        });
	});
    
});