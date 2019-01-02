<?php
class ProjectDb extends Model
{
    public $currency;

    public function __construct()
    {
        Parent::__construct();
        $this->currency['NGN'] = '&#8358;';
        $this->currency['USD'] = "$";
        $this->currency['EUR'] = '&#8364;';
        $this->currency['GBP'] = '&#8356;';

        $this->tender_dict['complete'] = "The tender process is complete.";
        $this->tender_dict['active'] = "A tender process is currently taking place";
        $this->tender_dict['cancelled'] = "The tender process has been cancelled.";
        $this->tender_dict['selective'] = "Only qualified suppliers are invited to submit a tender.";
        $this->tender_dict["direct"] = "The contract is awarded to a single supplier without competition.";
        $this->tender_dict['limited'] = "The procuring entity contacts a number of suppliers of its choice.";
        $this->tender_dict['open'] = "All interested suppliers may submit a tender.";
        $this->tender_dict['priceOnly'] = "The award will be made to the qualified bid with the lowest price.";
        $this->tender_dict['costOnly'] = "The award will be made to the qualified bid demonstrating the lowest overall cost. A cost assessment could cover the entire monetary
         implications of the proposal, including the price paid to the supplier and the running costs, switching costs or other non-price costs of choosing a particular option";
        $this->tender_dict['qualityOnly'] = "The award will be made to the qualified bid demonstrating the 
        highest quality against some assessment method. The price is either fixed, or with a maximum set and price factors not included in the evaluation.";
        $this->award_dict['N/A'] = "This information is unavailable";



        $this->award_dict['active'] = "This award has been made, and is currently in force.";
        $this->award_dict['pending'] = "This award has been proposed, but is not yet in force. This may be due to a cooling off period, or some other process.";
        $this->award_dict['cancelled'] = "This award has been cancelled.";
        $this->award_dict['N/A'] = "This information is unavailable";

        $this->contract_dict['active'] = "This contract has been signed by all the parties, and is now legally in force";
        $this->contract_dict['pending'] = "This contract has been proposed, but is not yet in force. It may be awaiting signature.";
        $this->contract_dict['complete'] = "This contract was signed and in force, and has now come to a close. This may be due to successful completion of the contract, or may be early termination due to some non-completion.";
        $this->contract_dict['terminated'] = "This contract was signed and in force, and has now come to a close. This may be due to successful completion of the contract, or may be early termination due to some non-completion.";
        $this->contract_dict['cancelled'] = "This contract has been cancelled prior to being signed.";
        $this->contract_dict['N/A'] = "This information is unavailable";

    }
    public function render_project($oc_id)
    {
        $ocds = $this->fetch_release($oc_id);
        if (!$ocds) {
            echo "could not finde release file " . $oc_id;
            die;
        }
        $pieces = $this->parse_OCDS($ocds);
        $html = $this->fetch_renderer($pieces);
        return $html;

    }
    private function fetch_release($oc_id)
    {
        $filename = $oc_id . '.json';
        if (file_exists(COMPILED_PATH . $filename)) {
            $obj_str = file_get_contents(COMPILED_PATH . $filename);
            $obj = json_decode($obj_str);
            return $obj;
        } else {
            return false;
        }
    }
    private function fetch_renderer($release)
    {
        $tablist = '';
        $tab = '';
        foreach ($release as $type => $data) {
            switch ($type) {
                case 'planning':
                    $tablist .= $this->renderTablist($type);
                    $tab .= $this->renderPlanning($data);
                    break;
                case 'award':
                    $tablist .= $this->renderTablist($type);
                    $tab .= $this->renderAward($data);
                    break;
                case 'tender':
                    $tablist .= $this->renderTablist($type);
                    $tab .= $this->renderTender($data);
                    break;
                case 'contract':
                    $tablist .= $this->renderTablist($type);
                    $tab .= $this->renderContract($data);
                    break;
                case 'implementation':
                    $tablist .= $this->renderTablist($type);
                    $tab .= $this->renderImplementation($data);
                    break;
            }
        }
        $return['tab'] = $tab;
        $return['tablist'] = $tablist;
        return $return;

    }
    private function renderTablist($name)
    {
        $html = '<li class="tabs-title"><a href="#' . $name . '" class="button button-like"><i class="far fa-calendar-alt"></i>
        <span class="hide-for-small-only">' . ucfirst($name) . '</span></a></li>';
        return $html;
    }
    public function getProject($id, $prefix = "")
    {
        $query = "SELECT p.*, c.amount AS ct_amount, b.budget_amount AS b_amount,ct.contractor_id, i.name, i.id AS ctid FROM projects p LEFT JOIN contract c ON p.id = c.project_id LEFT JOIN planning b ON p.id = b.project_id LEFT
        JOIN contractors ct ON p.id = ct.project_id LEFT JOIN institutions i ON ct.contractor_id = i.id WHERE p.id = " . $id;
        $return_obj = $this->queryToJson($query, $prefix);
        return $return_obj;

    }

