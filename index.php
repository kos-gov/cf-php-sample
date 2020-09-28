<?php

$debug_mode = false;

$json_string = getenv("VCAP_APPLICATION");

if (empty($json_string)) {
	//echo "\tVCAP_APPLICATION is empty \n";
	$json_string = file_get_contents("jcap_app.json");
}

$R = new RecursiveIteratorIterator(
	new RecursiveArrayIterator(json_decode($json_string, TRUE)),
	RecursiveIteratorIterator::SELF_FIRST
);

if ($debug_mode) {
	echo "****************** VCAP_APPLICATION *****************\n";
}

$cfapp = [];
$keyName = "";
$uris = "";
$arrCnt = 0;
$cnt = 0;

foreach ($R as $key => $val) {
	if (is_array($val)) {
		if ($debug_mode) {
			echo "Array ==> key : " . $key . ", value : (array) \n";
		}
		if (strcasecmp($key, "uris") == 0) {
			$keyName = "application_uris";
		} else {
			$keyName = $key;
		}
		$arrCnt = count($val);
	} else {
		if ($val != null) {
			if ($debug_mode) {
				echo "Non-Array ==> Key : " . $key . ", value : " . $val . "\n";
			}
		} else {
			if ($debug_mode) {
				echo "Non-Array ==> Key : " . $key . ", value : null \n";
			}
		}

		if (strcasecmp($key, "name") == 0) {
			$keyName = "application_name";
			$cfapp[$keyName] = $val;
		} else if (strcasecmp($key, "mem") == 0) {
			$keyName = "mem";
			$cfapp[$keyName] = $val;
		} else if (strcasecmp($key, "disk") == 0) {
			$keyName = "disk";
			$cfapp[$keyName] = $val;
		} else {
			if (strcasecmp($key, "0") == 0) {
				$cnt = 0;
			} else {
				$cnt = (int) $key;
				if ($cnt == 0) {
					// we should change zero into -1 because if $key is a string then $cnt becomes zero
					$cnt = -1;
				}
			}

			if (strcasecmp($keyName, "application_uris") == 0 && $cnt >= 0) {
				if ($cnt == 0) {
					$uris = $val;
				} else {
					$uris = $uris . ", " . $val;
				}
				if ($arrCnt == ($cnt + 1)) {
					$cfapp[$keyName] = $uris;
				}
			} else {
				if ($val != null) {
					$keyName = $key;
					$cfapp[$keyName] = $val;
				}
			}
		}
	}
}

if (!isset($cfapp['instance_index'])) {
	$cfapp['instance_index'] = 0;
}

if ($debug_mode) {
	echo "\n";
	echo "=================[ Result ] ===================\n";

	foreach ($cfapp as $key => $val) {
		echo "cfapp" . " $key => $val \n";
	}

	echo "===============================================\n";
	echo "\n";
}

?>

<?php

$json_string = getenv("VCAP_SERVICES");

if (empty($json_string)) {
	//echo "\tVCAP_SERVICES is empty \n";
	$json_string = file_get_contents("jcap_services.json");
}

$R = new RecursiveIteratorIterator(
	new RecursiveArrayIterator(json_decode($json_string, TRUE)),
	RecursiveIteratorIterator::SELF_FIRST
);

if ($debug_mode) {
	echo "****************** VCAP_SERVICES *****************\n";
}

$cfservice = [];
$cfservicename = "";
$index = 0;
$bMatchedLabel = false;
$bCredentials = false;

foreach ($R as $key => $val) {
	if (is_array($val)) {
		if ($debug_mode) {
			echo "Array ==> key : " . $key . ", value : (array) \n";
		}
		if ($index == 0 && $key != "VCAP_SERVICES") {
			$cfservicename = $key;
			if ($debug_mode) {
				echo "index => " . $index . ", cfservicename =>" . $cfservicename . "\n";
			}
		}
		if ($index == 1 && empty($cfservicename)) {
			$cfservicename = $key;
		}
		if ($key === "credentials") {
			$bCredentials = true;
		}
		$index = $index + 1;
	} else {
		if ($val != null) {
			if ($debug_mode) {
				echo "Non-Array ==> Key : " . $key . ", value : " . $val . "\n";
			}
			if ($key === "label") {
				$bMatchedLabel = true;
			}
			if ($bMatchedLabel == true && $key === "name") {
				if ($bCredentials == false) {
					$keyName = $key;
					$cfservice[$keyName] = $val;
				} else {
					// "credentials"의 자식인 "name"의 값은 무시한다.
				}
			}
			if ($bMatchedLabel == true && $key === "plan") {
				$keyName = $key;
				$cfservice[$keyName] = $val;
			}
			if ($key === "username") {
				$bCredentials = false;
			}
		} else {
			if ($debug_mode) {
				echo "Non-Array ==> Key : " . $key . ", value : null \n";
			}
		}
	}
}

if ($debug_mode) {
	echo "\n";
	echo "=================[ Result ] ===================\n";

	echo "cfservicename => " . $cfservicename . "\n";

	foreach ($cfservice as $key => $val) {
		echo "cfsvc" . " $key => $val \n";
	}

	echo "===============================================\n";
	echo "\n";
}

