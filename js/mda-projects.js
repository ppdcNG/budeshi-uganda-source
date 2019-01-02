
$(document).ready(function(){
$("#add_release").click(add_release_hanlder);
$("#edit-project-saved").click(edit_project_handler);
$("#add_project_form").submit(add_project_handler);
$("#save-project-button").click(add_project_handler);
$("#delete-project-not").click(delete_project_handler);
$("#projects").DataTable({});
});


function add_project_handler(){
    var formjson =  form2js("add_project_form", ".", false);
    console.log(formjson);
    formjson = JSON.stringify(formjson);
    var url = ABS_PATH +"Project/ajax/add";
    console.log(url);
    modalAction("#add_project_form",formjson,url);
    //setTimeout(function(){window.location.reload()},3000);
    
}
function edit_project_handler(){
    console.log("blah");
    var formjson = form2js("edit-project-form",".", false);
    formjson = JSON.stringify(formjson);
    console.log(formjson);
    var url = ABS_PATH +"Project/ajax/edit";
    console.log(url);
    modalAction("#edit-project-form",formjson,url);
    //setTimeout(function(){window.location.reload()},3000);

}
function add_release_hanlder(){
    var type = $("#add_rel_type").val()
    var id = $("#project_id").val();
    var mda = $("#mda_id").val();
    var url = ABS_PATH + "Release/add/" + type + "/"+id + "/" + mda
    console.log(mda);
    window.location = url;
}
function add_project(id){
    $("#project_id").val(id);
    console.log(id);
}

function proccessJson(data) {
    var obj = JSON.parse(data);
    return obj;
}

function get_modal_params(to_url, data_id, success_callback) {
    var dataObj = {
        id: data_id
    }
    var return_data = false;
    $.ajax({
        type: "post",
        data: dataObj,
        url: to_url,
        success: success_callback
    });

}
function edit_callback(data){
    console.log(data);
    var return_data = proccessJson(data);
    console.log(return_data);
    if(return_data.ajaxstatus == "success"){
        $("#e_title").val(return_data.title);
        $("#e_description").val(return_data.description);
        $("#e_location").val(return_data.state);
        $("#e_status").val(return_data.status);
        $("#e_year").val(return_data.year);
        $("#e_published").val(return_data.published);
        $("#e_monitored").val(return_data.monitored);
        $("#budget").val(return_data.b_amount);
        $("#contract").val(return_data.ct_amount);
        
        var option = return_data.e_name?'<option value = "' + return_data.e_ctid + '" selected>' + return_data.e_name + '</option>': "";
        $("#contractor").html(option);
        var modal = UIkit.modal("#edit-project");
        modal.show();
    }
    else {
        UIkit.notification("failed operation", { status: 'danger', timeout: 3000 });
    }
}

function editmodal(id) {
    console.log("started edit modal" + id);
    var url = ABS_PATH + "Project/ajaxget/";
    $("#e_project_id").val(id);
    get_modal_params(url, id, edit_callback);
    

}
function getReleases(id){
    var url = ABS_PATH + "Project/getReleases/";
    get_modal_params(url,id,get_release_callback);
}
function get_release_callback(data){
    console.log(data);
    $("#releases").html(data);
    var modal = UIkit.modal("#view-releases");
    modal.show();
}
function deleteProject(id){
    console.log(id);
$("#del_project_id").val(id);
}
function delete_project_callback(data){
    console.log(data);
    var return_data = proccessJson(data);
    console.log(return_data);
    if(return_data.ajaxstatus == "success"){
      UIkit.notification(return_data.message, { status: 'success', timeout: 3000 });   
    }
    else{
        UIkit.notification(return_data.message, { status: 'success', timeout: 3000 }); 
    }
}
function delete_project_handler(){
    var id = $("#del_project_id").val();
    console.log(id);
    var url = ABS_PATH + "Project/delete/";
    get_modal_params(url,id,delete_project_callback);
    setTimeout(function(){window.location.reload()},3000);
}
function deleteRelease(id, type){
    $("#del_rel_id").val(id);
    $("#del_type").val(type);
    var modal = UIkit.modal($("#delete-release")[0]);
    modal.show();
}
function deleteR(){
    let id =  $("#del_rel_id").val();
    let type = $("#del_type").val();
    let url = ABS_PATH + "Release/delete/" + id + "/" + type;
    modalAction("modal","data",url);
}
