<script type="text/javascript">
jQuery.noConflict();

var app = angular.module('monitoramentoPde', ['ngResource','ngAnimate','ui.bootstrap','angular.filter']);

app.factory('FontesDados', function($resource){
	return $resource('/wp-json/monitoramento_pde/v1/fontes_dados/');
});

app.factory('DadoAberto', function($resource){
	return $resource('/wp-json/monitoramento_pde/v1/dado_aberto/:fonte_dados');
});

app.factory('Instrumentos', function($resource){
	return $resource('/wp-json/monitoramento_pde/v1/instrumentos/');
});

app.filter('trustedHtml',
   function($sce) {
     return function(ss) {
       return $sce.trustAsHtml(ss)
   };
});

app.controller("dadosAbertos", function($scope, $http, $filter, FontesDados, DadoAberto) {
 
 	FontesDados.query({ativa:true},function(fontesDados) {
		$scope.fontesDados = fontesDados;
	
	$scope.menuTipoDados = [
		{	
			titulo:'Banco de dados',
			introducao: '<p>Os indicadores são fruto do cálculo e cruzamento de dados organizados em bancos que alimentam o sistema de monitoramento e avaliação.</p><p>Veja abaixo a lista de bancos de dados.</p>',
			tipoArquivo:['XLSX', 'CSV', 'TXT'],
			seletor:'instrumento',
			dados:$scope.fontesDados
			//['oodc','tdc','tdc_certidoes','zepam','cota-solidariedade'];
		},
		{	
			titulo:'Ficha técnica dos instrumentos', 
			introducao: '<p>Os Instrumentos Urbanísticos e de Gestão Ambiental são meio para viabilizar a efetivação dos princípios e objetivos do Plano Diretor.</p><p>Veja abaixo a lista dos instrumentos.</p><p>Se desejar, filtre por estratégia:</p>',
			tipoArquivo:['DOC |','PDF'],
			seletor:'estrategia',
			dados:['Transferência do direito de construir','Estudo de Impacto Ambiental','Estudo de viabilidade ambiental', 'Avaliação ambiental estratégica']
		},
		{
			titulo:'Ficha técnica dos indicadores',
			introducao: '<p>Os indicadores de monitoramento e avaliação contemplam, abordando a eficiência, eficácia e efetividade, das diferentes dimensões de avaliação das políticas públicas presentes no Plano Diretor.</p><p>Veja abaixo a lista dos indicadores.</p><p>Se desejar, filtre por estratégia:</p>',
			tipoArquivo:['DOC','PDF'],
			seletor:'estrategia',
			dados:['Percentual de áreas grafadas como ZEPAM','Variação da cobertura vegetal em ZEPAM', 'Densidade de ZEPAM por habitante', 'Distribuição de usos nas áreas marcadas como ZEPAM']
		}
	];
	
	//$scope.menuTipoDados[0].dados.push('Ficha Técnica dos Instrumentos');
	
	$scope.item = $scope.menuTipoDados[0];

	// DECODE JSON dados_disponiveis
	for (var i = $scope.item.dados.length - 1; i >= 0; i--) {
		$scope.item.dados[i].dados_disponiveis = JSON.parse($scope.item.dados[i].dados_disponiveis);
	}
	
	 $scope.estrategias = [
			{id:1,nome:'Socializar os ganhos da produção na cidade'},
			{id:2,nome:'Assegurar o direito à moradia digna para quem precisa'},
			{id:3,nome:'Melhorar a mobilidade urbana'},
			{id:4,nome:'Qualificar a vida nos bairros'},
			{id:5,nome:'Orientar o crescimento da cidade nas proximidades do transporte público'},
			{id:6,nome:'Reorganizar as dinâmicas metropolitanas'},
			{id:7,nome:'Promover o desenvolvimento econômico da cidade'},
			{id:8,nome:'Incorporar a agenda ambiental ao desenvolvimento da cidade'},
			{id:9,nome:'Preservar o patrimonio e valorizar as iniciativas culturais'},
			{id:10,nome:'Fortalecer a participação popular nas decisoes dos rumos da cidade'}
	 ];
	 
	$scope.instrumentos = [
		{id:13,nome:'FUNDURB'},
		{id:12,nome:'Eixos de Estruturação da Transformação Urbana [EETU]'},
		{id:18,nome:'Zonas Produtivas [ZPI+ZDE]'},
		{id:15,nome:'Perímetros de Incentivo ao Desenvolvimento Econômico'},
		{id:16,nome:'Parcelamento, Edificação e Utilização Compulsórios [PEUC]'},
		{id:14,nome:'IPTU Progressivo no Tempo'},
		{id:22,nome:'ZEIS'},
		{id:23,nome:'Regularização Fundiária'},
		{id:24,nome:'Termo de Compensação Ambiental [TCA]'},
		{id:11,nome:'EIA-RIMA'},
		{id:19,nome:'ZEPAM'},
		{id:17,nome:'Transferência do Direito de Construir [TDC]'},
		{id:26,nome:'Outorga Onerosa do Direito de Construir [OODC]'},
		{id:25,nome:'Operação Urbana Consorciada [OUC]'},
		{id:21,nome:'ZEPEC'},
		{id:20,nome:'Tombamento'}
	 ];	 
	});

 	// ISSUE 53
	$scope.formataData = function(rawDate) {
		let dataFinal = $filter('date')(rawDate, 'MMMM yyyy');
		dataFinal = dataFinal.charAt(0).toUpperCase() + dataFinal.slice(1); // Torna primeira letra maiúscula
		return "Atualizado até: "+dataFinal;
	}
	
	$scope.pontoParaVirgula = function(v){
		// Realiza a operação somente se TODA a string representa número válido, se o valor não contém whitespace, e se a string não tem um 0 não seguido de ponto à esquerda
		if(!isNaN(v) && !isNaN(parseFloat(v)) && (!(/^[0]/.test(v)) || (/^[0]\./.test(v)))){
			let vString = parseFloat(v).toString(); //Remove os zeros finais após a vírgula
			v = vString.replace('.',',');
			}
		return v;
	}
	
		function Workbook() {
			if(!(this instanceof Workbook)) return new Workbook();
			this.SheetNames = [];
			this.Sheets = {};
		}
		
		function s2ab(s) {
			var buf = new ArrayBuffer(s.length);
			var view = new Uint8Array(buf);
			for (var i=0; i!=s.length; ++i) view[i] = s.charCodeAt(i) & 0xFF;
			return buf;
		}
		
		function criarCelula(c, r, val){
			var cell = {v: val };
			
			if(cell.v == null) cell.v = '';
			
			if(typeof cell.v === 'number') cell.t = 'n';
			else if(typeof cell.v === 'boolean') cell.t = 'b';
			else if(cell.v instanceof Date) {
				cell.t = 'n'; cell.z = XLSX.SSF._table[14];
				cell.v = datenum(cell.v);
				
			}
			else cell.t = 's';
			
			return cell;
		}
			
			
		function sheet_from_array_of_objects(data, offset) {
			var ws = {};
			data.unshift(data[0]); // Duplica primeiro item do array para evitar supressão dos valores
			var range = {s: {c:10000000, r:10000000}, e: {c:0, r:0 }};
			
			for(var R = 0; R < data.length; R++) {
				C = 0;
				
				for(var prop in data[R]) {

					if(prop.substring(prop.length-1,prop.length) <= offset || prop.substring(0,prop.length-1)!= 'Variavel'){
						if(range.s.r > R) range.s.r = R;
						if(range.s.c > C) range.s.c = C;
						if(range.e.r < R) range.e.r = R;
						if(range.e.c < C) range.e.c = C;
						if(typeof data[R][prop] === 'function') continue;
						if(R == 0)
							var cell = {v: prop };
						else
							var cell = {v: data[R][prop] };
						
						if(cell.v == null) cell.v = '';
						var cell_ref = XLSX.utils.encode_cell({c:C,r:R});
						
						if(typeof cell.v === 'number') cell.t = 'n';
						else if(typeof cell.v === 'boolean') cell.t = 'b';
						else if(cell.v instanceof Date) {
							cell.t = 'n'; cell.z = XLSX.SSF._table[14];
							cell.v = datenum(cell.v);
						}
						else cell.t = 's';
						
						ws[cell_ref] = cell;
						C++;
					}
				}
			}
			
			if(range.s.c < 10000000) ws['!ref'] = XLSX.utils.encode_range(range);
			return ws;
		}
	
	$scope.exportarDadoAberto = function(id_fonte, formato){
		DadoAberto.query({fonte_dados:id_fonte},function(dadoAberto) {
			// ALTERA PONTO PARA VIRGULA
			for(dado in dadoAberto){
				for(valor in dadoAberto[dado]){
		 			dadoAberto[dado][valor] = $scope.pontoParaVirgula(dadoAberto[dado][valor]);
		 		}
		 	}
		 	// FIM ALTERA PONTO PARA VIRGULA
			$scope.dadoAberto = dadoAberto;
		 	fonteDados = $scope.item.dados.filter((fonteDados) => fonteDados.id_fonte_dados == id_fonte)[0];
			var wb = new Workbook();
			
			wsDadoAberto = sheet_from_array_of_objects(dadoAberto,0);
			wb.SheetNames.push('dados');
			wb.Sheets['dados'] = wsDadoAberto;
			
			switch(formato){
				case 'XLSX':
					extensaoArquivo = 'xlsx';
					var wbout = XLSX.write(wb, {bookType:'xlsx', bookSST:false, type: 'binary'});
					break;
				case 'CSV':
					extensaoArquivo = 'csv';
					var wbout = XLSX.utils.sheet_to_csv(wsDadoAberto,{FS:";"});
					break;
				case 'TXT':
					extensaoArquivo = 'txt';
					var wbout = XLSX.utils.sheet_to_csv(wsDadoAberto,{FS:"\t"});
					break;
			}
			
			saveAs(new Blob([s2ab(wbout)],{type:"application/octet-stream"}), fonteDados.nome_tabela + '.'  + extensaoArquivo);
			
		});
	}
	
	$scope.verificaTabela = function (id_fonte) {
		try {
			/*
			DadoAberto.query({fonte_dados:id_fonte},function(dadoAberto) {
				console.log(typeof(dadoAberto));
				// if(dadoAberto.length > 0)
				// 	existe = true;
			});
			*/
			/*
			var xhttp = new XMLHttpRequest();
			xhttp.onreadystatechange = function() {
				if(this.readyState == 4 && this.status == 200) {
					console.log("SUCESSO");
					// console.log(xhttp.responseText);
					let resposta = JSON.parse(this.responseText);
					console.log(typeof(resposta));
					return (typeof(resposta) == "object");
				}
				else {
					return false;
				}
			}
			xhttp.open("GET", "/wp-json/monitoramento_pde/v1/dado_aberto/"+id_fonte, true);
			xhttp.send();
			*/
		}
		catch(err) {
			console.warn(err);
			return false;
		}
	}

	$scope.capitalize = function(texto) {
		let textoRetorno = texto.charAt(0).toUpperCase() + texto.slice(1);
		if(textoRetorno.length <= 3)
			textoRetorno = textoRetorno.toUpperCase();
		return textoRetorno;
	}

	$scope.trataFormatoFonte = function(formato, exibicao = false) {
		let formatoTratado = formato.toLowerCase().replaceAll(/[^a-z]/g, "");
		
		if(exibicao)
			formatoTratado = formatoTratado.toUpperCase();
		else if(formatoTratado === "xlsx")
			formatoTratado = "xls";

		return formatoTratado;
	}

	$scope.debugLog = function (el) {
		console.warn("DebugLOG:");
		console.log(el);
	}
});

