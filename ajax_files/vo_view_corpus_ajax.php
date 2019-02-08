<?php
session_start();
if(!isset($_SESSION['TRANS_VO_ID']) && !isset($_SESSION['desk_deo_id']))
{
	exit("Validation: Session Expired, Please login again");
}
	 $shg_code= trim($_REQUEST[shg_id]);
         $fp = fopen("Logs/$shg_code.txt","a");
	if($_SERVER[REMOTE_ADDR]=='182.19.66.187' || $_SERVER[REMOTE_ADDR]== '202.56.197.65')
         fwrite($fp,"\n------TEST----Project 53---".date("Y-m-d H:i:s a"));
	else	
         fwrite($fp,"\n----------Project 53---".date("Y-m-d H:i:s a"));
         fclose($fp);
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
		
		if($ms_qry_row[IS_VERIFIED]!='Y' && $vo_qry_row[IS_VERIFIED]!='Y')
		{
		echo $message="Validation: Documents not submmited at ms and vo level. DISTRICT_ID=$DISTRICT_ID,MANDAL_ID=$MANDAL_ID,void_code=$void_code,ms_IS_VERIFIED=$ms_qry_row[IS_VERIFIED],vo_IS_VERIFIED=$vo_qry_row[IS_VERIFIED]\n";	
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);

		exit;
	
		}
		else
		{
		  if($ms_qry_row[IS_VERIFIED]!='Y')
		   {
			echo $message="Validation: Documents not submmited at ms level. ";//DISTRICT_ID=$DISTRICT_ID,MANDAL_ID=$MANDAL_ID,void_code=$void_code,ms_IS_VERIFIED=$ms_qry_row[IS_VERIFIED]\n";
			$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
			exit;

		   }
		  if($vo_qry_row[IS_VERIFIED]!='Y')
		   {
			echo $message="Validation: Documents not submmited at vo level. ";//DISTRICT_ID=$DISTRICT_ID,MANDAL_ID=$MANDAL_ID,void_code=$void_code,ms_IS_VERIFIED=$ms_qry_row[IS_VERIFIED],vo_IS_VERIFIED=$vo_qry_row[IS_VERIFIED]\n";
			$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
			exit;

		   }
		}
		//End Putting conditions for Documents Verification By Ashok


		if($status_valied<'1')
		{

		echo $message="Validation:  $caller is not present in VO_INFO ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		exit;
		}
		

		$check_urban=mssql_num_rows(mssql_query("select VO_ID from VO_RURALTOURBAN(nolock) where VO_ID='$void_code'"));
                if($check_urban == "1"){echo $message="Validation: VO_RURALTOURBAN ,Hangup the call";
                $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
                 exit;  }


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

		}*/
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
		//$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		
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
			//$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
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
		}

	list_SHGs($void_code);
} 
if($_REQUEST[Action]=='ValidateSHG')
{
$shg_id= trim($_REQUEST[shg_id]);
$void_code = $_REQUEST[vo_id];
//echo "$shg_id---$vo_id";
	$test_vonumber=$GLOBALS['test_vonumber'];
	 $project=53;
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
	$triggered_loan_count_res=mssql_fetch_array(mssql_query("select count(SHG_MEMBER_LOAN_ACCNO) from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW with (nolock) where SHG_ID='$shg_code'"));
		$triggered_loan_count=$triggered_loan_count_res[0];
		$message="Triggered Loans: $triggered_loan_count select count(SHG_MEMBER_LOAN_ACCNO) from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW with (nolock) where SHG_ID='$shg_code'";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
			$curr_odos=0;
			if($triggered_loan_count > 0)
			{
				$shg_overdue_res=mssql_fetch_array(mssql_query("select OVERDUE,CURRENT_OVERDUE,CURRENT_OUTSTANDING from SAMSNBSTG.SN.SHG_OVERDUE_STATUS with (nolock) where OVERDUE>0 and  SHG_ID='$shg_code'"));
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
//	echo $message="Validation: SHG OVERDUE AMT $overdue_amt is gretaer than 10000";
	echo $message="Validation: Bank Linkage AMT ($overdue_amt) is gretaer than 10000";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		exit;
	}
	$path="/var/lib/asterisk/sounds/sthreenidhi/shg/$shg_dist_code/$shg_mandal_code/$shg_name.wav";
  $cmd="/bin/ls ".$path;
    $list=exec($cmd);
	$vo_active_rs=mssql_fetch_array(mssql_query("select IS_ACTIVE,CIF_RECOVERY_PERCENTAGE from vo_info(nolock) where TRANS_VO_ID='$void_code'"));
    $vo_active_stat=$vo_active_rs['IS_ACTIVE'];
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
/*	if($vo_actual_credit=='0')
	{
	echo	$message="Validation:vo_actual_credit $vo_actual_credit";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		
	      $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/zero_credit_limit";
		   exit;
	}
*/
$search_purpose_ig="'Agriculture','Dairy','Income generation Activity','Weavers'";
$search_purpose_non_ig="'Education','Marriage','Emergency Needs/Health','Emergency Needs'";
	   		$x=3;
		$shg_max_loans_ivrs=2;
	   $member_limit=shg_member_limit($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$project,$shg_code,$shg_max_loans_total,$shg_max_loans_ivrs);
	      $message="member_limit: $member_limit";
		$member_limit = split("~",$member_limit);	   
$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);	
		if($member_limit[0]>=1)
		{
			$message="SUCCESS: member_limit: $member_limit Greater Than or Equal to 1";
			$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);	
			$amt_stat='Y';
			list_members($void_code,$shg_code);
		}
		   else
		{
		  //   $message="Validation: FAIL: member_limit: $member_limit Less Than 1 , Morethan ".$shg_max_loans_ivrs." loans ";
		  echo   $message="Validation: FAIL: Morethan ".$shg_max_loans_ivrs." loans | Members already having Loans : $member_limit[1]";
	   $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);	
