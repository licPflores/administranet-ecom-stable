<script type="text/javascript">

	
	var menuModel = new DHTMLSuite.menuModel();
	DHTMLSuite.configObj.setCssPath('suitedhtml/demos/css/');
	
	menuModel.addItemsFromMarkup('menuModel');
	menuModel.setMainMenuGroupWidth(00);	
	menuModel.init();
	
	var menuBar = new DHTMLSuite.menuBar();
	menuBar.addMenuItems(menuModel);
	menuBar.setLayoutCss('menu-bar-ps.css');
	menuBar.setMenuItemLayoutCss('menu-item-ps.css');

	menuBar.setTarget('innerDiv');
	
	menuBar.init();
	
	DHTMLSuite.configObj.resetCssPath();
	
	function mostrard(pagina)
	{
		vuelta=window.showModalDialog(pagina+"?clave=<?=$_SESSION["idusuario"]?>","","help:no;status:no;dialogHeight:350px;dialogWidth:420px;"); 
		//location.href="listascan.php";	
		//paneSplitter.loadContent("center",pagina,0);
		//paneSplitter.showContent("center");
	}
</script>		