</script>


<div id="conteudo" class="content-page container text-justify" data-ng-app="monitoramentoPde" data-ng-controller="dadosAbertos">
<?php the_content(); ?>
	<div ng-bind-html="item.introducao | trustedHtml"></div>
	<ul class="list-group">
		
		<li class="list-group-item row list-pontilhada" data-ng-repeat="dado in item.dados | orderBy: 'nome'">
			<div class="col-sm-8">
				<span><b>{{!dado.nome? dado : dado.nome}}</b></span>
				<br>
				<span>{{dado.data_atualizacao ? formataData(dado.data_atualizacao) : ''}}</span>					
			</div>
			<div class="text-right badge-list" ng-if="!dado.dados_disponiveis[0]"> 
				<ul>
					<li ng-show="(dado.colunas.length > 1)" data-ng-repeat="formato in item.tipoArquivo">
						<a href="" ng-click="exportarDadoAberto(dado.id_fonte_dados,formato)" class="label" data-format="{{formato.toLowerCase()}}"> <strong> {{formato}} </strong></a>
					</li>
					<li ng-if="dado.arquivo_metadados">
						<a href="<?php echo bloginfo('url'); ?>/app/uploads/{{dado.nome_tabela}}/{{dado.arquivo_metadados}}" class="label" data-format="xls"><strong>Metadados</strong></a> 
					</li>
					<li ng-if="dado.arquivo_mapas">
						<a href="<?php echo bloginfo('url'); ?>/app/uploads/{{dado.nome_tabela}}/{{dado.arquivo_mapas}}" class="label"><strong>SHP</strong></a> 
					</li>
					<li ng-if="dado.arquivo_tabelas">
						<a href="<?php echo bloginfo('url'); ?>/app/uploads/{{dado.nome_tabela}}/{{dado.arquivo_tabelas}}" class="label" data-format="tbl"><strong>Tabelas</strong></a>
					</li>
				</ul>
			</div>
			<div class="text-right badge-list">
				<ul>
					<li ng-if="dado.colunas.length > 1 && dado.dados_disponiveis[0] && dado.dados_disponiveis[0].disponivel" data-ng-repeat="formato in item.tipoArquivo">
						<a href="" ng-click="exportarDadoAberto(dado.id_fonte_dados,formato)" data-format="{{trataFormatoFonte(formato)}}" class="label">{{trataFormatoFonte(formato, true)}}</a>
						<!-- <a ng-show="(dado.colunas.length > 1)" href="" ng-click="exportarDadoAberto(dado.id_fonte_dados,formato)" data-ng-repeat="formato in item.tipoArquivo"> <strong> {{formato}} </strong></a> -->
					</li>
					<li data-ng-repeat="arquivo in dado.dados_disponiveis | orderBy: 'tipo.length'" ng-if="arquivo.tipo !== 'fonte de dados' && arquivo.disponivel">
						<a ng-if="arquivo.tipo === 'tabelas'" href="<?php echo bloginfo('url'); ?>/app/uploads/{{dado.nome_tabela}}/{{arquivo.nome}}" class="label" data-format="tbl">{{capitalize(arquivo.tipo)}}</a> 
						<a ng-if="arquivo.tipo !== 'tabelas'" href="<?php echo bloginfo('url'); ?>/app/uploads/{{dado.nome_tabela}}/{{arquivo.nome}}" class="label" data-format="{{arquivo.formato}}">{{capitalize(arquivo.tipo)}}</a> 
					</li>
				</ul>
			</div>
		</li>
		
	</ul>