//		  	$res_dtmf=$agi->get_data("/var/lib/asterisk/sounds/vo_ivrs/$language/morethan_".$shg_max_loans_ivrs."_loans", 5000, 1);
	      $more_res=$res_dtmf ["result"];
			exit;

		}
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
	$member_row[SHG_NAME] = str_replace("-","",$member_row[SHG_NAME]);
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

	
function shg_member_limit($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$project,$shg_code,$shg_max_loans_total,$shg_max_loans_ivrs){

		$test_vonumber=$GLOBALS['test_vonumber'];
		$message="Calculating outstanding loans of SHG in project $project  ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		
$shg_oustanding_project_rslt = mssql_query("SELECT MEMBER_LONG_CODE,MEMBER_SHORT_CODE FROM (   
SELECT DISTINCT MEMBER_ID MEMBER_LONG_CODE,SHORT_CODE MEMBER_SHORT_CODE  FROM EVGEN.IVRS_LOAN_REQUEST (NOLOCK) WHERE SHG_ID ='$shg_code'     
AND PROJECT_TYPE  IN($project) AND IS_PROCESSED='N'    UNION   
 SELECT DISTINCT MEMBER_LONG_CODE,MEMBER_SHORT_CODE FROM VO_REQUEST_MESSAGES(NOLOCK) WHERE SHG_ID ='$shg_code'
 AND PROJECT_TYPE  IN($project) AND IS_PROCESSED='N'    UNION 
SELECT DISTINCT MEMBER_LONG_CODE,MEMBER_SHORT_CODE   FROM SHG_MEMBER_MCP_INFO (NOLOCK) WHERE SHG_ID ='$shg_code'     
AND PROJECT_TYPE  IN($project) AND LOAN_STATUS ='OPEN' UNION    
SELECT DISTINCT MEMBER_LONG_CODE,MEMBER_SHORT_CODE  FROM SHG_LOAN_APPLICATION (NOLOCK) WHERE SHG_ID ='$shg_code'     
AND PROJECT_TYPE  IN($project) AND STATUS_ID NOT IN(5,11)) A order by MEMBER_SHORT_CODE");

$shg_oustanding_project_rows = mssql_num_rows($shg_oustanding_project_rslt);
while($shg_oustanding_project_array = mssql_fetch_array($shg_oustanding_project_rslt))
{
$shg_oustanding_memmbers .= $shg_oustanding_project_array[MEMBER_SHORT_CODE].",";
}


		$message="shg_oustanding_project: $shg_oustanding_project_rows";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
                $shg_limit_project=$shg_max_loans_ivrs-$shg_oustanding_project_rows;
		$shg_allowed_loans = $shg_limit_project ;

         $fp = fopen("Logs/$shg_code.txt","a");
         fwrite($fp,"\n shg_allowed_loans : $shg_allowed_loans(shg_max_loans_ivrs-shg_oustanding_project_rows : $shg_max_loans_ivrs-$shg_oustanding_project_rows)");
         fclose($fp);	
		if($shg_allowed_loans > 0 ){
		$message="SUCCESS: shg_allowed_loans: $shg_allowed_loans Greater than 0 AND TCS_shg_allowed_loans:$TCS_shg_allowed_loans Greater than 0";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		return $shg_allowed_loans;
		}else{
		$message="FAIL: shg_allowed_loans: $shg_allowed_loans Less than 0 OR TCS_shg_allowed_loans:$TCS_shg_allowed_loans Less than 0";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		return "0~$shg_oustanding_memmbers";
		}
}
function shg_member_limit_old($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$project,$shg_code,$shg_max_loans_total,$shg_max_loans_ivrs){
		
		
		$test_vonumber=$GLOBALS['test_vonumber'];
		$message="Calculating outstanding loans of SHG in project $project  ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		
		$shg_applied_project_rs=mssql_fetch_array(mssql_query("select count(vo_id) from IVRS_LOAN_REQUEST(nolock) where shg_id='$shg_code' and PROJECT_TYPE='$project' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'"));
		$shg_applied_project=$shg_applied_project_rs[0];
		
/*		$shg_applied_project_live_rs=mssql_fetch_array(mssql_query("select count(vo_id) from IVRS_LOAN_REQUEST_LIVE(nolock) where shg_id='$shg_code' and PROJECT_TYPE='$project' and IVRS_ID='$ivr_call_id'"));
		$shg_applied_project_live=$shg_applied_project_live_rs[0];
*/		
		$shg_rejected_project_rs=mssql_fetch_array(mssql_query("select count(vo_id) from SHG_LOAN_APPLICATION(nolock) where shg_id='$shg_code' and PROJECT_TYPE='$project' and STATUS_ID='11'"));
		$shg_rejected_project=$shg_rejected_project_rs[0];
		$shg_repaid_project_rs=mssql_fetch_array(mssql_query("select count(smlsn.VO_ID) FROM SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) smlsn,SHG_LOAN_APPLICATION(nolock) sla WHERE smlsn.SHG_ID='$shg_code' and smlsn.SHG_MEMBER_LOAN_ACCNO=sla.SHG_MEM_LOAN_ACCNO and sla.project_type='$project' and smlsn.IS_CLOSED='1'"));
		$shg_repaid_project=$shg_repaid_project_rs[0];
		
	
		$shg_oustanding_project=$shg_applied_project+$shg_applied_project_live-$shg_rejected_project-$shg_repaid_project+$shg_applied_loans_tcs;
		
		$message="shg_oustanding_project: $shg_oustanding_project = shg_applied_project:$shg_applied_project + shg_applied_project_live:$shg_applied_project_live - shg_rejected_project:$shg_rejected_project - shg_repaid_project:$shg_repaid_project + shg_applied_loans_tcs : $shg_applied_loans_tcs";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		
		
		$shg_limit_project=$shg_max_loans_ivrs-$shg_oustanding_project;
		
		$message="shg_limit_project: $shg_limit_project = shg_max_loans_ivrs: $shg_max_loans_ivrs - shg_oustanding_project: $shg_oustanding_project ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		$shg_allowed_loans=$shg_limit_project;
		
		
		$message="Calculating outstanding loans of SHG in project $project using TCS Tables only ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		
		$TCS_shg_applied_project_rs=mssql_fetch_array(mssql_query("select count(vo_id) from SHG_LOAN_APPLICATION(nolock) where shg_id='$shg_code' and PROJECT_TYPE='$project'"));
		$TCS_shg_applied_project=$TCS_shg_applied_project_rs[0];
		
/*		$TCS_shg_applied_project_live_rs=mssql_fetch_array(mssql_query("select count(vo_id) from IVRS_LOAN_REQUEST_LIVE(nolock) where shg_id='$shg_code' and PROJECT_TYPE='$project' and IVRS_ID='$ivr_call_id'"));
		$TCS_shg_applied_project_live=$TCS_shg_applied_project_live_rs[0];
*/		
		$TCS_shg_rejected_project_rs=mssql_fetch_array(mssql_query("select count(vo_id) from SHG_LOAN_APPLICATION(nolock) where shg_id='$shg_code' and PROJECT_TYPE='$project' and STATUS_ID='11'"));
		$TCS_shg_rejected_project=$TCS_shg_rejected_project_rs[0];
		
		$TCS_shg_repaid_project_rs=mssql_fetch_array(mssql_query("select count(smlsn.VO_ID) FROM SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) smlsn,SHG_LOAN_APPLICATION(nolock) sla WHERE smlsn.SHG_ID='$shg_code' and smlsn.SHG_MEMBER_LOAN_ACCNO=sla.SHG_MEM_LOAN_ACCNO and sla.project_type='$project' and smlsn.IS_CLOSED='1'"));
		$TCS_shg_repaid_project=$TCS_shg_repaid_project_rs[0];
		
		
		$TCS_shg_oustanding_project=$TCS_shg_applied_project+$TCS_shg_applied_project_live-$TCS_shg_rejected_project-$TCS_shg_repaid_project;
		
		$message="TCS_shg_oustanding_project: $TCS_shg_oustanding_project = TCS_shg_applied_project:$TCS_shg_applied_project + TCS_shg_applied_project_live:$TCS_shg_applied_project_live - TCS_shg_rejected_project:$TCS_shg_rejected_project - TCS_shg_repaid_project:$TCS_shg_repaid_project ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		
		$TCS_shg_limit_project=$shg_max_loans_total-$TCS_shg_oustanding_project;
		$message="TCS_shg_limit_project: $TCS_shg_limit_project = shg_max_loans_total: $shg_max_loans_total - TCS_shg_oustanding_project: $TCS_shg_oustanding_project ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		$TCS_shg_allowed_loans=$TCS_shg_limit_project;

		
		if($shg_allowed_loans > 0 && $TCS_shg_allowed_loans > 0){
		$message="SUCCESS: shg_allowed_loans: $shg_allowed_loans Greater than 0 AND TCS_shg_allowed_loans:$TCS_shg_allowed_loans Greater than 0";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		
		return $shg_allowed_loans;
		}else{
		$message="FAIL: shg_allowed_loans: $shg_allowed_loans Less than 0 OR TCS_shg_allowed_loans:$TCS_shg_allowed_loans Less than 0";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		return 0;
		}
		
		
		
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
$overdue_rs= mssql_fetch_array(mssql_query("select sum(OVERDUE) from SHG_OVER_DUES(nolock) where SHG_ID='$shg_code' group by SHG_ID"));
	$overdue_amt=$overdue_rs[0];
	if($overdue_amt>0 && $overdue_amt<=10000)
	{
	$is_over_due='Y';	
	}

				$shg_overdue_res=mssql_fetch_array(mssql_query("select OVERDUE,CURRENT_OVERDUE,CURRENT_OUTSTANDING from SAMSNBSTG.SN.SHG_OVERDUE_STATUS with (nolock) where SHG_ID='$shg_code'"));
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


		$shg_id_query=mssql_query("select TRANS_SHG_ID from SHG_INFO(nolock) where  VO_ID='$void_code'");
$vo_shgs='';
while($shg_id_array=mssql_fetch_array($shg_id_query)){
    $vo_shg_id=$shg_id_array['TRANS_SHG_ID'];
    $vo_shgs.="'".$vo_shg_id."'".",";
	}
	$vo_shgs=substr($vo_shgs,0,-1);
	
	$message="Fetching SHGs in VO $void_code :  $vo_shgs ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);


/*
		$shg_limit_array=shg_limits($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$project,$shg_code);
		$shg_max_loans_total=$shg_limit_array[$shg_code]['shg_max_loans_total'];
		$shg_max_loans_ivrs=$shg_limit_array[$shg_code]['shg_max_loans_ivrs'];
		$shg_max_credit_limit=$shg_limit_array[$shg_code]['shg_max_credit_limit'];
*/
// SHG Number of loans validation START
		$project=53;
		$shg_max_loans_ivrs=2;
	   $member_limit=shg_member_limit($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$health,$vo_shgs,$project,$shg_code,$shg_max_loans_total,$shg_max_loans_ivrs);

         $fp = fopen("Logs/$shg_code.txt","a");
         fwrite($fp,"\n member_limit: $member_limit");
         fclose($fp);
		$member_limit = split("~",$member_limit);
		if($member_limit[0]<1)
		  {
		  echo $message="Validation: FAIL : Morethan ".$shg_max_loans_ivrs." loans ";
		  $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		   exit;
		  }
// SHG Number of loans validation END
$member_id_rs=mssql_fetch_array(mssql_query("select MEMBER_ID,SP_ACTIVITY,MOBILE_NUM,UID_NO,AGE,IS_POP_MEM from SHG_MEMBER_INFO(nolock)  where  SHG_ID='$shg_code' and MEMBER_ID='$memberId'"));
 $member_id=$member_id_rs['MEMBER_ID'];
 $member_mobile_num=$member_id_rs['MOBILE_NUM'];
 $member_uid=$member_id_rs['UID_NO'];
  $member_age=$member_id_rs['AGE'];
  $member_type=$member_id_rs['IS_POP_MEM'];
 				
$message="Member details:  MEMBER_ID: $member_id , Short Code:  $memberId,member_uid: $member_uid , member_age:$member_age , IS_POP_MEM:$IS_POP_MEM , member_mobile_num: $member_mobile_num @ ValidateMember";
$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);


