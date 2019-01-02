<?php
class Scrapper extends Model{
    function __construct()
    {
        Parent::__construct();
    }

    public function insert_project($obj, $mda_id, $year = '2015-2016'){
        $insert['oc_id'] = $obj->ocid;
        $insert['title'] = $obj->tender->title;
        $insert['description'] = $obj->tender-> title;
        $insert['project_category'] = $obj->tender->mainProcurementCategory;
        $insert['mda_id'] = $mda_id;
        $insert['year'] = $year;
        $id = $this->insert($insert,'projects');
        return $id;

    }
    public function insert_mda($obj){
        $query = "SELECT id FROM mdas WHERE ug_id = ".$obj->id;
        $result = $this->query($query);
        $id = null;
        if(mysqli_num_rows($result)> 0){
            $id = mysqli_fetch_assoc($result)['id'];
            return  $id;
        }
        else{
            $insert['name'] = $obj->name;
            $insert['ug_id'] = $obj->id;
            $id = $this->insert($insert,'mdas');
            return $id;
        }

    }
    public function insert_planning($pro_id, $mda_id, $obj){
        $insert['project_id'] = $pro_id;
        $insert['mda_id'] = $mda_id;
        $insert['release_id'] = $obj->ocid.'-planning-001';
        $insert['title'] = $obj->tender->title;
        $insert['description'] = $obj->tender->title;
        $insert['budget_amount'] = $obj->planning->budget->amount->amount;
        $id = $this->insert($insert,'planning');

        $rel['release_id'] = $obj->ocid.'-planning-001';
        $rel['project_id'] = $pro_id;
        $rel['type'] = 'planning';
        
        $this->insert($rel,'releases');
        
        $release = json_decode(file_get_contents(DATA_DIR."planning.json"));
        $release->ocid = $obj->ocid;
        $release->id = $obj->id;
        $release->date = $obj->date;
        $release->tag = ['planning'];
        $release->planning->budget = $obj->planning->budget;
        $release->planning->budget->project = $obj->tender->title;
        $release->parties = $obj->parties;
        $file = fopen(FILE_ROOT . "app/releases/" . $rel['release_id'] . ".json", "w");
        fwrite($file, json_encode($release, JSON_PRETTY_PRINT));
    }
    public function insert_tender($pro_id, $mda_id, $obj, $num){
        $insert['project_id'] = $pro_id;
        $up['mda_id']= $insert['mda_id'] = $mda_id;
        $up['release_id'] = $insert['release_id'] = $obj->ocid.'-tender-'.$num;
        $up['title'] = $insert['title'] = $obj->tender->title;
        $up['description'] = $insert['description'] = $obj->tender->title;
        $up['procurement_method'] = $insert['procurement_method'] = $obj->tender->procurementMethod;
        $up['category'] = $insert['category'] = $obj->tender->mainProcurementCategory;
        if(isset($obj->tender->tenderPeriod->startDate)) $insert['start_date'] = date("Y-m-d",strtotime($obj->tender->tenderPeriod->startDate));
        if(isset($obj->tender->tenderPeriod->endDate)) $insert['start_date'] = date("Y-m-d",strtotime($obj->tender->tenderPeriod->endDate));
        if(isset($obj->tender->tenderPeriod->durationInDays)) $insert['start_date'] = date("Y-m-d",strtotime($obj->tender->tenderPeriod->durationInDays));

        $id = $this->upsert($insert,$up,'tender');
        $rel['project_id']  = $pro_id;
        $rel['release_id'] = $insert['release_id'];
        $rel['type'] = 'tender';
        $this->insert($rel, 'releases');
        
        $release = json_decode(file_get_contents(DATA_DIR."tender.json"));
        $release->tender = $obj->tender;
        $release->ocid =$obj->ocid;
        $release->id = $obj->id;
        $release->date = $obj->date;
        $release->parties = $obj->parties;

        $file = fopen(FILE_ROOT . "app/releases/" . $rel['release_id'] . ".json", "w");
        fwrite($file, json_encode($release, JSON_PRETTY_PRINT));
        return true;
    }
    public function insert_contractor($name, $project_id, $phone = "")
    {
        $name = rtrim($name, " ,");
        $names = explode(',',$name);
        $i_id = null;
        if(count($names) > 1){
            $query = "SELECT id FROM institutions WHERE name = 'Multiple'";
            $result = $this->query($query);
            if(mysqli_num_rows($result)< 1){
                $a_query = "INSERT INTO institutions(name) VALUES ('Multiple')";
                $result = $this->query($a_query);
                $i_id   = mysqli_insert_id($this->conn);

            }
            else{
                $i_id = mysqli_fetch_array($result)[0];
            }
            
            $query = "INSERT INTO contractors (contractor_id, project_id) VALUES (".$i_id.",".$project_id.")";
            $result = $this->query($query);

        }
        else{
            $contractor_name = $names[0];
            $query = "SELECT id FROM institutions WHERE name = '".mysqli_real_escape_string($this->conn,$contractor_name)."'";
            $result = $this->query($query);
            if(mysqli_num_rows($result) > 0){
                $i_id =  mysqli_fetch_array($result)[0];
                $query = "INSERT INTO contractors (contractor_id,project_id) VALUES (".$i_id.",".$project_id.")";
                $result = $this->query($query);
            }
            else{
                $query = "INSERT INTO institutions (name) VALUES ('".mysqli_real_escape_string($this->conn,$contractor_name)."')";
                $result = $this->query($query);
                $i_id = mysqli_insert_id($this->conn);
                $query = "INSERT INTO contractors (contractor_id,project_id) VALUES (".$i_id.",".$project_id.")";
                $result = $this->query($query);
            }
        }
        return $i_id;
    }
    public function insert_award($pro_id, $mda_id, $obj){
        if(isset($obj->awards[0]->suppliers[0]->name)){
            $name = $obj->awards[0]->suppliers[0]->name;
            $id = $obj->awards[0]->suppliers[0]->id;
            $contractor = $this->insert_contractor($name,$pro_id);
            
        }
        $award = $obj->awards[0];
        $insert_award['title'] = $award->title;
        $insert_award['description'] = $award->description;
        $insert_award['project_id'] = $pro_id;
        $insert_award['mda_id'] = $mda_id;
        $insert_award['release_id'] = $obj->ocid . '-award-0001';
        $insert_award['date_modified'] = date("Y-m-d H:i:s");
        $insert_award['award_date'] = date("Y-m-d", strtotime($award->date));
        $insert_award['amount'] = $award->value->amount;
        $insert_award['currency'] = $award->value->currency;
        $this->insert($insert_award,'award');
        $rel['project_id']  = $pro_id;
        $rel['release_id'] = $insert_award['release_id'];
        $rel['type'] = 'award';
        $rel_id = $this->insert($rel, 'releases');
        //Suppliers
        $suppliers = [];
        if(isset($obj->awards[0]->suppliers[0]->name)){
            $ocds_org = json_decode(file_get_contents(DATA_DIR."organisation.json"));
            $ocds_org->identifier->legalName = $award->suppliers[0]->name;
            $ocds_org->identifier->id = $award->suppliers[0]->id;
        }
        
        $release = json_decode(file_get_contents(DATA_DIR."award.json"));
        
        $release->id = $rel_id;
        $release->ocid = $obj->ocid;
        $release->award->title = $obj->awards[0]->title;
        $release->award->description = $obj->awards[0]->description;
        $release->award->date = $obj->awards[0]->date;
        $release->award->value = $award->value;
        $release->award->suppliers[0]= isset($ocds_orgs)? $ocds_orgs: [];
        $release->award->contractPeriod = $award->contractPeriod;
        $release->parties = $obj->parties;


        $file = fopen(FILE_ROOT . "app/releases/" . $insert_award['release_id'] . ".json", "w");
        fwrite($file, json_encode($release, JSON_PRETTY_PRINT));
        return $release;

    }
    public function add_contractor($project_id, $contractor_id)
    {
        $data['project_id'] = $project_id;
        $data['contractor_id'] = $contractor_id;
        $this->insert($data, 'contractors');
    }
    public function insert_contract($pro_id, $mda_id, $obj){
        $contract = $obj->contracts[0];
        $insert_contract['title'] = $contract->title;
        $insert_contract['description'] = $contract->title;
        $insert_contract['project_id'] = $pro_id;
        $insert_contract['mda_id'] = $mda_id;
        $insert_contract['release_id'] = $obj->ocid . '-contract-0001';
        $insert_contract['date_modified'] = date(DATE_ISO8601, strtotime(date('Y-m-d H:i:s')));
        $insert_contract['amount'] = $contract->value->amount;
        $insert_contract['currency'] = $contract->value->currency;
        $this->insert($insert_contract,'contract');
        $rel["release_id"] = $insert_contract["release_id"];
        $rel["project_id"] = $pro_id;
        $rel['type'] = "contract";
        $rel_id = $this->insert($rel, 'releases');
        $release = json_decode(file_get_contents(DATA_DIR."contract.json"));
        $release->id =  $rel_id;
        $release->ocid = $obj->ocid;
        $release->contract->id =  $obj->ocid;
        $release->contract->awardID = $contract->awardID;
        $release->contract->title = $contract->title;
        $release->contract->description = $contract->description;
        $release->contract->value = $contract->value;
        $release->contract->dateSigned = $contract->dateSigned;
        $release->contract->period = $contract->period;
        $release->parties = $obj->parties;

        $file = fopen(FILE_ROOT . "app/releases/" . $insert_contract['release_id'] . ".json", "w");
        fwrite($file, json_encode($release, JSON_PRETTY_PRINT));
        return $release;
             
    }
    public function insert_dis(){
        $file_path = "C:/Users/Ramsiz/Downloads/ugandadis.csv";
        $file = fopen($file_path,'r');
        while($row = fgetcsv($file,500000)){
            $ins['name'] = $row[2];
            $ins['region'] = $row[1];
            $ins['population'] = $row[3];
            $this->insert($ins, "districts");
            echo "just entered ".$row[2]."<br>";
        }
    }
}
?>