</div>

<style type="text/css">
	ul {
		list-style: none;
	}
	.label {
		display: inline-block;
    padding: 2px 4px;
    font-size: 11.844px;
    font-weight: bold;
    line-height: 14px;
    color: #ffffff;
    vertical-align: baseline;
    white-space: nowrap;
    text-shadow: 0 -1px 0 rgba(0,0,0,0.25);
    background-color: #999999;
	}
	a.label {
		color: #ffffff;
	}
	.label[data-format=zip], .label[data-format*=zip] {
    background-color: #686868;
  }
  .dataset-resources li a {
    background-color: #aaaaaa;
  }
  .label[data-format=xls], .label[data-format*=xls] {
		background-color: #2db55d;
  }
  .label[data-format=csv], .label[data-format*=csv] {
    background-color: #dfb100;
  }
  .label[data-format=pdf], .label[data-format*=pdf] {
		background-color: #e0051e;
  }
  .label[data-format=txt], .label[data-format*=txt] {
		background-color: #e25a10;
  }
  .label[data-format=kmz], .label[data-format*=kmz] {
		background-color: #1464a4;
  }
  .label[data-format=shp], .label[data-format*=shp] {
		background-color: #951b81;
  }
  .label[data-format=tbl], .label[data-format*=tbl] {
		background-color: #0099da;
  }
  .badge-list li {
  	display: inline-block;
  	margin: 3px;
  }
</style>

<?php wp_link_pages(['before' => '<nav class="page-nav"><p>' . __('Pages:', 'sage'), 'after' => '</p></nav>']); ?>
