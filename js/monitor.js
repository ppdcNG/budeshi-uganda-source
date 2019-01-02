$(document).ready(function(){
    $("#search").keyup(searchInstitution);
})
function delete_mda(id){
    console.log("blah");
    $("#delete-id").val(id);
    var modal = UIkit.modal($("#delete-institution")[0]);
    modal.show();
}

function deleteM(){
    var id = $("#delete-id").val();
    var url =  ABS_PATH + "Monitor/delete/" + id;
    console.log(url);
    modalAction("modal", "data", url);
    setTimeout(function() {
        window.location.reload();
    }, 3000);


}
function searchInstitution() {
    var searchText = document.getElementById('search'),
        table = document.getElementById('institutions'),
        rows = table.getElementsByTagName('tr');
       
    searchText = searchText.value.toUpperCase();
    var i,
        institutionName;
    for (i = 1; i < rows.length; i++) {
        institutionName = rows[i].getElementsByTagName('td')[1];
        console.log(typeof(institutionName));
        if (institutionName.innerHTML.toUpperCase().indexOf(searchText) > -1) {
            rows[i].style.display = '';
        }
        else {
            rows[i].style.display = 'none';
        }
    }
}
var require_fields = ["commonName", "shortname"];
$("#add-mda").click(function(){
    let require_fields = ["commonName","shortname"];
    var valid = validateFields(require_fields);
    if(valid != true){
        UIkit.notification("Missing Required Field " + valid, { status: 'danger', timeout: 3000 });
        return;
    }
    var mda = form2js("mda-details", "," ,false);
    console.log(mda);
    var mda_data = JSON.stringify(mda);
    var url =  ABS_PATH + "/Monitor/addMDA"
    ajaxrequest("modal",mda_data, url, function(data){
        console.log(data);
        let param = proccessJson(data);
        UIkit.notification(param.message, {status: param.ajaxstatus, timeout: 2000});

    })
   // setTimeout(function() {window.location.reload();}, 2000);

});

$("#edit-mda").click(function(){
    let require_fields = ["e_commonName", "e_shortname"];
    var valid = validateFields(require_fields);
    if(valid != true){
        UIkit.notification("Missing Required Field " + valid, { status: 'danger', timeout: 3000 });
        return;
    }
    var mda = form2js("mda-edit", ",", false);
    var data = JSON.stringify(mda);
    var id = $("#e_project_id").val();
    var url =  ABS_PATH + "/Monitor/edit/" + id;
    modalAction("modal",data,url);
    setTimeout(function() {window.location.reload();}, 2000);

});

function edit_callback(data){
    console.log(data);
    var return_data = proccessJson(data);
    console.log(return_data);
    if(return_data.ajaxstatus == "success"){
        //
        
        $("#e_commonName").val(return_data.e_name);
        $("#e_address").val(return_data.e_address);
        $("#e_phone").val(return_data.e_phone);
        $("#e_shortname").val(return_data.e_short_name);
        $("#e_sector").val(return_data.e_sector);
        $("#e_website").val(return_data.e_sector);
        $("#e_email").val(return_data.e_email);
        $("#e_ug_id").val(return_data.e_ug_id);
        $("#e_scheme").val(return_data.e_scheme);
                
        var modal = UIkit.modal("#edit-institution");
        modal.show();
    }
    else {
        UIkit.notification("failed operation", { status: 'danger', timeout: 3000 });
    }
}

function editmodal(id) {
    console.log("started edit modal");
    var url =  ABS_PATH + "Monitor/ajaxget/";
    $("#e_project_id").val(id);
    get_modal_params(url, id, edit_callback);
    

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
