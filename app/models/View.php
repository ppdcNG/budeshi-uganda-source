<?php 
class View extends Model{


    public function loadPlanningView($title, $budget_amount, $source){
		$budget_amount = ($budget_amount == "" or is_string($budget_amount))? "Not Provided" : number_format($budget_amount);
        $html = '<li>
						<div class="uk-child-width-1-2@s uk-grid-collapse uk-text-center" uk-scrollspy="cls: uk-animation-fade; target: > div > .uk-tile; delay: 500; repeat: true"
						 uk-grid>
							<div>
								<div class="uk-tile uk-tile-default uk-tile-small">
									<h4 class="uk-heading-line uk-text-right"><em title="The name of the project that through which this contracting process is funded (if applicable)" uk-tooltip="pos: left"><span class="uk-margin-small-right"uk-icon="icon: tag; ratio: 1"></span>Project Title</em></h4>
									<div class="border-right uk-padding-small">
										<p class="uk-text-right">'.$title.'</p>
									</div>
								</div>
							</div>
							<div>
								<div class="uk-tile uk-tile-default uk-tile-small">
									<h4 class="uk-heading-line uk-text-right"><em title="Amount estimated for the total cost of an item on the budget line" uk-tooltip="pos: left">&#8358; Budget Amount</em></h4>
									<div class="">
										<p class="uk-text-right"><span class="uk-text-uppercase uk-text-success">UGX </span>'.$budget_amount.'</p>
									</div>
								</div>
							</div>
							<div>
								<div class="uk-tile uk-tile-default uk-tile-small">
									<h4 class="uk-heading-line uk-text-right"><em title="May be used to provide the title of the budget line, or the programme used to fund this project." uk-tooltip="pos: left"><span class="uk-margin-small-right"uk-icon="icon: link; ratio: 1"></span>Budget Source</em></h4>
									<div class="border-right uk-padding-small">
										<p class="uk-text-right">'.$source.'</p>
									</div>
								</div>
							</div>

						</div>
						<!-- End of planning -->
						<a href="#" class="uk-margin" title="To the top" uk-tooltip="pos: right" uk-totop uk-scroll></a>
					</li>';
            return $html;
    }
    public function loadTenderView($mda_name, $status,$amendments,$tenderers, $documents, $items){
        $html = '<li>
						<!-- Tender -->
						<div class="uk-child-width-1-2@s uk-grid-collapse uk-text-center" uk-scrollspy="cls: uk-animation-fade; target: > div > .uk-tile; delay: 500; repeat: true"
						 uk-height-match uk-grid>
							<div>
								<div class="uk-tile uk-tile-default uk-tile-small">
									<h4 class="uk-heading-line uk-text-right"><em title="The entity managing the procurement. This may be different from the buyer who pays for, or uses, the items being procured."
										 uk-tooltip="pos: left"><span class="uk-margin-small-right"uk-icon="icon: home; ratio: 1"></span>Procuring Entity</em></h4>
									<div class="border-right uk-padding-small">
										<p class="uk-text-right">'.$mda_name.'</p>
									</div>
								</div>
							</div>
							<div>
								<div class="uk-tile uk-tile-default uk-tile-small">
									<h4 class="uk-heading-line uk-text-right"><em title="	The current status of the tender." uk-tooltip="pos: left"><span class="uk-margin-small-right"uk-icon="icon: folder; ratio: 1"></span>Tender Status</em></h4>
									<div class="">
										<p class="uk-text-right">'.$status.'</p>
									</div>
								</div>
							</div>

							<div>
								<div class="uk-tile uk-tile-default uk-tile-small">
									<h4 class="uk-heading-line uk-text-right"><em title="A tender amendment is a formal change to the tender, and generally involves the publication of a new tender notice/release."
										 uk-tooltip="pos: left"><span class="uk-margin-small-right"uk-icon="icon: file-edit; ratio: 1"></span>Amendments</em></h4>
									<div class="border-right uk-padding-small">
										<table class="uk-table uk-transition-toggle">
											<thead class="uk-transition-slide-top-small">
												<tr>
													<th>
														Description
													</th>
													<th>
														Rationale
													</th>
												</tr>
											</thead>
											<tbody>
												'.$amendments.'
											</tbody>
										</table>
									</div>
								</div>
							</div>
							<div>
								<div class="uk-tile uk-tile-default uk-tile-small">
									<h4 class="uk-heading-line uk-text-right"><em title="The procurement method is the procedure used to purchase the relevant works, goods or services." uk-tooltip="pos: left"><span class="uk-margin-small-right"uk-icon="icon: settings; ratio: 1"></span>Procurment Method</em></h4>
									<div class="">
										<p class="uk-text-right">Selective</p>
									</div>
								</div>
							</div>
							<div>
								<div class="uk-tile uk-tile-default uk-tile-small">
									<h4 class="uk-heading-line uk-text-right"><em title="All parties who submited a bid on a tender." uk-tooltip="pos: left"><span class="uk-margin-small-right"uk-icon="icon: users; ratio: 1"></span>Tenderers</em></h4>
									<div class="border-right uk-padding-small">
										<ul class="uk-list uk-column-1-2 uk-column-divider">
											'.$tenderers.'
										</ul>
									</div>
								</div>
							</div>
							<div>
								<div class="uk-tile uk-tile-default uk-tile-small">
									<h4 class="uk-heading-line uk-text-right"><em title="All documents and attachments related to the tender, including any notices." uk-tooltip="pos: left"><span class="uk-margin-small-right"uk-icon="icon: copy; ratio: 1"></span>Tender Documents</em></h4>
									<div class="uk-grid-small uk-child-width-1-2@s uk-text-center" uk-grid uk-height-match="target: > div > .uk-card; row: false"
									 uk-grid>'.$documents.'
										
									</div>
								</div>
							</div>
							<div>
								<div class="uk-tile uk-tile-default uk-tile-small">
									<h4 class="uk-heading-line uk-text-right"><em title="The goods and services to be purchased, broken into line items wherever possible." uk-tooltip="pos: left"><span class="uk-margin-small-right"uk-icon="icon: list; ratio: 1"></span>Items</em></h4>
									<div class="border-right uk-padding-small">
										<table class="uk-table uk-transition-toggle">
											<thead class="uk-transition-slide-top-small">
												<tr>
													<th>Name</th>
													<th>Amount</th>
													<th>Unit</th>
												</tr>
											</thead>
											<tbody>
												'.$items.'
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>
						<!-- End of Tender -->
						<a href="#" class="uk-margin" title="To the top" uk-tooltip="pos: right" uk-totop uk-scroll></a>
					</li>';
                return $html;
	}
	public function viewParties($project_id){
        $query = 'SELECT release_id FROM releases WHERE project_id = '.$project_id.' ORDER BY id DESC limit 1';
        $result = $this->query($query);
        $release_id = mysqli_fetch_array($result)['release_id'];
		$path = FILE_ROOT.'app/releases/'.$release_id.'.json';
		
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
    public function loadAwardView($mda_name, $award_date, $amendments,$items, $suppliers, $documents){
        $html = '<li>
						<!--Award -->
						<div class="uk-child-width-1-2@s uk-grid-collapse uk-text-center" uk-scrollspy="cls: uk-animation-fade; target: > div > .uk-tile; delay: 500; repeat: true"
						 uk-height-match uk-grid>
							<div>
								<div class="uk-tile uk-tile-default uk-tile-small">
									<h4 class="uk-heading-line uk-text-right"><em title="Award title." uk-tooltip="pos: left"><span class="uk-margin-small-right"uk-icon="icon: tag; ratio: 1"></span>Award Title</em></h4>
									<div class="border-right uk-padding-small">
										<p class="uk-text-right">'.$mda_name.'</p>
									</div>
								</div>
							</div>
							<div>
								<div class="uk-tile uk-tile-default uk-tile-small">
									<h4 class="uk-heading-line uk-text-right"><em title="The date of the contract award. This is usually the date on which a decision to award was made." uk-tooltip="pos: left"><span class="uk-margin-small-right"uk-icon="icon: calendar; ratio: 1"></span>Award Date</em></h4>
									<div class="">
										<p class="uk-text-right">'.$award_date.'</p>
									</div>
								</div>
							</div>

							<div>
								<div class="uk-tile uk-tile-default uk-tile-small">
									<h4 class="uk-heading-line uk-text-right"><em title="An award amendment is a formal change to the details of the award, and generally involves the publication of a new award notice/release."
										 uk-tooltip="pos: left"><span class="uk-margin-small-right"uk-icon="icon: file-edit; ratio: 1"></span>Amendments</em></h4>
									<div class="border-right uk-padding-small">
										<table class="uk-table uk-transition-toggle">
											<thead class="uk-transition-slide-top-small">
												<tr>
													<th>
														Description
													</th>
													<th>
														Rationale
													</th>
												</tr>
											</thead>
											<tbody>
												'.$amendments.'
											</tbody>
										</table>
									</div>
								</div>
							</div>
							<div>
								<div class="uk-tile uk-tile-default uk-tile-small">
									<h4 class="uk-heading-line uk-text-right"><em title="The goods and services awarded in this award, broken into line items wherever possible." uk-tooltip="pos: left"><span class="uk-margin-small-right"uk-icon="icon: list; ratio: 1"></span>Items</em></h4>
									<div class="">
										<table class="uk-table uk-transition-toggle">
											<thead class="uk-transition-slide-top-small">
												<tr>
													<th>Name</th>
													<th>Amount</th>
													<th>Unit</th>
												</tr>
											</thead>
											<tbody>
												'.$items.'
											</tbody>
										</table>
									</div>
								</div>
							</div>
							<div>
								<div class="uk-tile uk-tile-default uk-tile-small">
									<h4 class="uk-heading-line uk-text-right"><em title="The suppliers awarded this award." uk-tooltip="pos: left"><span class="uk-margin-small-right"uk-icon="icon: users; ratio: 1"></span>Suppliers</em></h4>
									<div onclick = "fetch_supplier()" class="border-right uk-padding-small">
										<ul class="uk-list uk-column-1-2 uk-column-divider">
											'.$suppliers.'
										</ul>
									</div>
								</div>
							</div>
							<div>
								<div class="uk-tile uk-tile-default uk-tile-small">
									<h4 class="uk-heading-line uk-text-right"><em title="All documents and attachments related to the award, including any notices." uk-tooltip="pos: left"><span class="uk-margin-small-right"uk-icon="icon: copy; ratio: 1"></span>Award Documents</em></h4>
									<div class="uk-grid-small uk-child-width-1-2@s uk-text-center" uk-grid uk-height-match="target: > div > .uk-card; row: false"
									 uk-grid>
										'.$documents.'
									</div>
								</div>
							</div>
						</div>
						<a href="#" class="uk-margin" title="To the top" uk-tooltip="pos: right" uk-totop uk-scroll></a>
					</li>';
					return $html;
    }
    public function loadConntractView($title, $desc, $status, $startDate, $endDate, $contract_amount,$items,$documents){
		$contract_amount = empty($contract_amount)? "Not Provided": number_format($contract_amount);
        $html = '<li>
						<!-- Contract -->
						<div class="uk-child-width-1-2@s uk-grid-collapse uk-text-center" uk-scrollspy="cls: uk-animation-fade; target: > div > .uk-tile; delay: 500; repeat: true"
						 uk-height-match uk-grid>
							<div>
								<div class="uk-tile uk-tile-default uk-tile-small">
									<h4 class="uk-heading-line uk-text-right"><em title="Contract title." uk-tooltip="pos: left"><span class="uk-margin-small-right"uk-icon="icon: tag; ratio: 1"></span>Contract Title</em></h4>
									<div class="uk-text-right border-right uk-padding-small">
										'.$title.'
									</div>
								</div>
							</div>
							<div>
								<div class="uk-tile uk-tile-default uk-tile-small">
									<h4 class="uk-heading-line uk-text-right"><em title="Contract description." uk-tooltip="pos: left"><span class="uk-margin-small-right"uk-icon="icon: table; ratio: 1"></span>Description</em></h4>
									<div>
                                    '.$desc.'
									</div>
								</div>
							</div>
							<div>
								<div class="uk-tile uk-tile-default uk-tile-small">
									<h4 class="uk-heading-line uk-text-right"><em title="The current status of the contract." uk-tooltip="pos: left"><span class="uk-margin-small-right"uk-icon="icon: folder; ratio: 1"></span>Contract Status</em></h4>
									<div class="border-right uk-padding-small">
										<p class="uk-text-right">'.$status.'</p>
									</div>
								</div>
							</div>
							<div>
								<div class="uk-tile uk-tile-default uk-tile-small">
									<h4 class="uk-heading-line uk-text-right"><em title="The start and end date for the contract." uk-tooltip="pos: left"><span class="uk-margin-small-right"uk-icon="icon: clock; ratio: 1"></span>Contract Period</em></h4>
									<div class="">
										<p class="uk-text-right">'.$startDate.' - '.$endDate.'</p>
									</div>
								</div>
							</div>
							<div>
								<div class="uk-tile uk-tile-default uk-tile-small">
									<h4 class="uk-heading-line uk-text-right"><em title="The total value of this contract. A negative value indicates that the contract will involve payments from the supplier to the buyer (commonly used in concession contracts)."
										 uk-tooltip="pos: left">&#8358; Contract Amount</em></h4>
									<div class="border-right uk-padding-small">
										<p class="uk-text-right"><span class="uk-text-uppercase uk-text-success">UGX </span>'.$contract_amount.'</p>
									</div>
								</div>
							</div>
							<div>
								<div class="uk-tile uk-tile-default uk-tile-small">
									<h4 class="uk-heading-line uk-text-right"><em title="All documents and attachments related to the contract, including any notices." uk-tooltip="pos: left"><span class="uk-margin-small-right"uk-icon="icon: copy; ratio: 1"></span>Contract Documents</em></h4>
									<div class="uk-grid-small uk-child-width-1-2@s uk-text-center" uk-grid uk-height-match="target: > div > .uk-card; row: false"
									 uk-grid>
										'.$documents.'
									</div>
								</div>
							</div>
							<div>
								<div class="uk-tile uk-tile-default uk-tile-small">
									<h4 class="uk-heading-line uk-text-right"><em title="The goods, services, and any intangible outcomes in this contract." uk-tooltip="pos: left"><span class="uk-margin-small-right"uk-icon="icon: list; ratio: 1"></span>Items</em></h4>
									<div class="border-right uk-padding-small">
										<table class="uk-table uk-transition-toggle">
											<thead class="uk-transition-slide-top-small">
												<tr>
													<th>Name</th>
													<th>Amount</th>
													<th>Unit</th>
												</tr>
											</thead>
											<tbody>
												'.$items.'
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>
						<!-- End of contract -->
						<a href="#" class="uk-margin" title="To the top" uk-tooltip="pos: right" uk-totop uk-scroll></a>
					</li>';
            return $html;
    }
    public function loadImplementationView($transactions, $monitor_report, $pictures){
        $html = '<li>
						<!-- Implementation -->
						<div class="uk-child-width-1-2@s uk-grid-collapse uk-text-center" uk-scrollspy="cls: uk-animation-fade; target: > div > .uk-tile; delay: 500; repeat: true"
						 uk-height-match uk-grid>
							<div class="uk-width-1-1@s">
								<div class="uk-tile uk-tile-default uk-tile-small">
									<h4 class="uk-heading-line uk-text-right"><em title="A list of the spending transactions made against this contract." uk-tooltip="pos: left"><span class="uk-margin-small-right"uk-icon="icon: users; ratio: 1"></span><span class="uk-margin-small-right"uk-icon="icon: credit-card; ratio: 1"></span>Transactions</em></h4>
									<table class="uk-table uk-transition-toggle">
										<thead class="uk-transition-slide-top-small">
											<tr>
												<th>Payer</th>
												<th>Amount</th>
												<th>Payee</th>
												<th>Date</th>
											</tr>
										</thead>
										<tbody class="">
											'.$transactions.'
										</tbody>
									</table>
								</div>
							</div>
                                    '.$monitor_report.'
						</div>
						<div class="uk-child-width-1-2@s uk-child-width-1-3@m uk-text-center uk-grid-match" uk-grid>
							'.$pictures.'
						</div>
						<!-- End of Implementation -->
						<a href="#" class="uk-margin" title="To the top" uk-tooltip="pos: right" uk-totop uk-scroll></a>
					</li>';
            return $html;
                
	}
	public function summaryTitle($title){
		$html = "<div>
		<div class='red'>
			<h5 class='uk-text-bold'>".$title."</h5>
			<hr>
		</div>
	</div>";
	return $html;
	}
	public function summaryItems($obj){
		$html = "<div> <div class='uk-grid-collapse uk-child-width-1-1@s' uk-grid>";
		//echo json_encode($obj);
		foreach($obj as $key=>$value){
			$html .= '<div>
			<div class="">
				<label class="uk-form-label" for="form-stacked-text">'.$key.':</label>
				<div class="uk-form-controls stint">
					<input class="uk-input" id="form-stacked-text" type="text" value="'.$value.'" readonly>
				</div>
			</div>
		</div>';
		}
		$html .= "</div></div>";
		return $html;
	}
	public function summaryBlock($title, $obj){
		$html = "<div><div class='uk-grid-collapse uk-child-width-1-1' uk-grid>";
		$html .= $this->summaryTitle($title);
		$html .= $this->summaryItems($obj);
		$html .= "</div></div>";
		return $html;
	}
}
?>