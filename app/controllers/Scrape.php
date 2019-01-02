<?php
class Scrape extends Controller
{
    function __construct()
    {

    }
    public function index()
    {
        echo 'blah';
    }
    public function view_object($type)
    {
        $url = 'http://gpp.ppda.go.ug/api/v1/releases/ocds-rdvc92-1504900597?tag=' . $type;
        $data = file_get_contents($url);
        $data = json_decode($data);
        $award = $data->releases[0];
        echo "<pre>" . json_encode($award, JSON_PRETTY_PRINT) . "</pre>";
    }
    public function get_ocids()
    {
        
        $url = "http://gpp.ppda.go.ug/api/v1/releases?tag=planning&fy=2017-2018&pde=&procurementType=&page=";
        $start = 1;
        $end = 750;
        $ids = [];
        for ($i = $start; $i <= $end; $i++) {
            echo 'Starting page ' . $i . "<br/>";
            echo $url . $i . "<br/>";

            $content = file_get_contents($url . $i);
            if ($content) {
                $ids = $this->get_ocid($content, $ids);
                echo "finish page " . $i.'<br/>';
            }
        }
        echo "finish: count: " . count($ids);
        file_put_contents('uganda_gpp2017-2018.json', json_encode($ids));
        echo "dararananan!!!!!! Done!!!!";
    }
    public function populate()
    {
        $ocids = json_decode(file_get_contents('uganda_gpp2017-2018.json'));
        echo 'About to start Entering '.count($ocids);
        $stages = ['planning', 'tender', 'award', 'contract'];
        $scraper = $this->load_model('Scrapper');
        $start = 12656;
        $end = 229;
        for ($i = $start; $i < count($ocids); $i++) {
            ////Project
            $starttime = time();
            $ocid =  $ocids[$i];
            if( $ocid == '_OCID_'){
                continue;
            }
            $url = 'http://gpp.ppda.go.ug/api/v1/releases/' . $ocid;

            $planning = json_decode(file_get_contents($url . '?tag=planning'));
            $tender = json_decode(file_get_contents($url . '?tag=tender'));
            $award = json_decode(file_get_contents($url . '?tag=award'));
            $contract = json_decode(file_get_contents($url . '?tag=contract'));
            $planning = !isset($planning->releases) || empty($planning->releases)? []: $planning->releases[0];
            $tender = !isset($tender->releases)|| empty($tender->releases)? []:$tender->releases[0];
            $award = !isset($award->releases)|| empty($award->releases)? []:$award->releases[0];
            $contract = !isset($contract->releases)|| empty($contract->releases)? []:$contract->releases[0];
            $mda_id = $scraper->insert_mda($planning->buyer);
            $project_id = $scraper->insert_project($planning, $mda_id,'2017-2018');
            ////planning
            if(!empty($planning)) $scraper->insert_planning($project_id, $mda_id, $planning,'2017-2018');
            ///tender
            if(!empty($planning))$scraper->insert_tender($project_id, $mda_id, $planning, '001');
            if(!empty($tender))$scraper->insert_tender($project_id, $mda_id, $tender, '002');
            ///award
            if(!empty($award)) $scraper->insert_award($project_id, $mda_id, $award);

            //contract
            if(!empty($contract)) $scraper->insert_contract($project_id, $mda_id, $contract);
            $rel_db = $this->load_model('ReleaseDb');
            $rel_db->compilePackage($project_id, $ocid);
            $time = time() - $starttime;
            echo 'echo finished ocid:' . $ocid.$time;
            echo '<br>'.$i;

        }
        echo "daraanananananznan!!!! Done";
    }
    private function get_ocid($file = "", $list)
    {

        $obj = json_decode($file);
        if (isset($obj->releases) && !empty($obj->releases)) {
            $releases = $obj->releases;

            foreach ($releases as $rel) {
                $list[] = $rel->ocid;
            }
            return $list;
        } else {
            return $list;
        }

    }
    public function viewStopped(){
        $array = json_decode(file_get_contents('uganda_gpp2017-2018.json'));
        $key = array_search('ocds-rdvc92-1504949156',$array);
        echo $key;
    }
    public function insert(){
        $db = $this->load_model('Scrapper');
        $db->insert_dis();
    }
    public function proMethod(){
        $path = 'http://gpp.ppda.go.ug/api/v2/procurement-method';
        $file = file_get_contents($path);
        $file = json_decode($file);
        $data = $file->data;
        $output = '';
        foreach($data as $dat){
            $output .= "<option value = ''>".$dat->title."</option>";

        }
        echo $output;
    }
}
?>