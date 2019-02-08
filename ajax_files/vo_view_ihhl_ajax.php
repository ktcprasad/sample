<?php

session_start();
if(!isset($_SESSION['TRANS_VO_ID']) && !isset($_SESSION['desk_deo_id']))
{
	exit("Validation: Session Expired, Please login again");
}

require_once 'common.php';
if($_REQUEST[Action]=='ValidateVO')
{	
	extract($_REQUEST);
	$caller= $mobile_number;
//sleep(5);
//$vo_name_array=mssql_query("select * from vo_info(nolock) where PRY_MOB_NO='$caller' and IS_ACTIVE!='N'");
$vo_id = $_SESSION['desk_TRANS_VO_ID'];

//Checking OB's registration 28-11-18 start
if(strlen($_SESSION['desk_IMEI_NO'])==15){
$vo_ob_registered_result=mssql_num_rows(mssql_query("GET_IVRS_OBS '$vo_id'"));
if($vo_ob_registered_result ==0)
{
echo $message="Validation: OBs registration is mandatory for loan request";exit;
}
}
//Checking OB's registration 28-11-18 end


///////////start vo meeting dae validation start 16-08-2017
$total_deposit_rs=mssql_fetch_array(mssql_query("VO_MEETING_DAY_VALIDATION '$vo_id'"));
		if($total_deposit_rs[FLAG]=='Y')
	{ echo $message="Validation:  Please update meeting date.";exit;}
///////////start vo meeting dae validation end 16-08-2017

///Allowing loans only to the VO'S  who applied tablet loan 22-11-2018
$vo_grade_rs=mssql_fetch_array(mssql_query("select GRADE,LOAN_ELIGBLE from VO_CREDIT_LIMIT where VO_ID='$vo_id'"));
$vo_grade=$vo_grade_rs['GRADE'];
if($vo_grade == 'A' || $vo_grade == 'B' || $vo_grade == 'C')
{
$vo_grade_array=mssql_query("VO_ELIGIBLE_GRADE_VALIDATION '$vo_id'");
$vo_grade_valid=mssql_num_rows($vo_grade_array);
if($vo_grade_valid==0){
echo $message="Validation:  Please apply VO TABLET loan.";exit;
}
///Allowing loans only to the VO'S  who applied tablet loan 22-11-2018
//Disabling web screen to A,B and C grade VO's after 15 days of dispatch of tablet PC start 26-11-2018
//echo $message="Validation:".$_SESSION['desk_IMEI_NO']." is IMEI";
log_ivr($ivr_call_id,$message);
if(strlen($_SESSION['desk_IMEI_NO'])!=15){
$vo_tablet_aquire_details=mssql_fetch_array(mssql_query("USP_GETTABLETACQUIRINGDETAILS '$vo_id'"));
$vo_tablet_aquire_days = $vo_tablet_aquire_details['DAYCOUNT'];
if($vo_tablet_aquire_days > 15)
{
echo $message="Validation:15 days time limit completed. VO can no longer apply loans through website. Please use tablet availed for appyling loan.";exit;
}
}
//Disabling web screen to A,B and C grade VO's after 15 days of dispatch of tablet PC end 26-11-2018
}




$vo_name_array=mssql_query("select * from vo_info(nolock) where TRANS_VO_ID='$vo_id' and IS_ACTIVE!='N'");
		$status_valied= mssql_num_rows($vo_name_array);

		$vo_name_array=mssql_fetch_array($vo_name_array);
		$void_code=$vo_name_array['TRANS_VO_ID'];
		$is_mepma=$vo_name_array['IS_MEPMA'];
		$DISTRICT_ID=$vo_name_array['DISTRICT_ID'];
		$MANDAL_ID=$vo_name_array['MANDAL_ID'];
		
		//Start Putting conditions for Documents Verification By Ashok
		$ms_qry="select IS_VERIFIED from MS_DOCS_VERIFIED where DISTRICT_ID='$DISTRICT_ID' and MANDAL_ID='$MANDAL_ID'";
		$ms_qry_res=mssql_query($ms_qry);
		$ms_qry_row=mssql_fetch_array($ms_qry_res);

		$vo_qry="select IS_VERIFIED from VO_DOCS_VERIFIED where VO_ID='$void_code'";
		$vo_qry_res=mssql_query($vo_qry);
		$vo_qry_row=mssql_fetch_array($vo_qry_res);
		//echo "SUCCESS";$_SESSION['vo_id']="$void_code";exit;
//echo "<br>".$message="ms_qry:$ms_qry\nvo_qry:$vo_qry\nDocuments submmition at ms and vo level. DISTRICT_ID=$DISTRICT_ID,MANDAL_ID=$MANDAL_ID,void_code=$void_code,ms_IS_VERIFIED=$ms_qry_row[IS_VERIFIED],vo_IS_VERIFIED=$vo_qry_row[IS_VERIFIED]\n";	


		if($ms_qry_row[IS_VERIFIED]!='Y' && $vo_qry_row[IS_VERIFIED]!='Y')
		{
		echo $message="Validation: Documents not submmited at ms and vo level. DISTRICT_ID=$DISTRICT_ID,MANDAL_ID=$MANDAL_ID,void_code=$void_code,ms_IS_VERIFIED=$ms_qry_row[IS_VERIFIED],vo_IS_VERIFIED=$vo_qry_row[IS_VERIFIED]\n";	

		exit;
	
		}
		else
		{
		  if($ms_qry_row[IS_VERIFIED]!='Y')
		   {
			echo $message="Validation: Documents not submmited at ms level. ";//DISTRICT_ID=$DISTRICT_ID,MANDAL_ID=$MANDAL_ID,void_code=$void_code,ms_IS_VERIFIED=$ms_qry_row[IS_VERIFIED]\n";
			exit;

		   }
		  if($vo_qry_row[IS_VERIFIED]!='Y')
		   {
			echo $message="Validation: Documents not submmited at vo level. ";//DISTRICT_ID=$DISTRICT_ID,MANDAL_ID=$MANDAL_ID,void_code=$void_code,ms_IS_VERIFIED=$ms_qry_row[IS_VERIFIED],vo_IS_VERIFIED=$vo_qry_row[IS_VERIFIED]\n";
			exit;

		   }
		}
		//End Putting conditions for Documents Verification By Ashok


		if($status_valied>='1')
		{

		$message=" $caller Authenticated in VO_INFO  ";
		
		}
		else
		{

		echo $message="Validation:  $caller is not present in VO_INFO ";
		exit;
		}
		

		$check_urban=mssql_num_rows(mssql_query("select VO_ID from VO_RURALTOURBAN(nolock) where VO_ID='$void_code'"));
                if($check_urban == "1"){echo $message="Validation: VO_RURALTOURBAN ,Hangup the call"; exit;  }
	################################## check_meeting_date ###########################################

	$vo_grade_rs=mssql_fetch_array(mssql_query("select GRADE,LOAN_ELIGBLE from VO_CREDIT_LIMIT where VO_ID='$void_code'"));
	$vo_grade=$vo_grade_rs['GRADE'];
        $vo_loan_eligble=$vo_grade_rs['LOAN_ELIGBLE'];
	
	    $vo_dist_code=substr($void_code,2,2);
$allow_loan=0;
if($vo_grade == 'A' || $vo_grade == 'B' || $vo_grade == 'C' || $vo_grade == 'D')
{
$allow_loan=1;
}
else{
			$allow_loan=check_meeting_date($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);
	}

		if($allow_loan == 0){
		exit("Validation: No access today");
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/no_access_today";			
		}

function check_vo_samrudhi($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs){
		$test_vonumber=$GLOBALS['test_vonumber'];
		/*$vo_samrudhi_validation=0;
		$vo_samrudhi=mssql_fetch_array(mssql_query("select vo_id from samrudhi_percentage() where samrudhi_percentage>=50 and VO_ID='$void_code'"));
		$vs_vo_id=$vo_samrudhi['vo_id'];
	
//		//if($vs_percentage>='80')	
//		if($vs_percentage>='50')

		
		if($vs_vo_id == $void_code)
			{
			$vo_samrudhi_validation=1;
			}
		else
		{
			if($x>='1')
		{
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_samrudhi_below_50";
		$x=$x-1;
		
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		   // mssql_close($link);
		  // exit;
		   }	
		}
		return $vo_samrudhi_validation;*/
$vo_samrudhi=mssql_fetch_array(mssql_query("select vo_id,samrudhi_percentage from samrudhi_percentage() where VO_ID='$void_code'"));
		$vs_vo_id=$vo_samrudhi['vo_id'];
		$vo_samrudhi_percentage=$vo_samrudhi['samrudhi_percentage'];
		return $vo_samrudhi_percentage;
	}


		$vo_samrudhi_percentage=check_vo_samrudhi($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs);

		if($vo_samrudhi_percentage<50)
		{
		echo $message="Validation: VO samrudhi percentage is :$vo_samrudhi_percentage below 50";
		//echo $message="Validation: vo_samrudhi_below_50";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
				
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_samrudhi_below_50";
		//$agi-> stream_file($wfile, '#');
		//mssql_close($link);
		//$agi->Hangup();
		exit;
		}
		/*else{
			echo '<br>'.$message="vo_samrudhi SUCCESS : $vo_samrudhi_validation ";
		//$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		}*/
			
function check_vo_cif_recovery($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$shg_code){

			$test_vonumber=$GLOBALS['test_vonumber'];
			$override=0;
			$recovery_count=mssql_num_rows(mssql_query("select  * from SAMSNBSTG.SN.VO_RECOVERY_STATUS(nolock) where vo_id='$void_code' "));
			if($recovery_count >= 1){
			$message=" select  * from SAMSNBSTG.SN.VO_RECOVERY_STATUS(nolock) where vo_id='$void_code' ";
			$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
			$cif=mssql_fetch_array(mssql_query("select  * from SAMSNBSTG.SN.VO_RECOVERY_STATUS(nolock) where vo_id='$void_code' "));
			$cif_recovery=$cif['RECOVERY'];
			if($cif_recovery == 0){
				$cif_DMD=$cif['DMD'];
				if($cif_DMD == 0){
					$cif_recovery=100;
				}
			}
			
			}else{
				$override=1;
			}
			

			if($override == 1){
				//echo $message="Validation: Record Not Found in SAMSNBSTG.SN.VO_RECOVERY_STATUS(nolock) where vo_id='$void_code'";
				//$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
				$cif_recovery=100;
				//exit;
			}
			
		return $cif_recovery;
		}

	
	$cif_recovery=check_vo_cif_recovery($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$shg_code);
		
		if($cif_recovery<90)
		{
		
		echo $message="Validation: VO Recovery less than 90 : $cif_recovery ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);	
			
		   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/cif_recovery";
			  // $agi-> stream_file($wfile, '#');
			   //mssql_close($link);
			   //$agi->Hangup();
			   exit;
		}else{
		//echo "<br>".$message=" cif_recovery SUCCESS : $cif_recovery ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
			//checking of NPA at VO level By Ashok
			//$npa_qry="SELECT top 1 1 FROM SAMSNBSTG.SN.SHG_MEMBER_LOAN_DEMAND_STATUS WHERE FYEAR='2018' and BAL>0 and  DATEDIFF(dd,OVERDUE_SINCE,getdate()-1)>90 AND VO_ID='$void_code'";
			$npa_cnt=0;
//			$npa_cnt=mssql_num_rows(mssql_query($npa_qry));
			// New NPA Validation on 11-09-2017 START
			//$npa_qry="SELECT ISNULL(CONVERT(NUMERIC(10,2),((SUM(CASE WHEN DATEDIFF(DAY,ISNULL(OVERDUE_SINCE ,getdate()),getdate()) > 90 THEN OUTSTANDING ELSE 0 END))/(nullif(SUM(OUTSTANDING),0)))*100),0) NPA_PER FROM SAMSNBSTG.SN.SHG_MEMBER_LOAN_DEMAND_STATUS WHERE  VO_ID='$void_code' AND FYEAR=YEAR(dateadd(month, 9, GETDATE()))-1";
			 $npa_qry = "USP_GET_IVRS_NPA '$void_code'";
			$npa_rslt = mssql_query($npa_qry);
		        $npa_cnt_array=mssql_fetch_array($npa_rslt);
			$npa_cnt= $npa_cnt_array[NPA_PER];
			// New NPA Validation on 11-09-2017 END
			if($caller==$test_vonumber)
			{
			$npa_cnt=0;
			}
			if($npa_cnt > 0.5)
			{
			echo $message="Validation: This VO is having NPA above 0.5% ";//:$npa_qry  ";
			$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
			
		   	  $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_npa";
			 // $agi-> stream_file($wfile, '#');
			  //mssql_close($link);
			 // $agi->Hangup();
			  exit;
			} 
		}

	list_SHGs($void_code);
} 
if($_REQUEST[Action]=='ValidateSHG')
{
$shg_id= trim($_REQUEST[shg_id]);
$void_code = $_REQUEST[vo_id];
//echo "$shg_id---$vo_id";
	$test_vonumber=$GLOBALS['test_vonumber'];
	 $project=1;
	 // $res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/shg_code", 10000, 3);
	  $shg_code_rs=$res_dtmf ["result"];
	  $shg_name_array=mssql_query("select * from SHG_INFO(nolock) where  VO_ID='$void_code' and  TRANS_SHG_ID='$shg_id'");
	  $status=mssql_num_rows($shg_name_array);	
	  $shg_name_array=mssql_fetch_array($shg_name_array);
	  $shg_name=$shg_name_array['SHG_NAME'];
	  $shg_code=$shg_name_array['TRANS_SHG_ID'];
	  $message="SHG DETAILS: TRANS_SHG_ID: $shg_code ,  SHG_NAME: $shg_name";
	  $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
//echo"Validation: $shg_id--$shg_code--select * from SHG_INFO(nolock) where  VO_ID='$void_code' and  TRANS_SHG_ID='$shg_id'";	
	  $shg_name=str_replace(' ','_',$shg_name);
	  $shg_name=str_replace('.','_',$shg_name);    
   	  $shg_name=str_replace(' ','_',$shg_name); 
   	  $shg_name=str_replace('__','_',$shg_name);  
	$due_id_lst=0;
	$is_over_due='N';


//START CHECKING IN IHHL_TAGGED_MEMBER_DETAILS
$ihhl_member_count_res=mssql_query("select * from IHHL_TAGGED_MEMBER_DETAILS with (nolock) where SHG_ID='$shg_code'");
$ihhl_member_count = mssql_num_rows($ihhl_member_count_res);

	if($ihhl_member_count == 0){
		$x=$x-1;
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/voice_IHHL_SHG";
		echo "Validation: This SHG is not eligible to take IHHL Loan";exit;
	}

//END CHECKING IN IHHL_TAGGED_MEMBER_DETAILS

	$triggered_loan_count_res=mssql_fetch_array(mssql_query("select count(SHG_MEMBER_LOAN_ACCNO) from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW with (nolock) where SHG_ID='$shg_code'"));
		$triggered_loan_count=$triggered_loan_count_res[0];
		$message="Triggered Loans: $triggered_loan_count select count(SHG_MEMBER_LOAN_ACCNO) from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW with (nolock) where SHG_ID='$shg_code'";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
			$curr_odos=0;
			if($triggered_loan_count > 0)
			{
				$shg_overdue_res=mssql_fetch_array(mssql_query("select OVERDUE,CURRENT_OVERDUE,CURRENT_OUTSTANDING from SAMSNBSTG.SN.SHG_OVERDUE_STATUS with (nolock) where OVERDUE>0 and SHG_ID='$shg_code'"));
				$shg_ovrdue=$shg_overdue_res[0];
				if($shg_ovrdue>0)
				{
				   $message="SHG HAS OVERDUE AMT $shg_ovrdue is gretaer than 0 in SAMSNBSTG.SN.SHG_OVERDUE_STATUS,system checks shg OD AND OS in SN";
				   $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
				   $curr_shg_odos=0;
				   $curr_shg_odos=$shg_overdue_res[1]+$shg_overdue_res[2];
				      if($curr_shg_odos<=100)
				      {
					$curr_odos=$curr_shg_odos;
				  	$message="SHG HAS CURRENT_OD,CURRENT_OS:$curr_odos Less than 100, so for this shg we are allowing SN loan. ";
				   	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
				      }
				      else
				      { 
						echo "Validation: SHG has overdue : $shg_ovrdue";
						//mssql_close($link);
		  				exit;

				     }
				
				}

			}
	$overdue_rs= mssql_fetch_array(mssql_query("select sum(OVERDUE) from SHG_OVER_DUES(nolock) where SHG_ID='$shg_code' group by SHG_ID"));
	$overdue_amt=$overdue_rs[0];
	if($overdue_amt>0 && $overdue_amt<=10000)
	{
	$is_over_due='Y';	
	}
	  if($overdue_amt>10000)
	{	
	echo $message="Validation: Bank Linkage AMT $overdue_amt is gretaer than 10000";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		exit;
	}
	$path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";
  $cmd="/bin/ls ".$path;
    $list=exec($cmd);

	$shg_active_rs=mssql_fetch_array(mssql_query("select IS_VALID from SHG_INFO(nolock) where VO_ID='$void_code' and TRANS_SHG_ID='$shg_code'"));
	$shg_active_stat=$shg_active_rs['IS_VALID'];
	if($shg_active_stat=='Y')
	{
	$message="SUCCESS: SHG SB Account Details are valid (select IS_VALID from SHG_INFO(nolock) where VO_ID='$void_code' and TRANS_SHG_ID='$shg_code') ,IS_VALID: $shg_active_stat ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
	}
	else
	{
		echo "Validation: FAIL: SHG SB Account Details are invalid";
		$message="Validation: FAIL: SHG SB Account Details are invalid (select IS_VALID from SHG_INFO where VO_ID='$void_code' and TRANS_SHG_ID='$shg_code') ,IS_VALID: $shg_active_stat";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		exit;

	}

 $check_shg_samrudhi=check_shg_samrudhi($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$shg_code);

					if($check_shg_samrudhi == 0)
					{

						echo $message="Validation: FAIL: SHG SAMRUDHI FAILED ";
						$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
						exit;
	}else{
		$message="SUCCESS: SHG SAMRUDHI PASSED ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
	}

	$shg_grade_dist_code=substr($void_code,2,2);
	$shg_grade_mandal_code=substr($void_code,4,2);
	$vo_grade_rs=mssql_fetch_array(mssql_query("select GRADE from MMS_CREDIT_LIMIT(nolock) where DISTRICT_ID='$shg_grade_dist_code' and MANDAL_ID='$shg_grade_mandal_code'"));
	$mms_grade=$vo_grade_rs['GRADE'];
    $vo_grade_rs_rej=mssql_fetch_array(mssql_query("select A.GRADE,A.ACTUAL_CREDIT_LIMIT+ISNULL(B.ACTUAL_CREDIT_LIMIT,0) AS ACTUAL_CREDIT_LIMIT,A.POP_CREDIT_LIMIT,A.NONPOP_CREDIT_LIMIT,GEN_LOAN_PER from VO_CREDIT_LIMIT(nolock) A LEFT JOIN VO_CREDIT_LIMIT25K(NOLOCK) B ON A.VO_ID=B.VO_ID where A.VO_ID='$void_code'"));
	$vo_grade=$vo_grade_rs_rej['GRADE'];
	$vo_actual_credit=$vo_grade_rs_rej['ACTUAL_CREDIT_LIMIT'];
	$message="Validating VO GRADE and ACTUAL_CREDIT_LIMIT,MMS Grade , VO Grade: $vo_grade,VO ACTUAL_CREDIT_LIMIT : $vo_actual_credit,MMS Grade :$mms_grade ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		if($vo_grade=='E')
	{
	echo	$message="Validation: VO grade $vo_grade";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_rej_grade_ine";
		   //$agi-> stream_file($wfile, '#');
		//mssql_close($link);
		   //$agi->Hangup();
		   exit;
	}

	if($vo_grade=='F')
	{
	echo	$message="Validation: VO grade $vo_grade";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
	   $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_rej_grade_inf";
		   exit;
	}
	if($vo_actual_credit=='0')
	{
	echo	$message="Validation:vo_actual_credit $vo_actual_credit";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		
	      $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/zero_credit_limit";
		   exit;
		}

	list_members($void_code,$shg_code);
}
function list_members($void_code,$shg_code)
{

	$member_result=mssql_query("select * from shg_member_info(nolock) where VO_ID='$void_code' and SHG_ID = '$shg_code' and member_is_active='Y'");
	while($member_row=mssql_fetch_array($member_result))
	{
		if ($member_row[IS_POP_MEM]=='Y')
		{
			$member_row_1='pop';
		}
		else
		{
			$member_row_1='non pop';
		}
//		echo "@@".$member_row[MEMBER_NAME]." - ".$member_row[MEMBER_ID]." - ".$member_row_1;
//		echo "@@".$member_row[MEMBER_NAME]." - ".$member_row[MEMBER_ID]." - (".trim($member_row[SHORT_CODE]).")"." - ".$member_row_1;
		echo "@@".$member_row[MEMBER_NAME]." - ".$member_row[MEMBER_ID]." - (".trim($member_row[SHORT_CODE]).")"." - ".$member_row_1." - ".$member_row[FATHER_HUSBAND_NAME];
	}
	

}
function list_SHGs($void_code)
{

	$member_result=mssql_query("select * from shg_info(nolock) where VO_ID='$void_code' and VO_ID ! = TRANS_SHG_ID");
	while($member_row=mssql_fetch_array($member_result))
	{
		
//		echo "@@".$member_row[SHG_NAME]." - ".$member_row[TRANS_SHG_ID];
		echo "@@".$member_row[SHG_NAME]." - ".$member_row[TRANS_SHG_ID]." - (".trim($member_row[SHORT_SHG_CODE]).")";
	}

}