//Member Mobile Number Updation START 01-08-2017
	
//    $mobile_count=mssql_num_rows(mssql_query("select MEMBER_ID from SHG_MEMBER_INFO(nolock)  where  MOBILE_NUM='$memberMobNo'"));
    $start_digit=substr($memberMobNo,0,1);

	if(trim($memberMobNo)=='')	 	
	{echo $message="Validation: Please enter Mobile Number";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
	exit;}
	
	if($memberMobNo>'1' && strlen($memberMobNo)=='10' && ($start_digit=='9'||$start_digit=='8'||$start_digit=='7'||$start_digit=='6'))
	{
//	echo $message="Validation: "."UPDATE SHG_MEMBER_INFO SET MOBILE_NUM='$memberMobNo'  where SHG_ID='$shg_code' and member_id='$memberId'";exit;
	if($_SERVER[REMOTE_ADDR]!='182.19.66.187' && $_SERVER[REMOTE_ADDR]!= '202.56.197.65'&& $_SERVER[REMOTE_ADDR]!= '125.16.9.129')
	mssql_query("UPDATE SHG_MEMBER_INFO SET MOBILE_NUM='$memberMobNo'  where SHG_ID='$shg_code' and member_id='$memberId'");
	}
	else
	{
	echo $message="Validation: Please enter Valid Mobile Number";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
	exit;
	}
//Member Mobile Number Updation END 01-08-2017


member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos,$memberId,$member_type,$loan_type,$reason_loan,$loan_amount);
}

function chk_ln_status($shg_code,$member_id,$PROJECT_TYPE,$ivr_call_id)
  {

//Ashok start adding log for checking loan status b4 allowing
	$sla_ntsp_qry="select a.CREATED_DATE,a.VO_ID,a.STATUS_ID,a.SHG_MEM_LOAN_ACCNO,b.OUTSTANDING,b.IS_CLOSED from SHG_LOAN_APPLICATION(nolock) a,SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) b where a.SHG_ID='$shg_code' and a.MEMBER_LONG_CODE='$member_id' and a.SHG_MEM_LOAN_ACCNO=b.SHG_MEMBER_LOAN_ACCNO ";
	//$sla_ntsp_qry="select a.CREATED_DATE,a.VO_ID,a.STATUS_ID,a.SHG_MEM_LOAN_ACCNO,b.OUTSTANDING,b.IS_CLOSED from SHG_LOAN_APPLICATION(nolock) a,SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) b where a.SHG_ID='$shg_code' and a.MEMBER_LONG_CODE='$member_id' and a.SHG_MEM_LOAN_ACCNO=b.SHG_MEMBER_LOAN_ACCNO and b.IS_CLOSED!='1' ";

	$sla_ntsp_res=mssql_query($sla_ntsp_qry);
	$sla_ntspno=mssql_num_rows($sla_ntsp_res);
	if($sla_ntspno>0)
	{
	  while($sla_ntsp_row=mssql_fetch_array($sla_ntsp_res))
	  {
 	        
		$message = "Date :".date('Y-m-d H:i:s')."\tivr_call_id:$ivr_call_id\tPROJECT_TYPE:$PROJECT_TYPE\tNTSP:\ta.CREATED_DATE:$sla_ntsp_row[CREATED_DATE]\ta.VO_ID:$sla_ntsp_row[VO_ID]\ta.SHG_ID:$shg_code\ta.MEMBER_LONG_CODE:$member_id\ta.STATUS_ID:$sla_ntsp_row[STATUS_ID]\ta.SHG_MEM_LOAN_ACCNO:$sla_ntsp_row[SHG_MEM_LOAN_ACCNO]\tb.OUTSTANDING:$sla_ntsp_row[OUTSTANDING]\tb.IS_CLOSED:$sla_ntsp_row[IS_CLOSED]";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
	  }
	}
	else
	{
	$sla_tsp_qry="select a.CREATED_DATE,a.VO_ID,a.STATUS_ID,a.SHG_MEM_LOAN_ACCNO,b.OUTSTANDING,b.IS_CLOSED from SHG_LOAN_APPLICATION(nolock) a,SAMSNBSTG.SN.TSP_SHG_MEMBER_LOAN_STATUS_NEW(nolock) b where a.SHG_ID='$shg_code' and a.MEMBER_LONG_CODE='$member_id' and a.SHG_MEM_LOAN_ACCNO=b.SHG_MEMBER_LOAN_ACCNO and b.IS_CLOSED!='1' ";

	$sla_tsp_res=mssql_query($sla_tsp_qry);
	$sla_tspno=mssql_num_rows($sla_tsp_res);
	if($sla_tspno>0)
	{
	  while($sla_tsp_row=mssql_fetch_array($sla_tsp_res))
	  {
       		$fp = fopen("Logs/$shg_code.txt","a");
		$msg_txt = "Date :".date('Y-m-d H:i:s')."\tivr_call_id:$ivr_call_id\tPROJECT_TYPE:$PROJECT_TYPE\tTSP:\ta.CREATED_DATE:$sla_tsp_row[CREATED_DATE]\ta.VO_ID:$sla_tsp_row[VO_ID]\ta.SHG_ID:$shg_code\ta.MEMBER_LONG_CODE:$member_id\ta.STATUS_ID:$sla_tsp_row[STATUS_ID]\ta.SHG_MEM_LOAN_ACCNO:$sla_tsp_row[SHG_MEM_LOAN_ACCNO]\tb.OUTSTANDING:$sla_tsp_row[OUTSTANDING]\tb.IS_CLOSED:$sla_tsp_row[IS_CLOSED]";
		fwrite($fp, $msg_txt);
		fwrite($fp, "\n\n");
		fclose($fp);
	  }
	}
	}

//Ashok end adding log for checking loan status b4 allowing


  }

