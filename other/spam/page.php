
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Stat</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
	<link rel="stylesheet" href="//cdn.datatables.net/plug-ins/1.10.10/integration/bootstrap/3/dataTables.bootstrap.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
	<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
	<script src="//cdn.datatables.net/1.10.10/js/jquery.dataTables.min.js"></script>
	<script src="//cdn.datatables.net/plug-ins/1.10.10/integration/bootstrap/3/dataTables.bootstrap.js"></script>
	<script type="text/javascript" charset="utf-8"> $(document).ready(function() { $('#List').dataTable({ "order": [[ 3, "desc" ]] }); }); </script>
	<style>.table td,th { text-align: center; }</style>
</head>
<body>
<div class="container">
	<div class="row">
		<div style="width: 600px; margin: auto;">
			<br/>
			<br/>
			<div class="panel panel-default">           
				<table class="table table-bordered" >
					<thead>
						<tr>
							<th>Заблокировано (<?php echo @round($stat['no']/($stat['no']+$stat['yes'])*100); ?>%)</th>
							<th>Отправлено (<?php echo @round($stat['yes']/($stat['no']+$stat['yes'])*100); ?>%)</th>
							<th>Обработано (100%)</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><?php echo $stat['no']; ?></td>
							<td><?php echo $stat['yes']; ?></td>
							<td><?php echo $stat['no']+$stat['yes']; ?></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<table id="List" class="table table-striped table-bordered" cellspacing="0" width="100%">
			<thead>
				<tr>
					<th>ID</th>
					<th>Hash</th>
					<th>Info</th>
					<th>Count</th>
					<th>Last</th>
				</tr>
			</thead>
			<tbody>
					<?php echo $table; ?>
			</tbody>
		</table>
	</div>
</div>
</body>
</html>