function check_shg_samrudhi($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$shg_code){
	
/*	$vo_name_array=mssql_query("select * from vo_info(nolock) where TRANS_VO_ID='$void_code' order by CREATED_DATE  desc");
$vo_name_array=mssql_fetch_array($vo_name_array);
$dist_id=$vo_name_array['DISTRICT_ID'];
$mandal_id=$vo_name_array['MANDAL_ID'];	
	 $shg_samrudhi_query="SELECT SHG_ID 
FROM (         
SELECT SI.VO_ID,SHG_ID,ISNULL(SI.SHG_REG_DATE,'2012-10-31 00:00:00.000') SHG_REG_DATE,SUM(DEPOSITED_AMOUNT) SAM_AMT                         
FROM SHG_DEPOSIT_INFO S (NOLOCK)                         
INNER JOIN SHG_INFO SI (NOLOCK) ON S.SHG_ID=SI.TRANS_SHG_ID                         
INNER JOIN BANKS_DEPOSIT_INFO B (NOLOCK) ON S.BANK_REF_NO=B.BANK_REF_NO                         
WHERE SI.TRANS_SHG_ID='$shg_code' AND B.SCHEME_ID='01' and DEPOSIT_STATUS='CLOSED' AND SI.IS_MEPMA='N'  --AND SI.DISTRICT_ID='$dist_id'                    
GROUP BY SI.VO_ID,SHG_ID,SI.SHG_REG_DATE                         
) D                         
 WHERE D.SAM_AMT>=case when d.SHG_REG_DATE<CONVERT(DATETIME,CONVERT(VARCHAR(10),'01/11/2012',103),103)                       
 then CAST(DATEDIFF(M,CONVERT(DATETIME,CONVERT(VARCHAR(10),'01/11/2012',103),103)  , 
   ( CASE WHEN  MONTH(GETDATE()) IN (4,5,10,11) THEN  DATEADD(Q,-1,DATEADD(Q,-1,DATEADD(Q,DATEDIFF(Q,0,GETDATE()),0)))-1 
          WHEN  MONTH(GETDATE()) IN (6,12) THEN DATEADD(Q,DATEDIFF(Q,0,GETDATE()),0)-1 
          WHEN MONTH(GETDATE()) IN (7,8,9,1,2,3) THEN DATEADD(Q,-1,DATEADD(Q,DATEDIFF(Q,0,GETDATE()),0))  END  ) )  AS INT)*100     
          
          ELSE CAST ( DATEDIFF(M,d.SHG_REG_DATE, ( CASE WHEN  MONTH(GETDATE()) IN (4,5,10,11) THEN  DATEADD(Q,-1,DATEADD(Q,-1,DATEADD(Q,DATEDIFF(Q,0,GETDATE()),0)))-1 
          WHEN  MONTH(GETDATE()) IN (6,12) THEN DATEADD(Q,DATEDIFF(Q,0,GETDATE()),0)-1 
          WHEN MONTH(GETDATE()) IN (7,8,9,1,2,3) THEN DATEADD(Q,-1,DATEADD(Q,DATEDIFF(Q,0,GETDATE()),0))  END  ) )  AS INT)*100 END 
UNION 
SELECT SHG_ID 
FROM ( 
SELECT SI.VO_ID,SHG_ID,ISNULL(SI.SHG_REG_DATE,'2012-10-31 00:00:00.000') SHG_REG_DATE,SUM(DEPOSITED_AMOUNT) SAM_AMT               
FROM SHG_DEPOSIT_INFO S (NOLOCK)                         
INNER JOIN SHG_INFO SI (NOLOCK) ON S.SHG_ID=SI.TRANS_SHG_ID                         
INNER JOIN BANKS_DEPOSIT_INFO B (NOLOCK) ON S.BANK_REF_NO=B.BANK_REF_NO                         
WHERE SI.TRANS_SHG_ID='$shg_code' AND B.SCHEME_ID='01' and DEPOSIT_STATUS='CLOSED' AND SI.IS_MEPMA='Y' -- AND SI.DISTRICT_ID='$dist_id'         
GROUP BY SI.VO_ID,SHG_ID,SI.SHG_REG_DATE 
) D 
 WHERE D.SAM_AMT>=case when d.SHG_REG_DATE<CONVERT(DATETIME,CONVERT(VARCHAR(10),'01/04/2013',103),103)                       
 then CAST(DATEDIFF(M,CONVERT(DATETIME,CONVERT(VARCHAR(10),'01/04/2013',103),103)  , 
   ( CASE WHEN  MONTH(GETDATE()) IN (4,5,10,11) THEN  DATEADD(Q,-1,DATEADD(Q,-1,DATEADD(Q,DATEDIFF(Q,0,GETDATE()),0)))-1 
          WHEN  MONTH(GETDATE()) IN (6,12) THEN DATEADD(Q,DATEDIFF(Q,0,GETDATE()),0)-1 
          WHEN MONTH(GETDATE()) IN (7,8,9,1,2,3) THEN DATEADD(Q,-1,DATEADD(Q,DATEDIFF(Q,0,GETDATE()),0))  END  ) )  AS INT)*100     
          
          ELSE CAST ( DATEDIFF(M,d.SHG_REG_DATE, ( CASE WHEN  MONTH(GETDATE()) IN (4,5,10,11) THEN  DATEADD(Q,-1,DATEADD(Q,-1,DATEADD(Q,DATEDIFF(Q,0,GETDATE()),0)))-1 
          WHEN  MONTH(GETDATE()) IN (6,12) THEN DATEADD(Q,DATEDIFF(Q,0,GETDATE()),0)-1 
          WHEN MONTH(GETDATE()) IN (7,8,9,1,2,3) THEN DATEADD(Q,-1,DATEADD(Q,DATEDIFF(Q,0,GETDATE()),0))  END  ) )  AS INT)*100     END
UNION 
select SHG_ID FROM SHG_DEPOSIT_INFO S (NOLOCK)                                  
INNER JOIN SHG_INFO SI (NOLOCK) ON S.SHG_ID=SI.TRANS_SHG_ID                                  
INNER JOIN BANKS_DEPOSIT_INFO B (NOLOCK) ON S.BANK_REF_NO=B.BANK_REF_NO                                  
WHERE B.SCHEME_ID='01' and DEPOSIT_STATUS='CLOSED' AND SI.TRANS_SHG_ID='$shg_code' and CAST(DEPOSITED_DATE AS DATE)>'2015-12-31'    
 AND SI.NEW_SAMRUDHI_ELIGBLE='Y'   AND SI.IS_MEPMA='N'   
group by SHG_ID having sum(DEPOSITED_AMOUNT)>=2400 

UNION 
select SHG_ID FROM SHG_DEPOSIT_INFO S (NOLOCK)                                  
INNER JOIN SHG_INFO SI (NOLOCK) ON S.SHG_ID=SI.TRANS_SHG_ID                                  
INNER JOIN BANKS_DEPOSIT_INFO B (NOLOCK) ON S.BANK_REF_NO=B.BANK_REF_NO                                  
WHERE B.SCHEME_ID='01' and DEPOSIT_STATUS='CLOSED' AND SI.TRANS_SHG_ID='$shg_code' and CAST(DEPOSITED_DATE AS DATE)>'2015-12-31'    
 AND SI.NEW_SAMRUDHI_ELIGBLE='Y'   AND SI.IS_MEPMA='Y'   
group by SHG_ID having sum(DEPOSITED_AMOUNT)>=2400    "; */
$shg_samrudhi_query="select *,DEPOSIT_PER from SHG_SAMRUDHI_PERCENTAGE() where shg_id='$shg_code'";
$total_deposit_rs=mssql_fetch_array(mssql_query($shg_samrudhi_query));
$check_shg_samrudhi=$total_deposit_rs['DEPOSIT_PER'];
			
		if($check_shg_samrudhi > 99)
		{
		$shg_samrudhi_validation=1;
		}
		else{
		$shg_samrudhi_validation=0;	
			
			}
			return $shg_samrudhi_validation;

	}