function member_loan($caller,$exten,$starttime,$agi,$time,$x,$unique_id,$ivr_call_id,$language,$void_code,$member_limit,$db_filed,$type,$length,$play_msg,$shg_code,$shg_name,$amt_stat,$is_over_due,$due_id_lst,$health,$vo_shgs,$shg_max_credit_limit,$curr_odos,$memberId,$member_type,$loan_type,$reason_loan,$loan_amount){
			
$member_id_rs=mssql_fetch_array(mssql_query("select MEMBER_ID,SP_ACTIVITY,MOBILE_NUM,UID_NO,AGE,IS_POP_MEM,SHORT_CODE from SHG_MEMBER_INFO(nolock)  where  SHG_ID='$shg_code' and MEMBER_ID='$memberId'"));
 $member_id=$member_id_rs['MEMBER_ID'];
 $member_mobile_num=$member_id_rs['MOBILE_NUM'];
 $member_uid=$member_id_rs['UID_NO'];
  $member_age=$member_id_rs['AGE'];
  $IS_POP_MEM=$member_id_rs['IS_POP_MEM'];
$member_short_code = str_replace(" ","",$member_id_rs['SHORT_CODE']);

	$message="Member details:  MEMBER_ID: $member_id , Short Code:  $member_short_code,member_uid: $member_uid , member_age:$member_age , IS_POP_MEM:$IS_POP_MEM , member_mobile_num: $member_mobile_num, Loan_type: $loan_type, Reason_loan: $reason_loan, Loan_amount: $loan_amount";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);

	$member_before_loan=mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST(nolock) where SHG_ID='$shg_code' and MEMBER_ID='$member_id'  and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D' and project_type='53'"));
	$member_rej_cnt_sht = mssql_num_rows(mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where  SHG_ID='$shg_code'  and  MEMBER_LONG_CODE='$member_id'  and STATUS_ID='11' and project_type='53' ")); 
	
	$member_repaid_loans = mssql_num_rows(mssql_query("select b.VO_ID from SHG_LOAN_APPLICATION(nolock) a,SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) b where a.SHG_ID='$shg_code' and a.MEMBER_LONG_CODE='$member_id' and a.SHG_MEM_LOAN_ACCNO=b.SHG_MEMBER_LOAN_ACCNO and b.IS_CLOSED='1'  and a.project_type='53'"));


if($member_before_loan == 0 ){
		 $member_repaid_loans=0;
		}
$message="message: \n(member_before_loan : ".($member_before_loan-$member_rej_cnt_sht-$member_repaid_loans).")member_before_loan:$member_before_loan-member_rej_cnt_sht:$member_rej_cnt_sht-member_repaid_loans:$member_repaid_loans";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		$member_before_loan=$member_before_loan-$member_rej_cnt_sht-$member_repaid_loans;

		if($member_before_loan >='1')
		{
		echo "Validation:Loan already applied by this member";exit;
		}
		$project = '53';
log_ivr($ivr_call_id,"member_age : $member_age");
 if($member_age > 63 || $member_age <= 18){
	echo "Validation: Member Age should be between 18-63 ";
 	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_age_greater_60";
	//$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	exit;	
 }
 ############################### ----------------

	$member_prev_loan_count = mssql_num_rows(mssql_query("select VO_ID from IVRS_LOAN_REQUEST(nolock) where SHG_ID='$shg_code' and MEMBER_ID='$member_id' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'"));
	
	$member_prev_loan_count_tcs = mssql_num_rows(mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where  SHG_ID='$shg_code' and MEMBER_LONG_CODE='$member_id' and STATUS_ID!='99'"));
	
	$member_prev_loan_count_tcs_pending = mssql_num_rows(mssql_query("select VO_ID from VO_REQUEST_MESSAGES(nolock) where  SHG_ID='$shg_code' and MEMBER_LONG_CODE='$member_id' and IS_PROCESSED='N'"));
	
	$member_rej_cnt_lng = mssql_num_rows(mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where  SHG_ID='$shg_code' and MEMBER_LONG_CODE='$member_id'  and STATUS_ID='11' and REQUESTED_ID!='201314' "));

	$member_rej_cnt_lng_tcs = mssql_num_rows(mssql_query("select VO_ID from SHG_LOAN_APPLICATION(nolock) where  SHG_ID='$shg_code' and MEMBER_LONG_CODE='$member_id'  and STATUS_ID='11' "));

	$member_repaid_loans = mssql_num_rows(mssql_query("select b.VO_ID from SHG_LOAN_APPLICATION(nolock) a,SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock) b where a.SHG_ID='$shg_code' and a.MEMBER_LONG_CODE='$member_id' and a.SHG_MEM_LOAN_ACCNO=b.SHG_MEMBER_LOAN_ACCNO and b.IS_CLOSED='1'"));

	$member_other_repaid_loans = mssql_num_rows(mssql_query("select b.VO_ID from SHG_LOAN_APPLICATION(nolock) a,SAMSNBSTG.SN.TSP_SHG_MEMBER_LOAN_STATUS_NEW(nolock) b where a.SHG_ID='$shg_code' and a.MEMBER_LONG_CODE='$member_id' and a.SHG_MEM_LOAN_ACCNO=b.SHG_MEMBER_LOAN_ACCNO and b.IS_CLOSED='1'"));


	$message="VO_ID='$void_code',SHG_ID='$shg_code',MEMBER_ID='$member_id',SHORT_CODE='$member_short_code':member_prev_loan_count=$member_prev_loan_count,member_prev_loan_count_tcs:$member_prev_loan_count_tcs,member_prev_loan_count_tcs_pending:$member_prev_loan_count_tcs_pending,member_prev_loan_count_live=$member_prev_loan_count_live,member_rej_cnt_lng=$member_rej_cnt_lng,member_rej_cnt_lng_tcs=$member_rej_cnt_lng_tcs,member_repaid_loans=$member_repaid_loans,member_other_repaid_loans=$member_other_repaid_loans";

	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);

if($member_repaid_loans == 1){
	$msquery="select INST_NO from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock)   where SHG_ID='$shg_code' and SHG_MEM_LOAN_SHORT_CODE='$member_short_code'";
	$member_installments_rs=mssql_fetch_array(mssql_query($msquery));
 	$member_installments=$member_installments_rs['INST_NO'];
 	}
		if($member_prev_loan_count == 0 && $member_prev_loan_count_tcs==0){
		 $member_repaid_loans=0;
		}

        $member_prev_loan_count=$member_prev_loan_count-$member_rej_cnt_lng+$member_prev_loan_count_live-$member_repaid_loans-$member_other_repaid_loans;

        $member_prev_loan_count_tcs=$member_prev_loan_count_tcs+$member_prev_loan_count_tcs_pending-$member_rej_cnt_lng_tcs+$member_prev_loan_count_live-$member_repaid_loans-$member_other_repaid_loans;

        $message="member_prev_loan_count=member_prev_loan_count-member_rej_cnt_lng+member_prev_loan_count_live-member_repaid_loans-member_other_repaid_loans:$member_prev_loan_count=$member_prev_loan_count-$member_rej_cnt_lng+$member_prev_loan_count_live-$member_repaid_loans-$member_other_repaid_loans";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);

	$message="member_prev_loan_count_tcs=member_prev_loan_count_tcs-member_rej_cnt_lng_tcs+member_prev_loan_count_live-member_repaid_loans-member_other_repaid_loans:$member_prev_loan_count_tcs=$member_prev_loan_count_tcs-$member_rej_cnt_lng_tcs+$member_prev_loan_count_live-$member_repaid_loans-$member_other_repaid_loans";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);

	$message="AT Corpus: IVRS: ".$member_prev_loan_count." TCS:  ".$member_prev_loan_count_tcs;	
	log_ivr($ivr_call_id,$message);
	if($member_prev_loan_count < $member_prev_loan_count_tcs){
		$message="Member loans less than TCS ".$member_prev_loan_count." less than ".$member_prev_loan_count_tcs;		
		$member_prev_loan_count=$member_prev_loan_count_tcs;
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
	}

