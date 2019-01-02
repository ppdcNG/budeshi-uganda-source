var milestones = [];
var documents = [];
var transactions =[];
var require_fields = [];
$("#save-release").click(function () {
    //var valid = validateFields(require_fields);
    /*if(valid != true){
        UIkit.notification("Missing Required Field " + valid, { status: 'danger', timeout: 3000 });
        return;
    }*/
    var release = form2js("release-form", ".", false);
    var adate = new Date();
    release.date = adate.toISOString();
    
    release.transactions = transactions.filter(filter_undefined);
    release.milestones = transactions.filter(filter_undefined);
    release.documents = documents.filter(filter_undefined);
    release.milestone = undefined
    var value = JSON.stringify(release);
    console.log(value);
    var id = $("#project-id").val();
    var mda = $("#mda-id").val();
    var url = ABS_PATH + "Release/transactadd/implementation/" + id + "/" + mda;
    UIkit.notification("<div uk-spinner></div>",{status:'warning', timeout: 3000});
    $(this).attr('disabled','disabled');
    modalAction("#planning",value,url);
    //setTimeout(function() {window.history.back();}, 3000);
});