?>

<!DOCTYPE HTML>
<html lang="ko">

<head>
	<title>PHP App</title>
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel="shortcut icon" type="image/png" href="/shortcut-icon.png" />
	<link rel="stylesheet" media="all" href="/pui-3.0.0/pivotal-ui.min.css" />
	<link rel="stylesheet" href="/font-mfizz-2.3.0/font-mfizz.css" />
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.2/jquery.min.js"></script>
	<script src="/javascripts/ping.js"></script>
</head>

<body class="bg-neutral-11">
	<div class="container pan">
		<!-- header -->
		<section class="header">
			<div class='row man'>
				<div class='col-xs-24 col-sm-24 col-md-24 col-lg-24 mvxxl txt-c'>
					<a href='https://www.php.net/' target="_blank">
						<img src="/images/php_logo.png" width="120" class="txt-m" alt="PHP" title="PHP" />
					</a>
				</div>
			</div>
		</section>
		<!-- content -->
		<section class="content">
			<ul class="list-group">
				<!-- buildpack -->
				<li class="list-group-item pln">
					<div class="row man">
						<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3 phxl">
							<span class="h1 pan man icon-spring type-neutral-5"
								style="top: 4px; left: -1px; position: relative;"></span>
						</div>
						<div class="col-xs-12 col-sm-15 col-md-15 col-lg-15">
							<div class="type-neutral-4 small">
								<div>BUILDPACK</div>
							</div>
							<div class="type-ellipsis">
								PHP
							</div>
						</div>
						<div class="col-xs-9 col-sm-6 col-md-6 col-lg-6 ptl txt-r type-ellipsis">
							<a href='https://docs.cloudfoundry.org/buildpacks/php/index.html' target='_blank' class='type-accent-4'>
								<span class="small">Buildpacks</span><span class="fa fa-icon fa-external-link plm txt-m small"></span>
							</a>
						</div>
					</div>
				</li>

				<!-- app -->
				<li class="list-group-item pln">
					<div class="row man">
						<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3 phxl">
							<span class="h1 pan man fa fa-icon fa-play-circle type-neutral-5"
								style="left: 0px; position: relative;"></span>
						</div>
						<div class="col-xs-5 col-sm-5 col-md-5 col-lg-5">
							<div class="type-neutral-4 small">
								<div class="type-ellipsis">APP NAME</div>
							</div>
							<div class="type-ellipsis">
								<?php
								if (!isset($cfapp) || !isset($cfapp['application_name'])) {
									echo "@application_name";
								} else {
									echo $cfapp['application_name'];
								}
								?>
							</div>
						</div>
						<div class="col-xs-5 col-sm-5 col-md-10 col-lg-10">
							<div class="type-neutral-4 small">
								<div class="type-ellipsis">APP URIS</div>
							</div>
							<div class="type-ellipsis">
								<?php
								if (!isset($cfapp) || !isset($cfapp['application_uris'])) {
									echo "@app_uris";
								} else {
									echo $cfapp['application_uris'];
								}
								?>
							</div>
						</div>
						<div class="col-xs-11 col-sm-11 col-md-6 col-lg-6 ptl txt-r type-ellipsis">
							<a href='https://docs.cloudfoundry.org/devguide/deploy-apps/routes-domains.html' target='_blank'
								class='type-accent-4'>
								<span class="small">Routes &amp; Domains</span><span
									class="fa fa-icon fa-external-link plm txt-m small"></span>
							</a>
						</div>
					</div>
				</li>


				<!-- limits -->
				<li class="list-group-item pln">
					<div class="row man">
						<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3 phxl">
							<span class="h1 pan man fa fa-icon fa-dashboard type-neutral-5"
								style="left: -2px; position: relative;"></span>
						</div>
						<div class="col-xs-5 col-sm-5 col-md-5 col-lg-5">
							<div class="type-neutral-4 small">
								<div class="type-ellipsis">INSTANCE INDEX</div>
							</div>
							<div class="type-ellipsis">
								<?php
								if (!isset($cfapp) || !isset($cfapp['instance_index'])) {
									echo "@app_instance_index";
								} else {
									echo $cfapp['instance_index'];
								}
								?>
							</div>
						</div>
						<div class="col-xs-5 col-sm-5 col-md-5 col-lg-5">
							<div class="type-neutral-4 small">
								<div class="type-ellipsis">MEMORY LIMIT</div>
							</div>
							<div class="type-ellipsis">
								<?php
								if (!isset($cfapp) || !isset($cfapp['mem'])) {
									echo "@application_mem_limits";
								} else {
									echo $cfapp['mem'];
								}
								?>
							</div>
						</div>
						<div class="col-xs-5 col-sm-5 col-md-5 col-lg-5">
							<div class="type-neutral-4 small">
								<div class="type-ellipsis">DISK LIMIT</div>
							</div>
							<div class="type-ellipsis">
								<?php
								if (!isset($cfapp) || !isset($cfapp['disk'])) {
									echo "@application_disk_limits";
								} else {
									echo $cfapp['disk'];
								}
								?>
							</div>
						</div>
						<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 ptl txt-r type-ellipsis">
							<a href='https://docs.cloudfoundry.org/devguide/deploy-apps/cf-scale.html' target='_blank'
								class='type-accent-4'>
								<span class="small">Scaling</span><span class="fa fa-icon fa-external-link plm txt-m small"></span>
							</a>
						</div>
					</div>
				</li>
				<!-- space -->
				<li class="list-group-item pln">
					<div class="row man">
						<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3 phxl">
							<span class="h1 pan man fa fa-icon fa-bullseye type-neutral-5"
								style="left: 0px; position: relative;"></span>
						</div>
						<div class="col-xs-12 col-sm-15 col-md-15 col-lg-15">
							<div class="type-neutral-4 small">
								<span class="type-ellipsis">SPACE NAME</span>
							</div>
							<div class="type-ellipsis">
								<?php
								if (!isset($cfapp) || !isset($cfapp['space_name'])) {
									echo "@app_space_name";
								} else {
									echo $cfapp['space_name'];
								}
								?>
							</div>
						</div>
						<div class="col-xs-9 col-sm-6 col-md-6 col-lg-6 ptl txt-r type-ellipsis">
							<a href='https://docs.cloudfoundry.org/concepts/roles.html' target='_blank' class='type-accent-4'>
								<span class="small txt-m">Orgs &amp; Spaces</span><span
									class="fa fa-icon fa-external-link plm txt-m small"></span>
							</a>
						</div>
					</div>
				</li>

				<!-- services -->
				<li class="list-group-item pln">
					<div class="row man">
						<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3 phxl">
							<span class="h1 pan man fa fa-icon fa-database type-neutral-5"
								style="left: 0px; position: relative;"></span>
						</div>
						<!-- service bound to the app -->
						<?php if (isset($cfservicename)) : ?>
						<div>
							<div class="col-xs-5 col-sm-5 col-md-5 col-lg-5">
								<div class="type-neutral-4 small">
									<div class="type-ellipsis">SERVICE</div>
								</div>
								<div class="type-ellipsis">
									<?php echo $cfservicename; ?>
								</div>
							</div>
							<div class="col-xs-5 col-sm-5 col-md-5 col-lg-5">
								<div class="type-neutral-4 small">
									<div class="type-ellipsis">SERVICE NAME</div>
								</div>
								<div class="type-ellipsis">
									<?php
										if (!isset($cfservice) || !isset($cfservice['name'])) {
											echo "@service_name";
										} else {
											echo $cfservice['name'];
										}
										?>
								</div>
							</div>
							<div class="col-xs-5 col-sm-5 col-md-5 col-lg-5">
								<div class="type-neutral-4 small">
									<div class="type-ellipsis">SERVICE PLAN</div>
								</div>
								<div class="type-ellipsis">
									<?php
										if (!isset($cfservice) || !isset($cfservice['plan'])) {
											echo "@service_plan";
										} else {
											echo $cfservice['plan'];
										}
										?>
								</div>
							</div>
						</div>
						<?php endif; ?>
						<!-- no service bound to the app -->
						<?php if (!isset($cfservicename)) : ?>
						<div>
							<div class="col-xs-12 col-sm-15 col-md-15 col-lg-15">
								<div class="ptl type-ellipsis">There aren't any services bound to this app.</div>
							</div>
							<div class="col-xs-9 col-sm-6 col-md-6 col-lg-6 ptl txt-r type-ellipsis">
								<a href='http://docs.cloudfoundry.org/devguide/services/managing-services.html' target='_blank'
									class='type-accent-4'>
									<span class="small">Manage Services</span><span
										class="fa fa-icon fa-external-link plm txt-m small"></span>
								</a>
							</div>
						</div>
						<?php endif; ?>
						<!-- service bound to the app -->
						<?php if (isset($cfservicename)) : ?>
						<div>
							<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 ptl txt-r type-ellipsis">
								<a href='https://docs.cloudfoundry.org/devguide/services/managing-services.html' target='_blank'
									class='type-accent-4'>
									<span class="small">Manage Services</span><span
										class="fa fa-icon fa-external-link plm txt-m small"></span>
								</a>
							</div>
						</div>
						<?php endif; ?>
					</div>
				</li>
			</ul>
	</div>
	</li>
	</ul>
	</section>
	<!-- footer -->
	<section class="footer">
		<div class="row mhn mvxxl txt-c">
			<a href="https://docs.cloudfoundry.org/devguide/" target="_blank">
				<img src="/images/koscom_nia_nbp_logo.png" width="180" class="txt-b" alt="KOSCOM/NIA/NBP"
					title="KOSCOM/NIA/NBP" />
				<br />
				<span class="txt-c small type-neutral-4">
					This is a Cloud Foundry sample application.
				</span>
			</a>
			<div data-ping="ping" style="display:none;"></div>
		</div>
	</section>
	</div>
</body>

</html>