//Ashok Changed for Corpus Loan Allowance End
	
	$message="MEMBER OUTSTANDING LOANS  (member_prev_loan_count-member_rej_cnt_lng+member_prev_loan_count_live-member_repaid_loans) member_prev_loan_count:$member_prev_loan_count ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
	
	
	if($member_prev_loan_count >= 1){
	
	$msquery="select VO_ID from IVRS_LOAN_REQUEST(nolock) where  SHG_ID='$shg_code' and MEMBER_ID='$member_id' and project_type!='53' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'";
	$member_other_loans_applied=mssql_num_rows(mssql_query($msquery));
			
	$message="MEMBER OTHER LOANS APPLIED  -  $msquery member_other_loans_applied:$member_other_loans_applied ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);		
	
	$msquery="select VO_ID from IVRS_LOAN_REQUEST_LIVE(nolock) where SHG_ID='$shg_code' and MEMBER_ID='$member_id' and IVRS_ID='$ivr_call_id' and project_type!='53' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'";
	$member_other_loans_applied_live=mssql_num_rows(mssql_query($msquery));
		 		
	$message="MEMBER OTHER LOANS APPLIED LIVE -  $msquery member_other_loans_applied_live:$member_other_loans_applied_live ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);	
	
	$msquery="select VO_ID from shg_loan_application(nolock) where SHG_ID='$shg_code' and MEMBER_LONG_CODE='$member_id' and project_type!='53' and STATUS_ID='11' ";
	$member_other_loans_rejected=mssql_num_rows(mssql_query($msquery));
	
	$message="MEMBER OTHER LOANS REJECTED -  $msquery member_other_loans_rejected:$member_other_loans_rejected ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);	
	
	$member_other_loans_repaid=0;
	
	$message="MEMBER OTHER LOANS REPAID -  $msquery member_other_loans_repaid:$member_other_loans_repaid ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);	
	
	$member_other_loans=$member_other_loans_applied+$member_other_loans_applied_live-$member_other_loans_rejected-$member_other_loans_repaid;
	
	$message="MEMBER OTHER LOANS OUTSTANDING (member_other_loans_applied+member_other_loans_applied_live-member_other_loans_rejected-member_other_loans_repaid) member_other_loans:$member_other_loans ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
	
	if($member_other_loans == 1)
	{
		$msquery="select OUTSTANDING from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW(nolock)   where SHG_ID='$shg_code' and SHG_MEM_LOAN_SHORT_CODE='$member_short_code' and IS_CLOSED=0";
		$member_outstanding_rs=mssql_fetch_array(mssql_query($msquery));
 $member_outstanding=$member_outstanding_rs['OUTSTANDING'];
 
 			$message="MEMBER OUTSTANDING  -  $msquery member_outstanding:$member_outstanding ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
	
		if($member_outstanding < 10000 )
		{
 		$member_other_loans=0;
	 	$message="MEMBER OUTSTANDING  Less than 10000 ,eligible for CORPUS LOAN: $member_outstanding ";
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		}
	}		
	
	$message="MEMBER LOANS B4 OUTSTANDING ($member_prev_loan_count+$member_other_loans) ,member_prev_loan_count:$member_prev_loan_count ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);

	$member_prev_loan_count=$member_prev_loan_count+$member_other_loans;	

	$message="MEMBER LOANS OUTSTANDING (member_prev_loan_count+member_other_loans) ,member_prev_loan_count:$member_prev_loan_count ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
	
	
	}
	if($member_prev_loan_count != 0 )
	{
	
	$message="MEMBER ALREADY HAVE OUTSTANDING LOAN,NOT ELIGIBLE FOR LOAN (member_prev_loan_count:$member_prev_loan_count,member_repaid_loans:$member_repaid_loans) : loan_already_applied";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
	echo "Validation: MEMBER ALREADY HAVE OUTSTANDING LOAN,NOT ELIGIBLE FOR LOAN";
//	echo "Validation: ($message)";exit;	
	exit;	
	}	
 ############################### ----------------
	
