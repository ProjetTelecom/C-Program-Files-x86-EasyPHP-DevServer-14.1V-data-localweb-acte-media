<?php
################################################################################
# @Name : index.php
# @Desc : main page include sub-pages
# @call : 
# @parameters : 
# @Author : Flox
# @Create : 07/03/2010
# @Update : 01/03/2016
# @Version : 3.1.7
################################################################################

session_start();

//initialize variables
if(!isset($_GET['page'])) $_GET['page'] = '';
if(!isset($currentpage)) $currentpage = '';

if ($_GET['page']!='ticket' && $_GET['page']!=='admin' && $_GET['page']) //avoid upload problems
{
    //avoid back problem with browser
    if(!empty($_POST) OR !empty($_FILES))
    {
        $_SESSION['bkp_post'] = $_POST;
        if(!empty($_SERVER['QUERY_STRING']))
        {
            $currentpage .= '?' . $_SERVER['QUERY_STRING'] ;
        }
        header('Location: ' . $currentpage);
        exit;
    }
    if(isset($_SESSION['bkp_post']))
    {
        $_POST = $_SESSION['bkp_post'] ;
        unset($_SESSION['bkp_post']);
    }
}

//initialize variables
if(!isset($_SESSION['user_id'])) $_SESSION['user_id'] = '';
if(!isset($_SESSION['profile_id'])) $_SESSION['profile_id'] = '';

if(!isset($_POST['keywords'])) $_POST['keywords'] = '';
if(!isset($_POST['userkeywords'])) $_POST['userkeywords'] = '';
if(!isset($_POST['assetkeywords'])) $_POST['assetkeywords'] = '';

if(!isset($_GET['userkeywords'])) $_GET['userkeywords'] = '';
if(!isset($_GET['assetkeywords'])) $_GET['assetkeywords'] = '';
if(!isset($_GET['action'])) $_GET['action'] = '';
if(!isset($_GET['keywords'])) $_GET['keywords'] = '';
if(!isset($_GET['page'])) $_GET['page'] = '';
if(!isset($_GET['searchengine'])) $_GET['searchengine'] = '';
if(!isset($_GET['download'])) $_GET['download'] = '';
if(!isset($_GET['subpage'])) $_GET['subpage'] = '';
if(!isset($_GET['ldap'])) $_GET['ldap'] = '';
if(!isset($_GET['category'])) $_GET['category'] = '';
if(!isset($_GET['subcat'])) $_GET['subcat'] = '';
if(!isset($_GET['technician'])) $_GET['technician'] = '';
if(!isset($_GET['title'])) $_GET['title'] = '';
if(!isset($_GET['date_create'])) $_GET['date_create'] = '';
if(!isset($_GET['priority'])) $_GET['priority'] = '';
if(!isset($_GET['criticality'])) $_GET['criticality'] = '';

if(!isset($keywords)) $keywords = '';

//redirect to home page on logoff
if ($_GET['action'] == 'logout')
{
	$_SESSION = array();
	session_destroy();
	session_start();
}

//initialize variables
if(!isset($_SESSION['user_id'])) $_SESSION['user_id'] = '';

//detect https connection
if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {$http='https';} else {$http='http';}

//connexion script with database parameters
require "connect.php";

//switch SQL MODE to allow empty values with lastest version of MySQL
$db->exec('SET sql_mode = ""');

//load parameters table
$query=$db->query("SELECT * FROM tparameters");
$rparameters=$query->fetch();
$query->closeCursor(); 

//load common variables
$daydate=date('Y-m-d');
$datetime = date("Y-m-d H:i:s");

//display error parameter
if ($rparameters['debug']==1) {
	ini_set('display_errors', 'On');
	error_reporting(E_ALL);
} else {
	ini_set('display_errors', 'Off');
	error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
}

//if user is connected
if ($_SESSION['user_id'])
{
	//load variables
	$uid=$_SESSION['user_id'];
	
	//load user table
	$quser=$db->query("SELECT * FROM tusers WHERE id=$_SESSION[user_id]");
	$ruser=$quser->fetch();
	$quser->closeCursor(); 
	
	//find profile id of connected user 
	$qprofile=$db->query("SELECT profile FROM tusers WHERE id LIKE $uid");
	$_SESSION['profile_id']=$qprofile->fetch();
	$qprofile->closeCursor(); 
	$_SESSION['profile_id']=$_SESSION['profile_id'][0];

	//load rights table
	$query=$db->query("SELECT * FROM trights WHERE profile=$_SESSION[profile_id]");
	$rright=$query->fetch();
	$query->closeCursor();
}

