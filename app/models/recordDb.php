<?php 
class Record extends Model
{
    public $planning;
    public $tender;
    public $award;
    public $contract;
    public $implementation;

    public $projectTitle;
    public $mda_name;
///////////////////////Array properties as html////////////
    public $tenderDocuments;
    public $awardDocuments;
    public $contractDocuments;
    public $implementationDocuments;

    public $tenderItems;
    public $awardItems;
    public $contractItems;

    public $awardAmendments;
    public $tenderAmendments;
    public $contractAmendments;

    public $implementationMiles;

    public $tenderers;
    public $suppliers;
    public $transactions;
    public $monitorimages;
////////////////////////////////////////////////////////

    public function __construct()
    {
        Parent::__construct();
    }


    public function getProjectObj($id)
    {
        $query = "SELECT oc_id FROM  projects WHERE id = " . $id;
        $result = $this->query($query);
        if (mysqli_num_rows($result) < 0) {
            return NULL;
        }
        $this->getProjectProp($id);
        $release_name = mysqli_fetch_array($result)[0];
        $url = 'http://gpp.ppda.go.ug/api/v1/releases/' . $release_name;
        $obj = json_decode(file_get_contents($url));
        return $obj;
           

    }
    public function getreleaseArray($id, $version = "1.1")
    {
        $OCDS = [];
        if ($releasePackage = $this->getProjectObj($id)) {
            if(empty($releasePackage->version)){
            $release = $releasePackage->releases[0];
            }
            else{
                $release = $releasePackage->records[0]->compiledRelease;
            }
            //$OCDS["release"] = $release;
            $planning = empty($release->planning) ? "": $release->planning;
            $tender = empty($release->tender) ? "" : $release->tender;
            $contracts = empty($release->contracts) ? "" : $release->contracts[0];
            $awards = empty($release->awards) ? "" : $release->awards[0];
            $oc_id = empty($release->id) ? "" : $release->id;

            if(!empty($planning)){
                $OCDS["Planning"] = $this->getValueFields($planning,"planning");
            }
            if(!empty($tender)){
                $OCDS["Tender"] = $this->getValueFields($tender,"tender");
            }
            if(!empty($contracts)){
                $OCDS["Contract"] = $this->getValueFields($contracts,"contracts");
            }
            if(!empty($awards)){
                $OCDS["Award"] = $this->getValueFields($awards,"awards");
            }

        }
        return $OCDS;
    }
    public function releaseObject($obj){
        $release = NULL;
        $release = $obj->releases[0];
        return $release;
    }
    public function getValueFields($obj, $type)
    {
        $to_return = null;
        switch ($type) {
            case "planning" :
                $arry = [];
                if (!empty($obj->budget) && !empty($obj->budget->amount->amount) && $obj->budget->amount->amount != 0) {
                    $arry["Budget Amount"] = $obj->budget->amount->currency . " " . number_format($obj->budget->amount->amount);
                }
                if (!empty($obj->budget) && !empty($obj->budget->source)) {
                    $arry["Budget Source"] = $obj->budget->source;
                }
                if (!empty($obj->budget) && !empty($obj->rationale)) {
                    $arry["Rationale"] = $obj->rationale;
                }
                if (!empty($obj->budget) && !empty($obj->budget->description)) {
                    $arry["Budget Source"] = $obj->budget->description;
                }
            
                $to_return = empty($arry) ? [] : $arry;
                break;
            case "tender" :
                $arry = [];
                if (!empty($obj->awardCriteria)) {
                    $arry["Award Criteria"] = $obj->awardCriteria;
                }
                if (!empty($obj->procurementMethod)) {
                    $arry["Procurement Method"] = $obj->procurementMethod;
                }
                if (!empty($obj->procuringEntity) && !empty($obj->procuringEntity->identifier) && !empty($obj->procuringEntity->identifier->legalName)) {
                    $arry["Procuring Entity"] = $obj->procuringEntity->identifier->legalName;
                }
                if (!empty($obj->mainProcurementMethod)) {
                    $arry["Procurement Method"] = $obj->mainProcurementMethod;
                }
                if (!empty($obj->status)) {
                    $arry["Tender Status"] = $obj->status;
                }
                $to_return = empty($arry) ? [] : $arry;
                break;
            case "awards" :
                $arry = [];
                if (!empty($obj->suppliers) && !empty($obj->suppliers[0]) && !empty($obj->suppliers[0]->identifier->legalName)) {
                    $arry["Suppliers"] = $obj->suppliers[0]->identifier->legalName;
                }
                if (!empty($obj->value) && !empty($obj->value->amount)) {
                    $arry["Amount"] = "NGN ".number_format($obj->value->amount);
                }
                $to_return = empty($arry) ? [] : $arry;
                break;
            case "contracts" :
                $arry = [];
                if (!empty($obj->status)) {
                    $arry["Contract Status"] = $obj->status;
                }
                if (!empty($obj->value) && !empty($obj->value->amount)) {
                    $arry["Amount"] = "NGN ".number_format($obj->value->amount);
                }
                $to_return = empty($arry) ? [] : $arry;
                break;
        }
        return $to_return;
    }
    public function getProjectProp($id)
    {
        $query = "SELECT p.title, m.name FROM projects p JOIN mdas m ON p.mda_id = m.id WHERE p.id = " . $id;
        $result = $this->query($query);
        $row = mysqli_fetch_assoc($result);
        $this->projectTitle = $row["title"];
        $this->mda_name = $row["name"];
    }
    public function getPlanningObj($obj)
    {
        $html = null;
        if (isset($obj) && !empty($obj)) {

        }
    }

