<?php 
class Release extends Controller
{


    public function index($id, $type)
    {

    }
    public function add($type, $id, $mda_id)
    {
        if (!$this->checkLogin()) {
            echo "Unauthorized you cant view this page";
            die();
        }
        switch ($type) {
            case "planning":
                
                $planning = $this->load_model('ReleaseDb');
                $data['oc_id'] = $planning->getOCID($id);
                $data['projectTitle'] = $planning->projectName;
                $data['release_id'] = $data['oc_id'] . "-planning-" . $planning->getNextId("planning", $id);
                $data['today'] = date("Y-m-d", time());
                $data['mda_id'] = $mda_id;
                $data['id'] = $id;
                $this->load_view('backend/new-planning-release', $data);
                break;
            case "tender":
                
                $tender = $this->load_model('ReleaseDb');
                $data['ocid'] = $tender->getOCID($id);
                $data['projectTitle'] = $tender->projectName;
                $data['project_id'] = $id;
                $data['release_id'] = $data['ocid'] . "-tender-" . $tender->getNextId("tender", $id);
                $data['today'] = date("Y-m-d", time());
                $data['mda_id'] = $mda_id;
                $data['id'] = $id;
                $this->load_view('backend/new-tender-release',$data);
                break;
            case "award":
                
                $award = $this->load_model('ReleaseDb');
                $data['ocid'] = $award->getOCID($id);
                $data['projectTitle'] = $award->projectName;
                $data['project_id'] = $id;
                $data['release_id'] = $data['ocid'] . "-award-" . $award->getNextId("award", $id);
                $data['amendment_releases'] = $award->fetchReleases("award", $id);
                $data['today'] = date("Y-m-d", time());
                $data['mda_id'] = $mda_id;
                $data['id'] = $id;
                $this->load_view('backend/new-award-release',$data);
                break;
            case "contract":
                
                $contract = $this->load_model('ReleaseDb');
                $data['ocid'] = $contract->getOCID($id);
                $data['projectTitle'] = $contract->projectName;
                $data['project_id'] = $id;
                $data['release_id'] = $data['ocid'] . "-contract-" . $contract->getNextId("contract", $id);
                $data['award_releases'] = $contract->fetchReleases("award", $id);
                $data['amendment_releases'] = $contract->fetchReleases("contract", $id);
                $data['today'] = date("Y-m-d");
                $data['mda_id'] = $mda_id;
                $data['id'] = $id;
                $this->load_view('backend/new-contract-release', $data);
                break;
            case "implementation":
                
                $implementation = $this->load_model('ReleaseDb');
                $data['ocid'] = $implementation->getOCID($id);
                $data['projectTitle'] = $implementation->projectName;
                $data['project_id'] = $id;
                $data['release_id'] = $data['ocid'] . "-implementation-" . $implementation->getNextId("implementation", $id);
                $data['contract_releases'] = $implementation->fetchReleases("contract", $id);
                $data['amendment_releases'] = $implementation->fetchReleases("contract", $id);
                $data['today'] = date("Y-m-d");
                $data['mda_id'] = $mda_id;
                $data['id'] = $id;
                $this->load_view('backend/new-implementation-release',$data);
                break;
        }
    }
    public function getorg()
    {
        $search_text = isset($_GET["searchText"]) ? $_GET["searchText"] : "";
        
        $planning = $this->load_model('ReleaseDb');;
        $institutions = $planning->getOrganisations($search_text);
        $institutions = json_encode($institutions);
        echo $institutions;


    }
    public function getmda(){
        $search_text = isset($_GET["searchText"]) ? $_GET["searchText"] : "";
        
        $planning = $this->load_model('ReleaseDb');;
        $institutions = $planning->getOrganisations($search_text, 'mdas');
        $institutions = json_encode($institutions);
        echo $institutions;

    }
    public function transactadd($type, $id, $mda_id)
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
        switch ($type) {
            case "planning":
                
                $json_data = $_POST["data"];
                $planning_obj = json_decode($json_data);
                $release_db = $this->load_model('ReleaseDb');
                $parties = $release_db->getParties($planning_obj->parties);
                $planning_obj->parties = $parties;
                //$buyer = $release_db->getOCDOrganisation($mda_id);
                //$planning_obj->buyer = $buyer;
                $release_db->addPlanningRelease($id, $mda_id, $planning_obj);
                $json_data = json_encode($planning_obj, JSON_PRETTY_PRINT);
                $release_path = FILE_ROOT . "app/releases/" . $planning_obj->id . ".json";
                $releasefile = fopen($release_path, "w") or die("fopen failed" . FILE_ROOT . "\n" . $_SERVER["DOCUMENT_ROOT"]);
                fwrite($releasefile, $json_data);
                $compile = $release_db->compilePackage($id, $planning_obj->ocid);
                $data_obj["message"] = "SuccessFully Saved the Release";
                $data_obj["ajaxstatus"] = "success";
                echo json_encode($data_obj);
                break;
            case "tender":
                
                $json_data = $_POST["data"];
                $tender_obj = json_decode($json_data);
                $release_db = $this->load_model('ReleaseDb');
                if (isset($tender_obj->parties) && is_array($tender_obj->parties) && count($tender_obj->parties) > 0 && !empty($tender_obj->parties)) {
                    $parties = $release_db->getParties($tender_obj->parties);
                    $tender_obj->parties = $parties;
                }
                if (isset($tender_obj->tender->tenderers) && !empty($tender_obj->tender->tenderers)) {
                    $tenderers = $release_db->getTenderers($tender_obj->tender->tenderers);
                    $tender_obj->tender->tenderers = $tenderers;
                }
                $release_db->addTenderRelease($id, $mda_id, $tender_obj);
                $json_data = json_encode($tender_obj, JSON_PRETTY_PRINT);
                $release_path = FILE_ROOT . "app/releases/" . $tender_obj->id . ".json";
                $releasefile = fopen($release_path, "w");
                fwrite($releasefile, $json_data);
                $compile = $release_db->compilePackage($id, $tender_obj->ocid);
                $data_obj["message"] = "SuccessFully Saved the Release";
                $data_obj["ajaxstatus"] = "success";
                echo json_encode($data_obj);
                break;
            case "award":
                
                $json_data = $_POST["data"];
                $award_obj = json_decode($json_data);
                $release_db = $this->load_model('ReleaseDb');
                //$award_obj->buyer = $release_db->getOCDOrganisation($id);
                if (isset($award_obj->parties) && is_array($award_obj->parties) && count($award_obj->parties) > 0) {
                    $parties = $release_db->getParties($award_obj->parties);
                    $award_obj->parties = $parties;
                }
                $supplier = $award_obj->award->suppliers[0];
                //$release_db->insertSuppliers($award_obj->award->suppliers,$id);
                $release_db->addContractors($award_obj->award->suppliers, $id);
                $award_obj->award->suppliers = $release_db->getSuppliers($award_obj->award->suppliers);
                $release_db->addAwardRelease($id, $mda_id, $supplier, $award_obj);
                $json_data = json_encode($award_obj, JSON_PRETTY_PRINT);
                $release_path = FILE_ROOT . "app/releases/" . $award_obj->id . ".json";
                $releasefile = fopen($release_path, "w");
                fwrite($releasefile, $json_data);
                $compile = $release_db->compilePackage($id, $award_obj->ocid);
                $data_obj["message"] = "SuccessFully Saved the Release";
                $data_obj["ajaxstatus"] = "success";
                echo json_encode($data_obj);
                break;
            case "contract":
                
                $json_data = $_POST["data"];
                $contract_obj = json_decode($json_data);
                $release_db = $this->load_model('ReleaseDb');
                //$contract_obj->buyer = $release_db->getOCDOrganisation($id);
                if (isset($contract_obj->parties) && is_array($contract_obj->parties) && count($contract_obj->parties) > 0) {
                    $parties = $release_db->getParties($contract_obj->parties);
                    $contract_obj->parties = $parties;
                }

                $release_db->addContractRelease($id, $mda_id, $contract_obj);
                $json_data = json_encode($contract_obj, JSON_PRETTY_PRINT);
                $release_path = FILE_ROOT . "app/releases/" . $contract_obj->id . ".json";
                $releasefile = fopen($release_path, "w");
                fwrite($releasefile, $json_data);
                $compile = $release_db->compilePackage($id, $contract_obj->ocid);
                $data_obj["message"] = "SuccessFully Saved the Release";
                $data_obj["ajaxstatus"] = "success";
                echo json_encode($data_obj);
                break;
            case "implementation":
                
                $json_data = $_POST["data"];
                $implementation_obj = json_decode($json_data);
                $release_db = $this->load_model('ReleaseDb');
                $transactions = [];
                foreach ($implementation_obj->transactions as $transact) {
                    $release_db->addImplementationRelease($implementation_obj, $id, $mda_id, $transact->value->amount, $transact->payer, $transact->payee);
                    $transact->payee = $release_db->getOCDOrganisation($transact->payee);
                    $transact->payer = $release_db->getOCDOrganisation($transact->payer);
                    $transactions[] = $transact;
                }
                ////Check Release File
                if (file_exists(FILE_ROOT . "app/releases/" . $implementation_obj->contractID . ".json")) {
                    
                    $contract_file = fopen(FILE_ROOT . "app/releases/" . $implementation_obj->contractID . ".json", "r");
                    $contract_json = fread($contract_file, 150000);
                    $contract_obj = json_decode($contract_json);
                    //$contract_obj->id = $implementation_obj->id;
                    $contract_obj->date = $implementation_obj->date;
                    $contract_obj->documents =$implementation_obj->documents;
                    $contract_obj->contract->implementation = $implementation_obj;
                    $json_data = json_encode($contract_obj, JSON_PRETTY_PRINT);
                    $release_path = FILE_ROOT . "app/releases/" . $implementation_obj->contractID . ".json";
                    //echo $release_path;
                    $releasefile = fopen($release_path, "w");
                    fwrite($releasefile, $json_data);
                } else {
                    $compiled_path = FILE_ROOT . "app/compiled/" . $implementation_obj->ocid . ".json";
                    if (!file_exists($compiled_path)) {
                        $data_obj["message"] = "Cannot Find Associated Release File";
                        $data_obj["ajaxstatus"] = "Failed";
                        echo json_encode($data_obj);
                        die();
                    }
                    $compiledRelease = file_get_contents($compiled_path);
                    $compiled_obj = json_decode($compiledRelease);


                    $contract_obj = isset($releases->contracts) ? $releases->contracts[0] : new stdclass;
                    $contract_obj->implementation = $implementation_obj;

                    $releases->contracts[0] = $contract_obj;
                    $compiled_obj->releases[0] = $releases;
                    $json = json_encode($compiled_obj, JSON_PRETTY_PRINT);
                    $release_path = FILE_ROOT . "app/compiled/" . $implementation_obj->ocid . ".json";
                    $releasefile = fopen($release_path, "w");
                    fwrite($releasefile, $json);


                }
                $compile = $release_db->compilePackage($id, $implementation_obj->ocid);
                $data_obj["message"] = "SuccessFully Saved the Release";
                $data_obj["ajaxstatus"] = "success";
                echo json_encode($data_obj);
                break;



        }
    }
    public function transactedit($type, $id, $mda_id)
    {
        $access = $this->checkLogin();
        if (!$access or $access > 1) {
            $object["ajaxstatus"] = "failed";
            $object["message"] = "You don't have clearance to perform this opperation";
            $data = json_encode($object);
            echo $data;
            die();
        }
        switch ($type) {
            case "planning":
                
                $json_data = $_POST["data"];
                $planning_obj = json_decode($json_data);
                $release_db = $this->load_model('ReleaseDb');
                $parties = $release_db->getParties($planning_obj->parties);
                $planning_obj->parties = $parties;
                $buyer = $release_db->getOCDOrganisation($mda_id);
                $planning_obj->buyer = $buyer;
                $release_db->editPlaningRelease($id, $mda_id, $planning_obj);
                $json_data = json_encode($planning_obj, JSON_PRETTY_PRINT);
                $release_path = FILE_ROOT . "app/releases/" . $planning_obj->id . ".json";
                $releasefile = fopen($release_path, "w") or die("fopen failed" . FILE_ROOT . "\n" . $_SERVER["DOCUMENT_ROOT"]);
                fwrite($releasefile, $json_data);
                $compile = $release_db->compilePackage($id, $planning_obj->ocid);
                $data_obj["message"] = "SuccessFully Edited the Release";
                $data_obj["ajaxstatus"] = "success";
                echo json_encode($data_obj);
                break;
            case "tender":
                
                $json_data = $_POST["data"];
                $tender_obj = json_decode($json_data);
                $release_db = $this->load_model('ReleaseDb');
                if (isset($tender_obj->parties) && is_array($tender_obj->parties) && count($tender_obj->parties) > 0 && !empty($tender_obj->parties)) {
                    $parties = $release_db->getParties($tender_obj->parties);
                    $tender_obj->parties = $parties;
                }
                if (isset($tender_obj->tender->tenderers) && !empty($tender_obj->tender->tenderers)) {
                    $tenderers = $release_db->getTenderers($tender_obj->tender->tenderers);
                    $tender_obj->tender->tenderers = $tenderers;
                }
                $release_db->editTenderRelease($id, $mda_id, $tender_obj);
                $json_data = json_encode($tender_obj, JSON_PRETTY_PRINT);
                $release_path = FILE_ROOT . "app/releases/" . $tender_obj->id . ".json";
                $releasefile = fopen($release_path, "w");
                fwrite($releasefile, $json_data);
                $compile = $release_db->compilePackage($id, $tender_obj->ocid);
                $data_obj["message"] = "SuccessFully Edited the Release";
                $data_obj["ajaxstatus"] = "success";
                echo json_encode($data_obj);
                break;
            case "award":
                
                $json_data = $_POST["data"];
                $award_obj = json_decode($json_data);
                $release_db = $this->load_model('ReleaseDb');
                //$award_obj->buyer = $release_db->getOCDOrganisation($id);
                if (isset($award_obj->parties) && is_array($award_obj->parties) && count($award_obj->parties) > 0) {
                    $parties = $release_db->getParties($award_obj->parties);
                    $award_obj->parties = $parties;
                }
                $supplier = $award_obj->award->suppliers[0];
            //$release_db->insertSuppliers($award_obj->award->suppliers,$id);
                $release_db->addContractors($award_obj->award->suppliers, $id);
                $award_obj->award->suppliers = $release_db->getSuppliers($award_obj->award->suppliers);
                $release_db->editAwardRelease($id,$mda_id,$supplier,$award_obj);
                $json_data = json_encode($award_obj, JSON_PRETTY_PRINT);
                $release_path = FILE_ROOT . "app/releases/" . $award_obj->id . ".json";
                $releasefile = fopen($release_path, "w");
                fwrite($releasefile, $json_data);
                $compile = $release_db->compilePackage($id, $award_obj->ocid);
                $data_obj["message"] = "SuccessFully Edited the Release";
                $data_obj["ajaxstatus"] = "success";
                echo json_encode($data_obj);
                break;
            case "contract":
                
                $json_data = $_POST["data"];
                $contract_obj = json_decode($json_data);
                $release_db = $this->load_model('ReleaseDb');
                //$contract_obj->buyer = $release_db->getOCDOrganisation($id);
                if (isset($contract_obj->parties) && is_array($contract_obj->parties) && count($contract_obj->parties) > 0) {
                    $parties = $release_db->getParties($contract_obj->parties);
                    $contract_obj->parties = $parties;
                }

                $release_db->editContractRelease($id, $mda_id, $contract_obj);
                $json_data = json_encode($contract_obj, JSON_PRETTY_PRINT);
                $release_path = FILE_ROOT . "app/releases/" . $contract_obj->id . ".json";
                $releasefile = fopen($release_path, "w");
                fwrite($releasefile, $json_data);
                $compile = $release_db->compilePackage($id, $contract_obj->ocid);
                $data_obj["message"] = "SuccessFully Edited the Release";
                $data_obj["ajaxstatus"] = "success";
                echo json_encode($data_obj);
                break;
            case "implementation":
                
                $json_data = $_POST["data"];
                $implementation_obj = json_decode($json_data);
                $release_db = $this->load_model('ReleaseDb');
                $transactions = [];
                foreach ($implementation_obj->transactions as $transact) {
                    $release_db->editImplementationRelease($implementation_obj, $id, $mda_id, $transact->value->amount, $transact->payer, $transact->payee);
                    $transact->payee = $release_db->getOCDOrganisation($transact->payee);
                    $transact->payer = $release_db->getOCDOrganisation($transact->payer);
                    $transactions[] = $transact;
                }
                $contract_file = fopen(FILE_ROOT . "app/releases/" . $implementation_obj->contractID . ".json", "r");
                $contract_json = fread($contract_file, 150000);
                $contract_obj = json_decode($contract_json);
                $contract_obj->id = $implementation_obj->id;
                $contract_obj->date = $implementation_obj->date;
                if (isset($implementation_obj->documents)) {
                    $contract_obj->contract->documents = $implementation_obj->documents;
                }
                $contract_obj->contract->transactions = $transactions;

                $json_data = json_encode($contract_obj, JSON_PRETTY_PRINT);
                $release_path = FILE_ROOT . "app/releases/" . $contract_obj->id . ".json";
                $releasefile = fopen($release_path, "w");
                fwrite($releasefile, $json_data);
                $data_obj["message"] = "SuccessFully Edited the Release";
                $data_obj["ajaxstatus"] = "success";
                echo json_encode($data_obj);
                break;



        }
    }
    public function ajaxdocument($type)
    {
        if (is_uploaded_file($_FILES["file"]["tmp_name"])) {
            $folder = $type == 'x_siteImages'? 'images/siteimages/': 'release_docs';
            if (move_uploaded_file($_FILES["file"]["tmp_name"], WEB_ROOT.$folder."/" . $_FILES["file"]["name"])) {
                $id = substr(md5(time()), 0, 5);
                $name = $id;
                $ext = explode(".", $_FILES["file"]["name"]);
                $name = $name . "." . $ext[count($ext) - 1];
                
                $newname = WEB_ROOT .$folder."/" . $name;
                $type = $_FILES["file"]["type"];
                rename(WEB_ROOT .$folder."/" . $_FILES["file"]["name"], $newname);
                $data_obj["url"] = $folder."/" . $name;
                $data_obj["id"] = $id;
                $data_obj["ajaxstatus"] = "success";
                $data_obj["message"] = "File upload successful";
                echo json_encode($data_obj);
            }
        } else {
            $data_obj["ajaxstatus"] = "failed";
            $data_obj["message"] = "temp file location not available";
            echo json_encode($data_obj);
        }
    }
    public function delajaxdocument($type)
    {
        $folder = $type == 'x_siteImage'? 'images/siteimages': 'release_docs';
        $data_obj = [];
        $access = $this->checkLogin();
        if (!$this->checkRequestMethod("POST") or !$access) {
            $data_obj["message"] = "Forbidden Access";
            $data_obj["ajaxstatus"] = "Failed";
            $json = json_encode($data_obj);
            echo $json;
            die();
        }
        $doc_path = explode("/", $_POST["data"]);
        $name = $doc_path[count($doc_path) - 1];
        $path = WEB_ROOT .$folder."/" . $name;
        if (file_exists($path)) {
            unlink($path);
            $data_obj["message"] = "File Successfully deleted";
            $data_obj["ajaxstatus"] = "success";
            $json = json_encode($data_obj);
            echo $json;
            die();
        } else {
            $data_obj["message"] = "File does not exists";
            $data_obj["ajaxstatus"] = "Failed";
            $json = json_encode($data_obj);
            echo $json;
            die();
        }
    }
    public function edit($id, $type)
    {
        if (!$this->checkLogin()) {
            die();
        }
        $type = strtolower($type);
        switch ($type) {
            case "planning":
                
                $release_db = $this->load_model('ReleaseDb');
                $release_array = $release_db->getRelease($id, $type);
                $project_id = $release_array["project_id"];
                $mda_id = $release_array["mda_id"];
                $rel_id = $id;
                $rel_type = "planning";
                $obj = $release_db->getReleaseJSON($release_array["release_id"]);
                $date = date("Y-m-d",strtotime($obj->date));
                $parties_html = "";
                $milestones_html = "";
                require_once("../app/views/backend/edit-planning-release.html");
                break;
            case "tender":
                
                $release_db = $this->load_model('ReleaseDb');
                $release_array = $release_db->getRelease($id, $type);
                $project_id = $release_array["project_id"];
                $rel_id = $id;
                $rel_type = 'tender';
                $mda_id = $release_array["mda_id"];
                $obj = $release_db->getReleaseJSON($release_array["release_id"]);
                $date = date("d/m/Y",strtotime($obj->date));
                $tenderers = $release_db->get_edit_tenderers($obj->tender->tenderers);
                //$tenderers = "";
                
                $today = date("Y-m-d");
                require_once("../app/views/backend/edit-tender-release.html");
                break;
            case "award":
                
                $release_db = $this->load_model('ReleaseDb');
                $release_array = $release_db->getRelease($id, $type);
                $project_id = $release_array["project_id"];
                $mda_id = $release_array["mda_id"];
                $rel_id = $id;
                $rel_type = 'award';
                $obj = $release_db->getReleaseJSON($release_array["release_id"]);
                $suppliers = $release_db->get_edit_tenderers($obj->award->suppliers);
                $today = date("Y-m-d");
                require_once("../app/views/backend/edit-award-release.html");
                break;
            case "contract":
                
                $release_db = $this->load_model('ReleaseDb');
                $release_array = $release_db->getRelease($id, $type);
                $project_id = $release_array["project_id"];
                $mda_id = $release_array["mda_id"];
                $rel_type = 'contract';
                $rel_id = $id;
                $release_id = $release_array["release_id"];
                $award_releases = $release_db->fetchReleases("award", $project_id);
                $obj = $release_db->getReleaseJSON($release_array["release_id"]);
                $today = date("Y-m-d");
                require_once("../app/views/backend/edit-contract-release.html");
                break;
            case "implementation":
                
                $release_db = $this->load_model('ReleaseDb');
                $release_array = $release_db->getRelease($id, $type);
                $project_id = $release_array["project_id"];
                $rel_id = $id;
                $obj = $release_db->getReleaseJSON($release_array["release_id"]);
                $today = date("Y-m-d");
                require_once("../app/views/backend/edit-tender-release.html");
                break;
        }
    }
    public function ajaxGetRelease($id, $type)
    {
        
        $release_db = $this->load_model('ReleaseDb');
        $release_array = $release_db->getRelease($id, $type);
        $obj = $release_db->getReleaseJSON($release_array["release_id"]);
        $return_obj = $this->getArrays($id, $type);
        $return_obj->release = $obj;
        echo json_encode($return_obj);
    }
    public function delete($id, $type)
    {
        $data_obj = [];
        $access = $this->checkLogin();
        if (!$access) {
            $data_obj["message"] = "You are not authorized to perform this action";
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
        
        $release_db = $this->load_model('ReleaseDb');
        $release_db->delete($id, $type);
        $data_obj["message"] = "Release Deleted";
        $data_obj["ajaxstatus"] = "success";
        $json = json_encode($data_obj);
        echo $json;
        die();

    }
    public function getArrays($id, $type)
    {
        if (!$this->checkLogin()) {
            die("Unauthorized");
        }
        if (!isset($id) and !isset($type)) {
            die("Wrong Params");
        }

        
        $release_db = $this->load_model('ReleaseDb');
        $return_obj = new stdClass;
        $release_array = $release_db->getRelease($id, $type);
        $obj = $release_db->getReleaseJSON($release_array["release_id"]);
        $return_obj->parties = empty($obj->parties) ? [] : $release_db->getJavaParties($obj->parties);
        $return_obj->milestones = empty($obj->$type->milestones) ? [] : $obj->$type->milestones;
        $return_obj->items = empty($obj->$type->items) ? [] : $obj->$type->items;
        $return_obj->documents = empty($obj->$type->documents) ? [] : $obj->$type->documents;
        $return_obj->amendments = empty($obj->$type->amendments) ? [] : $obj->$type->amendments;
        $return_obj->tenderers = empty($obj->$type->tenderers) ? [] : $release_db->getJavaParties($obj->$type->tenderers);
        $return_obj->ajaxstatus = "success";
        $return_obj->message = "Thank you for waiting";
        return $return_obj;
    }
    public function compileRelease()
    {
        
        $release_db = $this->load_model('ReleaseDb');;

        $release = $release_db->compilePackage(1264, "ocds-azam7x-a405e2ng-UBEC");
        echo $release;

    }

}


?>