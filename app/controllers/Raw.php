<?php
class Raw extends Controller{

    public function records($ocid = "", $download = false){
        require_once('../app/models/ApiModel.php');
        $api = new ApiModel();
        if(!$download) echo $api->fetch_records($ocid);
        else $api->download_records($ocid);
    }
    public function data(){
        require_once('../app/models/ApiModel.php');
        $api = new ApiModel();
        $api->get_CSV();
    }
    public function ocds_records($mda_id, $download = false){
        require_once('../app/models/ApiModel.php');
        $api = new ApiModel();
        $data = $api->mda_records($mda_id);
        if($download == false) echo json_encode($data);
        else{
            header('Content-Type: application/json; charset=utf-8');
        header('Content-Type: application/force-download');
        header('Content-Disposition: attachment; filename="ocdsrecords.json"');
        echo json_encode($data);
        }

    }
}
 ?>