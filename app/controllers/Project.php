<?php 
class Project extends Controller
{

    public function index($mda_id, $page = 1)
    {
        if (!$this->checkLogin()) {
            $data_obj["message"] = "unauthorized, this location is forbidden";
            $data_obj = json_encode($data_obj);
            die($data_obj);
        }

        $projects = $this->load_model('ProjectDb');
        $result = $projects->getMDAProjects($mda_id, $page,'Project/'.$mda_id.'/');
        $data['mda_name'] = $projects->mda_name;
        $data['projects'] = $result->table;
        $data['districts'] = $projects->getDistricts();
        $data['pagination'] = $result->pag_links;
        $data['mda_id'] = $mda_id;

        $this->load_view('backend/institution',$data);
    }
    public function getReleases()
    {
        require_once ("app/models/ProjectDb.php");
        $projectDb = new ProjectDb();
        $data_obj = [];
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            $data_obj["message"] = "Wrong request method";
            $data_obj = json_encode($data_obj);
            die($data_obj);
        }
        if (!$this->checkLogin()) {
            $data_obj["message"] = "unauthorized, this location is forbidden";
            $data_obj = json_encode($data_obj);
            die($data_obj);
        }
        $releases_html = "";
        $project_id = isset($_POST["id"]) ? $_POST["id"] : 0;
        $releases_html = $projectDb->getRealeases($project_id,"releases");
        if ($releases_html) {
        }
        else {
            $releases_html = "<li><em>No Releases found for this Record</em></li>";
        }
        echo $releases_html;
    }
    public function ajaxget()
    {
        $data_obj = [];
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            $data_obj["message"] = "Wrong request method";
            $data_obj = json_encode($data_obj);
            die($data_obj);
        }
        if (!$this->checkLogin()) {
            $data_obj["message"] = "unauthorized, this location is forbidden";
            $data_obj = json_encode($data_obj);
            die($data_obj);
        }
        if (isset($_POST["id"])) {
            require_once ("app/models/ProjectDb.php");
            $projectDb = new ProjectDb();
            $json = $projectDb->getProject($_POST["id"]);
            echo $json;
        }

    }
    public function ajax($type)
    {
        $access = $this->checkLogin();
        if (!$this->checkRequestMethod("POST") or !$access) {
            $data_obj["message"] = "Forbidden Access";
            $data_obj["ajaxstatus"] = "Failed";
            $json = json_encode($data_obj);
            echo $json;
            die();
        }
        if (!isset($_POST["data"])) {
            $data_obj["message"] = "Parameter Not set";
            $data_obj["ajaxstatus"] = "Failed";
            echo json_encode($json);
            die();
        }
        require_once ("app/models/ProjectDb.php");
        require_once ("app/models/ReleaseDb.php");

        switch ($type) {
            case "add" :
                $data_obj = json_decode($_POST["data"]);
                $project_db = new ProjectDb();
                $release_db = new ReleaseDb();
                $data_obj->updated_by = $access;
                $output = $project_db->addProject($data_obj);
                $output["ajaxstatus"] = "success";
                $json_output = json_encode($output);
                echo $json_output;
                break;
            case "edit" :
                $data_obj = json_decode($_POST["data"]);
                $project_db = new ProjectDb();
                $release_db = new ReleaseDb();
                $output = $project_db->editProject($data_obj);
                if(!empty($data_obj->contractor) && !empty($data_obj->contract))$release_db->setJSONSupplier($data_obj->e_project_id, $data_obj->contractor, $data_obj->contract, $data_obj->budget);
                $output["ajaxstatus"] = "success";
                $json_output = json_encode($output);
                echo $json_output;
                break;
        }
    }
    public function delete()
    {
        $data_obj = [];
        $access = $this->checkLogin();
        if (!$this->checkRequestMethod("POST") or !$access) {
            $data_obj["message"] = "Forbidden Access";
            $data_obj["ajaxstatus"] = "Failed";
            $json = json_encode($data_obj);
            echo $json;
            die();
        }
        if ($access > 2) {
            $data_obj["message"] = "You are not authorized to perform this action";
            $data_obj["ajaxstatus"] = "Failed";
            $json = json_encode($data_obj);
            echo $json;
            die();
        }
        if (!isset($_POST["id"])) {
            $data_obj["message"] = "Parameter Not set";
            $data_obj["ajaxstatus"] = "Failed";
            echo json_encode($data_obj);
            die();
        }
        require_once ("app/models/ProjectDb.php");
        $data_obj = json_decode($_POST["id"]);
        $project_db = new ProjectDb();
        $project_db->delete($_POST["id"]);
        $output = null;
        $output["message"] = "Delete Successfull";
        $output["ajaxstatus"] = "success";
        $json = json_encode($output);
        echo $json;
        die();
    }
    public function deleteRelease($id, $type)
    {
        $access = $this->checkLogin();
        if (!$this->checkRequestMethod("POST") or !$access) {
            $data_obj["message"] = "Forbidden Access";
            $data_obj["ajaxstatus"] = "Failed";
            $json = json_encode($data_obj);
            echo $json;
            die();
        }
        if ($access > 2) {
            $data_obj["message"] = "You are not authorized to perform this action";
            $data_obj["ajaxstatus"] = "Failed";
            $json = json_encode($data_obj);
            echo $json;
            die();
        }
        require_once ("app/models/ProjectDb.php");
        $project_db = new ProjectDb();
        $type = strtolower($type);
        $project_db->deleteRelease($id, $type);
        $output["message"] = "Delete Successfull";
        $output["ajaxstatus"] = "success";
        $json = json_encode($output);
        echo $json;
        die();


    }
    public function updatePub(){
        $access = $this->checkLogin();
        if (!$this->checkRequestMethod("POST") or !$access) {
            $data_obj["message"] = "Forbidden Access";
            $data_obj["ajaxstatus"] = "Failed";
            $json = json_encode($data_obj);
            echo $json;
            die();
        }
        $data = $_POST["data"];
        $params = json_decode($data);
        require_once ("app/models/ProjectDb.php");
        $project_db = new ProjectDb();
        $project_db->setUpdate($params->id, $params->res);
        $output["message"] = "project ".$params->res."ed !";
        $output["ajaxstatus"] = "success";
        $json = json_encode($output);
        echo $json;
        die();
    }
    
}


?>