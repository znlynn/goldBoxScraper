<?php

require_once '/insert-path-to-code/create-posts.php';

//Get most recent deals
//$recentURL = 'https://www.amazon.com/gp/goldbox/?gb_f_GB-SUPPLE=dealStates:AVAILABLE,sortOrder:BY_SCORE,dealTypes:LIGHTNING_DEAL';

//Get best deals
$bestURL = 'https://www.amazon.com/gp/goldbox/ref=gbps_ftr_s-3_0ee9_dct_70-?gb_f_GB-SUPPLE=dealStates:AVAILABLE,dealTypes:LIGHTNING_DEAL,sortOrder:BY_SCORE,discountRanges:70-';

//Get electronics deals
$elecURL = 'https://www.amazon.com/gp/goldbox/ref=gbps_ftr_s-3_0ee9_sort_RELV?gb_f_GB-SUPPLE=dealStates:AVAILABLE,dealTypes:LIGHTNING_DEAL,sortOrder:BY_SCORE,enforcedCategories:172282';

//Get deals of the day
//$dailyURL = 'https://www.amazon.com/gp/goldbox/ref=gbps_fcr_s-3_0ee9_dls_UPCM?gb_f_GB-SUPPLE=dealStates:AVAILABLE,sortOrder:BY_SCORE,dealTypes:DEAL_OF_THE_DAY';

//Get deals under $25
$under25URL = 'https://www.amazon.com/gp/goldbox/ref=gbps_ftr_s-3_0ee9_prc_-25?gb_f_GB-SUPPLE=dealStates:AVAILABLE,dealTypes:LIGHTNING_DEAL,sortOrder:BY_SCORE,priceRanges:-25';

//Get deals over $200
//$over200URL = 'https://www.amazon.com/gp/goldbox/ref=gbps_ftr_s-3_0ee9_prc_200-?gb_f_GB-SUPPLE=dealStates:AVAILABLE,dealTypes:LIGHTNING_DEAL,priceRanges:200-,sortOrder:BY_SCORE';

//Get beauty product deals
$beautyURL = 'https://www.amazon.com/gp/goldbox/ref=gbps_fcr_s-3_866c_wht_23357520?gb_f_GB-SUPPLE=enforcedCategories:3760911,dealStates:AVAILABLE,sortOrder:BY_SCORE,dealTypes:LIGHTNING_DEAL';
//Get deals on DVDs
//$dvdURL = 'https://www.amazon.com/gp/goldbox/ref=gbps_fcr_s-3_0ee9_dls_UPCM?gb_f_GB-SUPPLE=dealStates:AVAILABLE,sortOrder:BY_SCORE,enforcedCategories:2625373011,dealTypes:LIGHTNING_DEAL';

//Dont get the same item twice in one day
$previousItems = explode(',', file_get_contents('./amz-items.txt', true));
$dealIDs=[];
function getDeals($url, $cat) {
	$doc = file_get_contents($url);
	preg_match('/   "dealDetails" : ([\s\S]*?),\n   "responseMetadata" : \{/', $doc, $matches);
	$rawJSON = $matches[1];
	$deals = json_decode($rawJSON);
	$lPrice = '';
	$dPrice = '';
	$pOff = '';
	foreach($deals as $item) {
		//Don't get the same deal from multiple categories
		if(!in_array($item->dealID, $GLOBALS['dealIDs'])) {
		    	array_push($GLOBALS['dealIDs'], $item->dealID);
		    	if(!(in_array($item->dealID, $GLOBALS['previousItems']))){
		    		$newCat = [];
				$allCats = [];
			    	if($item->itemType == 'MULTI_ITEM') {
					//Brandwide, multi item deals
			    		$lPrice = number_format($item->minListPrice,2).'-'.number_format($item->maxListPrice,2);
			    		$dPrice = number_format($item->minDealPrice,2).'-'.number_format($item->maxDealPrice,2);
			    		$pOff = 'Up To '.$item->maxPercentOff;
			    		if(!(in_array(10, $cat)) && $item->maxDealPrice >= 200)
				        	array_push($newCat, 10);
				        if(!(in_array(9, $cat)) && $item->minDealPrice <= 25)
				        	array_push($newCat, 9);
				        if(!(in_array(4, $cat)) && $item->maxDealPrice >= 70)
				        	array_push($newCat, 4);
			    	} else {
			    		$lPrice = !empty($item->maxBAmount) ? number_format($item->maxBAmount, 2) : number_format($item->minBAmount, 2);
			    		$dPrice = !empty($item->dealPrice) ? number_format($item->dealPrice, 2) : !empty($item->maxDealPrice) ? number_format($item->maxDealPrice, 2) : number_format($item->minDealPrice, 2);
			    		$pOff = !empty($item->percentOff) ? $item->percentOff : $item->maxPercentOff;
			    		if(!(in_array(10, $cat)) && $dPrice >= 200)
				        	array_push($newCat, 10);
				        if(!(in_array(9, $cat)) && $dPrice <= 25)
				        	array_push($newCat, 9);
				        if(!(in_array(4, $cat)) && $pOff >= 70)
				        	array_push($newCat, 4);
			    	}
			        
			        
				if(strpos($item->egressUrl, '?') !== false) {
					$delim = '&';
				} else {
					$delim = '?';
				}
				$allCats = array_merge($cat,$newCat);
				$deal = '<div class="description"><span style="display:none" class="hiddenDealID">'.$item->dealID.'</span><p>'.$item->description.'</p></div><div class="body"><div class="img" style="display:inline-block; float:left"><a href="'.$item->egressUrl.$delim.'tag=bargainlink-20" target="_blank"><img src="'.$item->primaryImage.'" style="width:225px;height:225px"></a></div><div class="float" style="display:inline-block; margin-left:15px">Was: <strike>$<span class="was">'.$lPrice.'</span></strike><br>Sale: $<span class="sale">'.$dPrice.'</span><br>You Save: <span class="savings">'.$pOff.'%</span><div style="margin-top:25px"><a style="text-decoration:none;background-color:black;border:1px solid #660000;border-radius:5px;color:#fff;margin-top:25px;margin-right:10px;padding:17px 20px;font-weight:bold;" href="'.$item->egressUrl.$delim.'tag=bargainlink-20">Buy Now</a></div></div></div><br><p style="font-style:italic; clear:both">Bargainlinks may get a small commission for any purchases</p><hr>';
				//echo $deal;
				//exit;
				buildPost($item->title, $allCats, $deal);
				//Don't carry over data
				$allCats = [];
				$newCat = [];
				unset($allCats);
				unset($newCat);
				
			}
		}
	}
	$cat = [];
	unset($cat);
}
//Choose deals to scrape. May replace with GUI
getDeals($elecURL,array(8,5));
getDeals($beautyURL,array(8,13));
getDeals($bestURL,array(8,4));
getDeals($under25URL,array(8,9));
//getDeals($over200URL,array(8,10));
//getDeals($dailyURL,array(6));
//getDeals($recentURL,array(8));

//Update deal list to prevent duplicates
if(!empty($dealIDs))
    file_put_contents('./amz-items.txt', implode(',',$dealIDs));
?>
