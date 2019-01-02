<?php
class Organisation extends Controller{
    

    public function index($page = 1){
        if(!$this->checkLogin()){
            $this->redirect("Monitor/");
        }
        if(!isset($page)){
            $this->redirect("Monitor/");
        }

        require_once ("app/views/backend/organizations.html");

    }
    public function ajaxget(){
        $data_obj = [];
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            $data_obj["message"] = "Wrong request method";
            $data_obj = json_encode($data_obj);
            die($data_obj);
        }
        if (!$this->checkLogin()) {
            $data_obj["message"] = "unauthorized, this location is forbidden";
            $data_obj["ajaxstatus"]= "failed";
            $data_obj = json_encode($data_obj);
            die($data_obj);
        }
        if (isset($_POST["id"])) {
            require_once ("app/models/OrgDb.php");
            $org = new OrganisationModel();
            $json = $org->getOrg($_POST["id"], "");
            echo $json;
        }

    }
    public function tableget(){
        if (!$this->checkLogin()) {
            $data_obj["message"] = "unauthorized, this location is forbidden";
            $data_obj["ajaxstatus"]= "failed";
            $data_obj = json_encode($data_obj);
            die($data_obj);
        }
        require_once ("app/models/OrgDb.php");
        $org = new OrganisationModel();
        $order = $_POST['order'];
        //var_dump($order); die;
        $search_text = $_POST['search']['value'];
        $start = $_POST['start'];
        $length = $_POST['length'];
        $draw = $_POST['draw'];
        $data = $org->fetchOrgs($search_text,$start,$length, $order);

        $data_obj['data'] = $data;
        $data_obj['recordsTotal'] = (int)$org->total;
        $data_obj['recordsFiltered'] = (int)$org->filter_total;
        $data_obj['iTotalDisplayRecords'] = (int)$org->total;
        $data_obj['draw'] = $draw;
        $data_obj['total'] = $org->total;
        $json = json_encode($data_obj);
        echo $json;
        

    }
    public function edit($id) {
        if (!$this->checkLogin()) {
            $data_obj["message"] = "unauthorized, this location is forbidden";
            $data_obj = json_encode($data_obj);
            die($data_obj);
        }
        $obj = json_decode($_POST["data"]);
        require_once ("app/models/OrgDb.php");
        $org = new OrganisationModel();
        $org->updateOrg($id, $obj);
        $data_obj["message"] = "Org Edited";
        $data_obj["ajaxstatus"] = "success";
        $json = json_encode($data_obj);
        echo $json;
        die();
    }
    public function addOrg(){
        $data_obj = [];
        $access = $this->checkLogin();
        if (!$this->checkRequestMethod("POST") or !$access) {
            $data_obj["message"] = "Forbidden Access";
            $data_obj["ajaxstatus"] = "Failed";
            $json = json_encode($data_obj);
            echo $json;
            die();
        }
        $data = json_decode($_POST["data"]);
        require_once ("app/models/OrgDb.php");
        $org = new OrganisationModel();
        $result = $org->addOrg($data);
        if(!is_array($result)){
        $data_obj["message"] = "Organisation added";
        $data_obj["ajaxstatus"] = "success";
        $data_obj['id'] = $result;
        $data_obj['name'] = $data->name;
        $json = json_encode($data_obj);
        echo $json;
        }
        else{
            $data_obj['message'] = "Similar Organisations found!";
            $data_obj["ajaxstatus"] = "warning";
            $data_obj['orgs'] = $result;
            $json = json_encode($data_obj);
            echo $json;
        }
        die();
    }
    public function delete($id){
        $data_obj = [];
        $access = $this->checkLogin();
        if (!$this->checkRequestMethod("POST") or !$access) {
            $data_obj["message"] = "Forbidden Access";
            $data_obj["ajaxstatus"] = "Failed";
            $json = json_encode($data_obj);
            echo $json;
            die();
        }
        if($access > 1){
            $data_obj["message"] = "You don't have clearance for this operation";
            $data_obj["ajaxstatus"] = "Failed";
            $json = json_encode($data_obj);
            echo $json;
            die();
        }
        if(!isset($id) or !is_int((int)$id)){
            $data_obj["message"] = "Invalid Params";
            $data_obj["ajaxstatus"] = "Failed";
            $json = json_encode($data_obj);
            echo $json;
            die();
        }

        require_once ("app/models/OrgDb.php");
        $org = new OrganisationModel();
        $org->deleteOrg($id);
        $data_obj["message"] = "Organisation Deleted";
        $data_obj["ajaxstatus"] = "success";
        $json = json_encode($data_obj);
        echo $json;
        die();


    }
}
 ?>