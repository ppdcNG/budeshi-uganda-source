<?php
class ReleaseDb extends Model
{
    public $projectName;

    public function getRelease($id, $type)
    {
        $query = "SELECT r.*, p.mda_id FROM  releases r JOIN projects p ON r.project_id = p.id  WHERE r.id = " . $id;
        $result = $this->query($query);
        if (!$result) {
            die($this->error);
        }
        $result = mysqli_fetch_assoc($result);
        return $result;

    }
    public function getReleaseJSON($release_id)
    {
        $path = FILE_ROOT . "app/releases/" . $release_id . ".json";
        $release = json_decode(file_get_contents($path));
        return $release;

    }
    public function setJSONSupplier($project_id, $contractor_id, $contract, $budget){
        $ocid = $this->getOCID($project_id);
        $supplier = $this->getOCDOrganisation($contractor_id);
        $filepath = FILE_ROOT."compiled/".$ocid.".json";
        if(file_exists($filepath)){
            $obj = json_decode(file_get_contents($filepath));
            if(!empty($obj->version) and $obj->version == 1.1){
                $compObj = $obj->records[0]->compiledRelease;
                $compObj->awards[0]->suppliers[0] = $supplier;
                $compObj->planning->budget->amount->amount = $budget;
                $compObj->contracts[0]->value->amount = $contract;
                $compObj->awards[0]->value->amount = $contract;
                $obj->records[0]->compiledRelease = $compObj;
                $fobj = fopen($filepath,"w");
                fwrite($fobj,json_encode($obj,JSON_PRETTY_PRINT));
                fclose($fobj);
            }
            else{
                //echo json_encode($obj);
                $compObj = $obj->releases[0];
                if(isset($compObj->awards)) $compObj->awards[0]->suppliers[0] = $supplier;
                if(isset($compObj->planning)) $compObj->planning->budget->amount->amount = $budget;
                if(isset($compObj->contracts)) $compObj->contracts[0]->value->amount = $contract;
                if(isset($compObj->awards)) $compObj->awards[0]->value->amount = $contract;
                $obj->releases[0] = $compObj;
                $fobj = fopen($filepath,"w");
                fwrite($fobj,json_encode($obj,JSON_PRETTY_PRINT));
                fclose($fobj);
            }
            

        }
    }
    public function getOCID($id)
    {
        $query = "SELECT oc_id, title FROM projects WHERE id = " . $id;
        $result = $this->query($query);
        if (!$result) {
            die($this->error);
        }
        if (mysqli_num_rows($result) <= 0) {
            die("empty result set");
        }
        $row = mysqli_fetch_assoc($result);
        $oc_id = $row["oc_id"];
        $this->projectName = $row["title"];
        return $oc_id;
    }
    public function getNextId($type, $id)
    {
        $query = "SELECT COUNT(id) FROM releases WHERE project_id = " . $id. " AND type = '".$type."'";
        $result = $this->query($query);
        if ($result) $release_id = mysqli_fetch_array($result)[0];
        else $release_id = 0;
        $release_id = sprintf("%04d", ($release_id + 1));
        return $release_id;
    }
    public function getOrganisations($search_txt, $table = 'institutions')
    {
        $query = "SELECT id, name FROM ".$table." WHERE name LIKE '%" . $search_txt . "%' LIMIT 10";
        $result = $this->query($query);
        if (!$result) {
            die($this->error);
        }
        $output = array();
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data = new stdClass;
                $data->id = $row["id"];
                $data->text = $row["name"];
                $output[] = $data;
            }
        }
        return $output;


    }
    public function getParties($parties)
    {
        $parties_to_return = [];
        //build query to fetch each party
        $ids = [];
        $map = [];
        foreach ($parties as $party) {
            if (!empty($party)) {
                $ids[] = $party->id;
                $map[$party->id] = $party;
            }
        }
        if (!empty($ids)) {
            $ids = "(" . implode(",", $ids) . ")";
            $query = "SELECT * FROM institutions WHERE id IN " . $ids;
            $result = $this->query($query);
            if (!$result) {
                die($this->error);
            }
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $party_obj = new StdClass;
                    $identifier = new StdClass;
                    $contact = new StdClass;
                    $address = new StdClass;

                    $identifier->scheme = "CAC-RC";
                    $identifier->legalName = $row["name"];
                    $identifier->id = $row["ug_no"];
                    $identifier->uri = "http://publicsearch.cac.gov.ng/comsearch/";

                    $address->streetAddress = $row["address"];
                    $address->locality = $row["lga"];
                    $address->region = $row["state"];
                    $address->postalCode = $row["postal_code"];
                    $address->countryName = "Nigeia";

                    $contact->name = $row["name"];
                    $contact->telephone = $row["phone"];
                    $contact->email = $row["email"];
                    $contact->url = $row["url"];
                    $contact->faxNumber = "";

                    $party_obj->identifier = $identifier;
                    $party_obj->address = $address;
                    $party_obj->contactPoint = $contact;
                    $party_obj->roles = $map[$row["id"]]->roles;
                    $party_obj->id = $row["id"];

                    $parties_to_return[] = $party_obj;
                }
            }
        }
        return $parties_to_return;
    }
    public function getOCDOrganisation($id)
    {
        $query = "SELECT * FROM institutions WHERE id = " . $id;
        $result = $this->query($query);
        if (!$result) {
            die($this->error);
        }
        $party_obj;
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $party_obj = new StdClass;
            $identifier = new StdClass;
            $contact = new StdClass;
            $address = new StdClass;

            $identifier->scheme = "CAC-RC";
            $identifier->legalName = $row["name"];
            $identifier->id = $row["ug_no"];
            $identifier->uri = "http://publicsearch.cac.gov.ng/comsearch/";

            $address->streetAddress = $row["address"];
            $address->locality = $row["lga"];
            $address->region = $row["state"];
            $address->postalCode = $row["postal_code"];
            $address->countryName = "Nigeia";

            $contact->name = $row["name"];
            $contact->telephone = $row["phone"];
            $contact->email = $row["email"];
            $contact->url = $row["url"];
            $contact->faxNumber = "";

            $party_obj->identifier = $identifier;
            $party_obj->address = $address;
            $party_obj->contactPoint = $contact;
            $party_obj->id = $id;
        }
        return $party_obj;
    }
    public function get_edit_tenderers($array){
        if(!is_array($array)){
            return false;
        }
        $html = "";
        foreach($array as $tenderer){
            $html .= "<option value = '".$tenderer->id."'>".$tenderer->identifier->legalName."</option>";

        }
        return $html;
    }
    public function getTenderers($tenderers)
    {
        $parties_to_return = [];
        //build query to fetch each party
        $ids = [];
        foreach ($tenderers as $id) {
            $ids[] = (int)$id;
        }
        $ids = "(" . implode(",", $ids) . ")";
        $query = "SELECT * FROM institutions WHERE id IN " . $ids;
        $result = $this->query($query);
        if (!$result) {
            die($this->error);
        }
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $party_obj = new StdClass;
                $identifier = new StdClass;
                $contact = new StdClass;
                $address = new StdClass;

                $identifier->scheme = "CAC-RC";
                $identifier->legalName = $row["name"];
                $identifier->id = $row["ug_no"];
                $identifier->uri = "http://publicsearch.cac.gov.ng/comsearch/";

                $address->streetAddress = $row["address"];
                $address->locality = $row["lga"];
                $address->region = $row["state"];
                $address->postalCode = $row["postal_code"];
                $address->countryName = "Nigeia";

                $contact->name = $row["name"];
                $contact->telephone = $row["phone"];
                $contact->email = $row["email"];
                $contact->url = $row["url"];
                $contact->faxNumber = "";

                $party_obj->identifier = $identifier;
                $party_obj->address = $address;
                $party_obj->contactPoint = $contact;
                $party_obj->id = $row["id"];

                $parties_to_return[] = $party_obj;
            }
        }
        return $parties_to_return;
    }
    public function getPartiesID($party_obj)
    {
        $query = "SELECT id, name FROM institutions WHERE ug_no = " . $party_obj->identifier->id;
        $result = $this->query($query);
        if (!$result) {
            die($this->error);
        }
        $result = mysqli_fetch_assoc($result);
        return $result;
    }
    public function getJavaParties($parties)
    {
        $listoreturn = [];
        $ids = [];
        $roles = [];
        foreach ($parties as $party) {
            $ids[] = $party->id;
            //echo json_encode($party);
            $roles[$party->id] = $party->id;
        }
        $nos = implode(",", $ids);
        $nos = "(" . $nos . ")";

        $query = "SELECT id, name, ug_no FROM institutions WHERE id  IN " . $nos;
        $result = $this->query($query);
        if (!$result) {
            die($this->error);
        }
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $obj = new StdClass;
                $obj->id = $row["id"];
                $obj->roles = $roles[$row["id"]];
                $obj->name = $row["name"];
                $listoreturn[] = $obj;

            }
        }
        return $listoreturn;
    }
    public function addPlanningRelease($id, $mda_id, $obj)
    {
        
        $year = explode("-", $obj->date)[0];
        $date_mod = date("Y-m-d H:i:s");
        $amount = empty($obj->planning->budget->amount->amount)? "NULL": $obj->planning->budget->amount->amount;
        $query = "INSERT INTO planning (release_id, project_id, title, budget_amount, description, year, updated_by, date_modified,mda_id) VALUES ('" .
            $obj->id . "'," . $id . ",'" .mysqli_real_escape_string($this->conn, $obj->planning->budget->project) . "','" . $obj->planning->budget->amount->amount . "', '" .mysqli_real_escape_string($this->conn, $obj->planning->budget->description) . "', '" .
            $year . "'," . $_SESSION["id"] . ",'" . $date_mod . "'," . $mda_id . ") ON DUPLICATE KEY UPDATE release_id = '".$obj->id."', title = '".mysqli_real_escape_string($this->conn, $obj->planning->budget->project)."', 
            budget_amount = '".$amount."', description = '".mysqli_real_escape_string($this->conn, $obj->planning->budget->description)."', year = '".$year."', updated_by =".$_SESSION["id"]. 
            ", date_modified = '".$date_mod."'";
        $result = $this->query($query);
        if (!$result) {
            die($this->error);
        }
        $query = "INSERT INTO releases (release_id, project_id, type) VALUES ('".$obj->id."', '".$id."','planning') ";
        $result = $this->query($query);

    }
    public function editPlaningRelease($id, $mda_id, $obj)
    {
        $data = [];
        $data["release_id"] = $obj->id;
        $data["project_id"] = $id;
        $data["title"] = mysqli_real_escape_string($this->conn,$obj->planning->budget->project);
        $data["budget_amount"] = $obj->planning->budget->amount->amount;
        $data["description"] = mysqli_real_escape_string($this->conn,$obj->planning->budget->description);
        $data["year"] = explode($obj->date, "-")[0];
        $data["updated_by"] = $_SESSION["id"];
        $data["date_modified"] = date("Y-m-d H:i:s");
        $data["mda_id"] = $mda_id;
        $this->update($obj->id, $data, "planning");
    }
    public function addTenderRelease($id, $mda_id, $obj)
    {
        $year = explode($obj->date, "-")[0];
        $date_mod = date("Y-m-d H:i:s");
        $start_date = date("Y-m-d", strtotime($obj->tender->tenderPeriod->startDate));
        $end_date = date("Y-m-d", strtotime($obj->tender->tenderPeriod->endDate));
        $no = count($obj->tender->tenderers);
        $criteria = empty($obj->tender->awardCriteria)? "N/A": $obj->tender->awardCriteria;
        $procurementMethod = empty($obj->tender->procurementMethod ) ? "N/A": $obj->tender->procurementMethod;
        $category = empty($obj->tender->mainProcurementCategory) ? "N/A": $obj->tender->mainProcurementCategory;

        $tender_amount = empty($obj->tender->value->amount)? 0 : $obj->tender->value->amount;

        $query = "INSERT INTO tender (title, description, status, mda_id, amount, procurement_method, award_criteria, start_date, end_date, no_of_tenderers,
        project_id, release_id, category, date_modified, updated_by ) VALUES ('" . mysqli_real_escape_string($this->conn,$obj->tender->title) . "','" .mysqli_real_escape_string($this->conn, $obj->tender->description) . "','" . $obj->tender->status . "', " . $mda_id . "," . $tender_amount . ",
        '" . $procurementMethod. "','" . $criteria . "','" . $start_date . "','" . $end_date . "'," . $no . "," . $id . ",'" . $obj->id . "','" . $obj->tender->mainProcurementCategory . "','" .
            $date_mod . "'," . $_SESSION["id"] . ") ON DUPLICATE KEY UPDATE release_id = '".$obj->id."', title = '".mysqli_real_escape_string($this->conn,$obj->tender->title)."', description = '".mysqli_real_escape_string($this->conn,$obj->tender->description)."', 
            status = '".$obj->tender->status."', mda_id = ".$mda_id.", amount = ".$tender_amount.", procurement_method = '".$procurementMethod."', award_criteria = '".$criteria."', 
            start_date = '".$start_date."', end_date = '".$end_date."', no_of_tenderers = ".$no.", project_id = ".$id.", category = '".$category."',
            date_modified = '".$date_mod."', updated_by = ".$_SESSION["id"];
        $result = $this->query($query);
        if (!$result) {
            die($this->error);
        }
        $query = "INSERT INTO releases (release_id, project_id, type) VALUES ('".$obj->id."', '".$id."','tender') ";
        $result = $this->query($query);
    }
    public function editTenderRelease($id, $mda_id, $obj)
    {
        $data = [];
        $data["title"] = mysqli_real_escape_string($this->conn,$obj->tender->title);
        $data["description"] = mysqli_real_escape_string($this->conn,$obj->tender->description);
        $data["status"] = $obj->tender->status;
        $data["mda_id"] = $mda_id;
        $data["amount"] = $obj->tender->value->amount;
        $data["procurement_method"] = $obj->tender->procurementMethod;
        $data["award_criteria"] = $obj->tender->awardCriteria;
        $data["start_date"] = date("Y-m-d", strtotime($obj->tender->tenderPeriod->startDate));
        $data["end_date"] = date("Y-m-d", strtotime($obj->tender->tenderPeriod->endDate));
        $data["no_of_tenderers"] = count($obj->tender->tenderers);
        $data["project_id"] = $id;
        $data["release_id"] = $obj->id;
        $data["category"] = $obj->tender->mainProcurementCategory;
        $data["date_modified"] = date("Y-m-d H:i:s");
        $data["updated_by"] = $_SESSION["id"];
        $this->update($obj->id, $data, "tender");
    }
    public function addAwardRelease($id, $mda_id, $contractor, $obj)
    {
        $date_mod = date("Y-m-d H:i:s");
        $by = $_SESSION['id'];
        $no_contrs = count($obj->award->suppliers);
        $status = empty($obj->award->status) ? NULL: $obj->award->status; 
        $start_date = empty($obj->award->contractPeriod->startDate) ? NULL : $obj->award->contractPeriod->startDate;
        $end_date = empty($obj->award->contractPeriod->endDate)? NULL : $obj->award->contractPeriod->endDate;
        $query = "INSERT INTO award(oc_id, project_id, status, title, description, award_date, start_date, end_date, contractor_id, release_id, updated_by, no_of_contractors, date_modified, mda_id) VALUES ('" .
            $obj->ocid . "'," . $id . ", '" . $status . "', '" .mysqli_real_escape_string($this->conn, $obj->award->title) . "','" .mysqli_real_escape_string($this->conn, $obj->award->description) . "','" . $obj->award->date . "','" . $start_date . "','" . $end_date .
            "'," . $contractor . ",'" . $obj->id . "'," . $by . "," . $no_contrs . ",'" . $date_mod . "'," . $mda_id . ") ON DUPLICATE KEY UPDATE status = '".$status."', title = '".mysqli_real_escape_string($this->conn,$obj->award->title)."', description = '".mysqli_real_escape_string($this->conn,$obj->award->description)."' 
            , award_date = '".$obj->award->date."', start_date = '".$start_date."', end_date = '".$end_date."', contractor_id = '".$contractor."', release_id = '".$obj->id."', updated_by = ' 
            ".$by."', no_of_contractors = '".$no_contrs."', date_modified = '".$date_mod."', mda_id = ".$mda_id;
        $result = $this->query($query);
        if (!$result) {
            die($this->error);
        }
        $query = "INSERT INTO releases (release_id, project_id, type) VALUES ('".$obj->id."', '".$id."','award')";
        $result = $this->query($query);
    }
    public function editAwardRelease($id, $mda_id, $contractor, $obj)
    {
        $data = [];
        $data["oc_id"] = $obj->ocid;
        $data["project_id"] = $id;
        $data["status"] = $obj->award->status;
        $data["title"] = mysqli_real_escape_string($this->conn,$obj->award->title);
        $data["description"] = mysqli_real_escape_string($this->conn,$obj->award->description);
        $data["award_date"] = $obj->award->date;
        $data["start_date"] = $obj->award->contractPeriod->startDate;
        $data["end_date"] = $obj->award->contractPeriod->endDate;
        $data["contractor_id"] = $contractor;
        $data["release_id"] = $obj->id;
        $data["updated_by"] = $_SESSION["id"];
        $data["no_of_contractors"] = count($obj->award->suppliers);
        $data["date_modified"] = date("Y-m-d H:i:s");
        $data["mda_id"] = $mda_id;
        $this->update($obj->id, $data, "award");

        $c_data['project_id'] = $id;
        $c_data['contractor_id'] = $contractor;
        $this->update($id,$c_data,"contractors","project_id");
    }
    public function addContractRelease($id, $mda_id, $obj)
    {
        $date_mod = date("Y-m-d H:i:s");
        $by = $_SESSION['id'];
        $date = date("Y-m-d", strtotime($obj->date)); 
        $start_date = empty($obj->contract->period->startDate) ? "NULL": $obj->contract->period->startDate;
        $end_date =   empty($obj->contract->period->endDate) ? "NULL": $obj->contract->period->endDate;

        $query = "INSERT INTO contract(project_id, award_id,description, title, date, start_date, end_date, 
        release_id, date_modified, updated_by, amount, mda_id) VALUES (" . $id . ",'" . $obj->contract->awardID . "','" .
            mysqli_real_escape_string($this->conn,$obj->contract->description) . "','" .mysqli_real_escape_string($this->conn, $obj->contract->title) . "', '" . $date . "','" .$start_date . "','" . $end_date . "',
        '" . $obj->id . "','" . $date_mod . "'," . $by . "," . $obj->contract->value->amount . "," . $mda_id . ") ON DUPLICATE KEY UPDATE award_id = '". $obj->contract->awardID."', 
        description = '".mysqli_real_escape_string($this->conn,$obj->contract->description)."', title = '".mysqli_real_escape_string($this->conn,$obj->contract->title)."', date = '".$date."', start_date = '".$start_date."', end_date = '".$end_date."', release_id = '".
        $obj->id."', date_modified = '".$date_mod."', updated_by = '".$_SESSION["id"]."', amount = '".$obj->contract->value->amount."', mda_id = ".$mda_id;
        $result = $this->query($query);
        if (!$result) {
            die($this->error);
        }
        $query = "INSERT IGNORE INTO releases (release_id, project_id, type) VALUES ('".$obj->id."', '".$id."','contract')";
        $result = $this->query($query);

    }
    public function editContractRelease($id, $mda_id, $obj)
    {
        $data = [];
        $data["project_id"] = $id;
        $data["award_id"] = $obj->contract->awardID;
        $data["description"] = mysqli_real_escape_string($this->conn,$obj->contract->description);
        $data["title"] = mysqli_real_escape_string($this->conn,$obj->contract->title);
        $data["date"] = date("Y-m-d");
        $data["start_date"] = $obj->contract->period->startDate;
        $data["end_date"] = $obj->contract->period->endDate;
        $data["release_id"] = $obj->id;
        $data["date_modified"] = date("Y-m-d");
        $data["updated_by"] = $_SESSION['id'];
        $data["amount"] = $obj->contract->value->amount;
        $data["mda_id"] = $mda_id;
        $this->update($id, $data, "contract","project_id");
    }
    public function addImplementationRelease($obj, $id, $mda_id, $amount, $payer_id, $payee_id)
    {
        $date_mod = date("Y-m-d H:i:s");
        $by = $_SESSION['id'];
        $date = date("Y-m-d");
        $query = "INSERT INTO implementation(release_id, value, payer_id, payee_id, project_id, contract_id, updated_by, date_modified,mda_id) VALUES ('"
            . $obj->id . "'," . $amount . "," . $payer_id . "," . $payee_id . "," . $id . ",'" . $obj->contractID . "'," . $by . ",'" . $date_mod . "', " . $mda_id . ") ON DUPLICATE 
            KEY UPDATE release_id = '".$obj->id."', value = '".$amount."', payer_id = ".$payer_id.", payee_id = ".$payee_id.", contract_id = '".$obj->contractID."', updated_by = ".
            $by.", date_modified = '".$date_mod."', mda_id = ".$mda_id;
        $result = $this->query($query);
        if (!$result) {
            die($this->error);
        }
        $query = "INSERT INTO releases (release_id, project_id, type) VALUES ('".$obj->id."', '".$id."','implementation') ";
        $result = $this->query($query);


    }
    public function delete($id, $type)
    {
        $query = "SELECT release_id FROM releases WHERE project_id = " . $id. " AND type = '".$type."'";
        $result = $this->query($query);
        if (!$result) {
            die($this->error);
        }
        $release_id = mysqli_fetch_array($result)[0];
        $path = FILE_ROOT . "app/releases/" . $release_id . ".json";
        if(file_exists($path)){
            $obj = json_decode(file_get_contents($path));
            unlink($path);
        }
        $query = "DELETE FROM releases WHERE project_id = ".$id." AND type = '".$type."' LIMIT 1";
        $result = $this->query($query);
        $query = "SELECT project_id FROM releases WHERE project_id = ".$id;
        $result = $this->query($query);
        if(mysqli_num_rows($result) < 1){
            $query = "DELETE FROM " . $type . " WHERE project_id = " . $id . " LIMIT 1";
            $result = $this->query($query);
            if (!$result) {
                die($this->error);
            }

        }
        $this->compilePackage($id, $obj->ocid);

    }
    public function editImplementationRelease($obj, $id, $mda_id, $amount, $payer_id, $payee_id)
    {
        $data["release_id"] = $obj->id;
        $data["value"] = $amount;
        $data["payer_id"] = $payer_id;
        $data["payee_id"] = $payee_id;
        $data["contract_id"] = $obj->contractID;
        $data["project_id"] = $id;
        $data["updated_by"] = $_SESSION['id'];
        $data["mda_id"] = $mda_id;
        $data["date_modified"] = date("Y-m-d");

        $this->update($obj->id, $data, "implementation");
    }
    public function fetchReleases($table = "planning", $id)
    {
        $release = "";
        $query = "SELECT release_id, title FROM " . $table . " WHERE project_id = " . $id;
        $result = $this->query($query);
        if (!$result) {
            die($this->error);
        }
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $value = $row["release_id"];
                $name = $row["title"];
                $release .= $this->renderOption($name, $value);
            }
        }
        else {
            $release = "<option value = ''><em>there are no " . $table . " releases yet</em></option>";
        }
        return $release;
    }
    public function renderOption($name, $value)
    {
        $optionSelect = "<option value = '" . $value . "'>" . $name . "</option>";
        return $optionSelect;
    }
    public function partyRender($id, $name)
    {
        $party_html = "<div id='party-card" . $id . "' class='uk-section-mute uk-hover'>
                <table class='uk-table uk-table-divider'>
                <tbody><tr><td>" . $name . "</td>
                <td><a  id='remove-party' title='Delete Party' uk-tooltip='pos: bottom' class='uk-float-right'
                 onclick='removeParty(\"" . $id . "\")' ><span class= 'uk-margin-small-right' uk-icon='icon: trash'></span></a>
                </td>
                </tr>
               </tbody>
             </table>
            </div>";
        return $party_html;
    }
    public function milestoneRender($id)
    {
        $mile_html = '<div id = "milestone' . $id . '">
                            <div class="uk-card uk-card-default uk-card-body">Milestone ' . $id . '
                            <a id = "del" onclick = "removeMilestone(\'' . $id . '\')"  uk-tooltip=\'pos: bottom\' class=\'uk-float-right\'
                            ><span class= \'uk-margin-small-right\' uk-icon=\'icon: trash\'></span></a>
                                </div>
                            </div>';
        return $mile_html;
    }
    public function itemRender($id, $name, $quantity)
    {
        $item_html = '<div id=\'item-card' . $id . '\'>
                            <div class="uk-card uk-card-small uk-card-secondary uk-card-hover uk-card-body uk-light uk-margin">
                                <h3 class="uk-card-title" id=\'item-des-card-display\'>' . $name . '</h3>
                                <div class=\'uk-display-block\'>
                                    Quantity:' . $quantity . '
                                </div>
                                <div class="uk-card-footer">
                                    <a href="#view-item" title="View Item" uk-tooltip="pos: bottom" uk-toggle class=\'uk-float-left\'><span class="uk-margin-small-right" uk-icon="icon: expand"></span></a>
                                    <a href="#" id=\'remove-item\' title="Delete Item" uk-tooltip="pos: bottom" class=\'uk-float-right\' onclick="removeItem(\'' . $id . '\')" data-message="<span uk-icon=\'icon: check\'></span> Removed Item"
                                        data-status="success"><span class="uk-margin-small-right" uk-icon="icon: trash"></span></a>
                                </div>

                            </div>
                        </div>';
        return $item_html;
    }
    public function documentRender($id, $title, $type, $format, $path)
    {
        $document_html = '<div id=\'' . $id . '\'>
                            <div class="uk-card uk-card-small uk-card-secondary uk-card-hover uk-card-body uk-light uk-margin">
                                <h3 class="uk-card-title">' . $title . '</h3>
                                <div class=\'uk-display-block\'>' .
            $format .
            '</div>
                                <div class=\'uk-display-block\'>' .
            $type . '
                                </div>
                                <div class="uk-card-footer">
                                    <a href="#view-document" title="View Document" uk-tooltip="pos: bottom" uk-toggle class=\'uk-float-left\'><span class="uk-margin-small-right" uk-icon="icon: expand"></span></a>
                                    <a href="#" id=\'remove-ducument\' title="Delete Document" uk-tooltip="pos: bottom" class=\'uk-float-right\' onclick="removeDocument(\'' . $id . '\',\'' . $path . '\')"
                                        data-message="<span uk-icon=\'icon: check\'></span> Removed Document" data-status="success"><span class="uk-margin-small-right" uk-icon="icon: trash"></span></a>
                                </div>
                            </div>
                        </div>';
        return $document_html;
    }
    public function amendmentRender($id, $description, $rationale)
    {
        $amend_html = '
                        <div id=\'amendment-card' . $id . '\'>
                            <div class="uk-card uk-card-small uk-card-secondary uk-card-hover uk-card-body uk-light uk-margin">
                                <h3 class="uk-card-title">' . $description . '</h3>
                                <div class=\'uk-display-block\'>' .
            $rationale .
            '</div>
                                <div class="uk-card-footer">
                                    <a href="#view-amendment" title="View Amendment" uk-tooltip="pos: bottom" uk-toggle class=\'uk-float-left\'><span class="uk-margin-small-right" uk-icon="icon: expand"></span></a>
                                    <a href="#" id=\'remove-amendment\' title="Delete Amendment" uk-tooltip="pos: bottom" class=\'uk-float-right\' onclick="removeAmendment(\'' . $id . '\')"
                                        data-message="<span uk-icon=\'icon: check\'></span> Removed Party" data-status="success"><span class="uk-margin-small-right" uk-icon="icon: trash"></span></a>
                                    
                                </div>
                            </div>
                        </div>';
        return $amend_html;
    }
    public function transactionRender($id, $title, $amount, $currency = "NGN")
    {
        $transact_html = '<div id=\'transaction-card' . $id . '\'>
                            <div class="uk-card uk-card-small uk-card-secondary uk-card-hover uk-card-body uk-light uk-margin">
                                <h3 class="uk-card-title">' . $title . '</h3>
                                <div class=\'uk-display-block\'>
                                    ' . $amount . ': ' . $currency . '
                                </div>
                                <div class="uk-card-footer">
                                    <a href="#view-transaction" title="View Party" uk-tooltip="pos: bottom" uk-toggle class=\'uk-float-left\'><span class="uk-margin-small-right" uk-icon="icon: expand"></span></a>
                                    <a href="#" id=\'remove-transaction\' title="Delete Transaction" uk-tooltip="pos: bottom" class=\'uk-float-right\' onclick="removeTransaction(' . $id . ')"
                                        data-message="<span uk-icon=\'icon: check\'></span> Removed Transaction" data-status="success"><span class="uk-margin-small-right" uk-icon="icon: trash"></span></a>
                                </div>
                            </div>
                        </div>';
        return $transact_html;
    }
    public function partyHTML($obj)
    {
        $parties_html = "";
        if (isset($obj->parties) && is_array($obj->parties)) {
            $parties = $obj->parties;
            $count = 0;
            foreach ($parties as $party_obj) {
                $party = $this->getPartiesID($party_obj);
                $parties_html .= $this->partyRender($count, $party["name"]);
                $count++;
            }
        }
        return $parties_html;
    }
    public function milestonesHTML($obj)
    {
        $milestones_html = "";
        if (isset($obj->milestones) && is_array($obj->milestones)) {
            $milestones = $obj->milestones;
            $count = 1;
            for ($i = 1; $i <= count($milestones); $i++) {
                $milestones_html .= $this->milestoneRender($count);
                $count++;
            }
        }
        return $milestones_html;
    }
    public function itemsHTML($obj)
    {
        $items_html = "";
        if (isset($obj->items) && is_array($obj->items)) {
            $items = $obj->items;
            $count = 0;
            foreach ($items as $item) {
                $items_html .= $this->itemRender($count, $item->description, $item->quantity);
                $count++;
            }
        }
        return $items_html;

    }
    public function documentHTML($obj)
    {
        $doc_html = "";
        if (isset($obj->documents) && is_array($obj->documents)) {
            $documents = $obj->documents;
            $count = 0;
            foreach ($documents as $doc) {
                $doc_html .= $this->documentRender($count, $doc->title, $doc->documentType, $doc->format, $doc->uri);
                $count++;
            }
        }
        return $doc_html;
    }
    public function amendHTML($obj)
    {
        $amend_html = "";
        if (isset($obj->amendments) && is_array($obj->amendments)) {
            $amendments = $obj->amendments;
            $count = 0;
            foreach ($amendments as $amend) {
                $doc_html .= $this->amendmentRender($count, $amend->description, $amend->rationale);
                $count++;
            }
        }
        return $amend_html;
    }
    public function transactHTML($obj)
    {
        $transact_html = "";
        if (isset($obj->transactions) && is_array($obj->transactions)) {
            $transactions = $obj->transactions;
            $count = 0;
            foreach ($transactions as $transact) {
                $transact_html .= $this->transactionRender($count, $transact->source, $amend->value->amount);
                $count++;
            }
        }
        return $transact_html;
    }
    public function insertSuppliers($array){
        $values = "";
        foreach($array as $id){
            $values .= "(".$project_id.",".$id."),";
        
        }
        $values = rtrim($values," ,");
        $query = "INSERT INTO contractors(project_id, contractor_id) VALUES ".$values;
        $resutl  = $this->query($query);
    }
    public function getSuppliers($array)
    {
        $parties_to_return = [];
        $array = implode(',',$array);
        $array = "(".$array.")";
        $query = "SELECT * FROM institutions WHERE id IN ".$array;
        $result = $this->query($query);
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $party_obj = new StdClass;
                $identifier = new StdClass;
                $contact = new StdClass;
                $address = new StdClass;

                $identifier->scheme = "CAC-RC";
                $identifier->legalName = $row["name"];
                $identifier->id = $row["ug_no"];
                $identifier->uri = "http://publicsearch.cac.gov.ng/comsearch/";

                $address->streetAddress = $row["address"];
                $address->locality = $row["lga"];
                $address->region = $row["state"];
                $address->postalCode = $row["postal_code"];
                $address->countryName = "Nigeia";

                $contact->name = $row["name"];
                $contact->telephone = $row["phone"];
                $contact->email = $row["email"];
                $contact->url = $row["url"];
                $contact->faxNumber = "";

                $party_obj->identifier = $identifier;
                $party_obj->address = $address;
                $party_obj->contactPoint = $contact;
                $party_obj->id = $row["id"];

                $parties_to_return[] = $party_obj;
            }
        }
        return $parties_to_return;
    }
    public function addContractors($array, $project_id){
        /*$array_to_return = [];
        foreach($array as $id){
            $query = "INSERT INTO contractors(project_id, contractor_id) VALUES(".$project_id.",".$id.") ON DUPLICATE KEY UPDATE contractor_id = ".$id;
            $result = $this->query($query);
        }*/
        $contractor = $array[0];
        $query = $query = "INSERT INTO contractors(project_id, contractor_id) VALUES (".$project_id.",".$contractor.") ON DUPLICATE KEY UPDATE contractor_id = ".$contractor;
        $result = $this->query($query);

    }
    public function compileReleases($project_id,$oc_id){
        $releases = []; 
        $release = new stdclass;
        $compiled = new stdclass;
        $compiled->tags = ["compiled"];
        $planning = $this->mergeAndReturn("planning", $project_id);
        if($planning){
            $release->date = $planning["date"];
            $release->tag = ["planning"];
            $release->url = RELEASE_PATH."releases/".$planning["id"].".json";
            $releases[] = $release;
            $release = new stdclass;
            $compiled->ocid  = $planning["ocid"];
            $compiled->planning = $planning["planning"];

        }
        $tender = $this->mergeAndReturn("tender", $project_id);
        if($tender){
            $release->date = $tender["date"];
            $release->tag = ["tender"];
            $release->url = RELEASE_PATH."releases/".$tender["id"].".json";
            $releases[] = $release;
            //$compiled->ocid;
            $compiled->tender = $tender["tender"];
        }

        $contracts = $this->mergeAndReturn("contract",$project_id);
        if($contracts){
            $release->date = $contracts["date"];
            $release->tag = ["contract"];
            $release->url = RELEASE_PATH."releases/".$contracts["id"].".json";
            $releases[] = $release;
            $release = new stdclass;
            //echo json_encode($contracts['contract']['implementation']);
            $compiled->contracts[0]  = $contracts["contract"];
            //echo json_encode($compiled);
        }
        
        $awards = $this->mergeAndReturn("award",$project_id);
        if($awards){
            $release->date = $awards["date"];
            $release->tag[] = ["award"];
            $release->url = RELEASE_PATH."releases/".$awards["id"].".json";
            $releases[] = $release;
            $release = new stdclass;
            $compiled->awards = [];
            $compiled->awards[] = $awards["award"];

        }
        $compiled->ocid = $oc_id;
        $record = new stdclass;
        $record->ocid = $oc_id;
        $record->compiledRelease = $compiled;
        $record->releases = $releases;
        $record->versionedRelease = null;

        return $record;

    }
    public function compilePackage($project_id, $oc_id){
        $compiledPackage = new stdclass;
        $compiledPackage->version = 1.1;
        
        ////Publishers 
        $publisher = new stdclass;
        $publisher->scheme = "OCP";
        $publisher->name = "Public and Private Development Center";
        $publisher->uri = "http://procurementmonitor.org";
        $compiledPackage->publisher = $publisher;
        $packages = [];
        /// Get records 
        $record = $this->compileReleases($project_id, $oc_id);
        $compiledPackage->records [] = $record;
        /// fill packages
        $releases = $record->releases;
        foreach($releases as $release){
            $packages[] = $release->url;
        }
        $compiledPackage->packages = $packages;
        ///Compile Package
        $compiledPackage->license = null;
        $compiledPackage->publishDate = date(DATE_ISO8601,time());
        $compiledPackage->publicationPolicy = null;
        $file = fopen(FILE_ROOT."app/compiled/".$oc_id.".json","w");
        $json = json_encode($compiledPackage,JSON_PRETTY_PRINT);
        fwrite($file,$json);
        fclose($file);
        
        return $json;

    }
    public function mergeAndReturn($type, $project_id){
        $query = "SELECT release_id FROM releases WHERE project_id = ".$project_id." AND type = '".$type."'";
        $result = $this->query($query);
        $release = null;
        if(mysqli_num_rows($result) > 0){
            while($row = mysqli_fetch_assoc($result)){
                $filename = FILE_ROOT."app/releases/".$row["release_id"].".json";
                if(file_exists($filename)){
                    if(!$release){
                        $release = json_decode(file_get_contents($filename),true);
                    }
                    else{
                        $rel = json_decode(file_get_contents($filename),true);
                        $release = $rel + $release;
                    }
                }
            }
        }
        return $release;
    }

}
?>