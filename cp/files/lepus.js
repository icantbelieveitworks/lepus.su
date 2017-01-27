	function lepusCheck(val, filter) {
		switch (filter) {
			default:
				i = /[^0-9a-zA-Z._-]/i.test(val);
		}
		return i;
	}
	
	var getUrlParameter = function getUrlParameter(sParam) {
		var sPageURL = decodeURIComponent(window.location.search.substring(1)),
			sURLVariables = sPageURL.split('&'),
			sParameterName,
			i;
	
		for (i = 0; i < sURLVariables.length; i++) {
			sParameterName = sURLVariables[i].split('=');

			if (sParameterName[0] === sParam) {
				return sParameterName[1] === undefined ? true : sParameterName[1];
			}
		}
	};
	
	$(document).keypress(function(e) {
			if(e.which == 13 && document.getElementById("check_auth") !== null) lepus_login();
	});

	$(document).on("click", "[data-do-login]", function(e) {
		$(this).blur();
		e.preventDefault();
		lepus_login();
	});
		
	var lepus_login = function() {
		login = $('input[id=login]').val();
		passwd = $('input[id=password]').val();
		$.post("//"+document.domain+":"+location.port+"/api/login", {login: login, passwd: passwd}, function(json){
			data = JSON.parse(json);
			if(data.Err == 'OK'){
				location.reload();
			}else{
				alertify.error(data.Mes);
			}
			return;
		});
	};

	$(document).on("click", "[data-do-logout]", function(e) {
		$(this).blur();
		e.preventDefault();
		$.get("//"+document.domain+":"+location.port+"/api/exit");
		location.reload();
	});

	$(document).ready(function(){
		var page = getUrlParameter('page');
		if(page){
			var menu = [ '<li><a href="https://'+document.domain+'/phpmyadmin" target="_blank">phpMyAdmin</a></li>', 
						 '<li><a href="/?page=cp">WWW домены</a></li>',
						 '<li><a href="/?page=dns">Доменные имена</a></li>',
						 '<li><a href="/?page=cron">Планировщик задач</a></li>',
						 '<li><a href="https://github.com/poiuty/lepus.su/issues">Пожелания и ошибки</a></li>' ];
			
			var text = ""
			for (var key in menu) {
				text = text+menu[key];
			}
			$("#menu").append(text);
			$.post("//"+document.domain+":"+location.port+"/api/get", {val: "login"}, function(json){
				data = JSON.parse(json);
				if(data.Err == 'OK'){
					$("a#user").html(data.Mes+"<hr/>");
				}
				return;
			});
		}
		if(page == "cp"){
			var table = $('#mainList').DataTable();
			$.post("//"+document.domain+":"+location.port+"/api/get", {val: "www"}, function(json){
				data = JSON.parse(json);
				if(data.Err == 'OK'){
					j = JSON.parse(data.Mes);
					var i = 0;
					for (var key in j) {
						if(!j.hasOwnProperty(key)) continue;
						if(lepusCheck(key) || lepusCheck(j[key].http) || lepusCheck(j[key].status)) {
							continue;
						}
						i++;
						if(j[key].status == 'disable'){
							glyphicon = "glyphicon glyphicon-play";
						}else{
							glyphicon ="glyphicon glyphicon-pause";
						}
						table.row.add({
							DT_RowId: key,
							0:     i,
							1:     punycode.toUnicode(key),
							2:     j[key].ip,
							3:     j[key].http,
							4:     j[key].status,
							5:     '<a href="/?page=wwwedit&www='+key+'" title="Редактировать"><i class="glyphicon glyphicon-pencil"></i></a> &nbsp; <a href="#" data-delete-site='+key+' title="Удалить"><i class="glyphicon glyphicon-remove"></i></a> &nbsp; <a href="#" data-change-perm-site='+key+' title="Вкл/ выкл"><i id="permStatus" class="'+glyphicon+'"></i></a>'
						}).draw(false);
					}
				}
				return;
			});
		}
		
		if(page == "editdns"){
			$(".page-title").append(punycode.toUnicode(getUrlParameter('www')));
			
			var table = $('#mainList').DataTable();
			$.post("//"+document.domain+":"+location.port+"/api/get", {val: "dnsrecords", domain: getUrlParameter('www')}, function(json){
				data = JSON.parse(json);
				lines = data.Mes.split("\n");
				for (var key in lines){
					record = lines[key].split("\t");
					if((record.length - 1) != 3){
						continue;
					}
					table.row.add({
							0:     parseInt(key),
							1:     punycode.toUnicode(record[0]),
							2:     record[2],
							3:     record[3],
							4:     '<a href="#" data-delete-dnsrecord="'+btoa(lines[key])+'" title="Удалить"><i class="glyphicon glyphicon-remove"></i></a>'
					}).draw(false);
				}
			});
		}
		
		if(page == "cron"){
			var table = $('#mainList').DataTable();
			$.post("//"+document.domain+":"+location.port+"/api/get", {val: "cron"}, function(json){
				data = JSON.parse(json);
				if(data.Err == 'OK'){
					tasks = data.Mes.split("\n");
					
					for (var key in tasks){
						if(tasks[key] == "") continue;
						table.row.add({
							0:     parseInt(key)+1,
							1:     tasks[key],
							2:     '<a href="#" data-delete-cron="'+btoa(tasks[key])+'" title="Удалить"><i class="glyphicon glyphicon-remove"></i></a>'
						}).draw(false);
					}
					
				}
			});
		}
		
		if(page == "dns"){
			var table = $('#mainList').DataTable();
			$.post("//"+document.domain+":"+location.port+"/api/get", {val: "dns"}, function(json){
				data = JSON.parse(json);
				if(data.Err == 'OK'){
					domains = data.Mes.split(" ");
					
					for (var key in domains){
						if(domains[key] == "") continue;
						table.row.add({
							0:     parseInt(key)+1,
							1:     punycode.toUnicode(domains[key]),
							2:     '<a href="/?page=editdns&www='+domains[key]+'" title="Редактировать"><i class="glyphicon glyphicon-pencil"></i></a> &nbsp; <a href="#" data-delete-dns="'+domains[key]+'" title="Удалить"><i class="glyphicon glyphicon-remove"></i></a>'
						}).draw(false);
					}
					
				}
			});
		}
		
		if(page == "wwwedit"){
			if(lepusCheck(getUrlParameter('www'))){
				window.location = "/";
				return;
			}
			
			$.post("//"+document.domain+":"+location.port+"/api/get", {val: "type", site: getUrlParameter('www')}, function(json){
				data = JSON.parse(json);
				if(data.Err == 'OK'){
					$('select option[value="'+data.Mes+'"]').attr("selected",true);
				}
			});
			
			$(".page-title").append(punycode.toUnicode(getUrlParameter('www')));
			var table = $('#mainList').DataTable();
			$.post("//"+document.domain+":"+location.port+"/api/get", {val: "www", symlink: getUrlParameter('www')}, function(json){
				data = JSON.parse(json);
				if(data.Err == 'OK'){
					j = JSON.parse(data.Mes);
					var i = 0;
					for (var key in j) {
						if(!j.hasOwnProperty(key)) continue;
						if(key.includes("ServerAlias")){
							arr = key.split(" ");
							for (var x in arr){
								if(arr[x] == "ServerAlias" || arr[x] == "" || lepusCheck(arr[x]) || arr[x] == getUrlParameter("www")){
									continue;
								}
								i++;
								lepusAddLink(arr[x], i);
							}
						}else{
							if(lepusCheck(key) || lepusCheck(j[key].ip) || lepusCheck(j[key].status) || key == getUrlParameter("www")) {
								continue;
							}
							i++;
							lepusAddLink(key, i)
						}
					}
				}
				return;
			});
		}
	});

	function lepusAddLink(site, num){
		var table = $('#mainList').DataTable();
		table.row.add({
							DT_RowId: site,
							0:     num,
							1:     punycode.toUnicode(site),
							2:     '<a href="#" data-delete-link='+site+' title="Удалить"><i class="glyphicon glyphicon-remove"></i></a>',
						}).draw(false);
	}

	$(document).on("click", "[data-do-addLinkWWW]", function(e) {
		$(this).blur();
		e.preventDefault();
		var table = $('#mainList').DataTable();
		site = punycode.toASCII(getUrlParameter("www"));
		var link = punycode.toASCII($('input[id=link]').val());
		$.post("//"+document.domain+":"+location.port+"/api/weblink", {command: "add", val: site, link: link}, function(json){
			data = JSON.parse(json);
			if(data.Err == 'OK'){
				table.row.add({
					DT_RowId: data,
					0:     table.page.info().recordsTotal+1,
					1:     punycode.toUnicode(link),
					2:     '<a href="#" data-delete-link='+link+' title="Удалить"><i class="glyphicon glyphicon-remove"></i></a>',
				}).draw( false );
				alertify.success(data.Mes);
			}else{
				alertify.error(data.Mes);
			}
			return;
		});
	});

	$(document).on("click", "[data-do-addwww]", function(e) {
		$(this).blur();
		e.preventDefault();
		var site = $('input[id=site]').val();
		var mode = $('select[id=mode]').val();
		var table = $('#mainList').DataTable();
		$.post("//"+document.domain+":"+location.port+"/api/addwebdir", {val: punycode.toASCII(site), mode: mode}, function(json){
			data = JSON.parse(json);
			if(data.Err == 'OK'){
				if(lepusCheck(data.Mes)) {
					return;
				}
				table.row.add({
					DT_RowId: data,
					0:     table.page.info().recordsTotal+1,
					1:     site,
					2:     data.Mes,
					3:     mode,
					4:     'online',
					5:     '<a href="/?page=wwwedit&www='+punycode.toASCII(site)+'" title="Редактировать"><i class="glyphicon glyphicon-pencil"></i></a> &nbsp; <a href="/" data-delete-site='+punycode.toASCII(site)+' title="Удалить"><i class="glyphicon glyphicon-remove"></i>  &nbsp; <a href="#" data-change-perm-site='+punycode.toASCII(site)+' title="Вкл/ выкл"><i id="permStatus" class="glyphicon glyphicon-pause"></i></a>',
				}).draw( false );
				alertify.success("Done");
				if((punycode.toASCII(site).split(".").length - 1) == 1) {
					$.post("//"+document.domain+":"+location.port+"/api/weblink", {command: "add", val: site, link: "www."+site});
				}
			}else{
				alertify.error(data.Mes);
			}
			return;
		});
	});


	$(document).on("click", "[data-delete-link]", function(e) {
		$(this).blur();
		e.preventDefault();
		var id = this.id;
		link = $(this).data("delete-link");
		var row = $(this).closest("tr").get(0);
        var table = $('#mainList').dataTable();
		if(!confirm("Вы подтверждаете удаление?")) return;
		$.post("//"+document.domain+":"+location.port+"/api/weblink", {command: "del", val: getUrlParameter("www"), link: link}, function(json){
			data = JSON.parse(json);
			if(data.Err == 'OK'){
				table.fnDeleteRow(table.fnGetPosition(row));
				alertify.success(data.Mes);
			}else{
				alertify.error(data.Mes);
			}
			return;
		});
	});
	
	$(document).on("click", "[data-delete-site]", function(e) {
		$(this).blur();
		e.preventDefault();
		var id = this.id;
		site = $(this).data("delete-site");
        var row = $(this).closest("tr").get(0);
        var table = $('#mainList').dataTable();
		if(!confirm("Вы подтверждаете удаление?")) return;
		$.post("//"+document.domain+":"+location.port+"/api/delwebdir", {val: punycode.toASCII(site)}, function(json){
		data = JSON.parse(json);
			if(data.Err == 'OK'){
				table.fnDeleteRow(table.fnGetPosition(row));
				alertify.success(data.Mes);
			}else{
				alertify.error(data.Mes);
			}
			return;
		});
	});
	
	$(document).on("click", "[data-change-perm-site]", function(e) {
		$(this).blur();
		e.preventDefault();
		site = $(this).data("change-perm-site");
		var table = $('#mainList').dataTable();
		var row = $(this).closest("tr").get(0);
		$.post("//"+document.domain+":"+location.port+"/api/chwebdir", {val: site}, function(json){
			data = JSON.parse(json);
			if(data.Err == 'OK'){
				if(data.Mes == 'online'){
					$("#permStatus").removeClass("glyphicon-play");
					$("#permStatus").addClass("glyphicon-pause");
					table.fnUpdate('online', table.fnGetPosition(row), 4, false, false);
				}else{
					$("#permStatus").removeClass("glyphicon-pause");
					$("#permStatus").addClass("glyphicon-play");
					table.fnUpdate('disable', table.fnGetPosition(row), 4, false, false);
				}
				alertify.success(data.Mes);
			}else{
				alertify.error(data.Mes);
			}
			return;
		});
	});
	
	$(document).on("click", "[data-do-changemodewww]", function(e) {
		$(this).blur();
		e.preventDefault();
		mode = $('select[id=mode]').val();
		if(!confirm("Вы подтверждаете изменение?")) return;
		$.post("//"+document.domain+":"+location.port+"/api/chwebmode", {val: getUrlParameter("www"), mode: mode}, function(json){
			data = JSON.parse(json);
			if(data.Err == 'OK'){
				alertify.success(data.Mes);
			}else{
				alertify.error(data.Mes);
			}
		});
	});
	
	$(document).on("click", "[data-cron-add]", function(e) {
		$(this).blur();
		e.preventDefault();
		time = $('input[id=cronTime]').val();
		handler = $('select[id=cronHandler]').val();
		command = $('input[id=cronCommand]').val();
		var table = $('#mainList').DataTable();
		$.post("//"+document.domain+":"+location.port+"/api/cron", {val: "add", time: time, handler: handler, command: command}, function(json){
			data = JSON.parse(json);
			if(data.Err == 'OK'){
				data.Mes = data.Mes.replace(/(\r\n|\n|\r)/gm,"");
				table.row.add({
							0:     table.page.info().recordsTotal+1,
							1:     data.Mes,
							2:     '<a href="#" data-delete-cron="'+btoa(data.Mes)+'" title="Удалить"><i class="glyphicon glyphicon-remove"></i></a>'
				}).draw(false);
				alertify.success("Done");
			}else{
				alertify.error(data.Mes);
			}
		});
	});
	
	$(document).on("click", "[data-delete-cron]", function(e) {
		$(this).blur();
		e.preventDefault();
		task = $(this).data("delete-cron");
		var table = $('#mainList').dataTable();
		var row = $(this).closest("tr").get(0);
		$.post("//"+document.domain+":"+location.port+"/api/cron", {val: "del", task: task}, function(json){
			data = JSON.parse(json);
			if(data.Err == 'OK'){
				table.fnDeleteRow(table.fnGetPosition(row));
				alertify.success(data.Mes);
			}else{
				alertify.error(data.Mes);
			}
		});
	});
	
	$(document).on("click", "[data-dns-add]", function(e) {
		$(this).blur();
		e.preventDefault();
		domain = $('input[id=dnsDomain]').val();
		var table = $('#mainList').DataTable();
		$.post("//"+document.domain+":"+location.port+"/api/dns", {val: "add", domain: domain}, function(json){
			data = JSON.parse(json);
			if(data.Err == 'OK'){
				table.row.add({
							0:     table.page.info().recordsTotal+1,
							1:     domain,
							2:     '<a href="/?page=editdns&www='+punycode.toASCII(domain)+'" title="Редактировать"><i class="glyphicon glyphicon-pencil"></i></a> &nbsp; <a href="#" data-delete-dns="'+punycode.toASCII(domain)+'" title="Удалить"><i class="glyphicon glyphicon-remove"></i></a>'
				}).draw(false);
				alertify.success(data.Mes);
			}else{
				alertify.error(data.Mes);
			}
		});
	});
	
	$(document).on("click", "[data-delete-dns]", function(e) {
		$(this).blur();
		e.preventDefault();
		domain = $(this).data("delete-dns");
		var table = $('#mainList').dataTable();
		var row = $(this).closest("tr").get(0);
		$.post("//"+document.domain+":"+location.port+"/api/dns", {val: "del", domain: domain}, function(json){
			data = JSON.parse(json);
			if(data.Err == 'OK'){
				table.fnDeleteRow(table.fnGetPosition(row));
				alertify.success(data.Mes);
			}else{
				alertify.error(data.Mes);
			}
		});
	});

	$(document).on("click", "[data-delete-dnsrecord]", function(e) {
		$(this).blur();
		e.preventDefault();
		record = $(this).data("delete-dnsrecord");
		var table = $('#mainList').dataTable();
		var row = $(this).closest("tr").get(0);
		$.post("//"+document.domain+":"+location.port+"/api/dnsrecords", {val: "del", domain: getUrlParameter('www'), data: record}, function(json){
			data = JSON.parse(json);
			if(data.Err == 'OK'){
				table.fnDeleteRow(table.fnGetPosition(row));
				alertify.success(data.Mes);
			}else{
				alertify.error(data.Mes);
			}
		});
	});

	$(document).on("click", "[data-dns-zone-add]", function(e) {
		$(this).blur();
		e.preventDefault();
		record = $(this).data("delete-dnsrecord");
		var table = $('#mainList').DataTable();
		var zone = $('input[id=dnsZone]').val().trim().replace(/\s\s+/g, ' ');
		var type = $('select[id=dnsZoneType]').val();
		var info = $('input[id=dnsZoneData]').val().trim().replace(/\s\s+/g, ' ');
		if((zone.split(".").length - 1) > 0 && zone.slice(-1) != "."){
			zone += ".";
		}
		all = zone+"\tIN\t"+type+"\t"+info;
		$.post("//"+document.domain+":"+location.port+"/api/dnsrecords", {val: "add", domain: getUrlParameter('www'), data: all}, function(json){
			data = JSON.parse(json);
			if(data.Err == 'OK'){
				table.row.add({
							0:     table.page.info().recordsTotal+1,
							1:     zone,
							2:     type,
							3:     info,
							4:     '<a href="#" data-delete-dnsrecord="'+btoa(all)+'" title="Удалить"><i class="glyphicon glyphicon-remove"></i></a>'
				}).draw(false);
				alertify.success(data.Mes);
			}else{
				alertify.error(data.Mes);
			}
		});
	});