if($_SERVER[REMOTE_ADDR]=='182.19.66.187' || $_SERVER[REMOTE_ADDR]== '202.56.197.65')
{
//echo "Validation:  member_prev_loan_count : $member_prev_loan_count=$member_applied_loan_count-$member_rej_cnt_lng+$member_prev_loan_count_live-$member_repaid_loans-$member_other_repaid_loans <br>member_prev_loan_count_tcs :  $member_prev_loan_count_tcs=$member_prev_loan_count_tcs-$member_rej_cnt_lng_tcs+$member_prev_loan_count_live-$member_repaid_loans-$member_other_repaid_loans;"; exit;
}
	$message="AT SN: Evolgence: ".$member_prev_loan_count." TCS:  ".$member_prev_loan_count_tcs;	
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
	if($member_prev_loan_count < $member_prev_loan_count_tcs){
		$message="Member loans less than TCS ".$member_prev_loan_count." less than ".$member_prev_loan_count_tcs;		
		$member_prev_loan_count=$member_prev_loan_count_tcs;
		$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
	}
	
	//$message="Member loans ".$member_applied_loan_count."-".$member_rej_cnt_lng."+".$member_prev_loan_count_live."-".$member_repaid_loans."-".$member_corpus_loans." = ".$member_prev_loan_count;

	$message="Member loans ".$member_applied_loan_count."-".$member_rej_cnt_lng."+".$member_prev_loan_count_live."-".$member_repaid_loans."-".$member_other_repaid_loans." = ".$member_prev_loan_count;

	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
	if($member_prev_loan_count < 0 ){
	$member_prev_loan_count=0;
	}
	
	
	/*if($member_prev_loan_count>=1 || $member_repaid_loans>=1)*/
	if($member_prev_loan_count != 0)
	{
	
	echo "Validation: ".$message=".Member loan already applied ";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
		
	$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_already_applied";
	//$agi-> stream_file($wfile, '#');
	$amt_stat='Y';
	exit;	
	}
		
	if($loan_amount=='')
	{
	echo "Validation: Please enter Loan Amount";
	exit;
	}

