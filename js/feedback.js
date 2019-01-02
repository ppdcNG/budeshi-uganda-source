var feedback;

function togglePub(id){
    feedback = id;
    $("#publishi-feed").modal('show');
    UIkit.modal("#publish-feed").show();
}
function contact(id){
    let url = ABS_PATH + 'Feedback/contact/'+ id;
    console.log(url);
    ajaxrequest('modal','data', url,function(data){
        console.log(data);
        let it = proccessJson(data);
        $("#contact-mail").attr('href','mailto:'+ it.email);
        $("#contact-mail").html(it.email);
        $("#contact-phone").html(it.phone);
        UIkit.modal("#contact-feed").show();
    })

}
function del_feedback(id){
    feedback = id;

    UIkit.modal("#delete-feed").show();
    

}
function del_call(){
    let url = ABS_PATH +'Feedback/delete/'+feedback;
    console.log(url);
    ajaxrequest('modal', 'data', url, function(data){
        console.log(data);
        let dt = proccessJson(data) ||  UIkit.notification("Could not Delete!! something went wrong " + valid, { status: 'danger', timeout: 3000 });;
        if(dt.ajaxstatus =='Success'){
            UIkit.notification("Delete Successfull! ", { status: 'success', timeout: 2000 });
            setTimeout(function(){window.location.reload()},3000);
        }

    })

}
function pub_call(){
    let url = ABS_PATH +'Feedback/publish/'+feedback;
    ajaxrequest('modal', 'data', url, function(data){
        console.log(data);
        let dt = proccessJson(data) ||  UIkit.notification("Could not Publish!! something went wrong " + valid, { status: 'danger', timeout: 3000 });;
        if(dt.ajaxstatus =='success'){
            UIkit.notification("Published Successfull! ", { status: 'success', timeout: 2000 });
            setTimeout(function(){window.location.reload()},3000);
        }

    })

}