var require_fields = ["id"];
$(document).ready(function () {
    var pro_id = $("#project_id").val();
    var type = $("#rel_type").val();
    var id = $("#rel_id").val();
    console.log(type);
    var dataObject = { data: "get" }
    let to_url = abs_path + "Release/ajaxGetRelease/" + id + "/" + type;
    console.log(to_url);
    var element = $("#wait-modal")[0];
    $.ajax({
        type: "post",
        data: dataObject,
        url: to_url,
        content: "application/json",
        success: function (data) {
            console.log(data);
            var returnedData = proccessJson(data);
            var release = returnedData.release;
            console.log(release);
            js2form(document.getElementById("release-form"), release);
            loadArrays(returnedData);
            var date = release.date.split('T')[0];
            console.log(date);
            $("#date").val(date);
            let orgs = [];
            let select;
            var tenderers;
            if(type == 'tender'){
                tenderers = release.tender.tenderers;
            }
            if(type == 'award'){
                tenderers = release.award.suppliers;
            }
            for(let i = 0; i < tenderers.length; i++){
               orgs.push(tenderers[i].id);

            }
            console.log(orgs);
            $("#tender-org").val(orgs);
            $("#tender-org").trigger("change");

            
        }
    });
    
});




function loadRelease(type) {

    $.ajax({
        type: "post",
        data: dataObject,
        url: to_url,
        content: "application/json",
        success: function (data) {
            console.log(data);
            var returnedData = proccessJson(data);
            js2form(getElementById("release-form"), returnedData);
        }
    });
}
function loadArrays(obj){
    documents = obj.documents
    if(documents.length > 0){
        for(var i = 0; i<documents.length; i ++){
            showDocument(i, documents[i].title, documents[i].documentType,documents[i].format, documents[i].url);
        }
    }
    parties = obj.parties;
    if(parties.length > 0){
        for(var i = 0; i< parties.length; i++){
            showParty(i, parties[i].name);
        }
    }
    items = obj.items;
    if(items.length > 0){
        for(var i = 0; i< items.length; i ++){
            showItem(i + 1, items[i].description, items[i].quantity);
        }
    }
    milestones = obj.milestones;
    if(milestones.length > 0){
        for(var i = 0; i< milestones.length; i ++){
            showMilestone(i + 1);
        }
    }
    
}   