    public function project_properties($id)
    {

        $query = "SELECT p.id, p.oc_id,p.title, p.description, p.year, p.state, p.lga, m.name FROM projects p LEFT JOIN mdas m ON p.mda_id = m.id WHERE p.id = " . $id;
        $result = $this->query($query);
        $project = mysqli_fetch_assoc($result);
        return $project;
    }
    private function parse_OCDS($obj)
    {
        $record = $obj->records[0];
        $compiled = $record->compiledRelease;

        $releases = [];
        //Planning Release
        if (isset($compiled->planning)) {
            $planning = $compiled->planning;
            $planning_vals = [];
            $planning_vals['budget_source'] = empty($planning->budget->description) ? "N/A" : $planning->budget->description;
            $planning_vals['budget_amount'] = empty($planning->budget->amount->amount) ? "N/A" : number_format($planning->budget->amount->amount, 2);
            $planning_vals['budget_link'] = empty($planning->uri) ? "N/A" : $planning->uri;
            $planning_vals['currency'] = empty($planning->budget->amount->currency) ? "N/A" : $planning->budget->amount->currency;
            $releases['planning'] = $planning_vals;
        }
        //Tender Release
        if (isset($compiled->tender)) {
            $tender = $compiled->tender;
            $tender_vals = [];
            $tender_vals['status'] = empty($tender->status) ? "N/A" : $tender->status;
            $tender_vals['procurement_method'] = empty($tender->procurementMethod) ? "N/A" : $tender->procurementMethod;
            $tender_vals['award_criteria'] = empty($tender->awardCriteria) ? "N/A" : $tender->awardCriteria;
            $tender_vals['procurement_category'] = empty($tender->mainProcurementCategory) ? "N/A" : $tender->mainProcurementCategory;
            $tender_vals['start_date'] = empty($tender->period->startDate) ? "N/A" : date("jS F Y", strtotime($tender->period->startDate));
            $tender_vals['end_date'] = empty($tender->period->endDate) ? "N/A" : date("jS F Y", strtotime($tender->period->endDate));
            if (!empty($tender->tenderers) && is_array($tender->tenderers)) {
                $tenderers = [];
                //var_dump($tender->tenderers);
                foreach ($tender->tenderers as $tenderer) {
                    $obj = new stdclass;
                    $obj->name = empty($tenderer->identifier->legalName) ? "N/A" : $tenderer->identifier->legalName;
                    $obj->amount = empty($tenderer->x_tenderedAmount) ? "N/A" : number_format($tenderer->x_tenderedAmount, 2);
                    $tenderers[] = $obj;
                }
                $tender_vals['tenderers'] = $tenderers;
            }
            $releases['tender'] = $tender_vals;
        }

        //Award Release
        if (isset($compiled->awards)) {
            $award = $compiled->awards[0];
            $award_vals = [];
            $award_vals['status'] = empty($award->status) ? "N/A" : $award->status;
            $award_vals['date'] = empty($award->date) ? "N/A" : date("jS F Y", strtotime($award->date));
            $award_vals['amount'] = empty($award->value->amount) ? "N/A" : $award->value->amount;
            $award_vals['currency'] = empty($award->value->currency) ? "NGN" : $award->value->currency;
            if (!empty($award->suppliers && is_array($award->suppliers))) {
                $names = [];
                foreach ($award->suppliers as $supp) {
                    $names[] = [$supp->identifier->legalName, isset($supp->id) ? $supp->id : 0];
                }
                $award_vals['suppliers'] = $names;

            }

            $releases['award'] = $award_vals;
        }

        //Contract Release
        if (isset($compiled->contracts)) {
            $contract = $compiled->contracts[0];
            $contract_vals = [];
            $contract_vals['status'] = empty($contract->status) ? "N/A" : $contract->status;
            $contract_vals['amount'] = empty($contract->value->amount) ? "N/A" : $contract->value->amount;
            $contract_vals['currency'] = empty($contract->value->currency) ? "N/A" : $contract->value->currency;
            $contract_vals['start_date'] = empty($contract->period->startDate) ? "N/A" : date("jS F Y", strtotime($contract->period->startDate));
            $contract_vals['end_date'] = empty($contract->period->endDate) ? "N/A" : date("jS F Y", strtotime($contract->period->endDate));
            $contract_vals['date_signed'] = empty($contract->dateSigned) ? "N/A" : date("jS F Y", strtotime($contract->dateSigned));
            $releases['contract'] = $contract_vals;
        }

        //Implementation Release
        if (isset($compiled->contracts[0]->implementation)) {
            $implementation = $complied->contracts[0]->implementation;
            if (!empty($implementation->transactions)) {
                $transactions = $implementation->transactions;
                $trans = [];
                foreach ($transactions as $transact) {
                    $obj = new stdclass;
                    $obj->payer = empty($transact->payer->identifier->legalName) ? "N/A" : $transact->payer->identifier->legalName;
                    $obj->payee = empty($transact->payee->identifier->legalName) ? "N/A" : $transact->payee->identifier->legalName;
                    $obj->date = empty($transact->date) ? "N/A" : date("jS F Y", strtotime($transact->date));
                    $trans[] = obj;
                }
            }
            $releases['implementation'] = $trans;
        }
        return $releases;

    }
    private function renderPlanning($array)
    {
        $html = '<div class="tabs-panel" id="planning" style="background-color: transparent">
        <div class="callout secondary">
            <div class="" style="text-align: center"> <i class="far fa-calendar-alt"></i> Planning </div>
        </div>
        <div class="callout pallette1">
            <div class="grid-x grid-padding-x grid-margin-x grid-margin-y fluid small-up-1 medium-up-2 large-up-2" data-equalizer data-equalize-on="medium">
                <div class="cell primary">
                    <p>Budget Source:</p>
                    <div class="stat">' . $array['budget_source'] . '
                    </div>
                </div>
                <div class="cell">
                    <p>Budget Amount:</p>
                    <div class="stat">' . $this->currency[$array['currency']] . ' ' . $array['budget_amount'] . '</div>
                </div>
            </div>
        </div>
     </div>';
        return $html;
    }
    public function viewParties($project_id){
        $query = 'SELECT release_id FROM releases WHERE project_id = '.$project_id.' ORDER BY project_id DESC limit 1';
        $result = $this->query($query);
        $release_id = mysqli_fetch_array($query)['release_id'];
        $path = FILE_ROOT.'releases/'.$release_id.'.json';
        if(file_exists($path)){
            $file = json_decode(file_get_contents($path));
            if(isset($file->parties) && is_array($file->parties)){
                $list = '';
                foreach($file->parties as $party){
                    $id = empty($party->id)?"": $party->id;
                    $name = empty($party->name)?"N/A":$party->name;
                    $role = empty($party->roles[0])?"N/A":$party->roles[0];
                    $list .= $this->renderParties($id,$name, $role);
                }
                $head = '<div class="uk-border-rounded uk-margin">
                <div class="uk-background-muted uk-padding-small uk-padding uk-width-1-1 "><span class="uk-text-large">Parties Section</span> - Information about the organizations 
                and other participants involved in this contracting process. Hover over each item to view their role(s) in this contracting process.</div><div class=" uk-width-1-1 
                uk-padding-small"><ul class="uk-list  uk-list-bullet uk-child-width-1-3@s uk-padding" uk-grid>';
                $bottom = '</ul></div</div>';
                return $head.$list.$bottom;
            }
            else{
                return "";
            }

        }


    }
    public function renderParties($id, $company, $role){
        $html = '<li onclick = "view_party(\''.$id.'\')">
        <div class="uk-inline">
                <a class="uk-link-reset">'.$company.'</a>
            <div uk-dropdown>'.$role.'</div>
        </div>
    </li>';
    return $html;

    }
    private function renderTender($array)
    {
        $t_html = "";
        if (isset($array['tenderers'])) {
            $tenderers = $array['tenderers'];
            $t_html = ' <div class="callout">
        <div class="grid-x grid-padding-x grid-margin-x grid-margin-y fluid small-up-1 medium-up-1 large-up-1" data-equalizer data-equalize-on="medium">
            <div class="cell"><table class="hover"><thead><tr><th width="150">Tenderers</th><th width="150">Tender Amount</th></tr></thead><tbody>';
            foreach ($tenderers as $td) {
                $t_html .= '<tr><td>' . $td->name . '</td><td>&#8358;' . $td->amount . '</td></tr>';
            }
            $t_html .= '</tbody></table></div></div></div>';
        }
        $html = '<div class="tabs-panel" id="tender">
        <div class="callout primary">
            <div class="" style="text-align: center"> <i class="fas fa-users"></i> Tender </div>
        </div>
        <div class="callout">
            <div class="grid-x grid-padding-x grid-margin-x grid-margin-y fluid small-up-1 medium-up-3 large-up-3" data-equalizer data-equalize-on="medium">
                <div class="cell">
                    <p >Tender Status:</p>
                    <div class="stat" data-tooltip aria-haspopup="true" class="has-tip" data-disable-hover="false" tabindex="1" title="' . $this->tender_dict[$array['status']] . '">' . $array['status'] . '
                    </div>
                </div>
                <div class="cell">
                    <p>Procurement Method:</p>
                    <div class="stat" data-tooltip aria-haspopup="true" class="has-tip" data-disable-hover="false" tabindex="1" title="' . $this->tender_dict[$array['procurement_method']] . '">' . $array['procurement_method'] . '</div>
                </div>
                <div class="cell">
                    <p >Procurement Category:</p>
                    <div class="stat">' . $array['procurement_category'] . '</div>
                </div>
            </div>
        </div>
        <div class="callout">
            <div class="grid-x grid-padding-x grid-margin-x grid-margin-y fluid small-up-1 medium-up-2 large-up-2" data-equalizer data-equalize-on="medium">
                <div class="cell">
                    <p>Award Citeria:</p>
                    <div class="stat" data-tooltip aria-haspopup="true" class="has-tip" data-disable-hover="false" tabindex="1" title="' . $this->tender_dict[$array['award_criteria']] . '">' . $array['award_criteria'] . '

                    </div>
                </div>
                <div class="cell">
                    <p>Tender Period:</p>
                    <div class="stat">' . $array['start_date'] . ' - ' . $array['end_date'] . '
                    </div>
                </div>
            </div>
        </div>' . $t_html . '</div>';
        return $html;
    }
    private function renderAward($array)
    {
        $suppliers = empty($array['suppliers']) ? '<li>N/A</li>' : "";
        if (isset($array['suppliers'])) {
            foreach ($array['suppliers'] as $supp) {
                $suppliers .= '<li onclick = "show_contractor(\'' . $supp[1] . '\')">' . $supp[0] . '</li>';
            }

        }
        $html = '<div class="tabs-panel" id="award">
        <div class="callout primary">
                <div class="" style="text-align: center"> <i class="fas fa-trophy"></i> Award </div>
            </div>
            <div class="callout">
                <div class="grid-x grid-padding-x grid-margin-x grid-margin-y fluid small-up-1 medium-up-2 large-up-2" data-equalizer data-equalize-on="medium">
                    <div class="cell">
                        <p>Award Status:</p>
                        <div class="stat" data-tooltip aria-haspopup="true" class="has-tip" data-disable-hover="false" tabindex="1" title="' . $this->award_dict[$array['status']] . '">' . $array['status'] . '
                        </div>
                    </div>
                    <div class="cell">
                        <p>Award Date:</p>
                        <div class="stat">' . $array['date'] . '
                            </div>
                    </div>
                </div>
            </div>
            <div class="callout">
                    <div class="grid-x grid-padding-x grid-margin-x grid-margin-y fluid small-up-1 medium-up-2 large-up-2" data-equalizer data-equalize-on="medium">
                            <div class="cell">
                                    <p>Award Amount:</p>
                                    <div class="stat">' . $this->currency[$array['currency']] . ' ' . number_format($array['amount'], 2) . '</div>
                                </div>
                                <div class="cell">
                            <p>Supplier(s)</p>
                            <div class="stat"><ul>' . $suppliers . '
                                    </ul></div></div></div></div></div>';
        return $html;
    }
    private function renderContract($array)
    {
        $html = '<div class="tabs-panel" id="contract">
        <div class="callout secondary">
                <div class="" style="text-align: center"> <i class="far fa-file-alt"></i> Contract </div>
            </div>
            <div class="callout">
                <div class="grid-x grid-padding-x grid-margin-x grid-margin-y fluid small-up-1 medium-up-2 large-up-2" data-equalizer data-equalize-on="medium">
                    <div class="cell">
                        <p>Contract Status:</p>
                        <div class="stat" data-tooltip aria-haspopup="true" class="has-tip" data-disable-hover="false" tabindex="1" title="' . $this->contract_dict[$array['status']] . '">' .
            $array['status'] . '
                        </div>
                    </div>
                    <div class="cell">
                        <p>Date: Signed</p>
                        <div class="stat">' . $array['date_signed'] . '
                        </div>
                    </div>
                </div>
            </div>
            <div class="callout">
                    <div class="grid-x grid-padding-x grid-margin-x grid-margin-y fluid small-up-1 medium-up-2 large-up-2" data-equalizer data-equalize-on="medium">
                            <div class="cell">
                                    <p>Contract Amount:</p>
                                    <div class="stat">' . $this->currency[$array['currency']] . ' ' . number_format($array['amount'], 2) . '
                                        </div>
                                </div>
                                <div class="cell">
                            <p>Contract Perioid</p>
                            <div class="stat">' . $array['start_date'] . ' - ' . $array['end_date'] . '
                            </div></div></div></div></div>';
        return $html;
    }
    private function renderImplementation($array)
    {
        $transactions = is_array($array) ? "" : "<tr>N/A</tr>";
        foreach ($array as $obj) {
            $transactions .= '<tr><th>' . $obj->date . '</th><th>' . $obj->payer . '</th><th>' . $obj->payee . '</th><th>' . $obj->amount . '</th></tr>';
        }
        $html = '<div class="tabs-panel" id="implementation">
        <div class="callout primary">
                <div class="" style="text-align: center"> <i class="fas fa-cogs"></i> Implementation </div>
            </div>
            <div class="callout">
                <p>Transactions</p>
                    <div class="grid-x grid-padding-x grid-margin-x grid-margin-y fluid small-up-1 medium-up-1 large-up-1" data-equalizer data-equalize-on="medium">
                        <div class="cell">
                                <table>
                                        <thead><tbody>' . $transactions . '</tbody>
                                        <tfoot>
                                                <tr>
                                                  <td colspan="3">Total</td>
                                                  
                                                  <td>$180</td>
                                                </tr>
                                              </tfoot>
                                      </table>
                        </div></div></div></div>';
        return $html;
    }
    private function renderPageLinks($page, $total, $active_class = 'uk-active', $pag_url = 'ProcuringEntity/projects/')
    {
        $page = (int)$page;
        $pages = ceil($total / PERPAGE);
        $start = $page - 4;
        $end = $page + 4;
        // set pages to 1 incase negetive division
        if ($pages < 1) {
            $pages = 1;
        }
        //check if start page overflows
        if ($start < 1) {
            $end -= ($start - 1);
            $start = 1;

        }
        if ($end > $pages) {
            $start -= ($end - $pages);
            $end = $pages;
        }
        if ($end >= $pages && $start <= 0) {
            $end = $pages;
            $start = 1;
        }

        /// loop from start to end to build the links
        $url = ABS_PATH . $pag_url;
        $mid_links = "";
        for ($i = $start; $i <= $end; $i++) {
            $active = ($i == $page) ? $active_class : "";
            $link = $url . $i;
            $mid_links .= $this->onePageLink($i, $link, $active);
        }
        $front_link = $page > 5 ? '<li><a href="' . $url . '1">1</a></li><li class="uk-disabled"><span>...</span></li>' : "";
        $back_link = $page > ($pages - 4) ? "" : '<li class="uk-disabled"><span>...</span></li><li><a href="' . $url . $pages . '">' . $pages . '</a></li>';
        $prev = $page > 1 ? '<li><a href="' . $url . ($page - 1) . '"><span uk-pagination-previous></span></a></li>' : '<li class = "uk-disabled"><a href="#"><span uk-pagination-previous></span></a></li>';
        $next = $page >= $pages ? '<li class = "uk-disabled"><a href="#"><span uk-pagination-next></span></a></li>' : '<li><a href="' . $url . ($page + 1) . '"><span uk-pagination-next></span></a></li>';

        $pageLinks = $prev . $front_link . $mid_links . $back_link . $next;
        return $pageLinks;
    }
    private function onePageLink($number, $link, $active = "")
    {
        return '<li class = ' . $active . '><a href="' . $link . '">' . $number . '</a></li>';
    }
    public function getMDAProjects($id, $page, $path = 'Project/projects/')
    {
        $start_page = PERPAGE * ($page - 1);

        $output = "";
        $query = "SELECT SQL_CALC_FOUND_ROWS p.*, m.name, m.short_name FROM projects p LEFT JOIN mdas m ON p.mda_id = m.id WHERE mda_id = " . $id . " ORDER BY p.id DESC LIMIT " . PERPAGE . " OFFSET " . $start_page;
        $result = $this->query($query);
        $n_query = "SELECT FOUND_ROWS()";
        $number = $this->query($n_query);
        $number = mysqli_fetch_array($number)[0];

        $pub = "";
        $ans = "";
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $pub = $row["published"];
                $output .= $this->projectListTemplate($row["id"], $row["title"], $row["lga"], $row["year"], $row["oc_id"], $pub);
                $this->mda_name = $row['name'];

            }
        } else {
            $row = $this->select('name', 'mdas', 'id', $id);
            $this->mda_name = $row['name'];
            $output = $page == 1 ? "<td colspan = '7' style = 'text-align:center'><em>No Data Found</em></td>" : "<no>No result Found</no>";
        }
        $page_links = $this->renderPageLinks($page, $number, 'uk-active', $path);
        $obj = new stdclass;
        $obj->pag_links = $page_links;
        $obj->table = $output;
        return $obj;

    }
    public function getDistricts()
    {
        $districts = $this->queryToSelectOption('SELECT id, name FROM districts');
        return $districts;
    }
    private function projectListTemplate($id, $title, $location, $year, $oc_id, $pub)
    {
        $tableRow = '<tr>
                        <td><a title="Edit Project" uk-tooltip="pos: bottom" onclick = \'editmodal("' . $id . '")\'><span class="uk-margin-small-right" uk-icon="icon: file-edit"></span></a></td>
                        <td>' . $title . '</td>
                        <td>' . $oc_id . '</td>
                        <td>' . $year . '</td>
                        <td><a href="#" title="View Releases" uk-tooltip="pos: bottom" onclick = \'getReleases("' . $id . '")\'><span class="uk-margin-small-right" uk-icon="icon: folder"></span></a><a href="#add-release" onclick = \'add_project("' . $id . '")\'
                           title="Add Release" uk-tooltip="pos: bottom" uk-toggle><span class="uk-margin-small-right" uk-icon="icon: plus"></span></a></td>
                        <td><a href="#delete-project" onclick = \'deleteProject("' . $id . '")\' title="Delete Project" uk-tooltip="pos: bottom" uk-toggle><span class="uk-margin-small-right" uk-icon="icon: trash"></span></a></td>
                        <td>' . ucfirst($pub) . '</td>
                    </tr>';
        return $tableRow;
    }
    private function releaseList($id, $project_id, $release_id, $oc_id, $type = "Planning")
    {
        $list_item = "<li><a href = '" . ABS_PATH . "Release/edit/" . $id . "/" . $type . "'>" . $release_id . "</a><a href=\"#\" onclick = \"deleteRelease('" . $project_id . "','" . $type . "')\" title=\"Delete Project\" uk-tooltip=\"pos: bottom\"><span class=\"uk-margin-small-right\" uk-icon=\"icon: trash\"></span></a></li>";
        return $list_item;
    }
    public function getRealeases($project_id, $table)
    {
        $p_query = "SELECT r.id, r.release_id, r.type, r.project_id, p.oc_id FROM releases r LEFT JOIN projects p ON r.project_id = p.id WHERE r.project_id = " . $project_id;
        $result = $this->query($p_query);
        if (!$result) {
            die($this->error);
        }
        $row_html = "";
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_array($result)) {
                $row_html .= $this->releaseList($row["id"], $row["project_id"], $row["release_id"], $row["oc_id"], $row["type"]);
            }
            return $row_html;
        } else {
            return false;
        }
    }
    public function fillRelease($mda_id, $release_type, $table)
    {
        $output = "";
        $releases = $this->getRealeases($mda_id, $table); // returns array of rows 
        if ($releases) {
            foreach ($releases as $row) {
                $output .= $this->releaseList($row["id"], $row["release_id"], $row["oc_id"], $release_type);
            }
            return $output;
        } else return false;
    }
    public function addProject($data_obj)
    {
        $oc_id = $this->generate_ocid($data_obj->mda_id);
        $record = $oc_id . "-record";
        $output = null;
        $query = "INSERT INTO projects (oc_id, mda_id, state, year, updated_by, title, description, record_name, monitored, published) VALUES ('" . $oc_id . "'," . $data_obj->mda_id . ", 
        '" . $data_obj->location . "','" . $data_obj->year . "'," . $data_obj->updated_by . ",'" . mysqli_real_escape_string($this->conn, $data_obj->title) . "', '" . mysqli_real_escape_string($this->conn, $data_obj->description) . "','" . $record . "','" . $data_obj->monitored . "','" . $data_obj->published . "')";
        $result = $this->query($query);
        if (!$result) {
            die($this->error);
        }
        $output["message"] = "Project successfully Added";
        $output["project_id"] = mysqli_insert_id($this->conn);
        $output["ajaxstatus"] = "success";
        return $output;
    }
    public function editProject($data_obj)
    {
        $output = null;
        $last_update = date("Y-m-d");
        $query = "UPDATE projects SET title = '" . mysqli_real_escape_string($this->conn, $data_obj->e_title) . "', description = '" . mysqli_real_escape_string($this->conn, $data_obj->e_description) . "',
        year = '" . $data_obj->e_year . "', state = '" . $data_obj->e_location . "', updated_by = " . $_SESSION["id"] . ", status = '" . $data_obj->e_status . "',
        date_updated = '" . $last_update . "', monitored = '" . $data_obj->e_monitored . "', published = '" . $data_obj->e_published . "' WHERE id = " . $data_obj->e_project_id;
        
        

       // $result = $this->query($query);
       
        $data_obj->budget = empty($data_obj->budget) ? 0.00 : $data_obj->budget;
        $data_obj->contract = empty($data_obj->contract) ? "" : $data_obj->contract;
        $query = "UPDATE planning SET budget_amount = " . $data_obj->budget . " WHERE project_id = " . $data_obj->e_project_id;
        
        $result = $this->query($query);

        //sey contract amount 
        if (!empty($data_obj->contract)) {
            $query = "UPDATE contract SET amount = " . $data_obj->contract . " WHERE project_id = " . $data_obj->e_project_id;
            
            $result = $this->query($query);
        }
        ///set contractor
        if ($data_obj->contractor != "null" && !empty($data_obj->contractor)) {
            $query = "INSERT INTO contractors (contractor_id, project_id) VALUES (" . $data_obj->contractor . ", " . $data_obj->e_project_id . ") ON DUPLICATE KEY UPDATE contractor_id = " .
                $data_obj->contractor;
                
            $result = $this->query($query);
        }
        $output["message"] = "Project Edited Succesfully";
        $output["project_id"] = $data_obj->e_project_id;
        $output["ajaxstatus"] = "success";
        return $output;
    }
    public function delete($id)
    {
        $query = "DELETE FROM projects WHERE id = " . $id;
        $result = $this->query($query);
        if (!$result) {
            die($this->error);
        }
        $query = "DELETE FROM releases WHERE project_id = " . $id;
        $result = $this->query($query);
        $this->deleteRelease($id, "planning");
        $this->deleteRelease($id, "tender");
        $this->deleteRelease($id, "award");
        $this->deleteRelease($id, "contract");
        $this->deleteRelease($id, "implementation");

    }
}
?>