function shg_limits($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$project,$shg_code){
			
			$test_vonumber=$GLOBALS['test_vonumber'];
			$members_query="select MEMBER_ID from shg_member_info(nolock) where SHG_ID='$shg_code'";
			$shg_members=mssql_num_rows(mssql_query($members_query));
			$shg_limit_array=array();
			
			if($shg_members >= 12){
				$shg_max_loans_total=9;
				$shg_max_loans_ivrs=6;
				$shg_max_credit_limit=300000;
			}elseif($shg_members == 10 || $shg_members == 11){
				$shg_max_loans_total=8;
				$shg_max_loans_ivrs=6;
				$shg_max_credit_limit=250000;
			}elseif($shg_members <= 9){
				
				$shg_members_75percent=ceil($shg_members*0.75);
				if($shg_members_75percent > 6){
					$shg_max_loans_total=$shg_members_75percent;
				}else{
					$shg_max_loans_total=6;
				}
				
				$shg_max_loans_ivrs=4;
				$shg_max_credit_limit=200000;
			}
			
			$shg_limit_array[$shg_code]['shg_max_loans_total']=$shg_max_loans_total;
			$shg_limit_array[$shg_code]['shg_max_loans_ivrs']=$shg_max_loans_ivrs;
			$shg_limit_array[$shg_code]['shg_max_credit_limit']=$shg_max_credit_limit;
			
			$message="SHG LIMITS in project $project :: shg_max_loans_total : $shg_max_loans_total ,shg_max_loans_ivrs: $shg_max_loans_ivrs, shg_max_credit_limit: $shg_max_credit_limit";
			$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
			
			return $shg_limit_array;
		}