//put keywords in variable
if($_POST['keywords']||$_GET['keywords']) $keywords="$_GET[keywords]$_POST[keywords]";
if($_POST['userkeywords']||$_GET['userkeywords']) $userkeywords="$_GET[userkeywords]$_POST[userkeywords]"; else  $userkeywords='';
if($_POST['assetkeywords']||$_GET['assetkeywords']) $assetkeywords="$_GET[assetkeywords]$_POST[assetkeywords]"; else  $assetkeywords='';

//download file for backup page
if ($_GET['download']!='')
{
	header("location: ./backup/$_GET[download]"); 
}
?>
<!DOCTYPE html>
<html lang="fr">
	<head>
	    <?php header('x-ua-compatible: ie=edge'); //disable ie compatibility mode ?>
		<meta charset="UTF-8" />
		<?php if (($rparameters['auto_refresh']!=0)&&($_GET['page']=='dashboard')&&($_POST['keywords']=='')) echo '<meta http-equiv="Refresh" content="'.$rparameters['auto_refresh'].';">'; ?>
		<title>acte media | Gestion de support</title>
		<link rel="shortcut icon" type="image/ico" href="./images/<?php if($_GET['page']=='asset_list' || $_GET['page']=='asset' || $_GET['page']=='asset_stock') {echo 'favicon_asset.ico';} else {echo 'favicon_ticket.ico';} ?>" /> 
		<meta name="description" content="gestsup" />
		<meta name="robots" content="noindex, nofollow">
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<!-- basic styles -->
		<link href="./template/assets/css/bootstrap.min.css" rel="stylesheet" />
		<link rel="stylesheet" href="./template/assets/css/font-awesome.min.css" />
		<!-- timepicker styles -->
		<link rel="stylesheet" href="template/assets/css/bootstrap-timepicker.css" />


		<!--[if IE 7]>
		  <link rel="stylesheet" href="./template/assets/css/font-awesome-ie7.min.css" />
		<![endif]-->
		<!-- page specific plugin styles -->
		<!-- fonts -->
		<link rel="stylesheet" href="./template/assets/css/ace-fonts.css" />
		<link rel="stylesheet" href="./template/assets/css/jquery-ui-1.10.3.full.min.css" />
		<!-- ace styles -->
		<link rel="stylesheet" href="./template/assets/css/ace.min.css" />
		<link rel="stylesheet" href="./template/assets/css/ace-rtl.min.css" />
		<link rel="stylesheet" href="./template/assets/css/ace-skins.min.css" />
		
		<!--[if lte IE 8]>
		  <link rel="stylesheet" href="./template/assets/css/ace-ie.min.css" />
		<![endif]-->
		<!-- inline styles related to this page -->
		<!-- ace settings handler -->
		<script src="./template/assets/js/ace-extra.min.js"></script>
		<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!--[if lt IE 9]>
		<script src="./template/assets/js/html5shiv.js"></script>
		<script src="./template/assets/js/respond.min.js"></script>
		<![endif]-->
	</head>
	<?php
		//display  and nav bar if user is connected
		if ($_SESSION['user_id'])
		{
			//temporary variables to migrate to trights table
			if ($_SESSION['profile_id']==0)	{$profile="technician";}
			elseif ($_SESSION['profile_id']==1)	{$profile="user";}
			elseif ($_SESSION['profile_id']==4)	{$profile="technician";}
			elseif ($_SESSION['profile_id']==3) {$profile="user";}
			else {$profile="user";}
						
			//user bar queries 
			$query=$db->query("SELECT count(*) FROM tincidents WHERE $profile='$_SESSION[user_id]' and techread='0' and disable='0'");
			$cnt3=$query->fetch();
			$query->closeCursor(); 
			
			$query=$db->query("SELECT count(*) FROM tincidents WHERE technician='0' and t_group='0' and disable='0'");
			$cnt5=$query->fetch();
			$query->closeCursor(); 
			
			$query=$db->query("SELECT count(*) FROM tincidents WHERE $profile='$uid' and (state LIKE '1' OR state LIKE '2' OR state LIKE '6') AND disable='0'");
			$nbatt=$query->fetch();
			$query->closeCursor(); 
						
			$query=$db->query("SELECT count(*) FROM tincidents WHERE $profile='$uid' and (state LIKE '1' OR state LIKE '2') AND disable='0'");
			$nbatt2=$query->fetch();
			$query->closeCursor(); 
			
			$query=$db->query("SELECT count(*) FROM tincidents WHERE $profile='$uid' and state LIKE '3' AND disable='0'");
			$nbres=$query->fetch();
			$query->closeCursor(); 
			
			$query=$db->query("SELECT * FROM tusers WHERE id LIKE '$uid'");
			$reqfname=$query->fetch();
			$query->closeCursor(); 
			
			$query=$db->query("SELECT count(*) FROM tincidents WHERE   (date_create LIKE '$daydate%' OR date_res LIKE '$daydate%') AND disable='0'");
			$nbday=$query->fetch();
			$query->closeCursor(); 
			
			$query=$db->query("SELECT count(*) FROM tincidents WHERE TO_DAYS(NOW()) - TO_DAYS(date_create) >= $rparameters[lign_yellow] and TO_DAYS(NOW()) - TO_DAYS(date_create) <= $rparameters[lign_orange] and (state LIKE '2' or state LIKE '1') and technician LIKE '$uid' AND disable='0'" );
			$nb15=$query->fetch();
			$query->closeCursor();
			
			$query=$db->query("SELECT count(*) FROM tincidents WHERE TO_DAYS(NOW()) - TO_DAYS(date_create) > $rparameters[lign_orange] and (state LIKE '2' or state LIKE '1') and technician LIKE '$uid' AND disable='0'" );
			$nb30=$query->fetch();
			$query->closeCursor();
			
			$query=$db->query("SELECT SUM(time_hope-time) FROM tincidents WHERE time_hope-time>0 and technician LIKE '$uid' AND disable='0' AND (state='1' OR state='2' OR state='6')");
			$nbtps=$query->fetch();
			$query->closeCursor();
			
			$query=$db->query("SELECT SUM(time_hope-time) FROM tincidents WHERE time_hope-time>0 and technician LIKE '$uid' AND disable='0' AND (state='1' OR state='2' OR state='6')");
			$ra1=$query->fetch();
			$query->closeCursor();
			
			$query=$db->query("select count(*) from tincidents where technician LIKE '$uid' and date_create LIKE '$daydate' AND disable='0';");
			$ra2=$query->fetch();
			$query->closeCursor();
			
			$query=$db->query("SELECT count(*) FROM tincidents WHERE technician='0' and t_group='0' and techread='0' and disable='0'");
			$nbun=$query->fetch();
			$query->closeCursor();
			
			if ($nbun[0]!=0) {
				$new='<a href="./index.php?page=dashboard&userid=0&state=%"><img style="border-style: none" alt="img" title="'.$nbun[0].' nouvelles demandes" src="./images/wait_min.png" /></a>';
			} else {$new='';}
			if (($ra2[0]==0)&&($ra1[0]==0)){$ratio=0;}
			else if ($ra2[0]==0){$ratio=0;}
			else {
				$ratio=$ra1[0]/$ra2[0];
				$ratio= substr($ratio, 0, 3);
				}
			$nbtps=round($nbtps[0]/60);
			echo '
			<body class="'.$ruser['skin'].'">
				<div class="navbar navbar-default" id="navbar">
					<script type="text/javascript">
						try{ace.settings.check(\'navbar\' , \'fixed\')}catch(e){}
					</script>

					<div class="navbar-container" id="navbar-container">
						<div class="navbar-header pull-left">
							<a href="#" class="navbar-brand">
								
								
								<img style="border-style: none" alt="logo" src="./upload/logo/'; if ($rparameters['logo']=='') echo 'logo.png'; else echo $rparameters['logo'];  echo '" />
							</a><!--/.brand-->
						</div><!-- /.navbar-header -->
						<div class="navbar-header pull-right" role="navigation">
							<ul class="nav ace-nav">';
								if ($rright['userbar']!=0)
								{
									if ($cnt5[0]>0 && $rright['side_your_not_attribute']!=0)
									{
									echo'
										<li class="red">
											<a title="Ticket en attente d\'attribution" href="./index.php?page=dashboard&amp;userid=0&amp;t_group=0&amp;state=%">
												<i class="icon-bell-alt icon-animated-bell"></i>
												<span class="badge badge-important">'.$cnt5[0].'</span>
											</a>
										</li>';
									}
									if ($cnt3[0]>0 && $rright['side_your_not_read']!=0)
									{
									echo'
										<li class="light-orange">
											<a title="Tickets en attente de lecture" href="./index.php?page=dashboard&amp;userid='.$_SESSION['user_id'].'&amp;techread=0">
												<i class="icon-bell-alt icon-animated-bell"></i>
												<span class="badge badge-yellow">'.$cnt3[0].'</span>
											</a>
										</li>';
									}
									echo'
									<li class="blue">
										<a title="Tickets crées ou fermés ce jour" href="./index.php?page=dashboard&amp;userid=%&amp;state=%&amp;date_create=current">
											<i class="icon-calendar"></i>
											Ce jour
											<span class="badge badge-blue">'.$nbday[0].'</span>
										</a>
									</li>
									';
									//generate in treatment state
									if ($rparameters['meta_state']==1) {$link_meta_state='./index.php?page=dashboard&amp;userid='.$_SESSION['user_id'].'&amp;state=meta';} else {$link_meta_state='./index.php?page=dashboard&amp;userid='.$_SESSION['user_id'].'&amp;state=1';}
									echo'
									<li class="grey">
										<a title="Vos tickets en attente de prise en charge, en cours et en attente de retour" href="'.$link_meta_state.'">
											<i class="icon-flag"></i>
											A traiter
											<span title="Vos tickets en attente de prise en charge et en cours" class="badge badge-grey">'.$nbatt2[0].'</span>
											<span title="Vos tickets en attente de prise en charge, en cours et en attente de retour" class="badge badge-grey">'.$nbatt[0].'</span>
										</a>
									</li>
									<li class="purple">
										<a title="Nombre de travail estimé dans vos tickets ouverts" href="./index.php?page=dashboard&amp;userid='.$_SESSION['user_id'].'&amp;state=1">
											<i class="icon-dashboard"></i>
											Charge
											<span class="badge badge-important">'.$nbtps.'h</span>
										</a>
									</li>
									';
									
								}
								echo'
								<li class="green">
									<a title="Vos tickets résolus" href="./index.php?page=dashboard&amp;userid='.$_SESSION['user_id'].'&amp;state=3">
										<i class="icon-ok-circle "></i>
										Résolus
										<span class="badge badge-success">'.$nbres[0].'</span>
									</a>
								</li>';
								echo '
								<li class="light-blue">
									<a data-toggle="dropdown" href="#" class="dropdown-toggle">
										<img class="nav-user-photo" src="./images/avatar/';
											$query=$db->query("SELECT img FROM tprofiles WHERE level=$_SESSION[profile_id]");
											$rprofile_img=$query->fetch();
											$query->closeCursor();
											echo $rprofile_img[0];
										echo '
										" alt="img" />
										<span class="user-info">
											<small>Bienvenue,</small>
											'.$reqfname['firstname'].' '.$reqfname['lastname'].'
										</span>
										<i class="icon-caret-down"></i>
									</a>
									<ul class="user-menu pull-right dropdown-menu dropdown-yellow dropdown-caret dropdown-closer">
										<li>
											<a href="./index.php?page=admin/user&amp;action=edit&amp;userid='.$_SESSION['user_id'].'">
												<i class="icon-user"></i>
												Profil
											</a>
										</li>
										<li class="divider"></li>
										<li>
											<a href="./index.php?action=logout">
												<i class="icon-off"></i>
												Déconnexion
											</a>
										</li>
									</ul>
								</li>
							</ul><!--/.ace-nav-->
						</div><!--/.container-fluid-->
					</div><!--/.navbar-inner-->
				</div>
				<div class="main-container" id="main-container">
					<script type="text/javascript">
						try{ace.settings.check(\'main-container\' , \'fixed\')}catch(e){}
					</script>
					<div class="main-container-inner">
						<a class="menu-toggler" id="menu-toggler" href="#">
							<span class="menu-text"></span>
						</a>';
						//display menu and send paramters for the default page
						if ($_GET['page']=="") {$_GET['page']="dashboard"; $_GET['state']="%"; $_GET['userid']=$_SESSION['user_id'];}
						require('./menu.php'); echo'
						<div class="main-content">
							<div class="breadcrumbs" id="breadcrumbs">
								<script type="text/javascript">
									try{ace.settings.check(\'breadcrumbs\' , \'fixed\')}catch(e){}
								</script>
								<ul class="breadcrumb">
									<li>
										';
										////build navbar
										//previous page
										if($_GET['page']=='ticket' ) {echo "<a title=\"Retour à la liste\" href=\"./index.php?page=dashboard&amp;id=$row[id]&amp;state=$_GET[state]&amp;userid=$_GET[userid]&amp;technician=$_GET[technician]&amp;category=$_GET[category]&amp;subcat=$_GET[subcat]&amp;title=$_GET[title]&amp;date_create=$_GET[date_create]&amp;priority=$_GET[priority]&amp;criticality=$_GET[criticality]&amp;viewid=$_GET[viewid]\" ><i class=\"icon-arrow-left home-icon\"></i></a>";}
										//first level
										echo '<a href="./index.php?page=dashboard&userid='.$_SESSION['user_id'].'&state=%"><i class="icon-home home-icon"></i></a>';
										if(($_GET['page']=='dashboard' || $_GET['page']=='ticket' || $_GET['page']=='preview_mail' ) && $_GET['viewid']=='') echo ' <a href="./index.php?page=dashboard&amp;userid='.$_SESSION['user_id'].'">Tickets</a>';
										if($_GET['page']=='procedure') echo ' <a href="./index.php?page=procedure">Procédure</a>';
										if($_GET['page']=='planning') echo ' <a href="./index.php?page=planning">Calendrier</a>';
										if($_GET['page']=='stat') echo ' <a href="./index.php?page=stat">Statistiques</a>';
										if($_GET['page']=='admin/user' && $_GET['action']=='edit') echo ' <a href="index.php?page=admin/user&action=edit&userid='.$_GET['userid'].'">Fiche utilisateur</a>';
										if($_GET['page']=='plugins/availability/index') echo ' <a href="index.php?page=plugins/availability/index">Disponibilité</a>';
										if($_GET['page']=='admin' || $_GET['page']=='changelog') echo ' <a href="./index.php?page=admin">Administration</a>';
										if($_GET['viewid']!='' || $_GET['page']=='view') echo ' <a href="index.php?page=dashboard">Vues</a>';
										if($_GET['page']=='asset' || $_GET['page']=='asset_list') echo ' <a href="index.php?page=asset_list">Matériels</a>';
										if($_GET['page']=='asset_stock') echo ' <a href="index.php?page=asset_stock">Entrée en stock</a>';
										//second level
										if($_GET['subpage']=='parameters' ) echo '<span class="divider"><i class="icon-angle-right arrow-icon"></i></span><a href="index.php?page=admin&subpage=parameters">Paramètres</a> ';
										if($_GET['subpage']=='user' ) echo '<span class="divider"><i class="icon-angle-right arrow-icon"></i></span><a href="index.php?page=admin&subpage=user">Utilisateurs</a> ';
										if($_GET['subpage']=='group' ) echo '<span class="divider"><i class="icon-angle-right arrow-icon"></i></span><a href="index.php?page=admin&subpage=group">Groupes</a> ';
										if($_GET['subpage']=='profile' ) echo '<span class="divider"><i class="icon-angle-right arrow-icon"></i></span><a href="index.php?page=admin&subpage=profile">Droits</a> ';
										if($_GET['subpage']=='list' ) echo '<span class="divider"><i class="icon-angle-right arrow-icon"></i></span><a href="index.php?page=admin&subpage=list">Listes</a> ';
										if($_GET['subpage']=='backup' ) echo '<span class="divider"><i class="icon-angle-right arrow-icon"></i></span><a href="index.php?page=admin&subpage=backup">Sauvegardes</a> ';
										if($_GET['subpage']=='update' ) echo '<span class="divider"><i class="icon-angle-right arrow-icon"></i></span><a href="index.php?page=admin&subpage=update">Mise à jour</a> ';
										if($_GET['subpage']=='system' ) echo '<span class="divider"><i class="icon-angle-right arrow-icon"></i></span><a href="index.php?page=admin&subpage=system">Système</a> ';
										if($_GET['subpage']=='infos' || $_GET['page']=='changelog' ) echo '<span class="divider"><i class="icon-angle-right arrow-icon"></i></span><a href="index.php?page=admin&subpage=infos">Informations</a> ';
										if(($_GET['page']=='ticket' || $_GET['page']=='preview_mail') && $_GET['action']=='new') echo '<span class="divider"><i class="icon-angle-right arrow-icon"></i></span><a href="index.php?page=ticket&action=new&userid=1">Nouveau</a> ';
										if(($_GET['page']=='ticket' || $_GET['page']=='preview_mail') && $_GET['action']!='new') echo '<span class="divider"><i class="icon-angle-right arrow-icon"></i></span><a href="index.php?page=ticket&id='.$_GET['id'].'&userid='.$_GET['userid'].'&state='.$_GET['state'].'&category='.$_GET['category'].'&subcat='.$_GET['subcat'].'&viewid='.$_GET['viewid'].'">Edition</a> ';
										if($_GET['page']=='asset' && $_GET['action']!='new') echo '<span class="divider"><i class="icon-angle-right arrow-icon"></i></span><a href="index.php?page=asset&id='.$_GET['id'].'">Edition</a> ';
										if($_GET['page']=='asset' && $_GET['action']=='new') echo '<span class="divider"><i class="icon-angle-right arrow-icon"></i></span><a href="index.php?page=asset&action=new">Nouveau</a> ';
										//third level
										if($_GET['page']=='admin' && $_GET['subpage']=='user' && $_GET['ldap']==1) echo '<span class="divider"><i class="icon-angle-right arrow-icon"></i></span><a href="index.php?page=admin&subpage=user&ldap=1">Synchronisation LDAP</a> ';
										if($_GET['page']=='preview_mail') echo '<span class="divider"><i class="icon-angle-right arrow-icon"></i></span><a href="index.php?page=preview_mail&id='.$_GET['id'].'&userid='.$_GET['userid'].'&state='.$_GET['state'].'&category='.$_GET['category'].'&subcat='.$_GET['subcat'].'&viewid='.$_GET['viewid'].'">Envoi de mail</a> ';
										if($_GET['page']=='changelog') echo '<span class="divider"><i class="icon-angle-right arrow-icon"></i></span><a href="index.php?page=changelog">Changelog</a> ';
										echo '
									</li>
								</ul><!--.breadcrumb-->
								';
								if ($rright['search']!='0')
								{
									echo '
										<div class="nav-search" id="nav-search">
										';
										    if ($_GET['subpage']=='user')
											{
												echo '<form method="POST" action="index.php?page=admin&subpage=user" class="form-search">';
											} elseif ($_GET['page']=='asset_list' || $_GET['page']=='asset') {
												echo '<form method="POST" action="index.php?page=asset_list" class="form-search">';
											} else { 
												echo '<form method="POST" action="./index.php?page=dashboard&userid='.$_GET['userid'].'&state='.$_GET['state'].'" class="form-search">';
											}
												echo '
														<span class="input-icon">
															';
															if ($_GET['subpage']=='user')
															{
																echo '<input type="text" placeholder="Recherche util..." class="input-small nav-search-input" id="userkeywords" name="userkeywords" class="keywords" autocomplete="on" value="'.$userkeywords.'" />';
															} elseif ($_GET['page']=='asset_list' || $_GET['page']=='asset') {
																echo '<input type="text" placeholder="Recherche équipe..." class="input-small nav-search-input" id="assetkeywords" name="assetkeywords" class="keywords" autocomplete="on" value="'.$assetkeywords.'" />';
															} else {
																echo '<input type="text" placeholder="Recherche ticket..." class="input-small nav-search-input" id="keywords" name="keywords" class="keywords" autocomplete="on" value="'.$keywords.'" />';
															}
															echo '
															<i class="icon-search nav-search-icon"></i>
														</span>
													</form>
										</div><!--#nav-search-->
									';
								}
								echo '
							</div>
							<div class="page-content">
								';
								//security check own ticket right
								if(($_GET['page']=='ticket') && ($_GET['action']!='new')) 
								{
									$query=$db->query("SELECT user FROM tincidents WHERE id='$_GET[id]'");
									$rticket=$query->fetch();
									$query->closeCursor(); 
								} else $rticket[0]=$_SESSION['user_id'];
								
								//security check for page
								if((($_SESSION['profile_id']!=4 && $_SESSION['profile_id']!=0 && $_SESSION['profile_id']!=3) && ($_SESSION['user_id']!=$_GET['userid'])) || (($_SESSION['profile_id']!=4 && $_SESSION['profile_id']!=0 && $_SESSION['profile_id']!=3) &&($rticket[0]!=$_SESSION['user_id'])))
								{
									if ($_GET['page']=='plugins/availability/index' && $rright['availability']!=0 && $rparameters['availability']==1) 
									{include("$_GET[page].php");} 
									elseif ($_GET['page']=='asset_list' && $rright['asset']!=0 && $rparameters['asset']==1) {include("$_GET[page].php");}
									else
								    echo '<div class="alert alert-danger"><strong><i class="icon-remove"></i>Erreur:</strong> Vous n\'avez pas les droits d\'accès a cette page, contacter votre administrateur.<br></div>';
								} else	{
							    	include("$_GET[page].php");
								}
								echo '
							</div><!--/.page-content-->
						</div>
					</div>
				</div><!--/.main-container-->
				<a href="#" id="btn-scroll-up" class="btn-scroll-up btn btn-small btn-inverse">
					<i class="icon-double-angle-up icon-only bigger-110"></i>
				</a>
				';
				//display event modalbox
				include "./event.php"; 
				
				//display change user password modalbox
				$query=$db->query("SELECT * FROM tusers WHERE id='$_SESSION[user_id]'");
				$r=$query->fetch();
				$query->closeCursor(); 
				if ($r['chgpwd']=='1'){include "./modify_pwd.php";}
		}
		else 
		{
			require('./login.php');
		}
		// Close database access
		$db = null;
		?>
    
		<!-- basic scripts -->

		<!--[if !IE]> -->

		<script type="text/javascript">
			window.jQuery || document.write("<script src='./template/assets/js/jquery-2.0.3.min.js'>"+"<"+"/script>");
		</script>

		<!-- <![endif]-->

		<!--[if IE]>
		<script type="text/javascript">
		 window.jQuery || document.write("<script src='./template/assets/js/jquery-1.10.2.min.js'>"+"<"+"/script>");
		</script>
		<![endif]-->

		<!--
			// if("ontouchend" in document) document.write("<script src='./template/assets/js/jquery.mobile.custom.min.js'>"+"<"+"/script>");
			-->
		<script src="./template/assets/js/bootstrap.min.js"></script>
		<script src="./template/assets/js/typeahead-bs2.min.js"></script>

		<!-- Modalbox -->
		<script src="./template/assets/js/jquery-ui-1.10.3.full.min.js"></script>
		<script src="./template/assets/js/jquery.ui.touch-punch.min.js"></script>

		<!--[if lte IE 8]>
		  <script src="./template/assets/js/excanvas.min.js"></script>
		<![endif]-->
		
		<!--
		<script src="./template/assets/js/jquery-ui-1.10.3.custom.min.js"></script>
		<script src="./template/assets/js/jquery.ui.touch-punch.min.js"></script>
		<script src="./template/assets/js/jquery.slimscroll.min.js"></script>
		<script src="./template/assets/js/jquery.easy-pie-chart.min.js"></script>
		<script src="./template/assets/js/jquery.sparkline.min.js"></script>
		<script src="./template/assets/js/flot/jquery.flot.min.js"></script>
		<script src="./template/assets/js/flot/jquery.flot.pie.min.js"></script>
		<script src="./template/assets/js/flot/jquery.flot.resize.min.js"></script>
		-->

		<?php
		//bugfix stat page
		if($_GET['page']!='stat'){ echo'<script src="./template/assets/js/ace.min.js"></script><script src="./template/assets/js/ace-elements.min.js"></script>';}
		
		// Date conversion
		function date_convert ($date) 
		{return  substr($date,8,2) . "/" . substr($date,5,2) . "/" . substr($date,0,4) . " à " . substr($date,11,2	) . "h" . substr($date,14,2	);}
		?>
	</body>
</html>