$diff_loan_amt=intval(substr($loan_amount,-2,2));
if($diff_loan_amt=='0')
		{
		 if($loan_amount<=25000 && $loan_amount>=1000)
		     {
		//return $loan_amount;
		           }
				   else
				   {
					if($loan_amount>25000)
					{
					echo "Validation: Loan amount should not be more than 25000";exit;
					$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/member_loan_more_25000";
	                		//$agi-> stream_file($wfile, '#');
					 }
					 if($loan_amount<1000)
					 {
					echo "Validation: Loan amount should not be less than 1000";exit;
					 $wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_morethan_1000";
	                		//$agi-> stream_file($wfile, '#');
					 }
				  }
				  }
				   else
				 {
		$wfile="/var/lib/asterisk/sounds/vo_ivrs/$language/loan_multiples_100";
		//$agi-> stream_file($wfile, '#');
		echo "Validation: Please enter Loan amount in multiples of 100";exit;
				 }

	$msquery="select ALLOCATED_AMOUNT from sc_member_credit_limit(nolock) where member_id='$member_id' and shg_id='$shg_code'";
	$member_allocated_amt_rs=mssql_fetch_array(mssql_query($msquery));
	$member_allocated_amt=$member_allocated_amt_rs['ALLOCATED_AMOUNT'];
	$message="ALLOCATED AMOUNT FOR MEMBER - ".$msquery." , member_allocated_amt:".$member_allocated_amt;
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
	if($member_allocated_amt < $loan_amount){
	echo "Validation: ALLOCATED AMOUNT LESS THAN LOAN AMT , $member_allocated_amt < $loan_amount : member_credit_limit EXCEEDED ";exit;
	}

 if($loan_amount>='1' && $member_allocated_amt >= $loan_amount )
 {
$reason_loan='Income generation Activity';

 if(($reason_loan!='')&&($member_type=='Y'||$member_type=='N')&& (strlen($member_short_code)==2 && $member_prev_loan_count=='0' && strlen($member_id)>1))
 {
 $duration='24';
 $etime=date('Y-m-d H:i:s');
  	$msquery="select ALLOCATED_AMOUNT from sc_vo_credit_limit(nolock) where VO_ID='$void_code'";
	$vo_actual_credit_rs=mssql_fetch_array(mssql_query($msquery));
	$vo_actual_credit=$vo_actual_credit_rs['ALLOCATED_AMOUNT'];
	$message = $msquery;log_ivr($ivr_call_id,$message);
	
	$msquery="select sum(LOAN_AMOUNT) as AMT from IVRS_LOAN_REQUEST(nolock)  where shg_id in ($vo_shgs) and IS_POP='$member_type' and PROJECT_TYPE='53' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D' ";
	$applied_rs=mssql_fetch_array(mssql_query($msquery)); 
        $applied_amt = $applied_rs['AMT'];
	$message = $msquery;log_ivr($ivr_call_id,$message);
		
	$msquery="select sum(LOAN_AMOUNT) as AMT_LIVE from IVRS_LOAN_REQUEST_LIVE(nolock)  where shg_id in ($vo_shgs)  and IVRS_ID='$ivr_call_id' and PROJECT_TYPE='53' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'";
	$applied_rs_live=mssql_fetch_array(mssql_query($msquery));
        $applied_amt_live = $applied_rs_live['AMT_LIVE'];
	$applied_amt_live=intval($applied_amt_live);
	$message = $msquery;log_ivr($ivr_call_id,$message);

	$msquery="select SUM(LOAN_AMOUNT) as AMT_REJ from SHG_LOAN_APPLICATION(nolock) where shg_id in ($vo_shgs)  and PROJECT_TYPE='53'  and STATUS_ID='11'";
	$vo_rej_amt_rs=mssql_fetch_array(mssql_query($msquery));		
	$vo_rej_amt=$vo_rej_amt_rs['AMT_REJ'];
	$vo_rej_amt=intval($vo_rej_amt);
	$message = $msquery;log_ivr($ivr_call_id,$message."| vo_rej_amt : $vo_rej_amt");

	$msquery="select sum(PPR) as AMT_REPAID from SN.SHG_MEMBER_REPAY_VIEW(nolock)  where shg_id in ($vo_shgs)  and PROJECT_TYPE='53'";
	$vo_repaid_cat_rs=mssql_fetch_array(mssql_query($msquery));
    $repaid_cat_total = $vo_repaid_cat_rs['AMT_REPAID'];
	$message = $msquery;log_ivr($ivr_call_id,$message."| repaid_cat_total : $repaid_cat_total");
	
	$vo_cat_limit=$vo_actual_credit-($applied_amt+$applied_amt_live-$vo_rej_amt)+$repaid_cat_total;
	$vo_credit_lt=$vo_cat_limit;

	$msquery="select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and PROJECT_TYPE='53' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'";
	$shg_lt_max_rs=mssql_fetch_array(mssql_query($msquery));
	$shg_lt_max=$shg_lt_max_rs[0];
	$message = $msquery;log_ivr($ivr_call_id,$message."| Applied_LOAN_AMOUNT : $shg_lt_max");

	$msquery="select SUM(ACTUAL_AMOUNT) from SHG_LOAN_APPLICATION(nolock)  where  SHG_ID='$shg_code'  and PROJECT_TYPE='53' and STATUS_ID='11'";
	$shg_rejected_live_rs=mssql_fetch_array(mssql_query($msquery));
	$shg_live_rejected_amt=$shg_rejected_live_rs[0];
	$message = $msquery;log_ivr($ivr_call_id,$message."| shg_live_rejected_amt : $shg_live_rejected_amt");
	//$shg_repaid_rs=mssql_fetch_array(mssql_query("select SUM(LOAN_AMOUNT) from SAMSNBSTG.SN.SHG_MEMBER_LOAN_STATUS_NEW where SHG_ID='$shg_code' and IS_CLOSED='1'"));
	//$shg_repaid=$shg_repaid_rs[0];
	$shg_repaid=0;

	$applied_actual=$shg_lt_max-$shg_live_rejected_amt-$shg_repaid;




if($applied_actual>50000)
{
	$amt_live_to_add=$applied_actual-50000;
	$amt_live_to_deduct=$shg_live_rejected_amt-$amt_live_to_add;
}
else
{
	$amt_live_to_deduct=$shg_live_rejected_amt+$shg_repaid;
}
	
	$shg_lt_max=$shg_lt_max+$loan_amount-$amt_live_to_deduct;
		
	//shg_amount
	$msquery="select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST(nolock) where VO_ID='$void_code' and SHG_ID='$shg_code' and PROJECT_TYPE='53' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'";
	$shg_lt_max_tcs_rs=mssql_fetch_array(mssql_query($msquery));
	$shg_lt_max_tcs=$shg_lt_max_tcs_rs[0];
	log_ivr($ivr_call_id,$msquery."| shg_lt_max_tcs : $shg_lt_max_tcs");
	
	$shg_rejected_amt=$shg_live_rejected_amt;
	$applied_actual_tcs=$shg_lt_max_tcs-$shg_rejected_amt-$shg_repaid;

if($applied_actual_tcs>50000)
{
	$amt_to_add=$applied_actual_tcs-50000;
	$amt_to_deduct=$shg_rejected_amt-$amt_to_add;
}
else
{
	$amt_to_deduct=$shg_rejected_amt+$shg_repaid;
}
	log_ivr($ivr_call_id,"'shg_limit_max_tcs=shg_lt_max_tcs+loan_amount-amt_to_deduct;' : $shg_limit_max_tcs=$shg_lt_max_tcs+$loan_amount-$amt_to_deduct;");
	$shg_limit_max_tcs=$shg_lt_max_tcs+$loan_amount-$amt_to_deduct;

	$msquery="select ALLOCATED_AMOUNT from sc_vo_credit_limit(nolock) where VO_ID='$void_code'";
	$vo_actual_credit_rs=mssql_fetch_array(mssql_query($msquery));
	$vo_total_credit=$vo_actual_credit_rs['ALLOCATED_AMOUNT'];
	log_ivr($ivr_call_id,$msquery."| vo_total_credit : $vo_total_credit");
	
	$msquery="select SUM(ACTUAL_AMOUNT) from SHG_LOAN_APPLICATION(nolock)  where shg_id in ($vo_shgs) and  PROJECT_TYPE='53' and STATUS_ID='11'";
	$vo_amt_to_add_rs=mssql_fetch_array(mssql_query($msquery));
	$vo_amt_to=$vo_amt_to_add_rs[0];
	$vo_total_credit=$vo_total_credit+$vo_amt_to;
	log_ivr($ivr_call_id,$msquery."| vo_amt_to : $vo_amt_to");
	   
	$msquery="select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST(nolock) where shg_id in ($vo_shgs) and PROJECT_TYPE='53' and isnull(IS_PROCESSED,'NOTDUPLICATE') !='D'";
	$vo_lt_max_tcs_rs=mssql_fetch_array(mssql_query($msquery));
	$vo_lt_max_tcs=$vo_lt_max_tcs_rs[0];
	log_ivr($ivr_call_id,$msquery."| vo_lt_max_tcs : $vo_lt_max_tcs");
		
	$msquery="select SUM(LOAN_AMOUNT) from IVRS_LOAN_REQUEST_LIVE(nolock) where shg_id in ($vo_shgs) and IVRS_ID='$ivr_call_id' and PROJECT_TYPE='53'";
	$vo_lt_live_rs=mssql_fetch_array(mssql_query($msquery));
	$vo_lt_live=$vo_lt_live_rs[0];
	log_ivr($ivr_call_id,$msquery."| vo_lt_live : $vo_lt_live");
		
	$msquery="select sum(PPR) as AMT_REPAID_VO from SN.SHG_MEMBER_REPAY_VIEW(nolock)  where shg_id in ($vo_shgs)  and PROJECT_TYPE='53'";
	$vo_repaid_total_rs=mssql_fetch_array(mssql_query($msquery));
    $vo_repaid_total = $vo_repaid_total_rs['AMT_REPAID_VO'];
	$vo_repaid_total=intval($vo_repaid_total);
	log_ivr($ivr_call_id,$msquery."| vo_repaid_total(AMT_REPAID_VO) : $vo_repaid_total");
	log_ivr($ivr_call_id,"'vo_credit_max_tcs=vo_lt_max_tcs+vo_lt_live+loan_amount-vo_repaid_total;' : $vo_credit_max_tcs=$vo_lt_max_tcs+$vo_lt_live+$loan_amount-$vo_repaid_total;");
	$vo_credit_max_tcs=$vo_lt_max_tcs+$vo_lt_live+$loan_amount-$vo_repaid_total;

	$mms_dist_code=substr($void_code,2,2);
	$mms_mandal_code=substr($void_code,4,2);
	if($shg_lt_max<0)
	$shg_lt_max = 0 ;
		$message="'if(vo_credit_lt>=loan_amount && shg_lt_max<=50000 && shg_limit_max_tcs<=50000 && vo_credit_max_tcs<=vo_total_credit)'|if($vo_credit_lt>=$loan_amount && $shg_lt_max<=50000 && $shg_limit_max_tcs<=50000 && $vo_credit_max_tcs<=$vo_total_credit)";
		log_ivr($ivr_call_id,$message);
	if($vo_credit_lt>=$loan_amount && $shg_lt_max<=50000 && $shg_limit_max_tcs<=50000 && $vo_credit_max_tcs<=$vo_total_credit )
	{
	$message="VALIDATING LOAN AMOUNT (vo_credit_lt:$vo_credit_lt >= loan_amount:$loan_amount,shg_lt_max:$shg_lt_max <= 50000,shg_limit_max_tcs:$shg_limit_max_tcs <= 50000 ,vo_credit_max_tcs:$vo_credit_max_tcs <= vo_total_credit:$vo_total_credit) : SUCCESS";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);

 $shg_ivr_loan_rs=mssql_fetch_array(mssql_query("select * from IVRS_LOAN_REQUEST_LIVE(nolock) where  SHG_ID='$shg_code' and project_type='53' order by CREATED_DATE  desc "));
	  $shg_ivr_loan_num=$shg_ivr_loan_rs['SHG_IVRS_LOAN_NO'];	
	  $shg_ivr_loan_num=$shg_ivr_loan_num+1;


//Ashok Adding Current od and os to this member Start

				if($curr_odos!=0)
				{
					$shg_pending_ods_count=mssql_num_rows(mssql_query("select TOP 1 1 from SHG_MEMBER_PENDING_OVERDUE_STATUS with (nolock) where SHG_ID='$shg_code'"));
					if($shg_pending_ods_count>=1)
					{
					  $curr_odos=0;
				   	  $message="Already Current od and os inserted in to SHG_MEMBER_PENDING_OVERDUE_STATUS for shg $shg_code";
				   	  $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
					}
					else
					{
					  $shg_mem_ods_res=mssql_query("select SHG_ID,MEMBER_ID,CURRENT_OVERDUE,CURRENT_OUTSTANDING from SAMSNBSTG.SN.SHG_OVERDUE_STATUS with (nolock) where SHG_ID='$shg_code' and CURRENT_OVERDUE>0");
					  $rec_count=0;
					  while($shg_mem_ods_row=mssql_fetch_array($shg_mem_ods_res))
					   {
					if($_SERVER[REMOTE_ADDR]!='182.19.66.187' && $_SERVER[REMOTE_ADDR]!= '202.56.197.65')
						{
						   $pres_odos=$shg_mem_ods_row['CURRENT_OVERDUE']+$shg_mem_ods_row['CURRENT_OUTSTANDING'];
						   if($pres_odos<=100)
				      		    {
						     $shg_pending="INSERT INTO SHG_MEMBER_PENDING_OVERDUE_STATUS(SHG_ID,MEMBER_ID,CREATED_DATE,CURRENT_OVERDUE,CURRENT_OUTSTANDING,INSERTED_BY) VALUES ('".$shg_mem_ods_row['SHG_ID']."','".$shg_mem_ods_row['MEMBER_ID']."',GETDATE(),'".$shg_mem_ods_row['CURRENT_OVERDUE']."','".$shg_mem_ods_row['CURRENT_OUTSTANDING']."','IVRS')";
if($_SERVER[REMOTE_ADDR]!='182.19.66.187' && $_SERVER[REMOTE_ADDR]!= '202.56.197.65')
				 mssql_query($shg_pending);

	  		   $message="SHG HAS CURRENT_OD,CURRENT_OS as pres_odos :$pres_odos Less than 100, so for this shg we are inserting into at SN $shg_pending. ";
			   $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
						    }
						}
						$rec_count++;
					   }
					$message="Current od and os inserted in to SHG_MEMBER_PENDING_OVERDUE_STATUS from SAMSNBSTG.SN.SHG_OVERDUE_STATUS where shg $shg_code : rec_count:$rec_count :$shg_pending";
				   	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);					
					}
				}

//Ashok Adding Current od and os to this member End

	$PROJECT_TYPE='53';  

	chk_ln_status($shg_code,$member_id,$PROJECT_TYPE,$ivr_call_id);

	$loan_amount=$loan_amount+$curr_odos;	



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

if($unique_id==NULL && strlen($_SESSION[desk_IMEI_NO])==15)
{
$unique_id=$_SESSION[desk_IMEI_NO];
}

$loanRequestInsertQry="insert into IVRS_LOAN_REQUEST(VO_ID,SHG_ID,LOAN_AMOUNT,LOAN_REPAY_DURATION,CREATED_DATE,MOBILE,PURPOSE,IS_POP,IVRS_ID,LOAN_SANCTIONED_DATE,LOAN_STATUS,IVRS_CALL_ID,SHG_IVRS_LOAN_NO,SHG_LOAN_MARRIAGE,PROJECT_TYPE,IS_OVERDUE,OVERDUE_ID,IS_ADDITIONAL_AMT,SHORT_CODE,MEMBER_ID,UID_NO,DISTRICT_ID,MANDAL_ID,overdue_amount) VALUES ('$void_code','$shg_code','$loan_amount','$duration','$etime','$caller','$reason_loan','$member_type','$ivrs_id','$etime','open','$unique_id','$shg_ivr_loan_num','$marriage_amt','$PROJECT_TYPE','$is_over_due','$due_id_lst','$is_eligible','$member_short_code','$member_id','$member_uid','$mms_dist_code','$mms_mandal_code','$curr_odos.0')";
#############END

//$loanRequestInsertQry="insert into IVRS_LOAN_REQUEST(VO_ID,SHG_ID,LOAN_AMOUNT,LOAN_REPAY_DURATION,CREATED_DATE,MOBILE,PURPOSE,IS_POP,IVRS_ID,LOAN_SANCTIONED_DATE,LOAN_STATUS,IVRS_CALL_ID,SHG_IVRS_LOAN_NO,SHG_LOAN_MARRIAGE,PROJECT_TYPE,IS_OVERDUE,OVERDUE_ID,IS_ADDITIONAL_AMT,SHORT_CODE,MEMBER_ID,UID_NO,DISTRICT_ID,MANDAL_ID,overdue_amount) VALUES ('$void_code','$shg_code','$loan_amount','$duration','$etime','$caller','$reason_loan','$member_type','web - $_SESSION[USER_NAME]','$etime','open','$unique_id','$shg_ivr_loan_num','$marriage_amt','$PROJECT_TYPE','$is_over_due','$due_id_lst','$is_eligible','$member_short_code','$member_id','$member_uid','$mms_dist_code','$mms_mandal_code','$curr_odos.0')";	

if($_SERVER[REMOTE_ADDR]!='182.19.66.187' && $_SERVER[REMOTE_ADDR]!= '202.56.197.65'&& $_SERVER[REMOTE_ADDR]!= '125.16.9.129')
{
mssql_query($loanRequestInsertQry);
	$message="INSERTING Corpus LOAN REQUEST INTO IVR_LOAN_REQUEST_LIVE ($loanRequestINsertQry)";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);

//echo "Validation: $loanRequestInsertQry";
}
else
{
	$message="Test is success not inseted test num:caller=$caller test_vonumber:$test_vonumber";
	$message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);
}
		$curr_odos=0;
                $message="Insertion of SN Lead : $loanRequestInsertQry";
                $message=str_replace(')','^',$message);$message=str_replace('(','^',$message);log_ivr($ivr_call_id,$message);

