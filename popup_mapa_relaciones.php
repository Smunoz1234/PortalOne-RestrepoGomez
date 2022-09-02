<?php 
if(isset($_GET['id'])&&$_GET['id']!=""){
	require_once("includes/conexion.php");
	
	$ID=base64_decode($_GET['id']);
	$doctype=base64_decode($_GET['doctype']);
	
	$Param=array(
		"'".$doctype."'",
		"'".$ID."'",
		"'".$_SESSION['CodUser']."'"
	);
	$SQL=EjecutarSP('sp_ConsultarMapaRelaciones',$Param);
	
	$records=array();//La lista de los documentos que se van a dibujar
	$links=array();//La lista de los link (flechas) que se indican como se van a conectar
	$docs=array();//Los documentos de marketing que se estan visualizando, para controlar que no se repitan
	
	$i=0;
	$j=0;
	
	if($doctype=='191'){//Llamada de servicio
		while($row=sqlsrv_fetch_array($SQL)){
			if($i==0){
				$records[$i]=array(
					'key' => $row['ID_LlamadaServicio'],
					'docentry' => $row['ID_LlamadaServicio'],
					'name' => $row['Nombre'],
					'estado' => $row['DeEstadoLlamada'],
					'numero' => $row['DocNumLlamada'],
					'fecha' => $row['FechaCreacionLlamada'],
					'link' => $row['Link'],
					'color' => '#cdf4b0'
				);
				sqlsrv_next_result($SQL);
			}else{
				$records[$i]=array(
					'key' => $row['DocEntry'],
					'docentry' => $row['DocEntry'],
					'name' => $row['DeObjeto'],
					'estado' => $row['NombreEstado'],
					'numero' => $row['DocNum'],
					'fecha' => $row['DocDate'],
					'usuario' => $row['Usuario'],
					'link' => $row['Link']
				);
				
				if($row['IdObjeto']==4){//Lista de materiales
					$links[$i-1]=array(
						'from' => $row['DocEntry'],
						'to' => $row['ID_LlamadaServicio']
					);
				}else{
					$links[$i-1]=array(
						'from' => $row['ID_LlamadaServicio'],
						'to' => $row['DocEntry']
					);
				}				
			}

			$i++;
		}
	}else{//Documentos de marketing
		$SQL_Data=Seleccionar('uvw_tbl_MapaRelaciones_DatosTemp','*',"Usuario='".$_SESSION['CodUser']."'");
		while($row=sqlsrv_fetch_array($SQL_Data)){
			
			if(!in_array($row['DocType'].'_'.$row['DocEntry'],$docs)){
				$docs[$j]=$row['DocType'].'_'.$row['DocEntry'];
			
				$records[$j]=array(
					'key' => $row['DocType'].'_'.$row['DocEntry'],
					'docentry' => $row['DocEntry'],
					'name' => $row['DeObjeto'],
					'estado' => $row['NombreEstado'],
					'numero' => $row['DocNum'],
					'fecha' => $row['DocDate'],
					'usuario' => $row['UsuarioCreacion'],
					'link' => $row['Link'],
					'color' => (($doctype.'_'.$ID)==($row['DocType'].'_'.$row['DocEntry'])) ? '#cdf4b0' : '#ffffff'
				);
				
				$j++;
			}			
			
			if($row['TipoEnlace']=='Base'){
				$links[$i]=array(
					'from' => $row['DocBaseObjeto'].'_'.$row['DocBaseDocEntry'],
					'to' => $row['DocType'].'_'.$row['DocEntry']
				);
			}elseif($row['TipoEnlace']=='Destino'){
				$links[$i]=array(
					'from' => $row['DocType'].'_'.$row['DocEntry'],
					'to' => $row['DocDestinoObjeto'].'_'.$row['DocDestinoDocEntry']
				);
			}		
			
			$i++;
		}
	}
	
	
	$json_str=json_encode($records);
	$json_link=json_encode($links);
	
	
	
//	echo "<pre>";
//	print_r($json_str);
//	echo "<br>";
//	print_r($json_link);
//	echo "</pre>";
//	exit();
?>
<!doctype html>
<html>
<head>
<?php include_once("includes/cabecera.php"); ?>
<title>Mapa de relaciones | <?php echo NOMBRE_PORTAL;?></title>

  <script id="code">
    function init() {
      if (window.goSamples) goSamples();  // init for these samples -- you don't need to call this
      var $ = go.GraphObject.make;  // for conciseness in defining templates

      // some constants that will be reused within templates
      var mt8 = new go.Margin(8, 0, 0, 0);
      var mr8 = new go.Margin(0, 8, 0, 0);
      var ml8 = new go.Margin(0, 0, 0, 8);
      var roundedRectangleParams = {
        parameter1: 2,  // set the rounded corner
        spot1: go.Spot.TopLeft, spot2: go.Spot.BottomRight  // make content go all the way to inside edges of rounded corners
      };

      myDiagram =
        $(go.Diagram, "myDiagramDiv",  // the DIV HTML element
          {
            // Put the diagram contents at the top center of the viewport
            initialDocumentSpot: go.Spot.Top,
            initialViewportSpot: go.Spot.Top,
            // OR: Scroll to show a particular node, once the layout has determined where that node is
            // "InitialLayoutCompleted": function(e) {
            //  var node = e.diagram.findNodeForKey(28);
            //  if (node !== null) e.diagram.commandHandler.scrollToPart(node);
            // },
            layout:
              $(go.TreeLayout,  // use a TreeLayout to position all of the nodes
                {
//                  isOngoing: false,  // don't relayout when expanding/collapsing panels
//                  treeStyle: go.TreeLayout.StyleLastParents,
                  // properties for most of the tree:
//                  angle: 90,
                  layerSpacing: 120,
//                  // properties for the "last parents":
//                  alternateAngle: 0,
//                  alternateAlignment: go.TreeLayout.AlignmentStart,
//                  alternateNodeIndent: 15,
//                  alternateNodeIndentPastParent: 1,
//                  alternateNodeSpacing: 15,
//                  alternateLayerSpacing: 40,
//                  alternateLayerSpacingParentOverlap: 1,
//                  alternatePortSpot: new go.Spot(0.001, 1, 20, 0),
//                  alternateChildPortSpot: go.Spot.Top
                })
          });

      // This function provides a common style for most of the TextBlocks.
      // Some of these values may be overridden in a particular TextBlock.
      function textStyle(field) {
        return [
          {
			  font: "14px Roboto, sans-serif", 
			  stroke: "rgba(0, 0, 0, .80)",
			  margin: 2,
			  visible: false  // only show textblocks when there is corresponding data for them
          },
          new go.Binding("visible", field, function(val) { return val !== undefined; })
        ];
      }
		
	  function textEstado(field) {
//		  console.log("field",field)
        return [
          {
			  font: "14px Roboto, sans-serif", 
			  stroke: "rgba(0, 0, 0, .80)",
			  margin: 2,
			  visible: false  // only show textblocks when there is corresponding data for them
          },
          new go.Binding("visible", field, function(val) { /*console.log("val",val);*/ return val !== undefined;})
        ];
      }

      // define the Node template
     myDiagram.nodeTemplate =
    $(go.Node, "Auto",
        {
            locationSpot: go.Spot.Top,
            isShadowed: true, shadowBlur: 1,
            shadowOffset: new go.Point(0, 1),
            shadowColor: "rgba(0, 0, 0, .14)",
            selectionAdornmentTemplate:  // selection adornment to match shape of nodes
                $(go.Adornment, "Auto",
                    $(go.Shape, "RoundedRectangle", roundedRectangleParams,
                        { fill: null, stroke: "#7986cb", strokeWidth: 3 }
                    ),
                    $(go.Placeholder)
                )  // end Adornment
        },

        $(go.Shape, "RoundedRectangle", roundedRectangleParams,
            { name: "SHAPE", fill: "#ffffff", strokeWidth: 0 },
            // gold if highlighted, white otherwise
            new go.Binding("fill", "color")
        ),

        $(go.Panel, "Vertical",
            { margin: 8 },
            $(go.TextBlock,
                {
                    alignment: go.Spot.Left,
                    font: "bold 16px Roboto, sans-serif",
                },
                new go.Binding("text", "name")
            ),
            $("HyperlinkText", function(node) { return `${node.data.link}.php?id=${btoa(node.data.docentry)}&tl=1` },
                $(go.TextBlock,
                    {
                        alignment: go.Spot.Left,
                        font: "14px Roboto, sans-serif",
                        stroke: "blue",
		 				stretch: go.GraphObject.Fill
                    },
                    new go.Binding("text", "numero")
                ),
			  	{
		 			alignment: go.Spot.Left
	 			}
            ),
            $(go.Shape, "LineH",
                {
                    stroke: "rgba(0, 0, 0, .60)", 
		 			strokeWidth: 1,
                    height: 1, 
		 			stretch: go.GraphObject.Horizontal
                }
            ),

            $(go.TextBlock, textEstado("estado"),
                {
                    alignment: go.Spot.Left
                },
                new go.Binding("text", "estado")
            ),

            $(go.TextBlock, textStyle("fecha"),
                {
                    alignment: go.Spot.Left
                },
                new go.Binding("text", "fecha")
            ),

            $(go.TextBlock, textStyle("usuario"),
                {
                    alignment: go.Spot.Left
                },
                new go.Binding("text", "usuario")
            )

        )
    );

      // define the Link template, a simple orthogonal line
//      myDiagram.linkTemplate =
//        $(go.Link, go.Link.Orthogonal,
//          { corner: 5, selectable: false },
//          $(go.Shape, { strokeWidth: 3, stroke: "#424242" }));  // dark gray, rounded corner links
		
	  myDiagram.linkTemplate = $(
          go.Link,
		  {fromSpot: go.Spot.Right, toSpot: go.Spot.Left },
          $(go.Shape, {strokeWidth: 3, stroke: "#90b4db"},
//            new go.Binding("stroke", "color")
		   ),
          $(go.Shape, { scale: 2, fill: "#90b4db", toArrow: "Standard", stroke: null},
//            new go.Binding("fill", "color")
		   )
        );


      // set up the nodeDataArray, describing each person/position
//      var nodeDataArray = [
//		  { key: 0, name: "Llamada de servicio", estado: "Cerrada", numero: "300003966", fecha: "2021-01-07" },
//		  { key: 1, name: "Orden de venta", estado: "Cerrada", numero: "200004589", fecha: "2021-01-31" },
//		  { key: 1, name: "Orden de venta", estado: "Cerrada", numero: "200004589", fecha: "2021-01-31" },
//		  { key: 2, name: "Entrega de venta", estado: "Cerrada", numero: "400003286", fecha: "2021-02-01" },
//		  { key: 3, name: "Factura de venta", estado: "Abierta", numero: "500004251", fecha: "2021-02-02" },
//		  { key: 4, name: "Oferta de venta", estado: "Abierta", numero: "500004251", fecha: "2021-02-02" }
//      ];
		 var nodeDataArray = <?php echo $json_str;?>;
		
//	  var linkDataArray = [
//		  { from: 0, to: 1},
//          { from: 0, to: 2},
//		  { from: 0, to: 3},
//		  { from: 4, to: 1}
//        ];
		
		var linkDataArray = <?php echo $json_link;?>;

      // create the Model with data for the tree, and assign to the Diagram
      myDiagram.model =
        $(go.GraphLinksModel,
          {
            nodeDataArray,
		    linkDataArray
          });
		
//		 myDiagram.model =
//			$(go.TreeModel,
//			  {
//				nodeParentKeyProperty: "boss",  // this property refers to the parent node data
//				nodeDataArray: nodeDataArray
//			  });
		
		
    }

   
  </script>
</head>

<body onLoad="init()">

<div id="myDiagramDiv" style="background-color: #f2f2f2; width: 100%; height: 100%"></div>

</body>
</html>
<?php sqlsrv_close( $conexion );}?>