if($_REQUEST[Action]=='GetMemberMobile')
{
$memberId=$_REQUEST[memberId];
$shgId = $_REQUEST[shgId];

$role_query = "select * from shg_member_info where member_ID='$memberId' and shg_id='$shgId'";
$role_res = mssql_query($role_query);
$row = mssql_fetch_array($role_res);
echo $row[MOBILE_NUM];exit;

}
if($_REQUEST[Action]=='ValidateMember')
{

$void_code=$_REQUEST[vo_id];
$shg_code = $_REQUEST[shg_id];

$member_type= $_REQUEST[member_type];
$memberId = $_REQUEST[memberId];
$loan_amount = $_REQUEST[loan_amount];
$memberMobNo = $_REQUEST[memberMobNo];
//Action=ValidateMember&vo_id=011916080130102&shg_id=011916080130101020&member_type=Y&memberId=01191608013010102004&loan_category=IG&reason_loan=104
$loan_type = $_REQUEST[loan_category];
$reason_loan = strtolower($_REQUEST[reason_loan]);
$is_over_due='N';
$caller = $_REQUEST[mobile_number];


$member_id_rs=mssql_fetch_array(mssql_query("select MEMBER_ID,SP_ACTIVITY,MOBILE_NUM,UID_NO,AGE,IS_POP_MEM from SHG_MEMBER_INFO(nolock)  where  SHG_ID='$shg_code' and MEMBER_ID='$memberId'"));
 $member_id=$member_id_rs['MEMBER_ID'];
 $member_mobile_num=$member_id_rs['MOBILE_NUM'];
 $member_uid=$member_id_rs['UID_NO'];
  $member_age=$member_id_rs['AGE'];
  $member_type=$member_id_rs['IS_POP_MEM'];
 				
 $message="Member details:  MEMBER_ID: $member_id , Short Code:  $memberId,member_uid: $member_uid , member_age:$member_age , member_type:$member_type , member_mobile_num: $member_mobile_num";
$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);



