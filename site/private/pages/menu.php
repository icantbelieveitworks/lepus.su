<nav class="navbar navbar-default" style="margin-bottom: 0px;">
	<div class="container-fluid">
		<div class="navbar-header" style="width:100%">
			<ul class="nav navbar-nav navbar-right" style="float:right;">
				<li><lu class="navbar-brand" style="font-size: 13px;">ns1.lepus.su | ns2.poiuty.com</lu></li>
            </ul>
			<a class="navbar-brand" href="/">
				<img src="/images/lepuslogo.png" style="max-width: 100px;">
			</a>
			<ul class="nav navbar-nav">
				<?php echo lepus_getPageNavi(); ?>
			</ul>

		</div>
	</div>
</nav>

<div id="carousel-example-generic" class="carousel slide" data-ride="carousel" data-interval="7500">
	<ol class="carousel-indicators">
		<li data-target="#carousel-example-generic" data-slide-to="0" class="active"></li>
		<li data-target="#carousel-example-generic" data-slide-to="1"></li>
	</ol>
	<div class="carousel-inner" role="listbox">
		<div class="item active">
			<a href="https://lepus.su/pages/contacts.php"><img src="/images/lepusbar2.png"></a>
			<div class="carousel-caption"></div>
		</div>
		<div class="item">
			<a href="https://lepus.su/pages/vps.php"><img src="/images/lepusbar3.png"></a>
			<div class="carousel-caption"></div>
		</div>
		<a class="left carousel-control" href="#carousel-example-generic" role="button" data-slide="prev">
			<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
			<span class="sr-only">Previous</span>
		</a>
		<a class="right carousel-control" href="#carousel-example-generic" role="button" data-slide="next">
			<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
			<span class="sr-only">Next</span>
		</a>
	</div>
</div>