    public function getTenderObj($id, $type)
    {
        $obj = $this->getProjectObj($id, $type);
        if ($obj) {
            $this->tenderAmendments = $this->getAmendments($obj, "tender");
            $this->tenderDocuments = $this->getDocuments($obj, "tender");
            $this->tenderItems = $this->getItems($obj, "tender");
            $this->tenderers = $this->getTenderer($obj, "tender");


        }
        return $obj;
    }
    public function getAwardObj($id, $type)
    {
        $obj = $this->getProjectObj($id, $type);
        if ($obj) {
            $this->awardAmendments = $this->getAmendments($obj, "award");
            $this->awardDocuments = $this->getDocuments($obj, "award");
            $this->awardItems = $this->getItems($obj, "award");
            $this->suppliers = $this->getTenderer($obj, "award", "suppliers");

        }
        return $obj;

    }
    public function getContractObj($id, $type)
    {
        $obj = $this->getProjectObj($id, "contract");
        if ($obj) {
            $this->contractAmendments = $this->getAmendments($obj, "contract");
            $this->contractDocuments = $this->getDocuments($obj, "contract");
            $this->contractItems = $this->getItems($obj, "contract");
        }
        return $obj;
    }
    public function getImplementationObj($id, $type)
    {
        $obj = $this->getProjectObj($id, $type);
        if ($obj) {
            $this->implementationDocuments = $this->getDocuments($obj, "contract");
            $this->transactions = $this->getTransaction($obj, "contract");
            $this->monitorimages = $this->getMonitorImages($obj, "contract");
        }
        return $obj;
    }
    public function getAmendments($obj)
    {
        $list = "";
        $amendments = (isset($obj->amendments) && is_array($obj->amendments)) ? $obj->amendments : "";
        if (!empty($amendments)) {
            foreach ($amendments as $amend) {
                $list .= "<tr>" . $this->renderRow("td", $amend->description) . $this->renderRow("td", $amend->rationale) . "</tr>";
            }
        }
        else {
            $list .= "<tr><em>N/A</em></tr>";
        }
        return $list;

    }
    public function getDocuments($obj)
    {
        $list = "";
        $documents = (isset($obj->documents) && is_array($obj->documents)) ? $obj->documents : "";
        if (!empty($documents)) {
            foreach ($documents as $doc) {
                $doc_url = isset($doc->url) ? $doc->url : "#";
                $doc_title = isset($doc->title) ? $doc->title : "N/A";

                $list .= '<div>
							<div class="uk-card uk-card-default uk-card-body" style="background-color: #43c7f2">
								<div class="uk-inline-clip uk-transition-toggle">				
									' . $doc_title . '			
									<div class="uk-position-center uk-overlay uk-overlay-default uk-transition-fade uk-padding">				
										<a href = "' . $doc_url . '"><span uk-icon="icon: download; ratio: 2"></span></a>				
									</div>				
								</div>				
							</div>				
						</div>';
            }
        }
        else {
            $list .= "<tr><em>N/A</em></tr>";
        }
        return $list;
    }
    public function getItems($obj)
    {
        $list = "";
        $items = (isset($obj->items) && is_array($obj->items)) ? $obj->items : "";
        if (!empty($items)) {
            foreach ($items as $item) {
                if (isset($item->description) && isset($item->quantity) && isset($item->unit)) {
                    $list .= "<tr>" . $this->renderRow("td", $item->description) . $this->renderRow("td", $item->quantity) . $this->renderRow("td", $item->unit->name) . "</tr>";
                }
            }
        }
        else {
            $list .= "<tr><em>N/A</em></tr>";
        }
        return $list;

    }
    public function getTenderer($obj, $title = "tenderers")
    {
        $list = "";
        $tenderers = (isset($obj->$title) && is_array($obj->$title)) ? $obj->$title : "";
        if (!empty($tenderers)) {
            foreach ($tenderers as $tender) {
                if(!empty($tender->identifier->legalName)){
                     $list .= "<tr>" . $this->renderRow("td", $tender->identifier->legalName) . "</tr>";
                     continue;
                }
                
                if(!empty($tender->name)){
                    $list .= "<tr onclick = 'get_contractor()'>" . $this->renderRow("td", $tender->name) . "</tr>";
                    
                }
            }
        }
        else {
            $list .= "<tr><em>N/A</em></tr>";
        }
        return $list;

    }
    public function getTransaction($obj)
    {
        $list = "";
        $transactions = (isset($obj->transactions) && is_array($obj->transactions)) ? $obj->transactions : "";
        if (!empty($transactions)) {
            foreach ($transactions as $transaction) {
                if (isset($transaction->payer) && isset($transaction->payee)) {
                    $list .= "<tr>" . $this->renderRow("td", $transaction->payer->identifier->legalName) . $this->renderRow("td", $transaction->value->amount) . $this->renderRow("td", $transaction->payee->identifier->legalName) .
                        $this->renderRow("td", date("m/y/d", strtotime($transaction->date))) . "</tr>";
                }
            }
        }
        else {
            $list .= "<tr><em>N/A</em></tr>";
        }
        return $list;

    }
    public function getMonitorImages($obj, $type = "details")
    {
        $list = "";
        $documents = (isset($obj->documents) && is_array($obj->documents)) ? $obj->documents : "";
        if (!empty($documents)) {
            foreach ($documents as $doc) {
                if ($doc->documentType == "x_siteImages") {
                    if ($type == "details") {
                        $list .= '<div>
								<div class="uk-card uk-card-default uk-card-body">
									<img src="' . ABS_PATH . $doc->url . '">
								</div>
                            </div>';
                    }
                    else {
                        $list .= '<div>
                                <a class="uk-inline" href="' . ABS_PATH . $doc->url . '" caption="' . $doc->description . '">
                                        <img src="' . ABS_PATH . $doc->url . '" alt="">
                                </a>
                                </div>';
                    }
                }

            }
        }

        return $list;
    }
    public function getMonitorReports($obj, $type = "details")
    {
        $list = "";
        $documents = (isset($obj->documents) && is_array($obj->documents)) ? $obj->documents : "";
        if (!empty($documents)) {
            foreach ($documents as $doc) {
                if (!empty($doc->title) && $doc->documentType == "x_monitorsReport") {
                    if ($type == "details") {
                        $list .= '<div>
                    <div class="uk-tile uk-tile-default uk-tile-small">
                        ';
                        $list .= '<h4 class="uk-heading-line uk-text-right"><em title="Comments by procurement monitors who supervised the implementatio of a project" uk-tooltip="pos: left"><span class="uk-margin-small-right"uk-icon="icon: commenting; ratio: 1"></span>Monitors Report</em></h4>
                    <div clas="uk-panel uk-panel-scrollable">' . $doc->description . '
                    </div>';
                        $list .= '</div>
                    </div>';
                    }
                    else {
                        $list = $doc->description;
                    }
                }
            }

        }

        $list = empty($list) ? "<tr><em>N/A</em></tr>" : $list;

        return $list;

    }
    public function getMDAName($id)
    {
        $query = "SELECT p.title, i.name FROM planning p JOIN mdas i ON p.mda_id = i.id WHERE p.id = " . $id;
        $result = $this->query($query);
        $result = mysqli_fetch_assoc($result);
        $name = $result["name"];
        $this->projectTitle = $result["title"];
        return $name;
    }
    public function fetchFromGpp($id){

    }

}


?>