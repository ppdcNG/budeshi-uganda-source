var table;
var is_empty = (object)=>{return !Object.keys(object).length > 0}
$(document).ready(function () {
    var link =   ABS_PATH + "Organisation/tableget/"
     table = $("#org-table").DataTable({
        'processing': true,
        'serverSide': true,
        'paging':true,
        ajax: {
            url: link,
            type: 'POST',
            dataSrc: function(data){
                console.log(data);
                return data.data;

            },
            error:function(e){
                console.log(e)
                alert(e.responseText);
            }
        },
        columns: [
            {
                data: "org_id",
                render: function (data, type, row) {
                    return '<a href="#edit-institution" onclick = "editmodal(\'' + row.org_id + '\')" title="Edit Organization" uk-tooltip="pos: bottom" uk-toggle><span class="uk-margin-small-right" uk-icon="icon: file-edit"></span></a>'
                }
            },
            {
                data: "name"
            },
            {
                data: "org_id",
                render: function (data, type, row) {
                    return '<a href="#id" onclick = "delete_org(\'' + row.org_id + '\', this)" title="Delete Organization" uk-tooltip="pos: bottom" uk-toggle><span class="uk-margin-small-right" uk-icon="icon: trash"></span></a>'
                }
            }
        ]
    });
});



function delete_org(id) {
    console.log("blah");
    $("#delete_id").val(id);
    var modal = UIkit.modal($("#delete-org")[0]);
    modal.show();
}

function deleteO() {
    var id = $("#delete_id").val();
    var url =   ABS_PATH + "Organisation/delete/" + id;
    console.log(url);
    modalAction("modal", "data", url);
    table.draw(false);
    //$('#row'+id).remove();
    //setTimeout(function () {window.location.reload();}, 3000);


}
var require_fields = ["commonName"];
$("#add-org").click(function () {
    let require_fields = ["aname"];
    var valid = validateFields(require_fields);
    if (valid != true) {
        UIkit.notification("Missing Required Field " + valid, { status: 'danger', timeout: 3000 });
        return;
    }
    var mda = form2js("add-organisation", ".", false);
    console.log(mda)
    var data = JSON.stringify(mda);
    var url =   ABS_PATH + "/Organisation/addOrg"
    ajaxrequest("modal", data, url, handle_add);
    //setTimeout(function () {window.location.reload();}, 2000);

});
function handle_add(data) {
    console.log(data);
    var data = proccessJson(data);
    switch (data.ajaxstatus) {
        case 'warning':
            let html = data.orgs.join("<br>");
            UIkit.notification(data.message + "<br>" + html, { status: 'warning', timeout: 3000 });
            break;
        case 'success':
            UIkit.notification(data.message, { status: 'success', timeout: 3000 });
            table.clear().draw();
            break;
        case 'failed':
            UIkit.notification(data.message, { status: 'danger', timeout: 3000 });
            break;
    }
}

$("#edit-org").click(function () {
    let require_fields = ["name"];
    var valid = validateFields(require_fields);
    if (valid != true) {
        UIkit.notification("Missing Required Field " + valid, { status: 'danger', timeout: 3000 });
        return;
    }
    var mda = form2js("organisation", ".", false);
    console.log(mda)
    var data = JSON.stringify(mda);
    var id = $("#org_id").val();
    var url =   ABS_PATH + "Organisation/edit/" + id;
    ajaxrequest('modal',data, url, function(data){
        console.log(data);
        let ret = proccessJson(data);
        UIkit.notification(ret.message, {status:ret.ajaxstatus, timeout: 2000})
    });
    table.draw(false);
    //setTimeout(function () {window.location.reload();}, 2000);

});

function edit_callback(data) {
    console.log(data);
    var return_data = proccessJson(data);
    console.log(return_data);
    if (return_data.ajaxstatus == "success") {
        $("#name").val(return_data.name);
        $("#legalName").val(return_data.name);
        $("#id").val(return_data.ug_no);
        $("#streetName").val(return_data.address);
        $("#locality").val(return_data.lga);
        $("#region").val(return_data.state);
        $("#uri").val(return_data.url);
        $("#postalCode").val(return_data.postal_code);
        $("#phone").val(return_data.phone);
        $("#email").val(return_data.email);
        $("#contactName").val(return_data.contact_name);
        var modal = UIkit.modal("#edit-institution");
        modal.show();
        console.log('end edit');
    }
    else {
        UIkit.notification("failed operation", { status: 'danger', timeout: 3000 });
    }
}

function editmodal(id) {
    console.log("started edit modal");
    var url =   ABS_PATH + "Organisation/ajaxget/";
    $("#org_id").val(id);
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
function render_table_org(id, name){
    $html = `<tr id = "row`+id + `">
    <td><a href="#edit-institution" onclick = "editmodal( '`+ id + ` ')" title="Edit Organization" uk-tooltip="pos: bottom" uk-toggle><span class="uk-margin-small-right" uk-icon="icon: file-edit"></span></a></td>
    <td uk-toggle="target: #view-institution">`+ name + ` </td>
    <td><a href="#id" onclick = "delete_org('`+ id + `', this)" title="Delete Organization" uk-tooltip="pos: bottom" uk-toggle><span class="uk-margin-small-right" uk-icon="icon: trash"></span></a></td>
</tr>`;
$("#org-table tr:first").after($html);
}
