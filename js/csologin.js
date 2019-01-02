$(document).ready(function(){
    $("#sign-up").submit(function (e) { 
        e.preventDefault();
        let organisation = form2js("org-details", ".", false);
        console.log(organisation);
        let user = form2js("sign-up", '.', false);
        console.log(user);
        let data = { organisation, user };
        let str = JSON.stringify(data);
        let url = ABS_PATH + 'CSO/transact/signup';
        if(!user.email || !user.password || !user.confirm_pass){
            UIkit.notification('Some required Fields are missing', {status: 'danger', timeout:3000})

        }
        
        console.log(url);
        
        UIkit.notification('Please Wait..', { status: 'primary', timeout : 0});
        ajaxrequest('blah', str, url, function(data){
            console.log(data);
            let response = JSON.parse(data);
            UIkit.notification.closeAll();
            UIkit.notification(response.message, { status: response.status, timeout: 3000});
            if(response.status == 'success'){
                setTimeout(() => {
                    window.location.reload();
                }, 3100);
            }
        });
        
    });
    $('#org-details').submit(function(e){
        e.preventDefault();
        let organisation = form2js("org-details", ".", false);
        console.log(organisation);
        if(is_empty(organisation.general.legal_name) || is_empty(organisation.address.address) || is_empty(organisation.address.country) || is_empty(organisation.contact.phone) || is_empty(organisation.contact.email)){
            UIkit.notification('Some requried Fields are missing', {status: 'danger', timeout: 3000});
            return;
        }
        else{
            UIkit.modal('#modal-group-2').show();
        }
        
        
    })
});
var is_empty = (object)=>{return !Object.keys(object).length > 0}
function validate(use){

}