//START CHECKING IN IHHL_TAGGED_MEMBER_DETAILS
$ihhl_member_count_res=mssql_query("select * from IHHL_TAGGED_MEMBER_DETAILS with (nolock) where SHG_ID='$shg_code' and MEMBER_ID='$member_id'");
$ihhl_member_count = mssql_num_rows($ihhl_member_count_res);

	if($ihhl_member_count == 0){
			$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/voice_IHHL_MEMBER";
			echo "Validation: This Member is not eligible to take IHHL Loan";
			exit;
	}

//END CHECKING IN IHHL_TAGGED_MEMBER_DETAILS

//START CHECKING MEMBER_OVERDUE
$ihhl_member_overdue_res=mssql_fetch_array(mssql_query("select LOAN_DUE from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock)   where SHG_ID='$shg_code' and SHG_MEM_LOAN_SHORT_CODE='$member_short_code' and IS_CLOSED=0"));

if($ihhl_member_overdue_res['LOAN_DUE']>0)
{
		if($x>='1')
		{
		$x=$x-1;
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_sn_overdue"; //member overdue
		//$agi-> stream_file($wfile, '#');
		//$this->play_amount($ihhl_member_overdue_res[LOAN_DUE],$agi);
		$wfile="/var/lib/asterisk/sounds/telugu_digits/rupees";
		//$agi-> stream_file($wfile, '#');
		$x=$x-1;

	//$this-> IHHL_member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos) ;
		   }else
		   {
		    $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/wrong_entry";
		    //$agi-> stream_file($wfile, '#');
		    mssql_close($link);
		    //$agi->Hangup();
		    exit;
		   }
}
//END CHECKING MEMBER_OVERDUE

if(($member_age =="" || $member_age=="NULL")){
 	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_age_not_update";
	//$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	//$this-> IHHL_member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos) ;
	mssql_close($link);
	//$agi->Hangup();
	exit;
  }
 elseif($member_age >= 60 || $member_age <= 18)
 {
 	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_age_btw_1860";
	//$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	//$this-> IHHL_member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos) ;
	mssql_close($link);
	//$agi->Hangup();
	exit;
 }
//Member Mobile Number Updation START 01-08-2017
	
//    $mobile_count=mssql_num_rows(mssql_query("select MEMBER_ID from SHG_MEMBER_INFO(nolock)  where  MOBILE_NUM='$memberMobNo'"));
    $start_digit=substr($memberMobNo,0,1);

	if(trim($memberMobNo)=='')	 	
	{echo $message="Validation: Please enter Mobile Number";exit;}
	
	if($memberMobNo>'1' && strlen($memberMobNo)=='10' && ($start_digit=='9'||$start_digit=='8'||$start_digit=='7'||$start_digit=='6'))
	{
        if($_SERVER[REMOTE_ADDR]!='182.19.66.187' && $_SERVER[REMOTE_ADDR]!= '202.56.197.65')
//	echo $message="Validation: "."UPDATE SHG_MEMBER_INFO SET MOBILE_NUM='$memberMobNo'  where SHG_ID='$shg_code' and member_id='$memberId'";exit;
	mssql_query("UPDATE SHG_MEMBER_INFO SET MOBILE_NUM='$memberMobNo'  where SHG_ID='$shg_code' and member_id='$memberId'");
	}
	else
	{
	echo $message="Validation: Please enter Valid Mobile Number";exit;
	}
//Member Mobile Number Updation END 01-08-2017


member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos,$memberId,$member_type,$loan_type,$reason_loan,$loan_amount);
}

