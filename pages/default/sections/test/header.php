<?php
$isAuthenticated = isset($_SESSION['user_email'])?true:false;

$pagename = str_replace('.php', '', basename($_SERVER['PHP_SELF'])); 

$isEnableMenu = ($pagename != 'login')?true:false;

$userRoleId = (isset($_SESSION['user_type'])?$_SESSION['user_type']:0);

// echo "USER TYPE: " . $userRoleId;
?>


	<div class="navbar">
      <div class="navbar-inner navbar-inverse">
        <div class="container">
		
			<?php if($isEnableMenu): ?>
			<button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<?php endif; ?>
			
          <a class="brand" href="#">Punch List Pro</a>
		  <div class="pull-right profile-summary">
			<?php
			if($isAuthenticated):
			?>
				Welcome, <?php echo $_SESSION['user_firstname']; ?>!
			<?php
			endif;
			?>
		  </div>
		
			<?php if($isEnableMenu): ?>
			  <div class="nav-collapse collapse">
				<ul class="nav mainnav">
					<li <?php echo (($pagename=='index')?'class="active"':'') ?>><a href="index.html">Home</a></li>
					<?php if(($userRoleId == 1) ||
						     ($userRoleId == 2)): ?>
						<li <?php echo (($pagename=='users' || $pagename=='add_user' || $pagename=='view_user')?'class="active"':'') ?>><a href="users.html">Users</a></li>
					<?php endif; ?>
					<?php
					if($userRoleId == 5)
					 {
					 ?>						
						<li <?php echo (($pagename=='properties' || $pagename=='add_property' || $pagename=='edit_property' || $pagename=='add_report' || $pagename=='view_report')?'class="active"':'') ?>><a href="properties.html">Properties</a></li>
                        <li <?php echo (($pagename=='sub_contractors' || $pagename=='add_sub_contractor' || $pagename=='view_sub_contractor')?'class="active"':'') ?>><a href="view_sub_contractor.html?id=<?php print $_SESSION['user_id']; ?>">My Profile</a></li>
					<?php
					 }
					?>
					<?php if(($userRoleId == 2) ||
							 ($userRoleId == 3) ||
						     ($userRoleId == 4)): 
							 							?>
						<li <?php echo (($pagename=='properties' || $pagename=='add_property' || $pagename=='edit_property' || $pagename=='add_report' || $pagename=='view_report' || $pagename=="view_property_estimate" || $pagename == "edit_report")?'class="active"':'') ?>><a href="properties.html">Properties</a></li>
                        <li <?php echo (($pagename=='estimates' || $pagename=='add_estimate' || $pagename=='edit_estimate' || $pagename=='edit_property_estimate' || $pagename=='view_estimate' || $pagename=='view_report_estimates')?'class="active"':'') ?>><a href="estimates.html">Estimates</a></li>
						<li <?php echo (($pagename=='archives' || $pagename=='view_report_archive' || $pagename=='edit_archive_property' || $pagename=='view_archive_estimate')?'class="active"':'') ?>><a href="archives.html">Archives</a></li>
						<li <?php echo (($pagename=='community' || $pagename=='add_community' || $pagename=='edit_community')?'class="active"':'') ?>><a href="community.html">Community</a></li>
						<li <?php echo (($pagename=='profile')?'class="active"':'') ?>><a href="profile.html">My Profile</a></li>
						<li <?php echo (($pagename=='reports')?'class="active"':'') ?>><a href="reports.html">My Reports</a></li>
					<?php endif; ?>
					<?php if(($userRoleId == 2)): ?>
						<li <?php echo (($pagename=='room_templates' || $pagename=='add_room_template' || $pagename=='edit_room_template')?'class="active"':'') ?>><a href="room_templates.html">Room Templates</a></li>
						<li <?php echo (($pagename=='sub_contractors' || $pagename=='add_sub_contractor' || $pagename=='view_sub_contractor')?'class="active"':'') ?>><a href="sub_contractors.html">Subcontractors</a></li>
						<li <?php echo (($pagename=='work_categories' || $pagename=='add_work_category' || $pagename=='edit_work_category')?'class="active"':'') ?>><a href="work_categories.html">Work Categories</a></li>
						<!--<li <?php echo (($pagename=='companies' || $pagename=='add_company' || $pagename=='edit_company')?'class="active"':'') ?>><a href="companies.html">Companies</a></li> -->
					<?php endif; ?>
					
					<?php
					if($isAuthenticated):
					?>
						<li><a href="logout.html">Logout</a></li>
					<?php
					endif;
					?>
				</ul>
			  </div><!--/.nav-collapse -->
			<?php endif; ?>
		  
        </div>
      </div>
    </div>
