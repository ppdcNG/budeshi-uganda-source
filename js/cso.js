var searchTable = undefined;
var projectTable = undefined;
var deleteModal = { item: null, id: null};
var editReport = {};
$(document).ready(function () {
    $('.mda').select2({
        placeholder: "Procuring Entity",
        ajax: {
            url: ABS_PATH + "Release/getmda",
            type: "get",
            dataType: 'json',
            data: function (params) {
                console.log(params.term);
                return { searchText: params.term }
            },
            processResults: function (data) {
                console.log(data);
                //var obj = JSON.parse(data);
                //console.log(obj);
                return {
                    results: data
                }
            },
            cache: true
        }
    });
    tableSource();
    projectTableSource();
    $("#search").submit(function (e) { 
        e.preventDefault();
        let data = form2js("search", '.', false);
        tableSource(data);
        
    });
    $("#add-project-form").submit(function (e) {
        e.preventDefault();
        let data = form2js("add-project-form", '.', false);
        let url = ABS_PATH + "CSO/add_new_project";
        console.log(url);
        data = JSON.stringify(data);
        console.log(data);
        UIkit.notification("Please Wait...", { status: "primary", timeout: 0 })
        ajaxrequest('', data, url, function (response){
            console.log(response);
            let res = JSON.parse(response);
            UIkit.notification.closeAll();
            UIkit.notification(res.message, {status: res.status, timeout: 2000})
        });
    });

    $('#add-report-form').submit(function (e) {
        e.preventDefault();
        let formData = new FormData($('#add-report-form')[0]);
        let id = $('#project-id').val();
        let url  = ABS_PATH + 'CSO/add_report/' + id;
        console.log(id);
        console.log(url);
        //Validate Files
        if(!validateFile('image-1', ['png', 'jpeg','jpg']) || !validateFile('image-2', ['png', 'jpeg','jpg'])||!validateFile('image-3', ['png', 'jpeg','jpg'])||!validateFile('image-4', ['png', 'jpeg','jpg'])){
            UIkit.notification('file type not supported', {status: 'danger', timeout: 2000});
            console.log('invalid file');
            return
        }
        if(!validateFile('report-file',['pdf'])){
            UIkit.notification("Report File not <b>PDF</b>", {status: 'danger', timeout: 2000});
            console.log('invalid file');
            return
        }
        
        //
        UIkit.notification("Please Wait...", { status: "primary", timeout: 0 })
        $.ajax({
            processData: false,
            contentType: false,
            type: "post",
            url: url,
            data: formData,
            success: function (response) {
                console.log(response);
                console.log(response);
            let res = JSON.parse(response);
            UIkit.notification.closeAll();
            UIkit.notification(res.message, {status: res.status, timeout: 2000})
                
            }
        });
    });
    $('#edit-body').submit(function (e) { 
        e.preventDefault();
        let id = $("#report-id").val();
        let url = ABS_PATH + 'CSO/edit_report_body/'+id;
        console.log(url);
        let report = form2js('edit-body', '.', false);
        console.log(report);
        report = JSON.stringify(report);
        
        ajaxrequest('modal', report,url,function(response){
            console.log(response);
            let data = JSON.parse(response);
            console.log(data);
            UIkit.notification.closeAll();
            UIkit.notification(data.message, {status: data.status, timeout: 2000});

        })
        
    });
    $("#edit-report-file").submit(function (e) { 
        e.preventDefault();
        let formData = new FormData($('#edit-report-file')[0]);
        formData.append('filename',editReport.details.filename);
        let id = $('#report-id').val();
        let url  = ABS_PATH + 'CSO/edit_report_file/' + id;
        console.log(id);
        console.log(url);
        UIkit.notification("Please Wait...", { status: "primary", timeout: 0 })
        $.ajax({
            processData: false,
            contentType: false,
            type: "post",
            url: url,
            data: formData,
            success: function (response) {
                console.log(response);
                console.log(response);
            let res = JSON.parse(response);
            UIkit.notification.closeAll();
            UIkit.notification(res.message, {status: res.status, timeout: 2000})
                
            }
        });
        
    });
    $('#edit-uploads').submit(function (e) { 
        e.preventDefault();
        let formData = new FormData($('#edit-uploads')[0]);
        let id = $('#report-id').val();
        let url = ABS_PATH + 'CSO/edit_upload_files/' + id;
        UIkit.notification("Please Wait...", { status: "primary", timeout: 0 })
        $.ajax({
            processData: false,
            contentType: false,
            type: "post",
            url: url,
            data: formData,
            success: function (response) {
                console.log(response);
                console.log(response);
            let res = JSON.parse(response);
            UIkit.notification.closeAll();
            UIkit.notification(res.message, {status: res.status, timeout: 2000})
                
            }
        });
        
    });
});