function member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos,$memberId,$member_type,$loan_type,$reason_loan,$loan_amount){
			
$member_id_rs=mssql_fetch_array(mssql_query("select MEMBER_ID,SP_ACTIVITY,MOBILE_NUM,UID_NO,AGE,IS_POP_MEM,SHORT_CODE from SHG_MEMBER_INFO(nolock)  where  SHG_ID='$shg_code' and MEMBER_ID='$memberId'"));
 $member_id=$member_id_rs['MEMBER_ID'];
 $member_mobile_num=$member_id_rs['MOBILE_NUM'];
 $member_uid=$member_id_rs['UID_NO'];
  $member_age=$member_id_rs['AGE'];
  $IS_POP_MEM=$member_id_rs['IS_POP_MEM'];
$member_short_code = str_replace(" ","",$member_id_rs['SHORT_CODE']);
 		
//echo "Validation:".$message="Member details:  MEMBER_ID: $member_id , Short Code:  $member_short_code,member_uid: $member_uid , member_age:$member_age , IS_POP_MEM:$IS_POP_MEM , member_mobile_num: $member_mobile_num";
			$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);


	$member_before_loan=mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST(nolock) where SHG_ID='$shg_code' and MEMBER_ID='$member_id'  and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D' and project_type='71'"));
	$member_rej_cnt_sht = mssql_num_rows(mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where  SHG_ID='$shg_code'  and  MEMBER_LONG_CODE='$member_id'  and STATUS_ID='11' and project_type='71' ")); 
	
	$member_repaid_loans = mssql_num_rows(mssql_query("select b.VO_ID from SHG_LOAN_APPLICATION(nolock) a,SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) b where a.SHG_ID='$shg_code' and a.MEMBER_LONG_CODE='$member_id' and a.SHG_MEM_LOAN_ACCNO=b.SHG_MEMBER_LOAN_ACCNO and b.IS_CLOSED='1'  and a.project_type='71'"));

if($member_before_loan == 0 ){
		 $member_repaid_loans=0;
		}

		$member_before_loan=$member_before_loan-$member_rej_cnt_sht;//-$member_repaid_loans;
		$message =($member_before_loan-$member_rej_cnt_sht-$member_repaid_loans)."member_before_loan:$member_before_loan-member_rej_cnt_sht:$member_rej_cnt_sht-member_repaid_loans:$member_repaid_loans";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		if($member_before_loan >='1')
		{
		echo "Validation:Loan already applied by this member";exit;
		}

 if($member_age > 63 && $member_age != 0){
	echo "Validation: Member Age is Greater than 63 ";
 	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_age_greater_60";
	//$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	exit;	
 }
 
 
	$member_before_loan=mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST(nolock) where SHG_ID='$shg_code' and MEMBER_ID='$member_id'  and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D' and project_type='71'"));

	$member_rej_cnt_sht = mssql_num_rows(mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where  SHG_ID='$shg_code'  and  MEMBER_LONG_CODE='$member_id'  and STATUS_ID='11' and project_type='71'")); 	

	$member_repaid_loans = mssql_num_rows(mssql_query("select b.VO_ID from SHG_LOAN_APPLICATION(nolock) a,SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) b where a.SHG_ID='$shg_code' and a.MEMBER_LONG_CODE='$member_id' and a.SHG_MEM_LOAN_ACCNO=b.SHG_MEMBER_LOAN_ACCNO and b.IS_CLOSED='1' and project_type='71'"));

	$member_repaid_loans_tsp = mssql_num_rows(mssql_query("select b.VO_ID from SHG_LOAN_APPLICATION(nolock) a,SAMSNBSTG.SN.TSP_SHG_MEMBER_LOAN_STATUS_NEW(nolock) b where a.SHG_ID='$shg_code' and a.MEMBER_LONG_CODE='$member_id' and a.SHG_MEM_LOAN_ACCNO=b.SHG_MEMBER_LOAN_ACCNO and b.IS_CLOSED='1'"));

		if($member_before_loan == 0 ){
		 $member_repaid_loans=0;
		}
//	$member_before_loan=$member_before_loan-$member_rej_cnt_sht-$member_repaid_loans-$member_repaid_loans_tsp; 
	$member_before_loan=$member_before_loan-$member_rej_cnt_sht;// should not allow one more IHHL loan to member who availed ihhl loan already.
	 
	if($member_before_loan < 0){
		$member_before_loan=0;
	}

	if($member_before_loan != 0)
	{
	
	echo "Validation: ".$message="Member loan already applied";
	log_ivr($ivr_call_id,$message);	
		
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_already_applied";
	//$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	exit;	
	}	

  //$reason_loan =vo_group_entry($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name);


$reason_loan = 'IHHL';
$loan_amount='12000';

 if(($member_type=='Y'||$member_type=='N')&& (strlen($member_short_code)==2 && $member_before_loan=='0' && strlen($member_id)>1))
 {
 $duration='12';
 $etime=date('Y-m-d H:i:s');
 
############START
	$mms_dist_code=substr($void_code,2,2);
      $mms_mandal_code=substr($void_code,4,2);
if($_SESSION[USER_NAME]==NULL && strlen($_SESSION[desk_IMEI_NO])==15)
{
$_SESSION[USER_NAME]=$_SESSION[desk_IMEI_NO];
}

                                $paidDate =date('Y-m-d H:i:s');
                                $rand_number=rand(1,12000);
                                $insertIvrsCallInfoQry ="INSERT INTO IVRS_CALL_INFO (CALLER,CREATED_DATE,EXTENSION,CALL_STATUS,UNIQUE_ID,REQ_TYPE) VALUES ('".$caller."','".$paidDate."','".$caller."','".$_SESSION[USER_NAME]."','".$rand_number."','W')";
if($_SERVER[REMOTE_ADDR]!='182.19.66.187' && $_SERVER[REMOTE_ADDR]!= '202.56.197.65')
                                mssql_query("$insertIvrsCallInfoQry");

$res=mssql_query("SELECT ID FROM IVRS_CALL_INFO(NOLOCK) WHERE CALLER='".$caller."' AND UNIQUE_ID='".$rand_number."' AND REQ_TYPE='W' ORDER BY CREATED_DATE DESC");
$row = mssql_fetch_array($res);
$ivrs_id =$row['ID'];
$PROJECT_TYPE = 71;
 $shg_ivr_loan_rs=mssql_fetch_array(mssql_query("select * from IVRS_LOAN_REQUEST(nolock) where  SHG_ID='$shg_code' and project_type='71' order by CREATED_DATE  desc "));
	  $shg_ivr_loan_num=$shg_ivr_loan_rs['SHG_IVRS_LOAN_NO'];	
	  $shg_ivr_loan_num=$shg_ivr_loan_num+1;


if($unique_id==NULL && strlen($_SESSION[desk_IMEI_NO])==15)
{
$unique_id=$_SESSION[desk_IMEI_NO];
}
$loanRequestInsertQry="insert into IVRS_LOAN_REQUEST(VO_ID,SHG_ID,LOAN_AMOUNT,LOAN_REPAY_DURATION,CREATED_DATE,MOBILE,PURPOSE,IS_POP,IVRS_ID,LOAN_SANCTIONED_DATE,LOAN_STATUS,IVRS_CALL_ID,SHG_IVRS_LOAN_NO,SHG_LOAN_MARRIAGE,PROJECT_TYPE,IS_OVERDUE,OVERDUE_ID,IS_ADDITIONAL_AMT,SHORT_CODE,MEMBER_ID,UID_NO,DISTRICT_ID,MANDAL_ID,overdue_amount) VALUES ('$void_code','$shg_code','$loan_amount','$duration','$etime','$caller','$reason_loan','$member_type','$ivrs_id','$etime','open','$unique_id','$shg_ivr_loan_num','$marriage_amt','$PROJECT_TYPE','$is_over_due','$due_id_lst','$is_eligible','$member_short_code','$member_id','$member_uid','$mms_dist_code','$mms_mandal_code','$curr_odos.0')";
#############END

//$loanRequestInsertQry="insert into IVRS_LOAN_REQUEST(VO_ID,SHG_ID,LOAN_AMOUNT,LOAN_REPAY_DURATION,CREATED_DATE,MOBILE,PURPOSE,IS_POP,IVRS_ID,LOAN_SANCTIONED_DATE,LOAN_STATUS,IVRS_CALL_ID,SHG_IVRS_LOAN_NO,SHG_LOAN_MARRIAGE,PROJECT_TYPE,IS_OVERDUE,OVERDUE_ID,IS_ADDITIONAL_AMT,SHORT_CODE,MEMBER_ID,UID_NO,DISTRICT_ID,MANDAL_ID,overdue_amount) VALUES ('$void_code','$shg_code','$loan_amount','$duration','$etime','$caller','$reason_loan','$member_type','web - $_SESSION[USER_NAME]','$etime','open','$unique_id','$shg_ivr_loan_num','$marriage_amt','$PROJECT_TYPE','$is_over_due','$due_id_lst','$is_eligible','$member_short_code','$member_id','$member_uid','$mms_dist_code','$mms_mandal_code','$curr_odos.0')";	

if($_SERVER[REMOTE_ADDR]!='182.19.66.187' && $_SERVER[REMOTE_ADDR]!= '202.56.197.65')
{
mssql_query($loanRequestInsertQry);
//echo "Validation: $loanRequestInsertQry";
}

echo "Loan Applied Successfully";
	
 }
else
{
 echo "Validation:-- if(($member_type=='Y'||$member_type=='N')&& (strlen($member_short_code)==2 && $member_before_loan=='0' && strlen($member_id)>1))";

}	
	}

function check_meeting_date($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs){

	$test_vonumber=$GLOBALS['test_vonumber'];
	//$tday=date('d');
	$mday=date('m');
	$yday=date('Y');
	
	
	if($yday == "2015" && $mday == "03"){
		$allow_loan=1;
	}else{
		
	
	$vo_meeting_date_rs=mssql_fetch_array(mssql_query("select MEETING_DAY_1,MEETING_DAY_2,MEETING_DAY,NEW_MEETING_DAY from vo_info(nolock) where TRANS_VO_ID='$void_code'"));
		$meeting_date=$vo_meeting_date_rs['MEETING_DAY'];
		
		if(strlen($meeting_date) == "1"){
			$meeting_date="0".$meeting_date;
		}

		$edate=date('d');
	    
	    $new_meeting_date=$vo_meeting_date_rs['NEW_MEETING_DAY'];	
	    		
	    if(strlen($new_meeting_date) == "1"){
			$new_meeting_date="0".$new_meeting_date;
		}      
	    
	    $today_full=date('Y-m-d');
	  $full_meeting_date = date('Y')."-".date('m')."-".$meeting_date;
	 $full_new_meeting_date = date('Y')."-".date('m')."-".$new_meeting_date;
	 
 
		//$meeting_day_last_month_full=date('Y-m-d', strtotime($full_meeting_date. ' - 1 months'));
		
		//$meeting_day_last_month_full=date('Y', strtotime($full_meeting_date. ' - 1 months'))."-".date('m', strtotime($full_meeting_date. ' - 1 months'))."-".$meeting_date;
		if(date('m') == "02" && ($meeting_date == "29" || $meeting_date == "30" || $meeting_date == "31")){
			$meeting_day_last_month_full=date('Y')."-01-".$meeting_date;
		}else{
			$meeting_day_last_month_full=date('Y', strtotime($full_meeting_date. ' - 1 months'))."-".date('m', strtotime($full_meeting_date. ' - 1 months'))."-".$meeting_date;
		}
		
		
		$meeting_day_last_month_month=date('m',strtotime($meeting_day_last_month_full));
		$meeting_day_last_month_day=date('d',strtotime($meeting_day_last_month_full));
		
	//	$new_meeting_day_last_month_full=date('Y', strtotime($full_new_meeting_date. ' - 1 months'))."-".date('m', strtotime($full_new_meeting_date. ' - 1 months'))."-".$new_meeting_date;
	
			if(date('m') == "02" && ($new_meeting_date == "29" || $new_meeting_date == "30" || $new_meeting_date == "31")){
			$new_meeting_day_last_month_full=date('Y')."-01-".$new_meeting_date;
		}else{
			$new_meeting_day_last_month_full=date('Y', strtotime($full_new_meeting_date. ' - 1 months'))."-".date('m', strtotime($full_new_meeting_date. ' - 1 months'))."-".$new_meeting_date;
		}
		
		//$new_meeting_day_last_month_full=date('Y-m-d', strtotime($full_new_meeting_date. ' - 1 months'));
		$new_meeting_day_last_month_month=date('m',strtotime($new_meeting_day_last_month_full));
		$new_meeting_day_last_month_day=date('d',strtotime($new_meeting_day_last_month_full));
		
		//$full_meeting_date = date('Y')."-".$meeting_day_last_month_month."-".$meeting_date;
		//$full_new_meeting_date = date('Y')."-".$meeting_day_last_month_month."-".$new_meeting_date;
		
		$meeting_day_1_last_month_full=date('Y-m-d', strtotime($meeting_day_last_month_full. ' + 1 days'));
		$meeting_day_1_last_month_month=date('m',strtotime($meeting_day_1_last_month_full));
		$meeting_day_1_last_month_day=date('d',strtotime($meeting_day_1_last_month_full));
		
		
		$meeting_day_7_last_month_full=date('Y-m-d', strtotime($meeting_day_last_month_full. ' + 7 days'));
		$meeting_day_7_last_month_month=date('m',strtotime($meeting_day_7_last_month_full));
		$meeting_day_7_last_month_day=date('d',strtotime($meeting_day_7_last_month_full));
		
		$new_meeting_day_1_last_month_full=date('Y-m-d', strtotime($new_meeting_day_last_month_full. ' + 1 days'));
		$new_meeting_day_1_last_month_month=date('m',strtotime($new_meeting_day_1_last_month_full));
		$new_meeting_day_1_last_month_day=date('d',strtotime($new_meeting_day_1_last_month_full));
		
		$new_meeting_day_7_last_month_full=date('Y-m-d', strtotime($new_meeting_day_last_month_full. ' + 7 days'));
		$new_meeting_day_7_last_month_month=date('m',strtotime($new_meeting_day_7_last_month_full));
		$new_meeting_day_7_last_month_day=date('d',strtotime($new_meeting_day_7_last_month_full));
	
	    
	    
	    
	    
	    //$full_meeting_date = date('Y')."-".date('m')."-".$meeting_date;
	    $full_meeting_day_month=date('m',strtotime($full_meeting_date));
	    $full_meeting_day_day=date('d',strtotime($full_meeting_date));
				
		$meeting_day_1_full=date('Y-m-d', strtotime($full_meeting_date. ' + 1 days'));
		$meeting_day_1_day=date('d',strtotime($meeting_day_1_full));
		$meeting_day_1_month=date('m',strtotime($meeting_day_1_full));

		$meeting_day_7_full=date('Y-m-d', strtotime($full_meeting_date. ' + 7 days'));
		$meeting_day_7_day=date('d',strtotime($meeting_day_7_full));
		$meeting_day_7_month=date('m',strtotime($meeting_day_7_full));

		//$full_new_meeting_date = date('Y')."-".date('m')."-".$new_meeting_date;
		$full_new_meeting_day_month=date('m',strtotime($full_new_meeting_date));
	    $full_new_meeting_day_day=date('d',strtotime($full_new_meeting_date));
				
		$new_meeting_day_1_full=date('Y-m-d', strtotime($full_new_meeting_date. ' + 1 days'));
		$new_meeting_day_1_day=date('d',strtotime($new_meeting_day_1_full));
		$new_meeting_day_1_month=date('m',strtotime($new_meeting_day_1_full));

		$new_meeting_day_7_full=date('Y-m-d', strtotime($full_new_meeting_date. ' + 7 days'));
		$new_meeting_day_7_day=date('d',strtotime($new_meeting_day_7_full));
		$new_meeting_day_7_month=date('m',strtotime($new_meeting_day_7_full));
	    

		if($today_full == $full_meeting_date || $today_full == $full_new_meeting_date || $today_full == $meeting_day_last_month_full || $today_full == $new_meeting_day_last_month_full){
			$allow_loan=1;
		}
		
		
		
		if($allow_loan != 1){

			if($today_full == $meeting_day_1_full){
				
				$loans_on_meeting_date=mssql_num_rows(mssql_query("select RECEIVED_ID from SHG_LOAN_APPLICATION(nolock)  where vo_id='$void_code' and  cast(created_date as date)='$full_meeting_date' and status_id!='11'"));
				
				if($loans_on_meeting_date == 0){
					$allow_loan=1;
				}
				
				
			}
			
			
		}
		
		
		
		if($allow_loan != 1){
			
			if($today_full == $meeting_day_7_full){
				
				//$loans_on_meeting_date=mssql_num_rows(mssql_query("select RECEIVED_ID from SHG_LOAN_APPLICATION  where vo_id='$void_code' and  cast(created_date as date)='$meeting_day_1_full' and status_id!='11'"));
				
				//if($loans_on_meeting_date == 0){
					$allow_loan=1;
				//}
				
				
			}
			
			
		}
		
		
		if($allow_loan != 1){
			
			if($today_full == $new_meeting_day_1_full){
				
				$loans_on_meeting_date=mssql_num_rows(mssql_query("select RECEIVED_ID from SHG_LOAN_APPLICATION(nolock)  where vo_id='$void_code' and  cast(created_date as date)='$full_new_meeting_date' and status_id!='11'"));
				
				if($loans_on_meeting_date == 0){
					$allow_loan=1;
				}
				
				
			}
			
			
		}
		
		
		if($allow_loan != 1){
			
			if($today_full == $new_meeting_day_7_full){
				
				//$loans_on_meeting_date=mssql_num_rows(mssql_query("select RECEIVED_ID from SHG_LOAN_APPLICATION  where vo_id='$void_code' and  cast(created_date as date)='$new_meeting_day_1_full' and status_id!='11'"));
				
				//if($loans_on_meeting_date == 0){
					$allow_loan=1;
				//}
				
				
			}
			
			
		}		
		
		
		
		if($allow_loan != 1){
			
			if($today_full == $meeting_day_1_last_month_full){
				
				$loans_on_meeting_date=mssql_num_rows(mssql_query("select RECEIVED_ID from SHG_LOAN_APPLICATION(nolock)  where vo_id='$void_code' and  cast(created_date as date)='$meeting_day_last_month_full' and status_id!='11'"));
				
				if($loans_on_meeting_date == 0){
					$allow_loan=1;
				}
				
				
			}
			
			
		}
		
		if($allow_loan != 1){
			
			if($today_full == $meeting_day_7_last_month_full){
				
				//$loans_on_meeting_date=mssql_num_rows(mssql_query("select RECEIVED_ID from SHG_LOAN_APPLICATION  where vo_id='$void_code' and  cast(created_date as date)='$meeting_day_1_last_month_full' and status_id!='11'"));
				
				//if($loans_on_meeting_date == 0){
					$allow_loan=1;
				//}
				
				
			}
			
			
		}		
		
		

		
		if($allow_loan != 1){
			
			if($today_full == $new_meeting_day_1_last_month_full){
				
				$loans_on_meeting_date=mssql_num_rows(mssql_query("select RECEIVED_ID from SHG_LOAN_APPLICATION(nolock)  where vo_id='$void_code' and  cast(created_date as date)='$new_meeting_day_last_month_full' and status_id!='11'"));
				
				if($loans_on_meeting_date == 0){
					$allow_loan=1;
				}
				
				
			}
			
			
		}	
		
		
		if($allow_loan != 1){
			
			if($today_full == $new_meeting_day_7_last_month_full){
				
				//$loans_on_meeting_date=mssql_num_rows(mssql_query("select RECEIVED_ID from SHG_LOAN_APPLICATION  where vo_id='$void_code' and  cast(created_date as date)='$new_meeting_day_1_last_month_full' and status_id!='11'"));
				
				//if($loans_on_meeting_date == 0){
					$allow_loan=1;
				//}
				
				
			}
			
			
		}	


	    
	     
	        
	        
	     if($allow_loan != 1){   
		
		$check_vo_access_override=mssql_num_rows(mssql_query("select replace(convert(varchar, CREATED_DATE, 111),'/','-') from VO_LOAN_ACCESS(nolock) where replace(convert(varchar, CREATED_DATE, 111),'/','-')<=replace(convert(varchar,getdate() , 111),'/','-') and replace(convert(varchar, END_DATE, 111),'/','-')>=replace(convert(varchar,getdate() , 111),'/','-')  and vo_id='$void_code'"));
		if($check_vo_access_override > '0'){
			$allow_loan=1;			
			}
		}
		
		if($allow_loan == 1)
		{
			
		}else{
			
		$allow_loan=0;
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/vo_meeting_day_is_on";
		//$agi-> stream_file($wfile, '#');
		
		if($meeting_date != ""){
		
			
		if($today_full < $full_meeting_date)	{
		$month_file="/var/lib/asterisk/sounds/vo_ivrs/telugu_months/".$full_meeting_day_month;
		//$agi-> stream_file($month_file, '#');
		
		if(substr($full_meeting_day_day,0,1) == "0"){
			$full_meeting_day_day=substr($full_meeting_day_day,1,1);
		}
		
		//$this-> play_amount($full_meeting_day_day,$agi);
		sleep(1);
		}
		$meeting_dates = '';
		if($today_full < $meeting_day_1_full){
		$meeting_dates .= "$meeting_day_1_full,";
		$month_file="/var/lib/asterisk/sounds/vo_ivrs/telugu_months/".$meeting_day_1_month;
		//$agi-> stream_file($month_file, '#');
		
		if(substr($meeting_day_1_day,0,1) == "0"){
			$meeting_day_1_day=substr($meeting_day_1_day,1,1);
		}
		//$this-> play_amount($meeting_day_1_day,$agi);
		sleep(1);
		}

		if($today_full < $meeting_day_7_full){
		$meeting_dates .= "$meeting_day_7_full,";
		$month_file="/var/lib/asterisk/sounds/vo_ivrs/telugu_months/".$meeting_day_7_month;
		//$agi-> stream_file($month_file, '#');
		
		if(substr($meeting_day_7_day,0,1) == "0"){
			$meeting_day_7_day=substr($meeting_day_7_day,1,1);
		}
		
		//$this-> play_amount($meeting_day_7_day,$agi);
		sleep(1);
		}
		
		
		if($today_full < $meeting_day_last_month_full)	{
		$meeting_dates .= "$meeting_day_last_month_full,";
		$month_file="/var/lib/asterisk/sounds/vo_ivrs/telugu_months/".$meeting_day_last_month_month;
		//$agi-> stream_file($month_file, '#');
		
		if(substr($full_meeting_day_day,0,1) == "0"){
			$meeting_day_last_month_day=substr($meeting_day_last_month_day,1,1);
		}
		
		//$this-> play_amount($meeting_day_last_month_day,$agi);
		sleep(1);
		}
		
		
		if($today_full < $meeting_day_1_last_month_full)	{
		$meeting_dates .= "$meeting_day_1_last_month_full,";
		$month_file="/var/lib/asterisk/sounds/vo_ivrs/telugu_months/".$meeting_day_1_last_month_month;
		//$agi-> stream_file($month_file, '#');
		
		if(substr($meeting_day_1_last_month_day,0,1) == "0"){
			$meeting_day_1_last_month_day=substr($meeting_day_1_last_month_day,1,1);
		}
		
		//$this-> play_amount($meeting_day_1_last_month_day,$agi);
		sleep(1);
		}
		
	if($today_full < $meeting_day_7_last_month_full)	{
		$meeting_dates .= "$meeting_day_7_last_month_full,";
		$month_file="/var/lib/asterisk/sounds/vo_ivrs/telugu_months/".$meeting_day_7_last_month_month;
		//$agi-> stream_file($month_file, '#');
		
		if(substr($meeting_day_7_last_month_day,0,1) == "0"){
			$meeting_day_7_last_month_day=substr($meeting_day_7_last_month_day,1,1);
		}
		
		//$this-> play_amount($meeting_day_7_last_month_day,$agi);
		sleep(1);
		}
		}

		$new_meeting_dates = "";
		if($new_meeting_date != ""){
			
		sleep(1);	
		
		if($today_full < $full_new_meeting_date){
		$new_meeting_dates .= "$full_new_meeting_date,";
		$month_file="/var/lib/asterisk/sounds/vo_ivrs/telugu_months/".$full_new_meeting_day_month;
		//$agi-> stream_file($month_file, '#');
		
		if(substr($full_new_meeting_day_day,0,1) == "0"){
			$full_new_meeting_day_day=substr($full_new_meeting_day_day,1,1);
		}
		
		//$this-> play_amount($full_new_meeting_day_day,$agi);
		sleep(1);
		}
		
		if($today_full < $new_meeting_day_1_full){
		$new_meeting_dates .= "$new_meeting_day_1_full,";
		$month_file="/var/lib/asterisk/sounds/vo_ivrs/telugu_months/".$new_meeting_day_1_month;
		//$agi-> stream_file($month_file, '#');
		
		if(substr($new_meeting_day_1_day,0,1) == "0"){
			$new_meeting_day_1_day=substr($new_meeting_day_1_day,1,1);
		}
		
		//$this-> play_amount($new_meeting_day_1_day,$agi);
		sleep(1);
		}
		
		if($today_full < $new_meeting_day_7_full){
		$new_meeting_dates .= "$new_meeting_day_7_full,";
		$month_file="/var/lib/asterisk/sounds/vo_ivrs/telugu_months/".$new_meeting_day_7_month;
		//$agi-> stream_file($month_file, '#');
		
		if(substr($new_meeting_day_7_day,0,1) == "0"){
			$new_meeting_day_7_day=substr($new_meeting_day_7_day,1,1);
		}
		
		//$this-> play_amount($new_meeting_day_7_day,$agi);
		
		}
		
				//new meeting last month
		if($today_full < $new_meeting_day_last_month_full)	{
		$new_meeting_dates .= "$new_meeting_day_last_month_full,";
		$month_file="/var/lib/asterisk/sounds/vo_ivrs/telugu_months/".$new_meeting_day_last_month_month;
		//$agi-> stream_file($month_file, '#');
		
		if(substr($new_meeting_day_last_month_day,0,1) == "0"){
			$new_meeting_day_last_month_day=substr($new_meeting_day_last_month_day,1,1);
		}
		
		//$this-> play_amount($new_meeting_day_last_month_day,$agi);
		sleep(1);
		}
		
		
		if($today_full < $new_meeting_day_1_last_month_full)	{
		$new_meeting_dates .= "$new_meeting_day_1_last_month_full,";
		$month_file="/var/lib/asterisk/sounds/vo_ivrs/telugu_months/".$new_meeting_day_1_last_month_month;
		//$agi-> stream_file($month_file, '#');
		
		if(substr($new_meeting_day_1_last_month_day,0,1) == "0"){
			$new_meeting_day_1_last_month_day=substr($new_meeting_day_1_last_month_day,1,1);
		}
		
		//$this-> play_amount($new_meeting_day_1_last_month_day,$agi);
		sleep(1);
		}
		
	if($today_full < $new_meeting_day_7_last_month_full)	{
		$new_meeting_dates .= "$new_meeting_day_7_last_month_full,";
		$month_file="/var/lib/asterisk/sounds/vo_ivrs/telugu_months/".$new_meeting_day_7_last_month_month;
		//$agi-> stream_file($month_file, '#');
		
		if(substr($new_meeting_day_7_last_month_day,0,1) == "0"){
			$new_meeting_day_7_last_month_day=substr($new_meeting_day_7_last_month_day,1,1);
		}
		
		//$this-> play_amount($new_meeting_day_7_last_month_day,$agi);
		sleep(1);
		}
		
		
		}
		
		
		exit("Validation: Loan Can be applied on below Meeting Dates : $meeting_dates $new_meeting_dates");
		
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_only_on_meeting_day";
		//$agi-> stream_file($wfile, '#');
		}
	}		
	return $allow_loan;
	}


function log_ivr($ivr_call_id,$messgae){
	$shg_code= trim($_REQUEST[shg_id]);
        $fp = fopen("Logs/$shg_code.txt","a");
        fwrite($fp,"\n message: $messgae");
        fclose($fp);
	$ivr_call_id='test';
	$test_vonumber=$GLOBALS['test_vonumber'];
	$ivr_log_loan_cmd="/bin/echo ".$messgae." >> /var/log/ivrs/".$ivr_call_id.".log";
	exec($ivr_log_loan_cmd);
		
		
	}

?>