$current_limit=$vo_credit_lt-$loan_amount;

//echo $message = "Validation:".$current_limit."is current limit";
//exit;


if($_SERVER[REMOTE_ADDR]!='182.19.66.187' && $_SERVER[REMOTE_ADDR]!= '202.56.197.65')
{
mssql_query("update  IVRS_VO_CREDIT_LIMIT set $tbl_filed='$current_limit'  where vo_id='$void_code'");
}

if($member_type=='Y')
     {
	$pop_lmt_rs=mssql_fetch_array(mssql_query("select current_limit_pop from IVRS_VO_CREDIT_LIMIT(nolock)  where vo_id='$void_code'"));
	$pop_lmt=$pop_lmt_rs['current_limit_pop'];
	
		 if($pop_lmt>=$loan_amount)
		 {
	if($_SERVER[REMOTE_ADDR]!='182.19.66.187' && $_SERVER[REMOTE_ADDR]!= '202.56.197.65')
	 {
	 mssql_query("update IVRS_VO_CREDIT_LIMIT set current_limit_pop=current_limit_pop-$loan_amount  where vo_id='$void_code'");
	 }
		 }
	 }
	 if($member_type=='N')
		{
			
	$nonpop_lmt_rs=mssql_fetch_array(mssql_query("select current_limit_nonpop from IVRS_VO_CREDIT_LIMIT(nolock)  where vo_id='$void_code'"));
	$nonpop_lmt=$nonpop_lmt_rs['current_limit_nonpop'];
	
	if($nonpop_lmt>=$loan_amount)
	{
	if($_SERVER[REMOTE_ADDR]!='182.19.66.187' && $_SERVER[REMOTE_ADDR]!= '202.56.197.65')
		{
		mssql_query("update IVRS_VO_CREDIT_LIMIT set current_limit_nonpop=current_limit_nonpop-$loan_amount  where vo_id='$void_code'");
		}
	}
	   }
	
 }else{
echo "Validation: Loan amount is more than credit limit";exit;
}

echo "Loan Applied Successfully";
         $fp = fopen("Logs/$shg_code.txt","a");
         fwrite($fp,"\nLoan Applied Successfully\n");
         fclose($fp);	
 }
 //echo  $loan_amount.$reason_loan.$member_type;
 
		}

	else
	{
	 echo "Validation:dasdasdasd--$reason_loan--$member_type---".strlen($member_short_code)."---$member_prev_loan_count--$member_id--$loan_category";
	}
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