function edit_report(id){
    console.log(id);
    let url = ABS_PATH + 'CSO/get_edit_report/' + id;
    console.log(url);
    ajaxrequest('modal', "", url, function(response){
        console.log(response);
        let data = JSON.parse(response);
        editReport = data;
        console.log(data);
        $("#report-title").val(data.details.title);
        $("#report-text").val(data.details.report);
         $("#report-id").val(data.details.id);
        //images
        let image_path = ABS_PATH + 'images/monitoring/';
        $(".rep-img").each( function (index) {
        
            if(data.images[index]){
                let image = data.images[index];
                let id =$('.image-id').get(index)
                let name = $('.image-name').get(index);
                console.log(id);
                $(id).val(image.id);
                $(name).val(image.imagename);
                console.log(image_path, image.imagename)
                $(this).attr('src', image_path + image.imagename);
            }   
        });
        $(".image-input").each( function (index) {
            if(data.images[index]){
                let imgObj = data.images[index].id;
                $(this).attr('name',imgObj.imagename);
            }   
        });
    })
}

function projectTableSource(search_data = "", url = "CSO/projects") {
    if (projectTable != undefined) projectTable.destroy();
    projectTable = $("#project-table").DataTable({
        'processing': true,
        'serverSide': true,
        paging: true,
        pageLength: 7,
        ajax: {
            url: ABS_PATH + url,
            type: "POST",
            error: function (ts) { console.log(ts.responseText); alert(ts.responseText) },
            data: { 'search_data': search_data }
        },
        columns: [

            {
                data: "title"
            },
            {
                data: "mda",
            },
            {
                data: "year",
                render: function (data, type, row) {
                    if (!data) {
                        return '<span uk-tooltip = "title: Not Available">N/A</span>';
                    }
                    else {
                        return data
                    }
                }

            },
            {
                data:"contractor"
            },
            {
                data: 'date'
            },
            {
                data: "",
                render: function (data, type, row) {
                    
                        return `<a  uk-tooltip = "title:Click to add report to this project" onclick = "add_report(\'`+ row.id +`\')"><span  uk-icon = "icon:forward"></span> </a>
                        <a  class = "uk-margin-small-right" uk-tooltip = "title: Edit Project" onclick = "edit_project(\'`+ row.id +`\')"><span  uk-icon = "icon:file-edit"></span> </a>
                        <a  class = "uk-margin-small-right" uk-tooltip = "title: Click to delete this project from your portfolio" onclick = "deletePrompt('`+ row.id +`', 'project')"><span  uk-icon = "icon:trash"></span> </a>`;
                    
                    
                }
            }
            
        ]
    });
}
function tableSource(search_data = "", url = "CSO/search_data") {
    if (searchTable != undefined) searchTable.destroy();
    searchTable = $("#search-table").DataTable({
        'processing': true,
        'serverSide': true,
        searching:false,
        lengthChange: false,
        info: false,
        paging: true,
        pageLength: 7,
        ajax: {
            url: ABS_PATH + url,
            type: "POST",
            error: function (ts) { console.log(ts.responseText); alert(ts.responseText) },
            data: { 'search_data': search_data }
        },
        columns: [

            {
                data: "title"
            },
            {
                data: "name",
            },
            {
                data: "year",
                render: function (data, type, row) {
                    if (!data) {
                        return '<span uk-tooltip = "title: Not Available">N/A</span>';
                    }
                    else {
                        return data
                    }
                }

            }
            ,
            {
                data: "",
                render: function (data, type, row) {
                        return '<a  class = "uk-margin-small-right" uk-tooltip = "title:  Click to add this project to your list" onclick = "add_project(\''+ row.id +'\')"><span  uk-icon = "icon:forward"></span> </a>';
                    
                    
                }
            }
            
        ]
    });
}

function add_project(id){
    let url = ABS_PATH + 'CSO/add_cso_project/' + id;
    console.log(id);
    ajaxrequest("modal", '', url, function (response) {
        console.log(response);
        let data = JSON.parse(response);
        projectTable.draw();
        UIkit.notification(data.message, {status: data.status, timeout: 2000});
        
    } );
}
function add_report(id){
    console.log(id);
    $('#project-id').val(id);
    UIkit.modal("#add-report-modal").show();


}
function deleteItem(){
    let url = ABS_PATH + "CSO/deleteItem/" + deleteModal.id + '/' + deleteModal.item;
    console.log(url);
    ajaxrequest("modal", '',url, function (response){
        console.log(response);
        let res = JSON.parse(response);
        projectTable.draw();
        UIkit.notification(res.message, {status: res.status, timeout: 2000});
    });


}

function deletePrompt(id, item){
    console.log(id, item);
    deleteModal.id = id;
    deleteModal.item = item;
    $("#delete-item").text(item);
    UIkit.modal("#delete-modal").show();
    
}
function validateFile(input, validtypes){
    let filename = $("#"+input).val();
    console.log(filename)
    if(filename){
        let names = filename.split('.');
        console.log(names);

        let ext = names[names.length -1];
        ext = ext.toLowerCase();
        console.log(ext);
        if(validtypes.includes(ext)){
            return true;
        }
        else{
            return false;
        }
    }
    return true;
}
